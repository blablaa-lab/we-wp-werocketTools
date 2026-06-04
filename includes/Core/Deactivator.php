<?php
/**
 * Plugin Deactivator
 */

namespace WeRocket\Tools\Core;

use WeRocket\Tools\Modules\GoogleReviews\GoogleReviewsModule;
use WeRocket\Tools\Modules\Cookies\Scanner\ScanCron;

class Deactivator {

    public static function deactivate(): void {
        // Désinscription des crons planifiés par les modules
        GoogleReviewsModule::unschedule_cron();
        ScanCron::unschedule();

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
