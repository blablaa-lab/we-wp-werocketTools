<?php
/**
 * Abstract Module Base Class
 */

namespace WeRocket\Tools\Modules;

abstract class AbstractModule implements ModuleInterface {

    protected string $id;
    protected string $name;
    protected string $description;
    protected string $icon;
    protected string $option_key;

    /** Per-request cache to avoid repeated get_option + deep merge on the same hit. */
    private ?array $settings_cache = null;

    public function get_id(): string {
        return $this->id;
    }

    public function get_name(): string {
        return $this->name;
    }

    public function get_description(): string {
        return $this->description;
    }

    public function get_icon(): string {
        return $this->icon;
    }

    public function get_settings(): array {
        if ($this->settings_cache !== null) {
            return $this->settings_cache;
        }
        $defaults = $this->get_default_settings();
        $saved = get_option($this->option_key, []);
        if (!is_array($saved)) {
            $saved = [];
        }
        return $this->settings_cache = $this->array_merge_recursive_distinct($defaults, $saved);
    }

    /**
     * Recursively merge arrays, with $array2 values overwriting $array1
     * Unlike array_merge_recursive, this replaces values instead of creating arrays
     */
    protected function array_merge_recursive_distinct(array $array1, array $array2): array {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                // Check if it's an indexed array (like services) or associative
                if ($this->is_indexed_array($value)) {
                    // For indexed arrays, replace entirely
                    $merged[$key] = $value;
                } else {
                    // For associative arrays, merge recursively
                    $merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Check if array is indexed (sequential numeric keys starting from 0)
     */
    protected function is_indexed_array(array $array): bool {
        if (empty($array)) {
            return true;
        }
        return array_keys($array) === range(0, count($array) - 1);
    }

    public function save_settings(array $data): bool {
        // WP REST API applique wp_slash() automatiquement sur get_json_params()
        // → on retire les slashes ajoutés. La boucle nettoie également les valeurs
        // déjà polluées par les saves précédents (avant ce fix).
        $data = $this->deep_unslash($data);
        $sanitized = $this->sanitize_settings($data);
        $result = update_option($this->option_key, $sanitized);
        // Invalidate the per-request cache so the next read returns fresh data.
        $this->settings_cache = null;
        return $result;
    }

    private function deep_unslash(mixed $value): mixed {
        if (is_array($value)) {
            return array_map([$this, 'deep_unslash'], $value);
        }
        if (is_string($value)) {
            $previous = null;
            $iterations = 0;
            while ($previous !== $value && $iterations < 50) {
                $previous = $value;
                $value = stripslashes($value);
                $iterations++;
            }
        }
        return $value;
    }

    /**
     * Get default settings for the module
     */
    abstract protected function get_default_settings(): array;

    /**
     * Sanitize settings before saving
     */
    abstract protected function sanitize_settings(array $data): array;
}
