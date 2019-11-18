<?php

/**
 * Plugin Name: Paynow Payment Gateway
 * Description: Accepts payments by Paynow
 * Version: 1.0.3
 * Author: mElements S.A.
 * Author URI: https://www.melements.pl
 * License: GPL v3
 * License Uri: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woo-paynow
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'woo_paynow_init', 0 );

function woo_paynow_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	load_plugin_textdomain( 'woo-paynow', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	define( 'WOO_PAYNOW_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
	define( 'WOO_PAYNOW_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
	define( 'WOO_PAYNOW_PLUGIN_VERSION', '1.0.3');

	include_once WOO_PAYNOW_PLUGIN_PATH . '/vendor/autoload.php';
	include_once WOO_PAYNOW_PLUGIN_PATH . '/includes/class-woo-paynow-helper.php';
	include_once WOO_PAYNOW_PLUGIN_PATH . '/includes/class-woo-paynow-logger.php';
	include_once WOO_PAYNOW_PLUGIN_PATH . '/includes/class-woo-paynow.php';
	include_once WOO_PAYNOW_PLUGIN_PATH . '/includes/class-woo-paynow-notification-handler.php';

	add_filter( 'woocommerce_payment_gateways', 'woo_paynow_add_gateway' );
}

function woo_paynow_add_gateway( $methods ) {
	$methods[] = 'WC_Gateway_Paynow';

	return $methods;
}