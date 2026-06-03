<?php
/**
 * Email d'accusé de réception HTML.
 *
 * @var WC_Email $email
 * @var array    $request
 * @var string   $email_heading
 */
defined('ABSPATH') || exit;

do_action('woocommerce_email_header', $email_heading, $email);

$items = !empty($request['items']) ? (json_decode((string) $request['items'], true) ?: []) : [];
$scope_label = ($request['scope'] === 'total') ? __('totale', 'werocket-tools') : __('partielle', 'werocket-tools');
?>

<p><?php
printf(
    /* translators: %s : nom client */
    esc_html__('Bonjour %s,', 'werocket-tools'),
    esc_html((string) ($request['customer_name'] ?: $request['customer_email']))
);
?></p>

<p><?php esc_html_e('Nous accusons réception de votre demande de rétractation. Cet email tient lieu de support durable conformément à l\'article L221-21 du Code de la consommation.', 'werocket-tools'); ?></p>

<table class="td" cellspacing="0" cellpadding="6" border="0" style="width:100%;font-family:Helvetica,Arial,sans-serif;">
    <tr>
        <th align="left"><?php esc_html_e('Numéro de demande', 'werocket-tools'); ?></th>
        <td>#<?php echo esc_html((string) $request['id']); ?></td>
    </tr>
    <tr>
        <th align="left"><?php esc_html_e('Commande concernée', 'werocket-tools'); ?></th>
        <td>#<?php echo esc_html((string) $request['order_id']); ?></td>
    </tr>
    <tr>
        <th align="left"><?php esc_html_e('Portée', 'werocket-tools'); ?></th>
        <td><?php echo esc_html(ucfirst($scope_label)); ?></td>
    </tr>
    <tr>
        <th align="left"><?php esc_html_e('Date de réception (UTC)', 'werocket-tools'); ?></th>
        <td><?php echo esc_html((string) $request['created_at_gmt']); ?></td>
    </tr>
</table>

<?php if (!empty($items)) : ?>
    <h3><?php esc_html_e('Articles concernés', 'werocket-tools'); ?></h3>
    <ul>
        <?php foreach ($items as $it) : ?>
            <li>
                <?php echo esc_html((string) ($it['name'] ?? '')); ?>
                <?php if (!empty($it['qty'])) : ?>
                    × <?php echo esc_html((string) $it['qty']); ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (!empty($request['reason'])) : ?>
    <h3><?php esc_html_e('Motif (facultatif)', 'werocket-tools'); ?></h3>
    <p><?php echo nl2br(esc_html((string) $request['reason'])); ?></p>
<?php endif; ?>

<p>
    <?php esc_html_e('Nous reviendrons vers vous prochainement avec les modalités de retour et de remboursement. Vous pouvez répondre directement à cet email pour toute question.', 'werocket-tools'); ?>
</p>

<p><?php esc_html_e('Conservez cet email — il constitue la preuve de votre demande.', 'werocket-tools'); ?></p>

<?php
do_action('woocommerce_email_footer', $email);
