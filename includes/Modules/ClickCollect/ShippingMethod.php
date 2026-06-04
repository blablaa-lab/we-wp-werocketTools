<?php
/**
 * Méthode d'expédition WooCommerce — Clic & Collect.
 *
 * Crée une méthode "Clic & Collect" disponible dans les zones d'expédition.
 * Le coût et le titre proviennent des settings du module.
 */

namespace WeRocket\Tools\Modules\ClickCollect;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WC_Shipping_Method')) {
    return;
}

class ShippingMethod extends \WC_Shipping_Method {

    public function __construct($instance_id = 0) {
        $this->id                 = ClickCollectModule::SHIPPING_METHOD_ID;
        $this->instance_id        = absint($instance_id);
        $this->method_title       = __('Clic & Collect', 'werocket-tools');
        $this->method_description = __('Permet à vos clients de retirer leur commande dans un lieu paramétré.', 'werocket-tools');
        $this->supports           = ['shipping-zones', 'instance-settings', 'instance-settings-modal'];

        $settings = get_option('werocket_click_collect_settings', []);
        $this->title    = (string) ($settings['method_title'] ?? __('Clic & Collect', 'werocket-tools'));
        $this->tax_status = (string) ($settings['tax_status'] ?? 'none');

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = 'yes';
        $this->title = $this->get_option('title', $this->title);

        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields(): void {
        $settings = get_option('werocket_click_collect_settings', []);
        $this->instance_form_fields = [
            'title' => [
                'title'       => __('Titre affiché', 'werocket-tools'),
                'type'        => 'text',
                'description' => __('Nom de la méthode affiché au client.', 'werocket-tools'),
                'default'     => (string) ($settings['method_title'] ?? __('Clic & Collect', 'werocket-tools')),
                'desc_tip'    => true,
            ],
            'cost' => [
                'title'       => __('Coût additionnel', 'werocket-tools'),
                'type'        => 'price',
                'description' => __('Surcoût appliqué en plus de la configuration globale du module (laisser 0 pour gratuit).', 'werocket-tools'),
                'default'     => '0',
                'desc_tip'    => true,
            ],
        ];
    }

    public function calculate_shipping($package = []): void {
        $settings  = get_option('werocket_click_collect_settings', []);
        $base_cost = (float) ($settings['cost'] ?? 0);
        $extra     = (float) $this->get_option('cost', 0);

        $rate = [
            'id'      => $this->get_rate_id(),
            'label'   => $this->title,
            'cost'    => $base_cost + $extra,
            'package' => $package,
        ];
        $this->add_rate($rate);
    }
}
