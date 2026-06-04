<?php
/**
 * Plugin Activator
 */

namespace WeRocket\Tools\Core;

class Activator {

    public static function activate(): void {
        $default_options = [
            'active_modules' => [
                'cookies' => true,
                'google_reviews' => true,
                'retractation' => true,
                'click_collect' => true,
            ],
        ];

        if (!get_option('werocket_tools_options')) {
            add_option('werocket_tools_options', $default_options);
        }

        // Pré-enregistre l'endpoint Rétractation pour qu'il fonctionne dès l'activation.
        add_rewrite_endpoint('retractation', EP_PAGES);

        flush_rewrite_rules();
    }
}
