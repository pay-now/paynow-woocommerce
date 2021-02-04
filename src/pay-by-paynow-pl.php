<?php
/**
 * Plugin Name: Pay by paynow.pl
 * Plugin URI: https://github.com/pay-now/paynow-woocommerce
 * Description: Accepts secure BLIK, credit cards payments and fast online transfers by paynow.pl
 * Version: 2.0.1
 * Requires PHP: 7.1
 * Author: mElements S.A.
 * Author URI: https://www.paynow.pl
 * License: GPLv3
 * Text Domain: pay-by-paynow-pl
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

	load_plugin_textdomain( 'pay-by-paynow-pl', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	define( 'WC_PAY_BY_PAYNOW_PL_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
	define( 'WC_PAY_BY_PAYNOW_PL_PLUGIN_VERSION', get_plugin_version() );

	include_once dirname( __FILE__ ) . '/vendor/autoload.php';
	require_once dirname( __FILE__ ) . '/includes/pay-by-paynow-pl-helper.php';
	require_once dirname( __FILE__ ) . '/includes/pay-by-paynow-pl-logger.php';
	include_once dirname( __FILE__ ) . '/includes/pay-by-paynow-pl-gateway.php';
	include_once dirname( __FILE__ ) . '/includes/pay-by-paynow-pl-notification-handler.php';

	add_filter( 'woocommerce_payment_gateways', 'add_gateway_pay_by_paynow_pl' );
}

function add_gateway_pay_by_paynow_pl( $methods ) {
	array_unshift( $methods, 'WC_Gateway_Pay_By_Paynow_PL' );

	return $methods;
}

function get_plugin_version() {
	$plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ), false );

	return $plugin_data['Version'];
}
