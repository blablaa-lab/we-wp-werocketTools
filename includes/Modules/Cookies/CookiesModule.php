<?php
/**
 * Cookies Management Module - Klaro Integration
 *
 * Provides GDPR-compliant cookie consent with Google Consent Mode v2 support
 */

namespace WeRocket\Tools\Modules\Cookies;

use WeRocket\Tools\Admin\ViteAssets;
use WeRocket\Tools\Modules\AbstractModule;

class CookiesModule extends AbstractModule {

    protected string $id = 'cookies';
    protected string $name = 'Gestion des Cookies';
    protected string $description = 'Bandeau de consentement RGPD avec Klaro et Google Consent Mode v2';
    protected string $icon = '<svg class="w-6 h-6 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    protected string $option_key = 'werocket_cookies_settings';

    public function init(): void {
        if (!is_admin()) {
            add_action('wp_head', [$this, 'render_google_consent_default'], 1);
            add_action('wp_head', [$this, 'render_klaro_config'], 2);
            add_action('wp_head', [$this, 'render_klaro_script'], 3);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        }

        add_shortcode('werocket_cookie_settings', [$this, 'render_cookie_settings_shortcode']);
        add_shortcode('werocket_manage_cookies', [$this, 'render_cookie_settings_shortcode']);
    }

    /**
     * Shortcode to display a link/button to manage cookie preferences
     * Usage: [werocket_cookie_settings] or [werocket_cookie_settings text="Gérer mes cookies" class="my-class" tag="button"]
     */
    public function render_cookie_settings_shortcode($atts): string {
        $atts = shortcode_atts([
            'text' => 'Gérer mes cookies',
            'class' => '',
            'tag' => 'a', // 'a' or 'button'
            'style' => '', // Additional inline styles
        ], $atts);

        $classes = 'werocket-cookie-settings-link';
        if (!empty($atts['class'])) {
            $classes .= ' ' . esc_attr($atts['class']);
        }

        $style = !empty($atts['style']) ? ' style="' . esc_attr($atts['style']) . '"' : '';

        if ($atts['tag'] === 'button') {
            return sprintf(
                '<button type="button" class="%s" onclick="WeRocketCookies.showSettings()"%s>%s</button>',
                $classes,
                $style,
                esc_html($atts['text'])
            );
        }

        return sprintf(
            '<a href="#" class="%s" onclick="WeRocketCookies.showSettings(); return false;"%s>%s</a>',
            $classes,
            $style,
            esc_html($atts['text'])
        );
    }

    public function render_settings(): void {
        // Rendu délégué au SPA React via #werocket-admin-root
    }

