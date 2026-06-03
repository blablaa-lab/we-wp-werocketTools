<?php
/**
 * Enregistre les emails WC et dispatch :
 *  - AR client (support durable) — WC_Email subclass.
 *  - Notif marchand (simple wp_mail).
 */

namespace WeRocket\Tools\Modules\Retractation;

class Emails {

    private Repository $repository;

    public function __construct(Repository $repository) {
        $this->repository = $repository;
    }

    public function init(): void {
        add_filter('woocommerce_email_classes', [$this, 'register_wc_emails']);
        add_action('wr_retractation_received', [$this, 'on_retractation_received']);
    }

    public function register_wc_emails(array $emails): array {
        $emails['WR_Email_Acknowledgement'] = new EmailAcknowledgement();
        return $emails;
    }

    /** Hook callback : envoie AR client + notif marchand. */
    public function on_retractation_received(int $request_id): void {
        $request = $this->repository->get($request_id);
        if (!$request) {
            return;
        }

        // AR client — via le système WC_Email pour bénéficier du templating + logs.
        $mailer = WC()->mailer();
        $emails = $mailer->get_emails();
        if (isset($emails['WR_Email_Acknowledgement'])) {
            /** @var EmailAcknowledgement $email */
            $email = $emails['WR_Email_Acknowledgement'];
            $email->trigger($request);
        }

        // Notif marchand.
        $settings = get_option('werocket_retractation_settings', []);
        $notify = !empty($settings['merchant_notify']) ?? true;

        if ($notify) {
            $to = !empty($settings['merchant_email']) ? $settings['merchant_email'] : get_option('admin_email');
            $subject = sprintf(
                /* translators: %d: id de la demande */
                __('[%1$s] Nouvelle demande de rétractation #%2$d', 'werocket-tools'),
                wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
                $request_id
            );

            $admin_url = admin_url('admin.php?page=wr-retractations&view=' . $request_id);

            $lines = [
                __('Une nouvelle demande de rétractation vient d\'être enregistrée.', 'werocket-tools'),
                '',
                sprintf(__('Demande : #%d', 'werocket-tools'), (int) $request['id']),
                sprintf(__('Commande : #%d', 'werocket-tools'), (int) $request['order_id']),
                sprintf(__('Client : %s', 'werocket-tools'), $request['customer_name'] ?: $request['customer_email']),
                sprintf(__('Email : %s', 'werocket-tools'), $request['customer_email']),
                sprintf(__('Portée : %s', 'werocket-tools'), $request['scope'] === 'total' ? __('totale', 'werocket-tools') : __('partielle', 'werocket-tools')),
                sprintf(__('Reçue (UTC) : %s', 'werocket-tools'), $request['created_at_gmt']),
                '',
                __('Voir la demande :', 'werocket-tools'),
                $admin_url,
            ];

            wp_mail($to, $subject, implode("\n", $lines));
        }
    }
}
