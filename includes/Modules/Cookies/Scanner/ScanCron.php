<?php
/**
 * Scheduled cookie scan.
 *
 * Runs once a week. Unlike the manual scanner (which uses an iframe to capture
 * JS-set cookies in a real browser), this server-side scan only sees cookies
 * the SERVER sets via Set-Cookie headers (WordPress login, WooCommerce session,
 * Cloudflare, server-side analytics, etc.). It cannot see GA / FB Pixel / Hotjar
 * style cookies set client-side.
 *
 * The point isn't full coverage — it's an unattended canary that emails the
 * admin when something new appears, prompting them to run a full manual scan.
 */

namespace WeRocket\Tools\Modules\Cookies\Scanner;

class ScanCron {

    public const HOOK            = 'werocket_cookies_weekly_scan';
    public const RECURRENCE      = 'weekly';
    public const URL_FETCH_LIMIT = 5;
    public const TIMEOUT_SECONDS = 15;

    public static function register_hooks(): void {
        add_action(self::HOOK, [self::class, 'run']);
        // 'weekly' isn't a default WP interval — add it.
        add_filter('cron_schedules', [self::class, 'add_weekly_interval']);
        self::schedule();
    }

    public static function schedule(): void {
        if (!wp_next_scheduled(self::HOOK)) {
            // Start at +1 week so we don't collide with a fresh manual scan.
            wp_schedule_event(time() + WEEK_IN_SECONDS, self::RECURRENCE, self::HOOK);
        }
    }