    protected function get_default_settings(): array {
        return [
            // General settings
            'cookie_name' => 'werocket_consent',
            'cookie_expires_days' => 365,
            'cookie_domain' => '',

            // Behavior
            'must_consent' => false, // Set to false to prevent Klaro auto-showing on load
            'accept_all' => true,
            'hide_decline_all' => false,
            'hide_learn_more' => false,
            'hide_toggle_all' => false,
            'default' => false,
            'required' => false,
            'opt_out' => false,
            'group_by_purpose' => true,
            'storage_method' => 'cookie', // cookie, localStorage

            // Appearance
            'theme' => 'light', // light, dark, custom
            'position' => 'bottom-left', // bottom-left, bottom-right, top-left, top-right, center
            'modal_trigger_position' => 'bottom-left',
            'notice_as_modal' => false,
            'flip_buttons' => false,
            'html_texts' => true,

            // Colors (custom theme)
            'color_primary' => '#059669',
            'color_primary_hover' => '#047857',
            'color_background' => '#ffffff',
            'color_text' => '#1f2937',
            'color_text_secondary' => '#6b7280',
            'color_border' => '#e5e7eb',
            'color_toggle_on' => '#059669',
            'color_toggle_off' => '#d1d5db',

            // Texts
            'texts' => [
                'notice_title' => 'Gestion des cookies',
                'notice_description' => 'Nous utilisons des cookies et technologies similaires pour améliorer votre expérience, analyser le trafic et personnaliser le contenu. En cliquant sur "Tout accepter", vous consentez à leur utilisation.',
                'accept_all' => 'Tout accepter',
                'decline_all' => 'Tout refuser',
                'accept_selected' => 'Accepter la sélection',
                'save' => 'Enregistrer',
                'settings' => 'Personnaliser',
                'close' => 'Fermer',
                'privacy_policy' => 'Politique de confidentialité',
                'privacy_policy_url' => '',
                'imprint' => 'Mentions légales',
                'imprint_url' => '',
                'purposes_title' => 'Finalités',
                'purpose_necessary' => 'Nécessaire',
                'purpose_analytics' => 'Statistiques',
                'purpose_marketing' => 'Marketing',
                'purpose_preferences' => 'Préférences',
                'service_desc_template' => 'Ce service peut déposer {cookies} cookies.',
            ],

            // Google Consent Mode v2
            'gcm_enabled' => true,
            'gcm_default_analytics' => 'denied',
            'gcm_default_ad_storage' => 'denied',
            'gcm_default_ad_user_data' => 'denied',
            'gcm_default_ad_personalization' => 'denied',
            'gcm_default_functionality' => 'granted',
            'gcm_default_security' => 'granted',
            'gcm_wait_for_update' => 500,
            'gcm_region' => '', // Empty = all regions, or comma-separated: FR,BE,DE

            // Services/Apps
            'services' => [
                [
                    'name' => 'google-analytics',
                    'title' => 'Google Analytics',
                    'description' => 'Service d\'analyse de trafic fourni par Google.',
                    'purposes' => ['analytics'],
                    'cookies' => ['_ga', '_gid', '_gat', '__utma', '__utmb', '__utmc', '__utmz'],
                    'required' => false,
                    'default' => false,
                    'opt_out' => false,
                    'only_once' => false,
                    'enabled' => true,
                ],
                [
                    'name' => 'google-tag-manager',
                    'title' => 'Google Tag Manager',
                    'description' => 'Gestionnaire de balises Google pour le suivi et l\'analyse.',
                    'purposes' => ['analytics', 'marketing'],
                    'cookies' => ['_gcl_au'],
                    'required' => false,
                    'default' => false,
                    'opt_out' => false,
                    'only_once' => false,
                    'enabled' => false,
                ],
                [
                    'name' => 'google-ads',
                    'title' => 'Google Ads',
                    'description' => 'Service publicitaire et de remarketing Google.',
                    'purposes' => ['marketing'],
                    'cookies' => ['_gcl_au', '_gcl_aw', '_gcl_dc'],
                    'required' => false,
                    'default' => false,
                    'opt_out' => false,
                    'only_once' => false,
                    'enabled' => false,
                ],
                [
                    'name' => 'facebook-pixel',
                    'title' => 'Facebook Pixel',
                    'description' => 'Pixel de suivi Facebook pour le remarketing.',
                    'purposes' => ['marketing'],
                    'cookies' => ['_fbp', 'fr'],
                    'required' => false,
                    'default' => false,
                    'opt_out' => false,
                    'only_once' => false,
                    'enabled' => false,
                ],
                [
                    'name' => 'hotjar',
                    'title' => 'Hotjar',
                    'description' => 'Outil d\'analyse comportementale et heatmaps.',
                    'purposes' => ['analytics'],
                    'cookies' => ['_hj*'],
                    'required' => false,
                    'default' => false,
                    'opt_out' => false,
                    'only_once' => false,
                    'enabled' => false,
                ],
                [
                    'name' => 'linkedin-insight',
                    'title' => 'LinkedIn Insight',
                    'description' => 'Suivi des conversions LinkedIn.',
                    'purposes' => ['marketing'],
                    'cookies' => ['li_sugr', 'bcookie', 'lidc'],
                    'required' => false,
                    'default' => false,
                    'opt_out' => false,
                    'only_once' => false,
                    'enabled' => false,
                ],
                [
                    'name' => 'youtube',
                    'title' => 'YouTube',
                    'description' => 'Intégration de vidéos YouTube.',
                    'purposes' => ['marketing'],
                    'cookies' => ['VISITOR_INFO1_LIVE', 'YSC'],
                    'required' => false,
                    'default' => false,
                    'opt_out' => false,
                    'only_once' => false,
                    'enabled' => false,
                ],
                [
                    'name' => 'vimeo',
                    'title' => 'Vimeo',
                    'description' => 'Intégration de vidéos Vimeo.',
                    'purposes' => ['preferences'],
                    'cookies' => ['vuid'],
                    'required' => false,
                    'default' => false,
                    'opt_out' => false,
                    'only_once' => false,
                    'enabled' => false,
                ],
            ],

            // Purposes
            'purposes' => [
                'necessary' => [
                    'title' => 'Nécessaires',
                    'description' => 'Ces cookies sont essentiels au fonctionnement du site.',
                ],
                'analytics' => [
                    'title' => 'Statistiques',
                    'description' => 'Ces cookies nous aident à comprendre comment les visiteurs interagissent avec le site.',
                ],
                'marketing' => [
                    'title' => 'Marketing',
                    'description' => 'Ces cookies sont utilisés pour le suivi publicitaire et le remarketing.',
                ],
                'preferences' => [
                    'title' => 'Préférences',
                    'description' => 'Ces cookies permettent de mémoriser vos préférences.',
                ],
            ],

            // Advanced
            'additional_class' => '',
            'custom_css' => '',
            'callback_on_accept' => '',
            'callback_on_decline' => '',
        ];
    }

