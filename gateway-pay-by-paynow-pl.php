<?php
/**
 * Plugin Name: Pay by paynow.pl
 * Plugin URI: https://github.com/pay-now/paynow-woocommerce
 * Description: Accepts payments by paynow.pl
 * Version: 1.0.14
 * Requires PHP: 7.1
 * Author: mElements S.A.
 * Author URI: https://www.paynow.pl
 * License: MIT
 * Text Domain: gateway-pay-by-paynow-pl
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'gateway_pay_by_paynow_pl_init', 0 );

function gateway_pay_by_paynow_pl_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	load_plugin_textdomain( 'gateway-pay-by-paynow-pl', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	define( 'WC_PAYNOW_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
	define( 'WC_PAYNOW_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
	define( 'WC_PAYNOW_PLUGIN_VERSION', '1.0.14' );

	include_once dirname( __FILE__ ) . '/vendor/autoload.php';
	require_once dirname( __FILE__ ) . '/includes/pay-by-paynow-pl-helper.php';
	require_once dirname( __FILE__ ) . '/includes/pay-by-paynow-pl-logger.php';
	include_once dirname( __FILE__ ) . '/includes/pay-by-paynow-pl-gateway.php';
	include_once dirname( __FILE__ ) . '/includes/pay-by-paynow-pl-notification-handler.php';

	add_filter( 'woocommerce_payment_gateways', 'add_gateway_pay_by_paynow_pl' );
}

function add_gateway_pay_by_paynow_pl( $methods ) {
	array_unshift( $methods, 'WC_Gateway_Paynow' );

	return $methods;
}
