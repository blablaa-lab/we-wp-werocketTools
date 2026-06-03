<?php
/**
 * Formulaire de rétractation (2 étapes).
 *
 * Variables disponibles (extract dans Frontend::render_form()) :
 * @var WC_Order|null $order
 * @var int          $step
 * @var string       $error
 * @var bool         $success
 * @var int          $submitted_id
 * @var string       $default_email
 * @var array|null   $lookup_state
 * @var string       $submit_url
 * @var string       $nonce_field
 */

defined('ABSPATH') || exit;
?>

<div class="wr-retractation-form" style="max-width:680px;margin:1.5rem auto;">

    <?php if (!empty($success)) : ?>
        <div class="woocommerce-message" role="alert">
            <?php
            echo wp_kses_post(sprintf(
                /* translators: %d : numéro de demande */
                __('Votre demande de rétractation <strong>#%d</strong> a bien été enregistrée. Un email de confirmation (accusé de réception) vient de vous être adressé.', 'werocket-tools'),
                (int) $submitted_id
            ));
            ?>
        </div>
        <p>
            <?php esc_html_e('Nous traitons votre demande dans les meilleurs délais. Aucun document complémentaire n\'est requis.', 'werocket-tools'); ?>
        </p>
        <?php return; ?>
    <?php endif; ?>

    <?php if (!empty($error)) : ?>
        <div class="woocommerce-error" role="alert" style="margin-bottom:1rem;">
            <?php echo esc_html($error); ?>
        </div>
    <?php endif; ?>

    <p style="color:#555;font-size:0.95em;">
        <?php esc_html_e(
            'Vous disposez du droit de vous rétracter sans motif dans les 14 jours suivant la livraison. Le présent formulaire vous permet de notifier votre rétractation. Le remboursement intervient une fois la demande validée par nos services.',
            'werocket-tools'
        ); ?>
    </p>

    <?php if ($step === 1) : ?>

        <form method="post" action="<?php echo esc_url($submit_url); ?>" class="wr-retractation-step-1">
            <?php echo $nonce_field; // déjà escaped par wp_nonce_field ?>
            <input type="hidden" name="wr_step" value="1" />

            <p class="form-row form-row-wide">
                <label for="wr_order_id"><?php esc_html_e('Numéro de commande', 'werocket-tools'); ?> <span class="required">*</span></label>
                <input
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    class="woocommerce-Input input-text"
                    name="wr_order_id"
                    id="wr_order_id"
                    required
                    autocomplete="off"
                    style="width:100%;padding:8px 12px;"
                />
            </p>

            <p class="form-row form-row-wide">
                <label for="wr_email"><?php esc_html_e('Email utilisé lors de la commande', 'werocket-tools'); ?> <span class="required">*</span></label>
                <input
                    type="email"
                    class="woocommerce-Input input-text"
                    name="wr_email"
                    id="wr_email"
                    required
                    value="<?php echo esc_attr($default_email); ?>"
                    style="width:100%;padding:8px 12px;"
                />
            </p>

            <p class="form-row">
                <button type="submit" class="woocommerce-button button" style="padding:10px 18px;">
                    <?php esc_html_e('Continuer', 'werocket-tools'); ?>
                </button>
            </p>
        </form>

    <?php elseif ($step === 2 && $order instanceof WC_Order) : ?>

        <form method="post" action="<?php echo esc_url($submit_url); ?>" class="wr-retractation-step-2">
            <?php echo $nonce_field; ?>
            <input type="hidden" name="wr_step" value="2" />

            <h3 style="margin-top:1.4em;">
                <?php
                /* translators: %s : numéro de commande */
                printf(esc_html__('Commande #%s', 'werocket-tools'), esc_html($order->get_order_number()));
                ?>
            </h3>

            <fieldset style="border:1px solid #e3e6ea;padding:1rem 1.2rem;border-radius:8px;margin-bottom:1rem;">
                <legend style="padding:0 .5em;font-weight:600;"><?php esc_html_e('Articles concernés', 'werocket-tools'); ?></legend>
                <p style="color:#555;font-size:.9em;margin:0 0 .8em;">
                    <?php esc_html_e('Tous les articles sont cochés par défaut (rétractation totale). Décochez pour une rétractation partielle.', 'werocket-tools'); ?>
                </p>
                <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($order->get_items() as $item_id => $item) : ?>
                        <li style="padding:6px 0;border-bottom:1px solid #f3f4f6;">
                            <label style="display:flex;align-items:center;gap:.6em;cursor:pointer;">
                                <input
                                    type="checkbox"
                                    name="wr_items[]"
                                    value="<?php echo esc_attr((string) $item_id); ?>"
                                    checked
                                />
                                <span>
                                    <strong><?php echo esc_html($item->get_name()); ?></strong>
                                    <span style="color:#777;">× <?php echo esc_html((string) $item->get_quantity()); ?></span>
                                </span>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </fieldset>

            <p class="form-row form-row-wide">
                <label for="wr_customer_name"><?php esc_html_e('Nom et prénom', 'werocket-tools'); ?> <span class="required">*</span></label>
                <input
                    type="text"
                    class="woocommerce-Input input-text"
                    name="wr_customer_name"
                    id="wr_customer_name"
                    required
                    value="<?php echo esc_attr(trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name())); ?>"
                    style="width:100%;padding:8px 12px;"
                />
            </p>

            <p class="form-row form-row-wide">
                <label for="wr_customer_address"><?php esc_html_e('Adresse postale', 'werocket-tools'); ?></label>
                <textarea
                    class="woocommerce-Input input-text"
                    name="wr_customer_address"
                    id="wr_customer_address"
                    rows="3"
                    style="width:100%;padding:8px 12px;"
                ><?php echo esc_textarea(trim(
                    $order->get_billing_address_1() . "\n" .
                    $order->get_billing_address_2() . "\n" .
                    trim($order->get_billing_postcode() . ' ' . $order->get_billing_city()) . "\n" .
                    $order->get_billing_country()
                )); ?></textarea>
            </p>

            <p class="form-row form-row-wide">
                <label for="wr_reason"><?php esc_html_e('Motif (facultatif)', 'werocket-tools'); ?></label>
                <textarea
                    class="woocommerce-Input input-text"
                    name="wr_reason"
                    id="wr_reason"
                    rows="3"
                    placeholder="<?php esc_attr_e('Votre motif n\'est pas requis légalement.', 'werocket-tools'); ?>"
                    style="width:100%;padding:8px 12px;"
                ></textarea>
            </p>

            <p class="form-row" style="margin-top:1.2em;">
                <button type="submit" class="woocommerce-button button" style="padding:10px 18px;background:#0F766E;color:#fff;border-color:#0F766E;">
                    <?php esc_html_e('Envoyer ma demande de rétractation', 'werocket-tools'); ?>
                </button>
            </p>

            <p style="color:#777;font-size:.85em;margin-top:.5em;">
                <?php esc_html_e('En soumettant ce formulaire, vous recevrez immédiatement un accusé de réception par email (support durable).', 'werocket-tools'); ?>
            </p>
        </form>

    <?php endif; ?>
</div>