    protected function sanitize_settings(array $data): array {
        $sanitized = [
            // General
            'cookie_name' => sanitize_key($data['cookie_name'] ?? 'werocket_consent'),
            'cookie_expires_days' => absint($data['cookie_expires_days'] ?? 365),
            'cookie_domain' => sanitize_text_field($data['cookie_domain'] ?? ''),

            // Behavior
            'must_consent' => !empty($data['must_consent']),
            'accept_all' => !empty($data['accept_all']),
            'hide_decline_all' => !empty($data['hide_decline_all']),
            'hide_learn_more' => !empty($data['hide_learn_more']),
            'hide_toggle_all' => !empty($data['hide_toggle_all']),
            'default' => !empty($data['default']),
            'required' => !empty($data['required']),
            'opt_out' => !empty($data['opt_out']),
            'group_by_purpose' => !empty($data['group_by_purpose']),
            'storage_method' => in_array($data['storage_method'] ?? '', ['cookie', 'localStorage']) ? $data['storage_method'] : 'cookie',

            // Appearance
            'theme' => sanitize_key($data['theme'] ?? 'light'),
            'position' => sanitize_key($data['position'] ?? 'bottom-left'),
            'modal_trigger_position' => sanitize_key($data['modal_trigger_position'] ?? 'bottom-left'),
            'notice_as_modal' => !empty($data['notice_as_modal']),
            'flip_buttons' => !empty($data['flip_buttons']),
            'html_texts' => !empty($data['html_texts']),

            // Colors
            'color_primary' => sanitize_hex_color($data['color_primary'] ?? '#059669'),
            'color_primary_hover' => sanitize_hex_color($data['color_primary_hover'] ?? '#047857'),
            'color_background' => sanitize_hex_color($data['color_background'] ?? '#ffffff'),
            'color_text' => sanitize_hex_color($data['color_text'] ?? '#1f2937'),
            'color_text_secondary' => sanitize_hex_color($data['color_text_secondary'] ?? '#6b7280'),
            'color_border' => sanitize_hex_color($data['color_border'] ?? '#e5e7eb'),
            'color_toggle_on' => sanitize_hex_color($data['color_toggle_on'] ?? '#059669'),
            'color_toggle_off' => sanitize_hex_color($data['color_toggle_off'] ?? '#d1d5db'),

            // Texts
            'texts' => $this->sanitize_texts($data['texts'] ?? []),

            // GCM
            'gcm_enabled' => !empty($data['gcm_enabled']),
            'gcm_default_analytics' => in_array($data['gcm_default_analytics'] ?? '', ['granted', 'denied']) ? $data['gcm_default_analytics'] : 'denied',
            'gcm_default_ad_storage' => in_array($data['gcm_default_ad_storage'] ?? '', ['granted', 'denied']) ? $data['gcm_default_ad_storage'] : 'denied',
            'gcm_default_ad_user_data' => in_array($data['gcm_default_ad_user_data'] ?? '', ['granted', 'denied']) ? $data['gcm_default_ad_user_data'] : 'denied',
            'gcm_default_ad_personalization' => in_array($data['gcm_default_ad_personalization'] ?? '', ['granted', 'denied']) ? $data['gcm_default_ad_personalization'] : 'denied',
            'gcm_default_functionality' => in_array($data['gcm_default_functionality'] ?? '', ['granted', 'denied']) ? $data['gcm_default_functionality'] : 'granted',
            'gcm_default_security' => in_array($data['gcm_default_security'] ?? '', ['granted', 'denied']) ? $data['gcm_default_security'] : 'granted',
            'gcm_wait_for_update' => absint($data['gcm_wait_for_update'] ?? 500),
            'gcm_region' => sanitize_text_field($data['gcm_region'] ?? ''),

            // Services
            'services' => $this->sanitize_services($data['services'] ?? []),

            // Purposes
            'purposes' => $this->sanitize_purposes($data['purposes'] ?? []),

            // Advanced
            'additional_class' => sanitize_html_class($data['additional_class'] ?? ''),
            'custom_css' => wp_strip_all_tags($data['custom_css'] ?? ''),
            'callback_on_accept' => sanitize_text_field($data['callback_on_accept'] ?? ''),
            'callback_on_decline' => sanitize_text_field($data['callback_on_decline'] ?? ''),
        ];

        return $sanitized;
    }

