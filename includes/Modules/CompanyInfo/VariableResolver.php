<?php
/**
 * Remplace les variables {company.x} dans un contenu HTML.
 */

namespace WeRocket\Tools\Modules\CompanyInfo;

class VariableResolver {

    private CompanyInfoModule $module;

    public function __construct(CompanyInfoModule $module) {
        $this->module = $module;
    }

    /**
     * Remplace toutes les variables {company.x} par leur valeur depuis les settings.
     */
    public function render(string $content): string {
        $settings = $this->module->get_settings();
        $values   = $this->build_values($settings);

        return preg_replace_callback(
            '/\{company\.([a-z_]+)\}/i',
            function ($matches) use ($values) {
                $key = strtolower($matches[1]);
                return $values[$key] ?? $matches[0];
            },
            $content
        ) ?? $content;
    }

    /**
     * Construit la table des valeurs (avec adresse composite et logo URL).
     * @param array<string,mixed> $s
     * @return array<string,string>
     */
    private function build_values(array $s): array {
        $address_parts = array_filter([
            (string) ($s['street']      ?? ''),
            trim(((string) ($s['postal_code'] ?? '')) . ' ' . ((string) ($s['city'] ?? ''))),
            (string) ($s['country']     ?? ''),
        ]);
        $address_full = implode(', ', array_filter($address_parts, fn($v) => trim((string) $v) !== ''));

        $logo_url = '';
        if (!empty($s['logo_id'])) {
            $url = wp_get_attachment_image_url((int) $s['logo_id'], 'full');
            if ($url) $logo_url = $url;
        }

        return [
            'name'            => (string) ($s['name'] ?? ''),
            'commercial_name' => (string) ($s['commercial_name'] ?? ''),
            'legal_form'      => (string) ($s['legal_form'] ?? ''),
            'siren'           => (string) ($s['siren'] ?? ''),
            'siret'           => (string) ($s['siret'] ?? ''),
            'capital'         => (string) ($s['capital'] ?? ''),
            'rcs'             => (string) ($s['rcs'] ?? ''),
            'vat'             => (string) ($s['vat'] ?? ''),
            'ape_code'        => (string) ($s['ape_code'] ?? ''),
            'ape_label'       => (string) ($s['ape_label'] ?? ''),
            'director'        => (string) ($s['director'] ?? ''),
            'creation_date'   => (string) ($s['creation_date'] ?? ''),
            'address'         => $address_full,
            'street'          => (string) ($s['street'] ?? ''),
            'postal_code'     => (string) ($s['postal_code'] ?? ''),
            'city'            => (string) ($s['city'] ?? ''),
            'country'         => (string) ($s['country'] ?? ''),
            'phone'           => (string) ($s['phone'] ?? ''),
            'email'           => (string) ($s['email'] ?? ''),
            'website'         => (string) ($s['website'] ?? ''),
            'logo'            => $logo_url,
        ];
    }
}
