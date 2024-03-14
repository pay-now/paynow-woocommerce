<?php
/**
 * Plugin Name: LeaseLink - Leasing and Installments for Companies
 * Plugin URI: https://github.com/pay-now/paynow-woocommerce
 * Description: offer your business clients Leasing and Company Installments as payment
 * Version: 1.0.2
 * Requires PHP: 7.1
 * Author: LeaseLink Sp. z o.o.
 * Author URI: https://leaselink.pl/
 * License: GPLv3
 * Text Domain: leaselink-plugin-pl
 * Domain Path: /languages
 * Tested up to: 6.4
 * WC tested up to: 7.7.0
 *
 * @package Paynow
 */

defined( 'ABSPATH' ) || exit();

/**
 * Print message on PHP version requirement
 */
function wc_leaselink_plugin_pl_php_version_notice() {
	/* translators: %s: PHP version */
	$message = sprintf( __( 'Your PHP version is %s but LeaseLink - Leasing and Installments for Companies requires version 7.1+.', 'leaselink-plugin-pl' ), PHP_VERSION );
	echo '<div class="notice notice-error"><p style="font-size: 16px">' . esc_html( $message ) . '</p></div>';
}

if ( version_compare( PHP_VERSION, '7.1', '<' ) ) {
	add_action( 'admin_init', 'wc_leaselink_plugin_pl_php_version_notice' );

	return;
}

define( 'WC_LEASELINK_PLUGIN_FILE', __FILE__ );
define( 'WC_LEASELINK_PLUGIN_FILE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_LEASELINK_PLUGIN_ASSETS_PATH', plugin_dir_url( __FILE__ ) . 'assets/' );
define( 'WC_LEASELINK_PLUGIN_TEMPLATES_PATH', 'includes/templates/' );
define( 'WC_LEASELINK_PLUGIN_PREFIX', 'leaselink_pay_by_paynow_pl_' );

// include main plugin file.
require_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink-plugin-pl-functions.php';
require_once WC_LEASELINK_PLUGIN_FILE_PATH . 'vendor/autoload.php';
require_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-wc-leaselink-plugin-pl-manager.php';