    private function sanitize_texts(array $texts): array {
        $defaults = $this->get_default_settings()['texts'];
        $sanitized = [];

        foreach ($defaults as $key => $default) {
            if (in_array($key, ['privacy_policy_url', 'imprint_url'])) {
                $sanitized[$key] = esc_url_raw($texts[$key] ?? $default);
            } else {
                $sanitized[$key] = wp_kses_post($texts[$key] ?? $default);
            }
        }

        return $sanitized;
    }

    private function sanitize_services(array $services): array {
        $sanitized = [];

        foreach ($services as $service) {
            if (empty($service['name'])) continue;

            $sanitized[] = [
                'name' => sanitize_key($service['name']),
                'title' => sanitize_text_field($service['title'] ?? ''),
                'description' => wp_kses_post($service['description'] ?? ''),
                'purposes' => array_map('sanitize_key', (array)($service['purposes'] ?? [])),
                'cookies' => array_map('sanitize_text_field', (array)($service['cookies'] ?? [])),
                'required' => !empty($service['required']),
                'default' => !empty($service['default']),
                'opt_out' => !empty($service['opt_out']),
                'only_once' => !empty($service['only_once']),
                'enabled' => !empty($service['enabled']),
            ];
        }

        return $sanitized;
    }

    private function sanitize_purposes(array $purposes): array {
        $sanitized = [];

        foreach ($purposes as $key => $purpose) {
            $sanitized[sanitize_key($key)] = [
                'title' => sanitize_text_field($purpose['title'] ?? ''),
                'description' => wp_kses_post($purpose['description'] ?? ''),
            ];
        }

        return $sanitized;
    }

    public function enqueue_frontend_assets(): void {
        $settings = $this->get_settings();

        wp_enqueue_style('klaro', 'https://cdn.kiprotect.com/klaro/v0.7/klaro.min.css', [], '0.7.0');
        ViteAssets::enqueue_entry('frontend/cookies/main.tsx', 'werocket-cookies');

        // Pass full settings to React banner (strip server-only callback strings)
        $config = $settings;
        unset($config['callback_on_accept'], $config['callback_on_decline']);

        add_action('wp_footer', function () use ($config): void {
            printf(
                '<div id="werocket-cookies-banner" data-config="%s"></div>',
                esc_attr(wp_json_encode($config))
            );
        }, 5);
    }

    public function render_klaro_script(): void {
        echo '<div id="werocket-klaro"></div>' . "\n";
        ?>
<style id="werocket-klaro-hide">.klaro .cookie-modal,.klaro .cookie-notice,.klaro .cookie-modal-backdrop{display:none !important}</style>
<script defer src="https://cdn.kiprotect.com/klaro/v0.7/klaro.js"></script>
<script>
(function(){
    window.WeRocketCookies = {
        showSettings: function(){ document.dispatchEvent(new CustomEvent('werocket:open-settings')); },
        showBanner:   function(){ document.dispatchEvent(new CustomEvent('werocket:show-banner')); }
    };
})();
</script>
        <?php
    }

