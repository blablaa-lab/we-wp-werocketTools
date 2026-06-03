<?php
/**
 * Email d'accusé de réception — version texte brut.
 *
 * @var WC_Email $email
 * @var array    $request
 * @var string   $email_heading
 */
defined('ABSPATH') || exit;

$items = !empty($request['items']) ? (json_decode((string) $request['items'], true) ?: []) : [];
$scope_label = ($request['scope'] === 'total') ? __('totale', 'werocket-tools') : __('partielle', 'werocket-tools');

echo esc_html($email_heading) . "\n";
echo str_repeat('=', mb_strlen($email_heading)) . "\n\n";

printf(
    /* translators: %s : nom */
    esc_html__('Bonjour %s,', 'werocket-tools') . "\n\n",
    (string) ($request['customer_name'] ?: $request['customer_email'])
);

esc_html_e('Nous accusons réception de votre demande de rétractation. Cet email tient lieu de support durable conformément à l\'article L221-21 du Code de la consommation.', 'werocket-tools');
echo "\n\n";

echo esc_html__('Numéro de demande', 'werocket-tools') . ' : #' . (int) $request['id'] . "\n";
echo esc_html__('Commande concernée', 'werocket-tools') . ' : #' . (int) $request['order_id'] . "\n";
echo esc_html__('Portée', 'werocket-tools') . ' : ' . esc_html(ucfirst($scope_label)) . "\n";
echo esc_html__('Date de réception (UTC)', 'werocket-tools') . ' : ' . esc_html((string) $request['created_at_gmt']) . "\n";

if (!empty($items)) {
    echo "\n" . esc_html__('Articles concernés', 'werocket-tools') . " :\n";
    foreach ($items as $it) {
        $line = '- ' . (string) ($it['name'] ?? '');
        if (!empty($it['qty'])) {
            $line .= ' x ' . (int) $it['qty'];
        }
        echo $line . "\n";
    }
}

if (!empty($request['reason'])) {
    echo "\n" . esc_html__('Motif (facultatif)', 'werocket-tools') . " :\n";
    echo (string) $request['reason'] . "\n";
}

echo "\n";
esc_html_e('Nous reviendrons vers vous prochainement avec les modalités de retour et de remboursement.', 'werocket-tools');
echo "\n\n";
esc_html_e('Conservez cet email — il constitue la preuve de votre demande.', 'werocket-tools');
echo "\n";
