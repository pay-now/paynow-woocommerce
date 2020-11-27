<?php
/**
 * Plugin Name: Pay by paynow.pl
 * Plugin URI: https://github.com/pay-now/paynow-woocommerce
 * Description: paynow is a secure online payment by bank transfer, BLIK and card.
 * Version: 1.0.14
 * Requires PHP: 7.1
 * Author: mElements S.A.
 * Author URI: https://www.paynow.pl
 * License: GPLv3
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
	define( 'WC_PAYNOW_PLUGIN_VERSION', '1.0.14');

	include_once dirname( __FILE__ ) . '/vendor/autoload.php';
	require_once dirname( __FILE__ ) . '/includes/class-wc-paynow-helper.php';
	require_once dirname( __FILE__ ) . '/includes/class-wc-paynow-logger.php';
	include_once dirname( __FILE__ ) . '/includes/class-wc-gateway-paynow.php';
	include_once dirname( __FILE__ ) . '/includes/class-wc-paynow-notification-handler.php';

	add_filter( 'woocommerce_payment_gateways', 'woocommerce_paynow_add_gateway' );
}

function woocommerce_paynow_add_gateway( $methods ) {
	array_unshift($methods, 'WC_Gateway_Paynow' );

	return $methods;
}
