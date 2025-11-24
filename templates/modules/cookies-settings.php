<?php
/**
 * Cookies Module Settings Template - Klaro Integration
 *
 * @var array $settings
 */

defined('ABSPATH') || exit;

// Ensure purposes exists with defaults
$purposes = $settings['purposes'] ?? [
    'necessary' => ['title' => 'Necessaires', 'description' => ''],
    'analytics' => ['title' => 'Statistiques', 'description' => ''],
    'marketing' => ['title' => 'Marketing', 'description' => ''],
    'preferences' => ['title' => 'Preferences', 'description' => ''],
];
$settings['purposes'] = $purposes;
$purposes_list = array_keys($purposes);
?>

<form class="werocket-module-form" data-module="cookies">
    <div class="space-y-8">

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200">
            <nav class="flex gap-4" aria-label="Settings tabs">
                <button type="button" class="settings-tab active px-4 py-2 text-sm font-medium border-b-2 border-emerald-500 text-emerald-600" data-tab="general">
                    <?php esc_html_e('Comportement', 'werocket-tools'); ?>
                </button>
                <button type="button" class="settings-tab px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="appearance">
                    <?php esc_html_e('Apparence', 'werocket-tools'); ?>
                </button>
                <button type="button" class="settings-tab px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="texts">
                    <?php esc_html_e('Textes', 'werocket-tools'); ?>
                </button>
                <button type="button" class="settings-tab px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="services">
                    <?php esc_html_e('Services', 'werocket-tools'); ?>
                </button>
                <button type="button" class="settings-tab px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="gcm">
                    <?php esc_html_e('Google Consent Mode', 'werocket-tools'); ?>
                </button>
                <button type="button" class="settings-tab px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="advanced">
                    <?php esc_html_e('Avance', 'werocket-tools'); ?>
                </button>
            </nav>
        </div>

        <!-- Tab: General / Behavior -->
        <div class="settings-panel" data-panel="general">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Cookie Settings -->
                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?php esc_html_e('Cookie de consentement', 'werocket-tools'); ?>
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Nom du cookie', 'werocket-tools'); ?></label>
                            <input type="text" name="settings[cookie_name]" value="<?php echo esc_attr($settings['cookie_name']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Duree de validite (jours)', 'werocket-tools'); ?></label>
                            <input type="number" name="settings[cookie_expires_days]" value="<?php echo esc_attr($settings['cookie_expires_days']); ?>" min="1" max="730"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Domaine (optionnel)', 'werocket-tools'); ?></label>
                            <input type="text" name="settings[cookie_domain]" value="<?php echo esc_attr($settings['cookie_domain']); ?>" placeholder=".example.com"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <p class="mt-1 text-xs text-gray-500"><?php esc_html_e('Laissez vide pour le domaine actuel', 'werocket-tools'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Methode de stockage', 'werocket-tools'); ?></label>
                            <select name="settings[storage_method]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="cookie" <?php selected($settings['storage_method'], 'cookie'); ?>>Cookie</option>
                                <option value="localStorage" <?php selected($settings['storage_method'], 'localStorage'); ?>>localStorage</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Behavior Options -->
                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <?php esc_html_e('Comportement', 'werocket-tools'); ?>
                    </h3>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-emerald-300">
                            <input type="checkbox" name="settings[must_consent]" value="1" <?php checked($settings['must_consent']); ?>
                                   class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                            <div>
                                <span class="text-sm font-medium text-gray-900"><?php esc_html_e('Consentement obligatoire', 'werocket-tools'); ?></span>
                                <p class="text-xs text-gray-500"><?php esc_html_e('Bloque la navigation avec une modale centree (ignore la position)', 'werocket-tools'); ?></p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-emerald-300">
                            <input type="checkbox" name="settings[accept_all]" value="1" <?php checked($settings['accept_all']); ?>
                                   class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                            <div>
                                <span class="text-sm font-medium text-gray-900"><?php esc_html_e('Bouton "Tout accepter"', 'werocket-tools'); ?></span>
                                <p class="text-xs text-gray-500"><?php esc_html_e('Affiche un bouton pour accepter tous les services', 'werocket-tools'); ?></p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-emerald-300">
                            <input type="checkbox" name="settings[hide_decline_all]" value="1" <?php checked($settings['hide_decline_all']); ?>
                                   class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                            <div>
                                <span class="text-sm font-medium text-gray-900"><?php esc_html_e('Masquer "Tout refuser"', 'werocket-tools'); ?></span>
                                <p class="text-xs text-gray-500"><?php esc_html_e('Cache le bouton de refus global', 'werocket-tools'); ?></p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-emerald-300">
                            <input type="checkbox" name="settings[hide_learn_more]" value="1" <?php checked($settings['hide_learn_more']); ?>
                                   class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                            <div>
                                <span class="text-sm font-medium text-gray-900"><?php esc_html_e('Masquer "Parametres"', 'werocket-tools'); ?></span>
                                <p class="text-xs text-gray-500"><?php esc_html_e('Cache le lien vers les parametres detailles', 'werocket-tools'); ?></p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-emerald-300">
                            <input type="checkbox" name="settings[group_by_purpose]" value="1" <?php checked($settings['group_by_purpose']); ?>
                                   class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                            <div>
                                <span class="text-sm font-medium text-gray-900"><?php esc_html_e('Grouper par finalite', 'werocket-tools'); ?></span>
                                <p class="text-xs text-gray-500"><?php esc_html_e('Organise les services par categorie (Analytics, Marketing, etc.)', 'werocket-tools'); ?></p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-emerald-300">
                            <input type="checkbox" name="settings[default]" value="1" <?php checked($settings['default']); ?>
                                   class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                            <div>
                                <span class="text-sm font-medium text-gray-900"><?php esc_html_e('Services actifs par defaut', 'werocket-tools'); ?></span>
                                <p class="text-xs text-gray-500"><?php esc_html_e('Les services sont coches par defaut (opt-out)', 'werocket-tools'); ?></p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Appearance -->
        <div class="settings-panel hidden" data-panel="appearance">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Layout -->
                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4"><?php esc_html_e('Disposition', 'werocket-tools'); ?></h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Theme', 'werocket-tools'); ?></label>
                            <select name="settings[theme]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="light" <?php selected($settings['theme'], 'light'); ?>><?php esc_html_e('Clair', 'werocket-tools'); ?></option>
                                <option value="dark" <?php selected($settings['theme'], 'dark'); ?>><?php esc_html_e('Sombre', 'werocket-tools'); ?></option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Position du bandeau', 'werocket-tools'); ?></label>
                            <select name="settings[position]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="bottom-left" <?php selected($settings['position'], 'bottom-left'); ?>><?php esc_html_e('Bas gauche', 'werocket-tools'); ?></option>
                                <option value="bottom-right" <?php selected($settings['position'], 'bottom-right'); ?>><?php esc_html_e('Bas droite', 'werocket-tools'); ?></option>
                                <option value="top-left" <?php selected($settings['position'], 'top-left'); ?>><?php esc_html_e('Haut gauche', 'werocket-tools'); ?></option>
                                <option value="top-right" <?php selected($settings['position'], 'top-right'); ?>><?php esc_html_e('Haut droite', 'werocket-tools'); ?></option>
                            </select>
                        </div>
                        <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-emerald-300">
                            <input type="checkbox" name="settings[notice_as_modal]" value="1" <?php checked($settings['notice_as_modal']); ?>
                                   class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                            <div>
                                <span class="text-sm font-medium text-gray-900"><?php esc_html_e('Afficher en modal', 'werocket-tools'); ?></span>
                                <p class="text-xs text-gray-500"><?php esc_html_e('Modale centree (sans "Consentement obligatoire" pour un bandeau en coin)', 'werocket-tools'); ?></p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Colors -->
                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4"><?php esc_html_e('Couleurs personnalisees', 'werocket-tools'); ?></h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Couleur principale', 'werocket-tools'); ?></label>
                            <div class="flex gap-2">
                                <input type="color" name="settings[color_primary]" value="<?php echo esc_attr($settings['color_primary']); ?>"
                                       class="h-10 w-14 px-1 py-1 border border-gray-300 rounded-md">
                                <input type="text" value="<?php echo esc_attr($settings['color_primary']); ?>" readonly
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Survol principal', 'werocket-tools'); ?></label>
                            <div class="flex gap-2">
                                <input type="color" name="settings[color_primary_hover]" value="<?php echo esc_attr($settings['color_primary_hover']); ?>"
                                       class="h-10 w-14 px-1 py-1 border border-gray-300 rounded-md">
                                <input type="text" value="<?php echo esc_attr($settings['color_primary_hover']); ?>" readonly
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Arriere-plan', 'werocket-tools'); ?></label>
                            <div class="flex gap-2">
                                <input type="color" name="settings[color_background]" value="<?php echo esc_attr($settings['color_background']); ?>"
                                       class="h-10 w-14 px-1 py-1 border border-gray-300 rounded-md">
                                <input type="text" value="<?php echo esc_attr($settings['color_background']); ?>" readonly
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Texte', 'werocket-tools'); ?></label>
                            <div class="flex gap-2">
                                <input type="color" name="settings[color_text]" value="<?php echo esc_attr($settings['color_text']); ?>"
                                       class="h-10 w-14 px-1 py-1 border border-gray-300 rounded-md">
                                <input type="text" value="<?php echo esc_attr($settings['color_text']); ?>" readonly
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Toggle actif', 'werocket-tools'); ?></label>
                            <div class="flex gap-2">
                                <input type="color" name="settings[color_toggle_on]" value="<?php echo esc_attr($settings['color_toggle_on']); ?>"
                                       class="h-10 w-14 px-1 py-1 border border-gray-300 rounded-md">
                                <input type="text" value="<?php echo esc_attr($settings['color_toggle_on']); ?>" readonly
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Toggle inactif', 'werocket-tools'); ?></label>
                            <div class="flex gap-2">
                                <input type="color" name="settings[color_toggle_off]" value="<?php echo esc_attr($settings['color_toggle_off']); ?>"
                                       class="h-10 w-14 px-1 py-1 border border-gray-300 rounded-md">
                                <input type="text" value="<?php echo esc_attr($settings['color_toggle_off']); ?>" readonly
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Texts -->
        <div class="settings-panel hidden" data-panel="texts">
            <div class="space-y-6">
                <!-- Main Texts -->
                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4"><?php esc_html_e('Textes principaux', 'werocket-tools'); ?></h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Titre du bandeau', 'werocket-tools'); ?></label>
                            <input type="text" name="settings[texts][notice_title]" value="<?php echo esc_attr($settings['texts']['notice_title']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Description', 'werocket-tools'); ?></label>
                            <textarea name="settings[texts][notice_description]" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500"><?php echo esc_textarea($settings['texts']['notice_description']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Button Texts -->
                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4"><?php esc_html_e('Textes des boutons', 'werocket-tools'); ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Tout accepter', 'werocket-tools'); ?></label>
                            <input type="text" name="settings[texts][accept_all]" value="<?php echo esc_attr($settings['texts']['accept_all']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Tout refuser', 'werocket-tools'); ?></label>
                            <input type="text" name="settings[texts][decline_all]" value="<?php echo esc_attr($settings['texts']['decline_all']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Accepter selection', 'werocket-tools'); ?></label>
                            <input type="text" name="settings[texts][accept_selected]" value="<?php echo esc_attr($settings['texts']['accept_selected']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Enregistrer', 'werocket-tools'); ?></label>
                            <input type="text" name="settings[texts][save]" value="<?php echo esc_attr($settings['texts']['save']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Parametres', 'werocket-tools'); ?></label>
                            <input type="text" name="settings[texts][settings]" value="<?php echo esc_attr($settings['texts']['settings']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Fermer', 'werocket-tools'); ?></label>
                            <input type="text" name="settings[texts][close]" value="<?php echo esc_attr($settings['texts']['close']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>
                </div>

                <!-- Links -->
                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4"><?php esc_html_e('Liens legaux', 'werocket-tools'); ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Texte politique de confidentialite', 'werocket-tools'); ?></label>
                            <input type="text" name="settings[texts][privacy_policy]" value="<?php echo esc_attr($settings['texts']['privacy_policy']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('URL politique de confidentialite', 'werocket-tools'); ?></label>
                            <input type="url" name="settings[texts][privacy_policy_url]" value="<?php echo esc_url($settings['texts']['privacy_policy_url']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Texte mentions legales', 'werocket-tools'); ?></label>
                            <input type="text" name="settings[texts][imprint]" value="<?php echo esc_attr($settings['texts']['imprint']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('URL mentions legales', 'werocket-tools'); ?></label>
                            <input type="url" name="settings[texts][imprint_url]" value="<?php echo esc_url($settings['texts']['imprint_url']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>
                </div>

                <!-- Purposes -->
                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4"><?php esc_html_e('Finalites (categories)', 'werocket-tools'); ?></h3>
                    <div class="space-y-4">
                        <?php foreach ($settings['purposes'] as $purpose_key => $purpose): ?>
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        <?php echo esc_html($purpose_key); ?>
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Titre', 'werocket-tools'); ?></label>
                                        <input type="text" name="settings[purposes][<?php echo esc_attr($purpose_key); ?>][title]"
                                               value="<?php echo esc_attr($purpose['title']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Description', 'werocket-tools'); ?></label>
                                        <input type="text" name="settings[purposes][<?php echo esc_attr($purpose_key); ?>][description]"
                                               value="<?php echo esc_attr($purpose['description']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Services -->
        <div class="settings-panel hidden" data-panel="services">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600"><?php esc_html_e('Activez et configurez les services tiers que vous utilisez sur votre site.', 'werocket-tools'); ?></p>
                </div>

                <div class="grid grid-cols-1 gap-4" id="services-list">
                    <?php foreach ($settings['services'] as $index => $service): ?>
                        <div class="service-item bg-gray-50 rounded-lg p-5 border-2 <?php echo $service['enabled'] ? 'border-emerald-200' : 'border-transparent'; ?>">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="settings[services][<?php echo $index; ?>][enabled]" value="1"
                                               <?php checked($service['enabled']); ?>
                                               class="sr-only peer service-toggle">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                    </label>
                                    <div>
                                        <h4 class="text-base font-semibold text-gray-900"><?php echo esc_html($service['title']); ?></h4>
                                        <p class="text-sm text-gray-500"><?php echo esc_html($service['description']); ?></p>
                                    </div>
                                </div>
                                <button type="button" class="toggle-service-details text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>

                            <input type="hidden" name="settings[services][<?php echo $index; ?>][name]" value="<?php echo esc_attr($service['name']); ?>">

                            <div class="service-details hidden mt-4 pt-4 border-t border-gray-200 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Titre affiche', 'werocket-tools'); ?></label>
                                        <input type="text" name="settings[services][<?php echo $index; ?>][title]" value="<?php echo esc_attr($service['title']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Finalites', 'werocket-tools'); ?></label>
                                        <select name="settings[services][<?php echo $index; ?>][purposes][]" multiple
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                            <?php foreach ($purposes_list as $purpose): ?>
                                                <option value="<?php echo esc_attr($purpose); ?>" <?php selected(in_array($purpose, $service['purposes'])); ?>>
                                                    <?php echo esc_html(ucfirst($purpose)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Description', 'werocket-tools'); ?></label>
                                    <textarea name="settings[services][<?php echo $index; ?>][description]" rows="2"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500"><?php echo esc_textarea($service['description']); ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Cookies (separes par virgules)', 'werocket-tools'); ?></label>
                                    <input type="text" name="settings[services][<?php echo $index; ?>][cookies]" value="<?php echo esc_attr(implode(', ', $service['cookies'])); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
                                           placeholder="_ga, _gid, _gat">
                                    <p class="mt-1 text-xs text-gray-500"><?php esc_html_e('Utilisez * comme joker (ex: _hj* pour tous les cookies Hotjar)', 'werocket-tools'); ?></p>
                                </div>
                                <div class="flex flex-wrap gap-4">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="settings[services][<?php echo $index; ?>][required]" value="1" <?php checked($service['required']); ?>
                                               class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                                        <span class="text-sm text-gray-700"><?php esc_html_e('Requis', 'werocket-tools'); ?></span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="settings[services][<?php echo $index; ?>][default]" value="1" <?php checked($service['default']); ?>
                                               class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                                        <span class="text-sm text-gray-700"><?php esc_html_e('Actif par defaut', 'werocket-tools'); ?></span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="settings[services][<?php echo $index; ?>][opt_out]" value="1" <?php checked($service['opt_out']); ?>
                                               class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                                        <span class="text-sm text-gray-700"><?php esc_html_e('Opt-out', 'werocket-tools'); ?></span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="settings[services][<?php echo $index; ?>][only_once]" value="1" <?php checked($service['only_once']); ?>
                                               class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                                        <span class="text-sm text-gray-700"><?php esc_html_e('Executer une seule fois', 'werocket-tools'); ?></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tab: Google Consent Mode -->
        <div class="settings-panel hidden" data-panel="gcm">
            <div class="space-y-6">
                <!-- GCM Toggle -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-5 border border-blue-200">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-10 h-10 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Google Consent Mode v2</h3>
                            <p class="text-sm text-gray-600 mb-4"><?php esc_html_e('Integrez automatiquement le mode de consentement Google pour une conformite complete avec les reglementations sur la vie privee.', 'werocket-tools'); ?></p>
                            <label class="flex items-center gap-3">
                                <input type="checkbox" name="settings[gcm_enabled]" value="1" <?php checked($settings['gcm_enabled']); ?>
                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="text-sm font-medium text-gray-900"><?php esc_html_e('Activer Google Consent Mode v2', 'werocket-tools'); ?></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Default Consent States -->
                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4"><?php esc_html_e('Etats de consentement par defaut', 'werocket-tools'); ?></h3>
                    <p class="text-sm text-gray-600 mb-4"><?php esc_html_e('Ces valeurs sont appliquees AVANT que l\'utilisateur ne fasse son choix. Pour la conformite RGPD, laissez tout sur "Refuse".', 'werocket-tools'); ?></p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">analytics_storage</label>
                            <select name="settings[gcm_default_analytics]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="denied" <?php selected($settings['gcm_default_analytics'], 'denied'); ?>><?php esc_html_e('Refuse', 'werocket-tools'); ?></option>
                                <option value="granted" <?php selected($settings['gcm_default_analytics'], 'granted'); ?>><?php esc_html_e('Accorde', 'werocket-tools'); ?></option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500"><?php esc_html_e('Cookies analytiques (GA, etc.)', 'werocket-tools'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ad_storage</label>
                            <select name="settings[gcm_default_ad_storage]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="denied" <?php selected($settings['gcm_default_ad_storage'], 'denied'); ?>><?php esc_html_e('Refuse', 'werocket-tools'); ?></option>
                                <option value="granted" <?php selected($settings['gcm_default_ad_storage'], 'granted'); ?>><?php esc_html_e('Accorde', 'werocket-tools'); ?></option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500"><?php esc_html_e('Cookies publicitaires', 'werocket-tools'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ad_user_data</label>
                            <select name="settings[gcm_default_ad_user_data]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="denied" <?php selected($settings['gcm_default_ad_user_data'], 'denied'); ?>><?php esc_html_e('Refuse', 'werocket-tools'); ?></option>
                                <option value="granted" <?php selected($settings['gcm_default_ad_user_data'], 'granted'); ?>><?php esc_html_e('Accorde', 'werocket-tools'); ?></option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500"><?php esc_html_e('Donnees utilisateur pour la pub', 'werocket-tools'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ad_personalization</label>
                            <select name="settings[gcm_default_ad_personalization]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="denied" <?php selected($settings['gcm_default_ad_personalization'], 'denied'); ?>><?php esc_html_e('Refuse', 'werocket-tools'); ?></option>
                                <option value="granted" <?php selected($settings['gcm_default_ad_personalization'], 'granted'); ?>><?php esc_html_e('Accorde', 'werocket-tools'); ?></option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500"><?php esc_html_e('Personnalisation des annonces', 'werocket-tools'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">functionality_storage</label>
                            <select name="settings[gcm_default_functionality]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="granted" <?php selected($settings['gcm_default_functionality'], 'granted'); ?>><?php esc_html_e('Accorde', 'werocket-tools'); ?></option>
                                <option value="denied" <?php selected($settings['gcm_default_functionality'], 'denied'); ?>><?php esc_html_e('Refuse', 'werocket-tools'); ?></option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500"><?php esc_html_e('Fonctionnalites du site', 'werocket-tools'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">security_storage</label>
                            <select name="settings[gcm_default_security]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="granted" <?php selected($settings['gcm_default_security'], 'granted'); ?>><?php esc_html_e('Accorde', 'werocket-tools'); ?></option>
                                <option value="denied" <?php selected($settings['gcm_default_security'], 'denied'); ?>><?php esc_html_e('Refuse', 'werocket-tools'); ?></option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500"><?php esc_html_e('Securite et anti-fraude', 'werocket-tools'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Advanced GCM Settings -->
                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4"><?php esc_html_e('Parametres avances', 'werocket-tools'); ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Delai d\'attente (ms)', 'werocket-tools'); ?></label>
                            <input type="number" name="settings[gcm_wait_for_update]" value="<?php echo esc_attr($settings['gcm_wait_for_update']); ?>" min="0" max="2000"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <p class="mt-1 text-xs text-gray-500"><?php esc_html_e('Temps d\'attente avant de charger les scripts Google (recommande: 500)', 'werocket-tools'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Regions (optionnel)', 'werocket-tools'); ?></label>
                            <input type="text" name="settings[gcm_region]" value="<?php echo esc_attr($settings['gcm_region']); ?>" placeholder="FR, BE, DE, ES"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <p class="mt-1 text-xs text-gray-500"><?php esc_html_e('Codes pays separes par virgules. Vide = toutes les regions.', 'werocket-tools'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Advanced -->
        <div class="settings-panel hidden" data-panel="advanced">
            <div class="space-y-6">
                <!-- Shortcode & Integration Documentation -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-emerald-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        <?php esc_html_e('Integration et shortcodes', 'werocket-tools'); ?>
                    </h3>
                    <p class="text-sm text-emerald-800 mb-4"><?php esc_html_e('Utilisez ces methodes pour permettre aux utilisateurs de modifier leurs preferences de cookies apres avoir fait leur choix initial.', 'werocket-tools'); ?></p>

                    <div class="space-y-4">
                        <!-- Shortcode -->
                        <div class="bg-white rounded-lg p-4 border border-emerald-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-2"><?php esc_html_e('Shortcode WordPress', 'werocket-tools'); ?></h4>
                            <p class="text-xs text-gray-600 mb-2"><?php esc_html_e('Ajoutez ce shortcode dans une page, un article ou un widget pour afficher un lien de gestion des cookies :', 'werocket-tools'); ?></p>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm text-gray-800 font-mono">[werocket_cookie_settings]</code>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm text-gray-800 font-mono">[werocket_manage_cookies]</code>
                            </div>
                            <p class="text-xs text-gray-600 mb-2"><?php esc_html_e('Options disponibles :', 'werocket-tools'); ?></p>
                            <ul class="text-xs text-gray-600 list-disc list-inside space-y-1">
                                <li><code class="bg-gray-100 px-1 rounded">text="Gérer mes cookies"</code> - <?php esc_html_e('Texte du lien', 'werocket-tools'); ?></li>
                                <li><code class="bg-gray-100 px-1 rounded">tag="button"</code> - <?php esc_html_e('Utiliser un bouton au lieu d\'un lien (a ou button)', 'werocket-tools'); ?></li>
                                <li><code class="bg-gray-100 px-1 rounded">class="ma-classe"</code> - <?php esc_html_e('Classes CSS supplementaires', 'werocket-tools'); ?></li>
                                <li><code class="bg-gray-100 px-1 rounded">style="color:red"</code> - <?php esc_html_e('Styles inline', 'werocket-tools'); ?></li>
                            </ul>
                            <p class="text-xs text-gray-500 mt-2 italic"><?php esc_html_e('Exemple complet:', 'werocket-tools'); ?> <code class="bg-gray-100 px-1 rounded">[werocket_cookie_settings text="Parametres cookies" tag="button" class="btn"]</code></p>
                        </div>

                        <!-- Menu Link -->
                        <div class="bg-white rounded-lg p-4 border border-emerald-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-2"><?php esc_html_e('Lien dans un menu WordPress', 'werocket-tools'); ?></h4>
                            <p class="text-xs text-gray-600 mb-2"><?php esc_html_e('Pour ajouter un lien dans votre menu de navigation :', 'werocket-tools'); ?></p>
                            <ol class="text-xs text-gray-600 list-decimal list-inside space-y-1 mb-2">
                                <li><?php esc_html_e('Allez dans Apparence > Menus', 'werocket-tools'); ?></li>
                                <li><?php esc_html_e('Ajoutez un "Lien personnalise"', 'werocket-tools'); ?></li>
                                <li><?php esc_html_e('Utilisez l\'un de ces liens comme URL :', 'werocket-tools'); ?></li>
                            </ol>
                            <div class="flex flex-wrap gap-2">
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm text-gray-800 font-mono">#manage-cookies</code>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm text-gray-800 font-mono">#gerer-cookies</code>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm text-gray-800 font-mono">#cookie-settings</code>
                            </div>
                        </div>

                        <!-- HTML/CSS Class -->
                        <div class="bg-white rounded-lg p-4 border border-emerald-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-2"><?php esc_html_e('Integration HTML directe', 'werocket-tools'); ?></h4>
                            <p class="text-xs text-gray-600 mb-2"><?php esc_html_e('Vous pouvez aussi utiliser ces methodes en HTML :', 'werocket-tools'); ?></p>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-xs text-gray-500"><?php esc_html_e('Classe CSS :', 'werocket-tools'); ?></span>
                                    <code class="bg-gray-100 px-2 py-1 rounded text-xs text-gray-800 font-mono block mt-1">&lt;a href="#" class="werocket-cookie-settings-link"&gt;Gerer mes cookies&lt;/a&gt;</code>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500"><?php esc_html_e('Attribut data :', 'werocket-tools'); ?></span>
                                    <code class="bg-gray-100 px-2 py-1 rounded text-xs text-gray-800 font-mono block mt-1">&lt;button data-werocket-consent-manage&gt;Parametres cookies&lt;/button&gt;</code>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500"><?php esc_html_e('JavaScript :', 'werocket-tools'); ?></span>
                                    <code class="bg-gray-100 px-2 py-1 rounded text-xs text-gray-800 font-mono block mt-1">WeRocketCookies.showSettings();</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800"><?php esc_html_e('Zone avancee', 'werocket-tools'); ?></h3>
                            <p class="mt-1 text-sm text-yellow-700"><?php esc_html_e('Ces options sont destinees aux utilisateurs avances. Modifiez-les avec precaution.', 'werocket-tools'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4"><?php esc_html_e('CSS personnalise', 'werocket-tools'); ?></h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Styles CSS additionnels', 'werocket-tools'); ?></label>
                        <textarea name="settings[custom_css]" rows="8"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 font-mono text-sm"
                                  placeholder="/* Vos styles personnalises ici */
.klaro .cookie-notice {
    /* ... */
}"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4"><?php esc_html_e('Classe CSS additionnelle', 'werocket-tools'); ?></h3>
                    <div>
                        <input type="text" name="settings[additional_class]" value="<?php echo esc_attr($settings['additional_class']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="ma-classe-custom">
                        <p class="mt-1 text-xs text-gray-500"><?php esc_html_e('Classe CSS ajoutee au conteneur Klaro', 'werocket-tools'); ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-6 flex justify-end">
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <?php esc_html_e('Enregistrer', 'werocket-tools'); ?>
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab navigation
    const tabs = document.querySelectorAll('.settings-tab');
    const panels = document.querySelectorAll('.settings-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetPanel = tab.dataset.tab;

            tabs.forEach(t => {
                t.classList.remove('active', 'border-emerald-500', 'text-emerald-600');
                t.classList.add('border-transparent', 'text-gray-500');
            });

            tab.classList.add('active', 'border-emerald-500', 'text-emerald-600');
            tab.classList.remove('border-transparent', 'text-gray-500');

            panels.forEach(panel => {
                panel.classList.toggle('hidden', panel.dataset.panel !== targetPanel);
            });
        });
    });

    // Service toggle details
    document.querySelectorAll('.toggle-service-details').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.service-item');
            const details = item.querySelector('.service-details');
            const icon = btn.querySelector('svg');

            details.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        });
    });

    // Service enable/disable visual feedback
    document.querySelectorAll('.service-toggle').forEach(toggle => {
        toggle.addEventListener('change', () => {
            const item = toggle.closest('.service-item');
            item.classList.toggle('border-emerald-200', toggle.checked);
            item.classList.toggle('border-transparent', !toggle.checked);
        });
    });

    // Color picker sync
    document.querySelectorAll('input[type="color"]').forEach(colorInput => {
        const textInput = colorInput.nextElementSibling;
        if (textInput && textInput.type === 'text') {
            colorInput.addEventListener('input', () => {
                textInput.value = colorInput.value;
            });
        }
    });

    // Convert cookies string to array on form submit
    document.querySelector('.werocket-module-form').addEventListener('submit', function(e) {
        document.querySelectorAll('input[name*="[cookies]"]').forEach(input => {
            if (input.type === 'text') {
                const cookies = input.value.split(',').map(c => c.trim()).filter(c => c);
                // Create hidden inputs for array
                const name = input.name;
                input.name = '';
                cookies.forEach((cookie, i) => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = name.replace('[cookies]', `[cookies][${i}]`);
                    hidden.value = cookie;
                    input.parentNode.appendChild(hidden);
                });
            }
        });
    });
});
</script>
