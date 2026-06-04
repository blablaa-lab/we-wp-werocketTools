<?php
/**
 * Cookie Scanner
 *
 * Orchestrates a scan of the current site:
 *   1. Admin starts a scan with a list of URLs (start)
 *   2. The frontend iframe runner reports findings per URL (record)
 *   3. The scan is finalized and aggregated + classified (finalize)
 *   4. The admin imports detected cookies into the module's services (import)
 *
 * Security:
 *   - Same-origin URLs only (rejects any external host)
 *   - Per-scan random token validates report uploads
 *   - Cookie value samples truncated to 50 chars (no PII storage)
 */

namespace WeRocket\Tools\Modules\Cookies\Scanner;

use WeRocket\Tools\Modules\Cookies\CookiesModule;

class CookieScanner {

    public const MAX_URLS_PER_SCAN     = 20;
    public const MAX_COOKIES_PER_URL   = 100;
    public const COOKIE_VALUE_SAMPLE   = 50;
    public const COOKIE_NAME_MAX_LEN   = 200;

    private ScanStorage $storage;
    private ?CookiesModule $module;

    public function __construct(ScanStorage $storage, ?CookiesModule $module = null) {
        $this->storage = $storage;
        $this->module  = $module;
    }

    // ──────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────

    /**
     * Start a new scan. Returns a descriptor with scan_id, token and the
     * accepted URL list to visit.
     *
     * @param string[] $urls  Raw URLs from the admin. Empty = default to home.
     * @return array{id:string, token:string, urls:string[]}|\WP_Error
     */
    public function start(array $urls) {
        $accepted = $this->validate_urls($urls);

        if (empty($accepted)) {
            return new \WP_Error(
                'no_valid_urls',
                __('Aucune URL valide à scanner. Les URLs doivent appartenir à ce site.', 'werocket-tools'),
                ['status' => 400]
            );
        }

        return $this->storage->create($accepted);
    }

    /**
     * Record findings for one URL of an in-progress scan.
     */
    public function record(string $scan_id, string $token, string $url, array $payload) {
        $scan = $this->storage->verify($scan_id, $token);
        if (!$scan) {
            return new \WP_Error('invalid_scan', __('Scan invalide ou expiré.', 'werocket-tools'), ['status' => 403]);
        }

        $url = $this->normalize_url($url);
        if (!in_array($url, $scan['urls'] ?? [], true)) {
            return new \WP_Error('url_not_in_scan', __('URL non autorisée pour ce scan.', 'werocket-tools'), ['status' => 400]);
        }

        $cookies         = $this->sanitize_cookies($payload['cookies'] ?? []);
        $local_storage   = $this->sanitize_storage($payload['localStorage'] ?? []);
        $session_storage = $this->sanitize_storage($payload['sessionStorage'] ?? []);
        $resources       = $this->sanitize_resources($payload['resources'] ?? []);

        $ok = $this->storage->record_url_findings(
            $scan_id, $url, $cookies, $local_storage, $session_storage, $resources
        );

        return ['ok' => $ok];
    }

    /**
     * Finalize a scan: aggregate per-URL findings, classify via catalog,
     * diff against the previous completed scan, return the result.
     *
     * @return array{id:string, summary:array, cookies:array[], storage:array[], domains:array[]}|\WP_Error
     */
    public function finalize(string $scan_id, string $token) {
        $scan = $this->storage->verify($scan_id, $token);
        if (!$scan) {
            return new \WP_Error('invalid_scan', __('Scan invalide ou expiré.', 'werocket-tools'), ['status' => 403]);
        }

        $aggregated = $this->aggregate($scan);
        $this->storage->finalize($scan_id, $aggregated);

        return [
            'id'      => $scan_id,
            'summary' => $aggregated['summary'],
            'cookies' => array_values($aggregated['cookies']),
            'storage' => array_values($aggregated['storage']),
            'domains' => array_values($aggregated['third_party_domains']),
        ];
    }

    /**
     * Import detected services into the CookiesModule settings.
     * For each catalog service_id, ensure it exists in settings.services and
     * is enabled. Existing entries get their cookies merged (union) so we
     * don't lose admin customizations.
     *
     * @param string[] $service_ids
     * @return array{imported:string[], updated:string[], skipped:string[]}|\WP_Error
     */
    public function import_services(array $service_ids) {
        if (!$this->module) {
            return new \WP_Error('no_module', 'CookiesModule not available', ['status' => 500]);
        }

        $service_ids = array_values(array_unique(array_filter(array_map('sanitize_key', $service_ids))));
        if (empty($service_ids)) {
            return new \WP_Error('no_services', __('Aucun service à importer.', 'werocket-tools'), ['status' => 400]);
        }

        $settings = $this->module->get_settings();
        $services = $settings['services'] ?? [];
        $by_name  = [];
        foreach ($services as $i => $s) {
            $by_name[$s['name'] ?? ''] = $i;
        }

        $imported = $updated = $skipped = [];

        foreach ($service_ids as $sid) {
            $catalog_entry = CookieCatalog::to_service_settings($sid);
            if (!$catalog_entry) {
                $skipped[] = $sid;
                continue;
            }

            if (isset($by_name[$sid])) {
                // Merge into existing: union of cookies, force enabled = true,
                // keep admin's title/description/purposes if customized.
                $existing = $services[$by_name[$sid]];
                $merged_cookies = array_values(array_unique(array_merge(
                    $existing['cookies'] ?? [],
                    $catalog_entry['cookies']
                )));
                $existing['cookies'] = $merged_cookies;
                $existing['enabled'] = true;
                $services[$by_name[$sid]] = $existing;
                $updated[] = $sid;
            } else {
                $services[] = $catalog_entry;
                $by_name[$sid] = count($services) - 1;
                $imported[] = $sid;
            }
        }

        $settings['services'] = $services;
        $this->module->save_settings($settings);

        return [
            'imported' => $imported,
            'updated'  => $updated,
            'skipped'  => $skipped,
        ];
    }

