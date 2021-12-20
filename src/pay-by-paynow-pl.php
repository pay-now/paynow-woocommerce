<?php
/**
 * Plugin Name: Pay by paynow.pl
 * Plugin URI: https://github.com/pay-now/paynow-woocommerce
 * Description: Accepts secure BLIK, credit cards payments and fast online transfers by paynow.pl
 * Version: 2.4.0
 * Requires PHP: 7.1
 * Author: mElements S.A.
 * Author URI: https://www.paynow.pl
 * License: GPLv3
 * Text Domain: pay-by-paynow-pl
 * Domain Path: /languages
 * Tested up to: 5.8.1
 * WC tested up to: 5.8.0
 */
defined( 'ABSPATH' ) || exit();

function wc_pay_by_paynow_pl_php_version_notice() {
	$message = sprintf( __( 'Your PHP version is %s but Pay by paynow.pl requires version 7.1+.', 'pay-by-paynow-pl' ), PHP_VERSION );
	echo '<div class="notice notice-error"><p style="font-size: 16px">' . $message . '</p></div>';
}

if ( version_compare( PHP_VERSION, '7.1', '<' ) ) {
	add_action( 'admin_init', 'wc_pay_by_paynow_pl_php_version_notice' );

	return;
}

define( 'WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS_PATH', plugin_dir_url( __FILE__ ) . 'assets/' );
define( 'WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH', 'includes/templates/' );
define( 'WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX', 'pay_by_paynow_pl_' );

// include main plugin file.
require_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-pay-by-paynow-pl-manager.php';
include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-pay-by-paynow-pl-page.php';
require_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'vendor/autoload.php';

register_activation_hook( __FILE__, 'wc_pay_by_paynow_pl_activate' );
register_deactivation_hook( __FILE__, 'wc_pay_by_paynow_pl_deactivate' );

function wc_pay_by_paynow_pl_activate() {
	$page = new WC_Pay_By_Paynow_Pl_Page(WC_Pay_By_Paynow_Pl_Page::CONFIRM_BLIK_PAYMENT_ID);
	$page->set_title(__( 'Confirm BLIK payment', 'pay-by-paynow-pl' ));
	$page->add();
}

function wc_pay_by_paynow_pl_deactivate() {
	$page = new WC_Pay_By_Paynow_Pl_Page(WC_Pay_By_Paynow_Pl_Page::CONFIRM_BLIK_PAYMENT_ID);
	$page->remove();
}