    public static function unschedule(): void {
        $timestamp = wp_next_scheduled(self::HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::HOOK);
        }
        wp_clear_scheduled_hook(self::HOOK);
    }

    /**
     * @internal Used as a 'cron_schedules' filter callback.
     */
    public static function add_weekly_interval(array $schedules): array {
        if (!isset($schedules['weekly'])) {
            $schedules['weekly'] = [
                'interval' => WEEK_IN_SECONDS,
                'display'  => __('Une fois par semaine', 'werocket-tools'),
            ];
        }
        return $schedules;
    }

    /**
     * Main cron entry-point. Server-side scan + diff + email.
     */
    public static function run(): void {
        $storage = new ScanStorage();

        $urls = self::resolve_urls($storage);
        if (empty($urls)) return;

        $scan = $storage->create(array_slice($urls, 0, self::URL_FETCH_LIMIT), 'cron');
        $scan_id = $scan['id'];

        try {
            foreach ($scan['urls'] as $url) {
                $findings = self::fetch_url_findings($url);
                $storage->record_url_findings(
                    $scan_id,
                    $url,
                    $findings['cookies'],
                    [], [], $findings['resources']
                );
            }

            $aggregated = self::aggregate($storage, $scan_id);
            $storage->finalize($scan_id, $aggregated);

            // Compare against the previous COMPLETED scan (manual or cron) to
            // detect newly-seen cookies and email the admin if any.
            self::maybe_notify($aggregated);
        } catch (\Throwable $e) {
            $storage->mark_failed($scan_id, $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────
    // Internals
    // ──────────────────────────────────────────────────────────

    /**
     * Use the URL list of the most recent successful manual scan if any —
     * the admin has already validated those URLs are worth scanning. Falls
     * back to the homepage.
     */
    private static function resolve_urls(ScanStorage $storage): array {
        $last = $storage->get_last_completed();
        if ($last && !empty($last['urls'])) {
            return $last['urls'];
        }
        return [home_url('/')];
    }

    /**
     * Server-side fetch: parses cookies from the response headers and
     * extracts third-party resource hosts from the HTML.
     */
    private static function fetch_url_findings(string $url): array {
        $response = wp_remote_get($url, [
            'timeout'     => self::TIMEOUT_SECONDS,
            'redirection' => 3,
            'user-agent'  => 'WeRocketTools-CookieScanner/1.0 (+' . home_url('/') . ')',
            'sslverify'   => true,
        ]);

        if (is_wp_error($response)) {
            return ['cookies' => [], 'resources' => []];
        }

        $cookies = [];
        foreach (wp_remote_retrieve_cookies($response) as $cookie) {
            // WP_Http_Cookie object: name, value, expires, path, domain
            $name = isset($cookie->name) ? (string) $cookie->name : '';
            if ($name === '') continue;
            $cookies[] = [
                'name'   => $name,
                'value'  => substr((string) ($cookie->value ?? ''), 0, 50),
                'domain' => (string) ($cookie->domain ?? ''),
            ];
        }

        $body = (string) wp_remote_retrieve_body($response);
        $resources = self::extract_resource_hosts($body);

        return ['cookies' => $cookies, 'resources' => $resources];
    }

    /**
     * Very simple HTML parser: extracts hosts from <script src>, <link href>,
     * <img src>, <iframe src>. Good enough to spot trackers loaded by the page.
     */
    private static function extract_resource_hosts(string $html): array {
        if ($html === '') return [];

        $pattern = '/(?:src|href)\s*=\s*["\']([^"\']+)["\']/i';
        if (!preg_match_all($pattern, $html, $matches)) return [];

        $home_host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
        $hosts = [];
        foreach ($matches[1] as $url) {
            $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
            if ($host === '' || $host === $home_host) continue;
            if (!isset($hosts[$host])) {
                $hosts[$host] = ['domain' => $host, 'type' => 'html'];
            }
            if (count($hosts) >= 200) break;
        }
        return array_values($hosts);
    }

    /**
     * Reduced aggregation: only what the cron path can produce (cookies +
     * resource domains, no localStorage). Reuses the catalog for classification.
     */
    private static function aggregate(ScanStorage $storage, string $scan_id): array {
        $scan = $storage->get($scan_id);
        if (!$scan) return ['summary' => [], 'cookies' => [], 'storage' => [], 'third_party_domains' => []];

        $previous = $storage->get_last_completed();
        $previous_names = [];
        if ($previous && !empty($previous['aggregated']['cookies'])) {
            foreach ($previous['aggregated']['cookies'] as $c) {
                if (!empty($c['name'])) $previous_names[$c['name']] = true;
            }
        }

        $cookies_acc = [];
        $domains_acc = [];
        $counters = ['necessary' => 0, 'analytics' => 0, 'marketing' => 0, 'preferences' => 0, 'unclassified' => 0];

        foreach ($scan['findings'] ?? [] as $url => $finding) {
            foreach ($finding['cookies'] ?? [] as $cookie) {
                $name = $cookie['name'] ?? '';
                if ($name === '') continue;

                if (!isset($cookies_acc[$name])) {
                    $match = CookieCatalog::match($name);
                    $cookies_acc[$name] = [
                        'name'           => $name,
                        'domains'        => [],
                        'value_sample'   => $cookie['value'] ?? '',
                        'first_seen_url' => $url,
                        'occurrences'    => 0,
                        'service_id'     => $match['service_id'] ?? null,
                        'service_title'  => $match['title']      ?? null,
                        'provider'       => $match['provider']   ?? null,
                        'purpose'        => $match['purpose']    ?? null,
                        'required'       => $match['required']   ?? false,
                        'classified'     => $match !== null,
                        'is_new'         => !isset($previous_names[$name]),
                        'in_settings'    => false,
                    ];
                    $counters[$cookies_acc[$name]['purpose'] ?? 'unclassified']++;
                }
                $cookies_acc[$name]['occurrences']++;
                $dom = $cookie['domain'] ?? '';
                if ($dom !== '' && !in_array($dom, $cookies_acc[$name]['domains'], true)) {
                    $cookies_acc[$name]['domains'][] = $dom;
                }
            }

            foreach ($finding['resources'] ?? [] as $res) {
                $domain = $res['domain'] ?? '';
                if ($domain === '' || isset($domains_acc[$domain])) continue;
                $match = CookieCatalog::match_domain($domain);
                $domains_acc[$domain] = [
                    'domain'         => $domain,
                    'first_seen_url' => $url,
                    'service_id'     => $match['service_id'] ?? null,
                    'service_title'  => $match['title']      ?? null,
                    'purpose'        => $match['purpose']    ?? null,
                    'classified'     => $match !== null,
                    'cookie_seen'    => false,
                ];
            }
        }

        $summary = [
            'urls_scanned'    => count($scan['urls_scanned'] ?? []),
            'urls_total'      => count($scan['urls'] ?? []),
            'cookies_total'   => count($cookies_acc),
            'cookies_new'     => count(array_filter($cookies_acc, fn($c) => !empty($c['is_new']))),
            'cookies_unknown' => count(array_filter($cookies_acc, fn($c) => empty($c['classified']))),
            'by_purpose'      => $counters,
            'services_found'  => count(array_unique(array_filter(array_column($cookies_acc, 'service_id')))),
            'third_party_domains' => count($domains_acc),
        ];

        return [
            'summary'             => $summary,
            'cookies'             => $cookies_acc,
            'storage'             => [],
            'third_party_domains' => $domains_acc,
        ];
    }

    /**
     * Send an admin email if the scan found new cookies vs the previous one.
     * Single recipient (admin_email) — no settings UI for V1.
     */
    private static function maybe_notify(array $aggregated): void {
        $new = array_values(array_filter($aggregated['cookies'] ?? [], fn($c) => !empty($c['is_new'])));
        if (empty($new)) return;

        $unknown = array_values(array_filter($aggregated['cookies'] ?? [], fn($c) => empty($c['classified'])));
        $site_name = get_bloginfo('name');
        $admin_url = admin_url('admin.php?page=werocket-tools&module=cookies');

        $subject = sprintf(
            /* translators: %s = site name */
            __('[%s] Nouveaux cookies détectés sur votre site', 'werocket-tools'),
            $site_name
        );

        $lines = [];
        $lines[] = sprintf(__('Bonjour,', 'werocket-tools'));
        $lines[] = '';
        $lines[] = sprintf(
            /* translators: %1$d = count, %2$s = site name */
            _n(
                '%1$d nouveau cookie a été détecté sur %2$s depuis le dernier scan :',
                '%1$d nouveaux cookies ont été détectés sur %2$s depuis le dernier scan :',
                count($new), 'werocket-tools'
            ),
            count($new), $site_name
        );
        $lines[] = '';

        foreach ($new as $c) {
            $service = $c['service_title'] ?? __('Service inconnu', 'werocket-tools');
            $purpose = $c['purpose'] ? sprintf(' [%s]', $c['purpose']) : '';
            $lines[] = sprintf('  • %s — %s%s', $c['name'], $service, $purpose);
        }

        $lines[] = '';
        if (!empty($unknown)) {
            $lines[] = sprintf(
                /* translators: %d = count */
                _n(
                    '%d cookie inconnu nécessite une classification manuelle.',
                    '%d cookies inconnus nécessitent une classification manuelle.',
                    count($unknown), 'werocket-tools'
                ),
                count($unknown)
            );
            $lines[] = '';
        }
        $lines[] = __('Ce scan automatique ne voit que les cookies posés par le serveur. Pour une analyse complète incluant les cookies JavaScript (Analytics, pixels publicitaires, etc.), lancez un scan manuel depuis l\'admin :', 'werocket-tools');
        $lines[] = $admin_url;
        $lines[] = '';
        $lines[] = __('— WeRocket Tools', 'werocket-tools');

        $to = get_option('admin_email');
        $body = implode("\n", $lines);

        /**
         * Filter the recipient of the weekly scan notification.
         *
         * @param string $to   Admin email by default.
         * @param array  $new  List of newly detected cookies.
         */
        $to = apply_filters('werocket_cookies_scan_email_to', $to, $new);

        wp_mail($to, $subject, $body);
    }
}
