<?php
/**
 * Plugin Name: Pay by Paynow
 * Plugin URI: https://github.com/pay-now/paynow-woocommerce
 * Description: Accepts payments by Paynow
 * Version: 1.0.0
 * Author: mBank S.A.
 * Author URI: https://www.paynow.pl
 * License: MIT
 * Text Domain: woocommerce-gateway-paynow
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'woocommerce_gateway_paynow_init', 0 );

function woocommerce_gateway_paynow_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	load_plugin_textdomain( 'woocommerce-gateway-paynow', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	define( 'WC_PAYNOW_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
	define( 'WC_PAYNOW_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

	include_once dirname( __FILE__ ) . '/vendor/autoload.php';
	require_once dirname( __FILE__ ) . '/includes/class-wc-paynow-helper.php';
	require_once dirname( __FILE__ ) . '/includes/class-wc-paynow-payment-status.php';
	include_once dirname( __FILE__ ) . '/includes/class-wc-gateway-paynow.php';
	include_once dirname( __FILE__ ) . '/includes/class-wc-paynow-notification-handler.php';

	add_filter( 'woocommerce_payment_gateways', 'woocommerce_paynow_add_gateway' );
}

function woocommerce_paynow_add_gateway( $methods ) {
	$methods[] = 'WC_Gateway_Paynow';

	return $methods;
}