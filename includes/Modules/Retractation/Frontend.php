<?php
/**
 * Frontend : endpoint My Account + shortcode invité + formulaire 2 étapes + handler PRG.
 *
 * Principes non négociables :
 *  - Pas de gating à la soumission (validate_order accepte large).
 *  - Motif facultatif.
 *  - Horodatage GMT côté serveur.
 *  - Aucun remboursement automatique.
 */

namespace WeRocket\Tools\Modules\Retractation;

class Frontend {

    public const ENDPOINT_QUERY_VAR = 'retractation';
    public const SHORTCODE = 'wr_retractation';
    public const NONCE_ACTION = 'wr_retractation_submit';
    public const NONCE_FIELD = '_wr_retractation_nonce';

    private Repository $repository;

    public function __construct(Repository $repository) {
        $this->repository = $repository;
    }

    public function init(): void {
        add_action('init', [$this, 'register_endpoint']);
        add_filter('woocommerce_account_menu_items', [$this, 'add_account_menu_item']);
        add_action('woocommerce_account_' . self::ENDPOINT_QUERY_VAR . '_endpoint', [$this, 'render_form']);

        add_shortcode(self::SHORTCODE, [$this, 'render_shortcode']);

        add_action('template_redirect', [$this, 'maybe_handle_submission']);
    }

    public function register_endpoint(): void {
        add_rewrite_endpoint(self::ENDPOINT_QUERY_VAR, EP_PAGES);
    }

    public function add_account_menu_item(array $items): array {
        // Insère juste avant Déconnexion si présent.
        $new = [];
        foreach ($items as $key => $label) {
            if ($key === 'customer-logout') {
                $new[self::ENDPOINT_QUERY_VAR] = __('Rétractation', 'werocket-tools');
            }
            $new[$key] = $label;
        }
        if (!isset($new[self::ENDPOINT_QUERY_VAR])) {
            $new[self::ENDPOINT_QUERY_VAR] = __('Rétractation', 'werocket-tools');
        }
        return $new;
    }

    public function render_shortcode($atts = []): string {
        ob_start();
        $this->render_form();
        return (string) ob_get_clean();
    }

    /**
     * Rendu du formulaire (étape 1 ou étape 2 selon l'état).
     */
    public function render_form(): void {
        $step          = 1;
        $order         = null;
        $error_message = '';
        $success       = false;

        $submitted_id = isset($_GET['wr_success']) ? (int) $_GET['wr_success'] : 0;
        if ($submitted_id > 0) {
            $success = true;
        }

        // Re-render étape 2 après lookup OK (POST initial) — pattern PRG aussi pour étape 1.
        $lookup_state = $this->get_lookup_state();
        if ($lookup_state) {
            $order = wc_get_order($lookup_state['order_id']);
            if ($order) {
                $step = 2;
            }
        }

        // Récupère erreur transient (étape 2 invalidée par exemple).
        if (!$success) {
            $error_message = $this->consume_error_message();
        }

        $current_user = wp_get_current_user();
        $default_email = $current_user->ID ? $current_user->user_email : '';

        $form_data = [
            'order'         => $order,
            'step'          => $step,
            'error'         => $error_message,
            'success'       => $success,
            'submitted_id'  => $submitted_id,
            'default_email' => $default_email,
            'lookup_state'  => $lookup_state,
            'submit_url'    => esc_url_raw(remove_query_arg(['wr_success'])),
            'nonce_field'   => wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD, true, false),
        ];

