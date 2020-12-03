<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters(
	'wc_paynow_settings',
	[
		'information'              => [
			'title'       => __( 'Informations', 'gateway-pay-by-paynow-pl' ),
			'type'        => 'title',
			'description' => __( 'If you do not have an account in the Paynow system yet, <a href="https://paynow.pl/boarding" target="_blank">register in the Production</a> or <a href="https://panel.sandbox.paynow.pl/auth/register" target="_blank">Sandbox environment</a>.<br /> If you have any problem with configuration, please find the manual <a href="https://github.com/pay-now/paynow-woocommerce/blob/master/README.EN.md" target="_blank">here</a>.', 'gateway-pay-by-paynow-pl' ),
		],
		'enabled'                  => [
			'title'       => __( 'Enable/Disable', 'gateway-pay-by-paynow-pl' ),
			'label'       => __( 'Enable gateway', 'gateway-pay-by-paynow-pl' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		],
		'debug_logs'               => [
			'title'       => __( 'Debug', 'gateway-pay-by-paynow-pl' ),
			'label'       => __( 'Enable logs', 'gateway-pay-by-paynow-pl' ),
			'type'        => 'checkbox',
			'description' => __( 'Save debug messages to the WooCommerce System Status log.', 'gateway-pay-by-paynow-pl' ),
			'default'     => 'no',
			'desc_tip'    => true,
		],
		'production_title'         => [
			'title'       => __( 'Production configuration', 'gateway-pay-by-paynow-pl' ),
			'type'        => 'title',
			'description' => __( "Production authentication keys are available in <i>My Business > Paynow > Settings > Shops and payment points > Authentication data</i> in mBank's online banking.", 'gateway-pay-by-paynow-pl' ),
		],
		'production_api_key'       => [
			'title'   => __( 'Api Key', 'gateway-pay-by-paynow-pl' ),
			'type'    => 'password',
			'default' => '',
		],
		'production_signature_key' => [
			'title'   => __( 'Signature Key', 'gateway-pay-by-paynow-pl' ),
			'type'    => 'password',
			'default' => '',
		],
		'sandbox_title'            => [
			'title'       => __( 'Sandbox configuration', 'gateway-pay-by-paynow-pl' ),
			'type'        => 'title',
			'description' => __( 'Sandbox authentication keys can be found in <i>Settings > Shops and poses > Authentication data</i> in <a href="https://panel.sandbox.paynow.pl/auth/login" target="_blank">the Paynow Sandbox panel</a>.', 'gateway-pay-by-paynow-pl' ),
		],
		'sandbox'                  => [
			'title'       => __( 'Test mode (Sandbox)', 'gateway-pay-by-paynow-pl' ),
			'label'       => __( 'Enabled test mode', 'gateway-pay-by-paynow-pl' ),
			'description' => __( 'Enable if you are using test shop environment', 'gateway-pay-by-paynow-pl' ),
			'type'        => 'checkbox',
			'default'     => 'no',
			'desc_tip'    => true,
		],
		'sandbox_api_key'          => [
			'title'   => __( 'Api Key', 'gateway-pay-by-paynow-pl' ),
			'type'    => 'password',
			'default' => '',
		],
		'sandbox_signature_key'    => [
			'title'   => __( 'Signature Key', 'gateway-pay-by-paynow-pl' ),
			'type'    => 'password',
			'default' => '',
		],
		'support'                  => [
			'title'       => __( 'Support', 'gateway-pay-by-paynow-pl' ),
			'type'        => 'title',
			'description' => __( 'If you have any questions or issues, please contact our support at <a href="mailto:support@paynow.pl">support@paynow.pl</a>', 'gateway-pay-by-paynow-pl' ),
		],
	]
);
