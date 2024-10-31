<?php
/**
 * Price Meter Products Sync
 *
 * Plugin Name:       Price Meter Products Sync
 * Plugin URI:        https://pricemeter.pk/pricemeter-products-sync
 * Description:       This plugin will make it super easy to sync your store products on Price Meter without much effort
 * Version:           1.2.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Price Meter
 * Author URI:        https://pricemeter.pk
 * Text Domain:       pm-prod-sync
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

require __DIR__ . '/vendor/autoload.php';

\PMProdSync\Core::init();