    /**
     * Render Google Consent Mode default state
     * Must be output BEFORE any Google tags
     */
    public function render_google_consent_default(): void {
        $settings = $this->get_settings();

        if (empty($settings['gcm_enabled'])) {
            return;
        }

        $region = !empty($settings['gcm_region']) ? array_map('trim', explode(',', $settings['gcm_region'])) : null;

        ?>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}

gtag('consent', 'default', {
    'ad_storage': '<?php echo esc_js($settings['gcm_default_ad_storage']); ?>',
    'ad_user_data': '<?php echo esc_js($settings['gcm_default_ad_user_data']); ?>',
    'ad_personalization': '<?php echo esc_js($settings['gcm_default_ad_personalization']); ?>',
    'analytics_storage': '<?php echo esc_js($settings['gcm_default_analytics']); ?>',
    'functionality_storage': '<?php echo esc_js($settings['gcm_default_functionality']); ?>',
    'security_storage': '<?php echo esc_js($settings['gcm_default_security']); ?>',
    'wait_for_update': <?php echo absint($settings['gcm_wait_for_update']); ?><?php if ($region): ?>,
    'region': <?php echo wp_json_encode($region); ?><?php endif; ?>
});
</script>
        <?php
    }

    /**
     * Render Klaro configuration
     */
    public function render_klaro_config(): void {
        $settings = $this->get_settings();
        $config = $this->build_klaro_config($settings);

        ?>
<script>
var klaroConfig = <?php echo wp_json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>;

// Google Consent Mode v2 integration
<?php if (!empty($settings['gcm_enabled'])): ?>
klaroConfig.callback = function(consent, service) {
    var analyticsGranted = consent['google-analytics'] || consent['google-tag-manager'];
    var adsGranted = consent['google-ads'] || consent['google-tag-manager'];
    var marketingGranted = consent['facebook-pixel'] || consent['linkedin-insight'] || adsGranted;

    if (typeof gtag === 'function') {
        gtag('consent', 'update', {
            'analytics_storage': analyticsGranted ? 'granted' : 'denied',
            'ad_storage': adsGranted ? 'granted' : 'denied',
            'ad_user_data': marketingGranted ? 'granted' : 'denied',
            'ad_personalization': marketingGranted ? 'granted' : 'denied'
        });
    }

    // Dispatch custom event
    document.dispatchEvent(new CustomEvent('werocket_consent_update', {
        detail: { consent: consent, service: service }
    }));
};
<?php endif; ?>
</script>
        <?php
    }

    /**
     * Build Klaro configuration array
     */
    private function build_klaro_config(array $settings): array {
        $config = [
            'version' => 1,
            'elementID' => 'werocket-klaro',
            'storageMethod' => $settings['storage_method'],
            'storageName' => $settings['cookie_name'],
            'cookieExpiresAfterDays' => $settings['cookie_expires_days'],
            'cookieDomain' => $settings['cookie_domain'] ?: null,

            // Behavior
            'default' => $settings['default'],
            // Don't force mustConsent - let user control it
            // If forced to true, Klaro will auto-show its modal at page load
            'mustConsent' => !empty($settings['must_consent']),
            'acceptAll' => $settings['accept_all'],
            'hideDeclineAll' => $settings['hide_decline_all'],
            'hideLearnMore' => $settings['hide_learn_more'],
            'hideToggleAll' => $settings['hide_toggle_all'],
            'groupByPurpose' => $settings['group_by_purpose'],
            'noticeAsModal' => $settings['notice_as_modal'],
            'flipButtons' => $settings['flip_buttons'],
            'htmlTexts' => $settings['html_texts'],

            // Translations
            'translations' => [
                'fr' => $this->build_translations($settings),
            ],
            'lang' => 'fr',

            // Services
            'services' => $this->build_services_config($settings),
        ];

        // Add purposes if group by purpose is enabled
        if ($settings['group_by_purpose']) {
            $config['purposes'] = array_keys($settings['purposes']);
        }

        return $config;
    }

    /**
     * Build translations for Klaro
     */
    private function build_translations(array $settings): array {
        $texts = $settings['texts'];

        $translations = [
            'consentModal' => [
                'title' => $texts['notice_title'],
                'description' => $texts['notice_description'],
            ],
            'consentNotice' => [
                'title' => $texts['notice_title'],
                'description' => $texts['notice_description'],
                'changeDescription' => 'Des changements ont été apportés depuis votre dernière visite, veuillez mettre à jour votre consentement.',
                'learnMore' => $texts['settings'],
            ],
            'acceptAll' => $texts['accept_all'],
            'declineAll' => $texts['decline_all'],
            'acceptSelected' => $texts['accept_selected'],
            'save' => $texts['save'],
            'close' => $texts['close'],
            'ok' => 'OK',
            'service' => [
                'disableAll' => [
                    'title' => 'Tout activer/désactiver',
                    'description' => 'Utilisez ce bouton pour activer ou désactiver tous les services.',
                ],
                'optOut' => [
                    'title' => '(opt-out)',
                    'description' => 'Ce service est chargé par défaut (mais vous pouvez le désactiver)',
                ],
                'required' => [
                    'title' => '(requis)',
                    'description' => 'Ce service est requis pour le fonctionnement du site',
                ],
                'purposes' => 'Finalités',
                'purpose' => 'Finalité',
            ],
            'purposeItem' => [
                'service' => 'service',
                'services' => 'services',
            ],
            'purposes' => [],
        ];

        // Add purpose translations
        foreach ($settings['purposes'] as $key => $purpose) {
            $translations['purposes'][$key] = [
                'title' => $purpose['title'],
                'description' => $purpose['description'],
            ];
        }

        // Add privacy policy link
        if (!empty($texts['privacy_policy_url'])) {
            $translations['privacyPolicyUrl'] = $texts['privacy_policy_url'];
            $translations['privacyPolicy'] = [
                'name' => $texts['privacy_policy'],
                'url' => $texts['privacy_policy_url'],
            ];
        }

        return $translations;
    }

    /**
     * Build services configuration for Klaro
     */
    private function build_services_config(array $settings): array {
        $services = [];

        // Always add a "functional" service so Klaro displays
        $services[] = [
            'name' => 'functional',
            'title' => 'Cookies fonctionnels',
            'purposes' => ['necessary'],
            'cookies' => [],
            'required' => true,
            'default' => true,
            'optOut' => false,
            'onlyOnce' => false,
            'translations' => [
                'fr' => [
                    'description' => 'Ces cookies sont necessaires au fonctionnement du site.',
                ],
            ],
        ];

        foreach ($settings['services'] as $service) {
            if (empty($service['enabled'])) {
                continue;
            }

            $services[] = [
                'name' => $service['name'],
                'title' => $service['title'],
                'purposes' => $service['purposes'],
                'cookies' => $this->format_cookies($service['cookies'] ?? []),
                'required' => $service['required'] ?? false,
                'default' => $service['default'] ?? false,
                'optOut' => $service['opt_out'] ?? false,
                'onlyOnce' => $service['only_once'] ?? false,
                'translations' => [
                    'fr' => [
                        'description' => $service['description'] ?? '',
                    ],
                ],
            ];
        }

        return $services;
    }

    /**
     * Format cookies array for Klaro (supports regex patterns)
     */
    private function format_cookies(array $cookies): array {
        $formatted = [];

        foreach ($cookies as $cookie) {
            if (strpos($cookie, '*') !== false) {
                // Convert wildcard to regex
                $formatted[] = ['/^' . str_replace('*', '.*', preg_quote($cookie, '/')) . '$/', '/', '/'];
            } else {
                $formatted[] = $cookie;
            }
        }

        return $formatted;
    }

    /**
     * Get available purposes for settings page
     */
    public function get_available_purposes(): array {
        $settings = $this->get_settings();
        return $settings['purposes'] ?? [];
    }

    /**
     * Get enabled services for settings page
     */
    public function get_enabled_services(): array {
        $settings = $this->get_settings();
        return array_filter($settings['services'] ?? [], function($s) {
            return !empty($s['enabled']);
        });
    }
}
