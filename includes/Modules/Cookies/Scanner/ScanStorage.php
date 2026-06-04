<?php
/**
 * Scan history persistence.
 *
 * Stores all cookie scans in a single WordPress option. Capped at MAX_SCANS
 * with oldest-first pruning. Each scan also embeds a per-scan random token
 * used by the frontend to authenticate report uploads.
 */

namespace WeRocket\Tools\Modules\Cookies\Scanner;

class ScanStorage {

    public const OPTION_KEY = 'werocket_cookies_scan_history';
    public const MAX_SCANS  = 20;
    public const STATUS_RUNNING   = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED    = 'failed';

    /** Per-request cache. */
    private ?array $cache = null;

    public function get_all_lite(): array {
        $data = $this->load();
        $lite = [];
        foreach ($data['scans'] as $id => $scan) {
            $lite[] = [
                'id'           => $id,
                'source'       => $scan['source'] ?? 'manual',
                'started_at'   => $scan['started_at'] ?? 0,
                'completed_at' => $scan['completed_at'] ?? null,
                'status'       => $scan['status'] ?? self::STATUS_FAILED,
                'urls_count'   => count($scan['urls'] ?? []),
                'cookies_count'=> isset($scan['aggregated']['cookies']) ? count($scan['aggregated']['cookies']) : 0,
                'new_count'    => $this->count_new($scan),
            ];
        }
        // newest first
        usort($lite, fn($a, $b) => ($b['started_at'] <=> $a['started_at']));
        return $lite;
    }

    public function get(string $scan_id): ?array {
        $data = $this->load();
        return $data['scans'][$scan_id] ?? null;
    }

    public function get_last_completed(): ?array {
        $data = $this->load();
        if (empty($data['last_completed_id'])) return null;
        return $data['scans'][$data['last_completed_id']] ?? null;
    }

    /**
     * Create a new scan record and persist it.
     *
     * @param string[] $urls    Validated URLs.
     * @param string   $source  'manual' or 'cron'
     * @return array{id:string, token:string, urls:string[]}  Scan descriptor.
     */
    public function create(array $urls, string $source = 'manual'): array {
        $id    = $this->generate_id();
        $token = $this->generate_token();

        $scan = [
            'id'           => $id,
            'token'        => $token,
            'source'       => in_array($source, ['manual', 'cron'], true) ? $source : 'manual',
            'started_at'   => time(),
            'completed_at' => null,
            'status'       => self::STATUS_RUNNING,
            'urls'         => array_values($urls),
            'urls_scanned' => [],
            'findings'     => [],
            'aggregated'   => null,
        ];

        $data = $this->load();
        $data['scans'][$id] = $scan;
        $this->save($data);
        $this->prune();

        return ['id' => $id, 'token' => $token, 'urls' => $urls];
    }

    /**
     * Verify a scan_id + token combination. Returns the scan if valid.
     */
    public function verify(string $scan_id, string $token): ?array {
        $scan = $this->get($scan_id);
        if (!$scan) return null;
        if (!hash_equals((string)($scan['token'] ?? ''), $token)) return null;
        return $scan;
    }

    /**
     * Record findings for a single URL within a scan. Caps payload sizes
     * defensively (max 100 cookies / 100 storage / 200 resources per URL).
     */
    public function record_url_findings(
        string $scan_id,
        string $url,
        array $cookies,
        array $local_storage,
        array $session_storage,
        array $resources
    ): bool {
        $data = $this->load();
        if (!isset($data['scans'][$scan_id])) return false;

        $scan = &$data['scans'][$scan_id];
        if (($scan['status'] ?? '') !== self::STATUS_RUNNING) return false;

        $url = esc_url_raw($url);
        if ($url === '') return false;

        $scan['findings'][$url] = [
            'reported_at'     => time(),
            'cookies'         => array_slice($cookies, 0, 100),
            'localStorage'    => array_slice($local_storage, 0, 100),
            'sessionStorage'  => array_slice($session_storage, 0, 100),
            'resources'       => array_slice($resources, 0, 200),
        ];

        if (!in_array($url, $scan['urls_scanned'] ?? [], true)) {
            $scan['urls_scanned'][] = $url;
        }

        return $this->save($data);
    }