    /**
     * Default URL list when admin doesn't specify any. Just the homepage
     * — admin can add more from the UI.
     */
    public function get_default_urls(): array {
        return [home_url('/')];
    }

    // ──────────────────────────────────────────────────────────
    // Aggregation + classification
    // ──────────────────────────────────────────────────────────

    private function aggregate(array $scan): array {
        $previous_cookies = [];
        $previous_scan = $this->storage->get_last_completed();
        if ($previous_scan && !empty($previous_scan['aggregated']['cookies'])) {
            foreach ($previous_scan['aggregated']['cookies'] as $c) {
                $previous_cookies[$c['name']] = true;
            }
        }

        $known_in_settings = $this->cookies_already_in_settings();

        $cookies_acc = [];          // name → aggregated entry
        $storage_acc = [];          // key  → aggregated entry
        $domains_acc = [];          // host → entry
        $counters    = ['necessary' => 0, 'analytics' => 0, 'marketing' => 0, 'preferences' => 0, 'unclassified' => 0];

        foreach ($scan['findings'] ?? [] as $url => $finding) {
            // Cookies
            foreach ($finding['cookies'] ?? [] as $cookie) {
                $name = $cookie['name'] ?? '';
                if ($name === '') continue;

                if (!isset($cookies_acc[$name])) {
                    $match = CookieCatalog::match($name);
                    $entry = [
                        'name'           => $name,
                        'domains'        => [],
                        'value_sample'   => $cookie['value_sample'] ?? '',
                        'first_seen_url' => $url,
                        'occurrences'    => 0,
                        'service_id'     => $match['service_id']  ?? null,
                        'service_title'  => $match['title']       ?? null,
                        'provider'       => $match['provider']    ?? null,
                        'purpose'        => $match['purpose']     ?? null,
                        'required'       => $match['required']    ?? false,
                        'classified'     => $match !== null,
                        'is_new'         => !isset($previous_cookies[$name]),
                        'in_settings'    => isset($known_in_settings[$name]),
                    ];
                    $cookies_acc[$name] = $entry;

                    $counters[$entry['purpose'] ?? 'unclassified']++;
                }

                $cookies_acc[$name]['occurrences']++;
                $dom = $cookie['domain'] ?? '';
                if ($dom !== '' && !in_array($dom, $cookies_acc[$name]['domains'], true)) {
                    $cookies_acc[$name]['domains'][] = $dom;
                }
            }

            // localStorage / sessionStorage
            foreach (['localStorage', 'sessionStorage'] as $kind) {
                foreach ($finding[$kind] ?? [] as $item) {
                    $key = $item['key'] ?? '';
                    if ($key === '') continue;
                    $composite = $kind . ':' . $key;

                    if (!isset($storage_acc[$composite])) {
                        $match = CookieCatalog::match_storage_key($key);
                        $storage_acc[$composite] = [
                            'kind'           => $kind,
                            'key'            => $key,
                            'value_sample'   => $item['value_sample'] ?? '',
                            'first_seen_url' => $url,
                            'service_id'     => $match['service_id'] ?? null,
                            'service_title'  => $match['title']      ?? null,
                            'purpose'        => $match['purpose']    ?? null,
                            'classified'     => $match !== null,
                        ];
                    }
                }
            }

            // Third-party resources → try to identify services that loaded
            // their scripts but haven't (yet) posted a cookie.
            foreach ($finding['resources'] ?? [] as $res) {
                $domain = $res['domain'] ?? '';
                if ($domain === '') continue;
                if (isset($domains_acc[$domain])) continue;

                $match = CookieCatalog::match_domain($domain);
                $domains_acc[$domain] = [
                    'domain'         => $domain,
                    'first_seen_url' => $url,
                    'service_id'     => $match['service_id'] ?? null,
                    'service_title'  => $match['title']      ?? null,
                    'purpose'        => $match['purpose']    ?? null,
                    'classified'     => $match !== null,
                ];
            }
        }

        // Detect services seen as third-party domains but missing from cookies:
        // useful when a tracker is loaded but hasn't fired (e.g. consent denied).
        $services_seen_via_cookies = array_filter(array_column($cookies_acc, 'service_id'));
        $services_seen_via_cookies = array_flip($services_seen_via_cookies);
        foreach ($domains_acc as &$d) {
            $d['cookie_seen'] = !empty($d['service_id']) && isset($services_seen_via_cookies[$d['service_id']]);
        }
        unset($d);

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
            'storage'             => $storage_acc,
            'third_party_domains' => $domains_acc,
        ];
    }

    private function cookies_already_in_settings(): array {
        if (!$this->module) return [];
        $settings = $this->module->get_settings();
        $known = [];
        foreach ($settings['services'] ?? [] as $service) {
            if (empty($service['enabled'])) continue;
            foreach ($service['cookies'] ?? [] as $c) {
                $known[$c] = $service['name'] ?? '';
            }
        }
        return $known;
    }

    // ──────────────────────────────────────────────────────────
    // Validation / sanitization
    // ──────────────────────────────────────────────────────────

    /**
     * Reject URLs that aren't same-origin with home_url(). Dedupe + cap.
     */
    private function validate_urls(array $urls): array {
        if (empty($urls)) {
            $urls = $this->get_default_urls();
        }

        $home = wp_parse_url(home_url('/'));
        $home_host = strtolower($home['host'] ?? '');
        if ($home_host === '') return [];

        $valid = [];
        foreach ($urls as $url) {
            if (!is_string($url) || $url === '') continue;
            $url = esc_url_raw($url);
            if ($url === '') continue;

            $parts = wp_parse_url($url);
            if (empty($parts['host']) || empty($parts['scheme'])) continue;
            if (!in_array($parts['scheme'], ['http', 'https'], true)) continue;
            if (strtolower($parts['host']) !== $home_host) continue;

            $normalized = $this->normalize_url($url);
            if (!in_array($normalized, $valid, true)) {
                $valid[] = $normalized;
            }
            if (count($valid) >= self::MAX_URLS_PER_SCAN) break;
        }
        return $valid;
    }

    private function normalize_url(string $url): string {
        $url = esc_url_raw(trim($url));
        if ($url === '') return '';
        // Strip fragments — they don't affect what loads
        $hash = strpos($url, '#');
        if ($hash !== false) $url = substr($url, 0, $hash);
        return $url;
    }

    private function sanitize_cookies(array $cookies): array {
        $clean = [];
        foreach ($cookies as $c) {
            if (!is_array($c) || empty($c['name'])) continue;
            $name = $this->sanitize_token_name((string) $c['name']);
            if ($name === '') continue;

            $clean[] = [
                'name'         => $name,
                'domain'       => $this->sanitize_host((string) ($c['domain'] ?? '')),
                'value_sample' => $this->sanitize_value_sample((string) ($c['value'] ?? $c['value_sample'] ?? '')),
            ];
            if (count($clean) >= self::MAX_COOKIES_PER_URL) break;
        }
        return $clean;
    }

    private function sanitize_storage(array $items): array {
        $clean = [];
        foreach ($items as $i) {
            if (!is_array($i) || empty($i['key'])) continue;
            $key = $this->sanitize_token_name((string) $i['key']);
            if ($key === '') continue;

            $clean[] = [
                'key'          => $key,
                'value_sample' => $this->sanitize_value_sample((string) ($i['value'] ?? $i['value_sample'] ?? '')),
            ];
            if (count($clean) >= self::MAX_COOKIES_PER_URL) break;
        }
        return $clean;
    }

    private function sanitize_resources(array $resources): array {
        $clean = [];
        $seen = [];
        foreach ($resources as $r) {
            if (!is_array($r)) continue;
            $host = $this->sanitize_host((string) ($r['domain'] ?? $r['host'] ?? ''));
            if ($host === '' || isset($seen[$host])) continue;
            $seen[$host] = true;

            $type = sanitize_key((string) ($r['type'] ?? ''));
            $clean[] = [
                'domain' => $host,
                'type'   => substr($type, 0, 30),
            ];
            if (count($clean) >= 200) break;
        }
        return $clean;
    }

    private function sanitize_token_name(string $name): string {
        // Cookies/storage keys are short ASCII tokens. Strip control chars,
        // disallow whitespace, cap length.
        $name = preg_replace('/[^\x21-\x7E]/', '', $name); // printable ASCII
        $name = trim((string) $name);
        return substr($name, 0, self::COOKIE_NAME_MAX_LEN);
    }

    private function sanitize_host(string $host): string {
        $host = strtolower(trim($host));
        if ($host === '') return '';
        // Allow leading dot for cookie domain (.example.com)
        if (!preg_match('/^\.?[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/i', $host)) {
            return '';
        }
        return $host;
    }

    private function sanitize_value_sample(string $value): string {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value);
        $value = (string) wp_strip_all_tags((string) $value);
        return substr($value, 0, self::COOKIE_VALUE_SAMPLE);
    }
}
