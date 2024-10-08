<?php
/**
 * Plugin Name: Pay by paynow.pl
 * Plugin URI: https://github.com/pay-now/paynow-woocommerce
 * Description: Accepts secure BLIK, credit cards payments and fast online transfers by paynow.pl
 * Version: 2.5.7
 * Requires PHP: 7.2
 * Author: mElements S.A.
 * Author URI: https://www.paynow.pl
 * License: GPLv3
 * Text Domain: pay-by-paynow-pl
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
function wc_pay_by_paynow_pl_php_version_notice() {
	/* translators: %s: PHP version */
	$message = sprintf( __( 'Your PHP version is %s but Pay by paynow.pl requires version 7.1+.', 'pay-by-paynow-pl' ), PHP_VERSION );
	echo '<div class="notice notice-error"><p style="font-size: 16px">' . esc_html( $message ) . '</p></div>';
}

if ( version_compare( PHP_VERSION, '7.1', '<' ) ) {
	add_action( 'admin_init', 'wc_pay_by_paynow_pl_php_version_notice' );

	return;
}

define( 'WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE', __FILE__ );
define( 'WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS_PATH', plugin_dir_url( __FILE__ ) . 'assets/' );
define( 'WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH', 'includes/templates/' );
define( 'WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX', 'pay_by_paynow_pl_' );

// include main plugin file.
require_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/pay-by-paynow-pl-functions.php';
require_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-manager.php';
require_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'vendor/autoload.php';
