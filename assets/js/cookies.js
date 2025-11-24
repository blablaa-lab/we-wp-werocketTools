/**
 * WeRocket Tools - Klaro Cookie Consent Integration
 *
 * This file provides helper functions for interacting with Klaro consent.
 * The main Klaro library is loaded from CDN, and configuration is set via PHP.
 */

(function() {
    'use strict';

    /**
     * WeRocket Cookies API
     * Provides utility functions for checking and managing consent
     */
    window.WeRocketCookies = {

        /**
         * Check if a specific service has consent
         * @param {string} serviceName - The service name (e.g., 'google-analytics')
         * @returns {boolean}
         */
        hasConsent: function(serviceName) {
            if (typeof klaro === 'undefined' || typeof klaro.getManager !== 'function') {
                return false;
            }

            var manager = klaro.getManager();
            if (!manager) return false;

            var consents = manager.consents;
            return consents && consents[serviceName] === true;
        },

        /**
         * Get all current consents
         * @returns {object|null}
         */
        getConsents: function() {
            if (typeof klaro === 'undefined' || typeof klaro.getManager !== 'function') {
                return null;
            }

            var manager = klaro.getManager();
            return manager ? manager.consents : null;
        },

        /**
         * Open the consent settings modal
         * This is the main method to open cookie preferences
         */
        showSettings: function() {
            // Remove any existing custom notice
            var existingNotice = document.getElementById('werocket-cookie-notice');
            if (existingNotice) {
                existingNotice.remove();
            }

            // Show Klaro modal
            if (typeof klaro !== 'undefined' && typeof klaro.show === 'function') {
                klaro.show();
            } else {
                console.warn('WeRocket Cookies: Klaro not loaded');
            }
        },

        /**
         * Alias for showSettings
         */
        showModal: function() {
            this.showSettings();
        },

        /**
         * Reset all consents and show the modal again
         */
        resetConsents: function() {
            if (typeof klaro !== 'undefined' && typeof klaro.getManager === 'function') {
                var manager = klaro.getManager();
                if (manager) {
                    manager.resetConsents();
                    this.showSettings();
                }
            }
        },

        /**
         * Accept all cookies
         */
        acceptAll: function() {
            if (typeof klaro !== 'undefined' && typeof klaro.getManager === 'function') {
                var manager = klaro.getManager();
                if (manager) {
                    manager.saveAndApplyConsents(true);
                }
            }
            // Remove notice if exists
            var notice = document.getElementById('werocket-cookie-notice');
            if (notice) notice.remove();
        },

        /**
         * Decline all cookies (except required)
         */
        declineAll: function() {
            if (typeof klaro !== 'undefined' && typeof klaro.getManager === 'function') {
                var manager = klaro.getManager();
                if (manager) {
                    manager.saveAndApplyConsents(false);
                }
            }
            // Remove notice if exists
            var notice = document.getElementById('werocket-cookie-notice');
            if (notice) notice.remove();
        },

        /**
         * Check if consent has been given (any choice made)
         * @returns {boolean}
         */
        hasChoiceMade: function() {
            if (typeof klaro === 'undefined' || typeof klaro.getManager !== 'function') {
                return false;
            }

            var manager = klaro.getManager();
            return manager ? manager.confirmed : false;
        },

        /**
         * Listen for consent changes
         * @param {function} callback - Function to call when consent changes
         */
        onConsentChange: function(callback) {
            document.addEventListener('werocket_consent_update', function(e) {
                if (typeof callback === 'function') {
                    callback(e.detail.consent, e.detail.service);
                }
            });
        },

        /**
         * Execute callback only if service has consent
         * @param {string} serviceName - The service to check
         * @param {function} callback - Function to execute if consented
         */
        executeIfConsented: function(serviceName, callback) {
            if (this.hasConsent(serviceName)) {
                callback();
            } else {
                // Listen for future consent
                this.onConsentChange(function(consent) {
                    if (consent[serviceName]) {
                        callback();
                    }
                });
            }
        }
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Add click handler for elements with data-werocket-consent-manage attribute
        document.querySelectorAll('[data-werocket-consent-manage]').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                WeRocketCookies.showSettings();
            });
        });

        // Handle links with #manage-cookies or #cookie-settings hash
        document.querySelectorAll('a[href="#manage-cookies"], a[href="#cookie-settings"], a[href="#gerer-cookies"]').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                WeRocketCookies.showSettings();
            });
        });

        // Handle any element with class .werocket-cookie-settings-link
        document.querySelectorAll('.werocket-cookie-settings-link').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                WeRocketCookies.showSettings();
            });
        });
    });

})();
