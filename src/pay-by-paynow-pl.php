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

function activation_hook_action() {
	try {
		add_option('paynow_plugin_status_to_send', 'activated');
	} catch (Throwable $e) {
		//
	}
}
register_activation_hook( __FILE__, 'activation_hook_action' );

function deactivation_hook_action() {
	try {
		add_option('paynow_plugin_status_to_send', 'deactivated');
	} catch (Throwable $e) {
		//
	}
}
register_deactivation_hook( __FILE__, 'deactivation_hook_action' );

function uninstall_hook_action() {
	try {
		add_option('paynow_plugin_status_to_send', 'uninstalled');
	} catch (Throwable $e) {
		//
	}
}
register_uninstall_hook( __FILE__, 'uninstall_hook_action' );

function upgrade_hook_action( $upgrader_object, $options ) {
	try {
		$current_plugin_path_name = plugin_basename( __FILE__ );

		if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
			foreach( $options['plugins'] as $each_plugin ) {
				if ( $each_plugin == $current_plugin_path_name ) {
					add_option('paynow_plugin_status_to_send', 'upgraded');
				}
			}
		}
	} catch (Throwable $e) {
		//
	}

}
add_action( 'upgrader_process_complete', 'upgrade_hook_action', 10, 2 );