    /**
     * Finalize a scan: persist the aggregated result and mark as completed.
     * Also updates last_completed_id so future diffs use this as the baseline.
     */
    public function finalize(string $scan_id, array $aggregated): bool {
        $data = $this->load();
        if (!isset($data['scans'][$scan_id])) return false;

        $data['scans'][$scan_id]['status']       = self::STATUS_COMPLETED;
        $data['scans'][$scan_id]['completed_at'] = time();
        $data['scans'][$scan_id]['aggregated']   = $aggregated;
        $data['last_completed_id']               = $scan_id;

        return $this->save($data);
    }

    public function mark_failed(string $scan_id, string $reason = ''): bool {
        $data = $this->load();
        if (!isset($data['scans'][$scan_id])) return false;
        $data['scans'][$scan_id]['status']       = self::STATUS_FAILED;
        $data['scans'][$scan_id]['completed_at'] = time();
        $data['scans'][$scan_id]['error']        = substr(sanitize_text_field($reason), 0, 500);
        return $this->save($data);
    }

    public function delete(string $scan_id): bool {
        $data = $this->load();
        if (!isset($data['scans'][$scan_id])) return false;
        unset($data['scans'][$scan_id]);
        if (($data['last_completed_id'] ?? null) === $scan_id) {
            // Recompute last_completed_id
            $last = null;
            foreach ($data['scans'] as $id => $s) {
                if (($s['status'] ?? '') === self::STATUS_COMPLETED) {
                    if (!$last || ($s['completed_at'] ?? 0) > ($data['scans'][$last]['completed_at'] ?? 0)) {
                        $last = $id;
                    }
                }
            }
            $data['last_completed_id'] = $last;
        }
        return $this->save($data);
    }

    /**
     * Keep only the MAX_SCANS most recent (by started_at). Returns number pruned.
     */
    public function prune(): int {
        $data = $this->load();
        $scans = $data['scans'];
        if (count($scans) <= self::MAX_SCANS) return 0;

        uasort($scans, fn($a, $b) => ($b['started_at'] ?? 0) <=> ($a['started_at'] ?? 0));
        $keep = array_slice($scans, 0, self::MAX_SCANS, true);
        $removed = count($scans) - count($keep);

        // If last_completed_id was pruned, recompute.
        if (!isset($keep[$data['last_completed_id'] ?? ''])) {
            $last = null;
            foreach ($keep as $id => $s) {
                if (($s['status'] ?? '') === self::STATUS_COMPLETED) {
                    if (!$last || ($s['completed_at'] ?? 0) > ($keep[$last]['completed_at'] ?? 0)) {
                        $last = $id;
                    }
                }
            }
            $data['last_completed_id'] = $last;
        }

        $data['scans'] = $keep;
        $this->save($data);
        return $removed;
    }

    public function clear(): bool {
        $this->cache = ['scans' => [], 'last_completed_id' => null];
        return delete_option(self::OPTION_KEY);
    }

    // ──────────────────────────────────────────────────────────
    // Internals
    // ──────────────────────────────────────────────────────────

    private function load(): array {
        if ($this->cache !== null) return $this->cache;
        $raw = get_option(self::OPTION_KEY, []);
        if (!is_array($raw)) $raw = [];
        $this->cache = [
            'scans'             => isset($raw['scans']) && is_array($raw['scans']) ? $raw['scans'] : [],
            'last_completed_id' => $raw['last_completed_id'] ?? null,
        ];
        return $this->cache;
    }

    private function save(array $data): bool {
        $this->cache = $data;
        return update_option(self::OPTION_KEY, $data, false);
    }

    private function generate_id(): string {
        return 'scan_' . wp_generate_uuid4();
    }

    private function generate_token(): string {
        return bin2hex(random_bytes(16));
    }

    private function count_new(array $scan): int {
        if (empty($scan['aggregated']['cookies'])) return 0;
        $count = 0;
        foreach ($scan['aggregated']['cookies'] as $c) {
            if (!empty($c['is_new'])) $count++;
        }
        return $count;
    }
}