        $template = WEROCKET_TOOLS_PLUGIN_DIR . 'templates/modules/retractation/form.php';
        if (file_exists($template)) {
            extract($form_data, EXTR_SKIP); // phpcs:ignore WordPress.PHP.DontExtract
            include $template;
        }
    }

    public function maybe_handle_submission(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        if (!isset($_POST[self::NONCE_FIELD]) || !isset($_POST['wr_step'])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST[self::NONCE_FIELD]));
        if (!wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            return; // silently ignore — pas de feedback exploitable
        }

        $step = (int) $_POST['wr_step'];

        if ($step === 1) {
            $this->handle_step_one();
            return;
        }

        if ($step === 2) {
            $this->handle_step_two();
        }
    }

    private function handle_step_one(): void {
        $order_id = isset($_POST['wr_order_id']) ? absint(wp_unslash($_POST['wr_order_id'])) : 0;
        $email    = isset($_POST['wr_email']) ? sanitize_email(wp_unslash($_POST['wr_email'])) : '';

        if ($order_id === 0 || !is_email($email)) {
            $this->set_error_message(__('Veuillez renseigner un numéro de commande et un email valides.', 'werocket-tools'));
            $this->redirect_back();
            return;
        }

        $order = $this->validate_order($order_id, $email);
        if (!$order) {
            // Message générique anti-énumération.
            $this->set_error_message(__('Commande introuvable ou email ne correspondant pas à cette commande.', 'werocket-tools'));
            $this->redirect_back();
            return;
        }

        $this->set_lookup_state([
            'order_id' => $order_id,
            'email'    => $email,
        ]);
        $this->redirect_back();
    }

    private function handle_step_two(): void {
        $state = $this->get_lookup_state();
        if (!$state) {
            $this->set_error_message(__('Session expirée. Veuillez recommencer.', 'werocket-tools'));
            $this->redirect_back();
            return;
        }

        $order = wc_get_order($state['order_id']);
        if (!$order) {
            $this->set_error_message(__('Commande introuvable.', 'werocket-tools'));
            $this->clear_lookup_state();
            $this->redirect_back();
            return;
        }

        $selected_items_raw = isset($_POST['wr_items']) && is_array($_POST['wr_items']) ? (array) $_POST['wr_items'] : [];
        $selected_items_raw = array_map('absint', wp_unslash($selected_items_raw));

        $all_items = $order->get_items();
        $all_item_ids = array_map('intval', array_keys($all_items));

        // Filtre les items sélectionnés pour ne garder que ceux de la commande
        $selected = array_values(array_intersect($all_item_ids, $selected_items_raw));

        if (empty($selected)) {
            $this->set_error_message(__('Veuillez sélectionner au moins un article concerné par la rétractation.', 'werocket-tools'));
            $this->redirect_back();
            return;
        }

        $scope = count($selected) === count($all_item_ids) ? 'total' : 'partial';

        $items_payload = [];
        foreach ($selected as $iid) {
            if (!isset($all_items[$iid])) {
                continue;
            }
            $item = $all_items[$iid];
            $items_payload[] = [
                'order_item_id' => (int) $iid,
                'qty'           => (int) $item->get_quantity(),
                'name'          => (string) $item->get_name(),
            ];
        }

        $customer_name = isset($_POST['wr_customer_name'])
            ? sanitize_text_field(wp_unslash($_POST['wr_customer_name']))
            : trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());

        $customer_address = isset($_POST['wr_customer_address'])
            ? sanitize_textarea_field(wp_unslash($_POST['wr_customer_address']))
            : '';

        $reason = isset($_POST['wr_reason']) ? sanitize_textarea_field(wp_unslash($_POST['wr_reason'])) : '';

        $insert_id = $this->repository->insert([
            'order_id'         => (int) $order->get_id(),
            'customer_email'   => (string) $state['email'],
            'customer_name'    => $customer_name,
            'customer_address' => $customer_address,
            'scope'            => $scope,
            'items'            => $items_payload,
            'reason'           => $reason,
            'user_ip'          => $this->get_client_ip(),
            'user_agent'       => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);

        if ($insert_id <= 0) {
            $this->set_error_message(__('Une erreur est survenue à l\'enregistrement. Merci de réessayer.', 'werocket-tools'));
            $this->redirect_back();
            return;
        }

        // Note + meta sur la commande (HPOS-aware : passe par l'API WC).
        $note = sprintf(
            /* translators: %1$d demande id, %2$s portée */
            __('Demande de rétractation #%1$d reçue (portée : %2$s).', 'werocket-tools'),
            $insert_id,
            $scope === 'total' ? __('totale', 'werocket-tools') : __('partielle', 'werocket-tools')
        );
        $order->add_order_note($note);
        $order->update_meta_data('_wr_retractation_id', $insert_id);
        $order->save();

        /**
         * Action déclenchée à la réception d'une demande.
         * Hook pour Emails::send_acknowledgement_and_notify().
         */
        do_action('wr_retractation_received', $insert_id);

        $this->clear_lookup_state();
        $this->redirect_back(['wr_success' => $insert_id]);
    }

    /**
     * Vérifie l'appariement commande/email — anti-énumération.
     * Volontairement permissif sur l'éligibilité (délai, statut) : l'arbitrage
     * est manuel côté marchand. Voir Principe non négociable #1.
     */
    public function validate_order(int $order_id, string $email): ?\WC_Order {
        $order = wc_get_order($order_id);
        if (!$order instanceof \WC_Order) {
            return null;
        }
        $billing_email = strtolower((string) $order->get_billing_email());
        if ($billing_email !== strtolower($email)) {
            return null;
        }
        return $order;
    }

    private function set_lookup_state(array $state): void {
        $key = 'wr_lookup_' . $this->session_token();
        set_transient($key, $state, 30 * MINUTE_IN_SECONDS);
    }

    private function get_lookup_state(): ?array {
        $key = 'wr_lookup_' . $this->session_token();
        $val = get_transient($key);
        return is_array($val) ? $val : null;
    }

    private function clear_lookup_state(): void {
        $key = 'wr_lookup_' . $this->session_token();
        delete_transient($key);
    }

    private function set_error_message(string $message): void {
        set_transient('wr_error_' . $this->session_token(), $message, MINUTE_IN_SECONDS);
    }

    private function consume_error_message(): string {
        $key = 'wr_error_' . $this->session_token();
        $val = get_transient($key);
        if ($val) {
            delete_transient($key);
            return (string) $val;
        }
        return '';
    }

    private function session_token(): string {
        $user_id = get_current_user_id();
        if ($user_id) {
            return 'u_' . $user_id;
        }
        // Invité : on hash IP+UA. Pas parfait, mais suffisant pour différencier les soumissions.
        $ip = $this->get_client_ip();
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        return 'g_' . substr(md5($ip . '|' . $ua), 0, 16);
    }

    private function get_client_ip(): string {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        return preg_match('/^[0-9a-f.:]+$/i', $ip) ? $ip : '';
    }

    private function redirect_back(array $extra_query = []): void {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        // Strip POST params en gardant le path + query strings safe.
        $url = strtok($url, '?'); // path only
        if ($extra_query) {
            $url = add_query_arg($extra_query, $url);
        }
        wp_safe_redirect($url);
        exit;
    }
}
