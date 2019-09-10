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
		'sandbox'                  => [
			'title'       => __( 'Test mode (Sandbox)', 'woocommerce-gateway-paynow' ),
			'label'       => __( 'Enabled test mode', 'woocommerce-gateway-paynow' ),
			'description' => __( 'Enable if you are using test shop environment', 'woocommerce-gateway-paynow' ),
			'type'        => 'checkbox',
			'default'     => 'no',
			'desc_tip'    => true,
		],
		'sandbox_api_key'          => [
			'title'    => __( 'Api Key (Sandbox)', 'woocommerce-gateway-paynow' ),
			'type'     => 'password',
			'default'  => '',
		],
		'sandbox_signature_key'    => [
			'title'    => __( 'Signature Key (Sandbox)', 'woocommerce-gateway-paynow' ),
			'type'     => 'password',
			'default'  => '',
		],
		'production_api_key'       => [
			'title'    => __( 'Api Key (Production)', 'woocommerce-gateway-paynow' ),
			'type'     => 'password',
			'default'  => '',
		],
		'production_signature_key' => [
			'title'    => __( 'Signature Key (Production)', 'woocommerce-gateway-paynow' ),
			'type'     => 'password',
			'default'  => '',
		],
	]
);
