<?php

// Declaring constants
define("PMWOOPS_PLUGIN_NAME", "Price Meter Products Sync");
define("PMWOOPS_PLUGIN_SLUG", "price-meter-products-sync");

define("PMWOOPS_TOKEN", "pm_woo_prod_sync_token");
define("PMWOOPS_PROD_TABLE_KEY", "pm_woo_prod_sync");
define("PMWOOPS_PROD_SYNC_META_FIELD", "pm_woo_prod_sync_status");
define("PMWOOPS_PROD_SYNC_ACTION", "pm_woo_prod_start_sync");
define("PMWOOPS_PROD_KEYWORDS_FIELD", "pm_woo_prod_keywords");
define("PMWOOPS_PROD_NONCE_URL", "pm_woo_prod_%s");
define("PMWOOPS_PROD_DOWNLOAD_CSV_ACTION", "pm_woo_prod_start_download_csv");
define("PMWOOPS_CSV_NONCE_URL", "pm_woo_prod_csv");
define("PMWOOPS_NOTIFICATION_SUCCESS_KEY", "pm_woo_prod_success_notifications");
define("PMWOOPS_NOTIFICATION_ERROR_KEY", "pm_woo_prod_error_notifications");

define("PMWOOPS_STATUS_INSERT", 1);
define("PMWOOPS_STATUS_UPDATE", 2);
define("PMWOOPS_STATUS_DELETE", 3);

define('PMWOOPS_ROOT', dirname(dirname(__DIR__)));
define('PMWOOPS_ROOT_SRC', PMWOOPS_ROOT . '/src');
define('PMWOOPS_ROOT_VIEW', PMWOOPS_ROOT . '/src/views');


/**
 * Insert a value or key/value pair after a specific key in an array.  If key doesn't exist, value is appended
 * to the end of the array.
 *
 * @param array $array
 * @param string $key
 * @param array $new
 *
 * @return array
 */
function array_insert_after( array $array, $key, array $new ) {
    $keys = array_keys( $array );
    $index = array_search( $key, $keys );
    $pos = false === $index ? count( $array ) : $index + 1;

    return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
}
