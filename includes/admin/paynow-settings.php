<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters(
	'wc_paynow_settings',
	[	
		'enabled'                  => [
			'title'       => __( 'Enable/Disable', 'woocommerce-gateway-paynow' ),
			'label'       => __( 'Enable Paynow', 'woocommerce-gateway-paynow' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		],
		'debug_logs'               => [
			'title'       => __( 'Debug', 'woocommerce-gateway-paynow' ),
			'label'       => __( 'Enable logs', 'woocommerce-gateway-paynow' ),
			'type'        => 'checkbox',
			'description' => __( 'Save debug messages to the WooCommerce System Status log.', 'woocommerce-gateway-paynow' ),
			'default'     => 'no',
			'desc_tip'    => true,
		],
		'production_title'       => [
			'title'   => __( 'Production configuration', 'woocommerce-gateway-paynow' ),
			'type'    => 'title',
			'description' => __( 'Production keys can be found in <i>Settings > Shops and poses > Authentication data</i> in the Paynow merchant panel.', 'woocommerce-gateway-paynow' ),
		],
		'production_api_key'       => [
			'title'   => __( 'Api Key (Production)', 'woocommerce-gateway-paynow' ),
			'type'    => 'password',
			'default' => '',
		],
		'production_signature_key' => [
			'title'   => __( 'Signature Key (Production)', 'woocommerce-gateway-paynow' ),
			'type'    => 'password',
			'default' => '',
		],
		'sandbox_title'       => [
			'title'   => __( 'Sandbox configuration', 'woocommerce-gateway-paynow' ),
			'type'    => 'title',
			'description' => __( 'Sandbox keys can be found in <i>Settings > Shops and poses > Authentication data</i> in <a href="https://panel.sandbox.paynow.pl/auth/login" target="_blank">the Paynow Sandbox panel</a>.', 'woocommerce-gateway-paynow' ),
		],
		'sandbox'                  => [
			'title'       => __( 'Test mode (Sandbox)', 'woocommerce-gateway-paynow' ),
			'label'       => __( 'Enabled test mode', 'woocommerce-gateway-paynow' ),
			'description' => __( 'Enable if you are using test shop environment', 'woocommerce-gateway-paynow' ),
			'type'        => 'checkbox',
			'default'     => 'no',
			'desc_tip'    => true,
		],
		'sandbox_api_key'          => [
			'title'   => __( 'Api Key (Sandbox)', 'woocommerce-gateway-paynow' ),
			'type'    => 'password',
			'default' => '',
		],
		'sandbox_signature_key'    => [
			'title'   => __( 'Signature Key (Sandbox)', 'woocommerce-gateway-paynow' ),
			'type'    => 'password',
			'default' => '',
		],
		'help'       => [
			'title'   => __( 'Support', 'woocommerce-gateway-paynow' ),
			'type'    => 'title',
			'description' => __( 'If you have any problems with configuration, please find the instruction <a href="https://github.com/pay-now/paynow-woocommerce/blob/master/README.EN.md" target="_blank">here</a>.<br /> If you have any questions or issues, please contact our support at <a href="mailto:support@paynow.pl">support@paynow.pl</a>', 'woocommerce-gateway-paynow' ),
		]
	]
);
