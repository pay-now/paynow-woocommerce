<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters(
	'wc_paynow_settings',
	[
		'enabled'                  => [
			'title'       => __( 'Enable/Disable', 'woo-paynow' ),
			'label'       => __( 'Enable Paynow', 'woo-paynow' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		],
		'sandbox'                  => [
			'title'       => __( 'Test mode (Sandbox)', 'woo-paynow' ),
			'label'       => __( 'Enabled test mode', 'woo-paynow' ),
			'description' => __( 'Enable if you are using test shop environment', 'woo-paynow' ),
			'type'        => 'checkbox',
			'default'     => 'no',
			'desc_tip'    => true,
		],
		'sandbox_api_key'          => [
			'title'    => __( 'Api Key (Sandbox)', 'woo-paynow' ),
			'type'     => 'password',
			'default'  => '',
		],
		'sandbox_signature_key'    => [
			'title'    => __( 'Signature Key (Sandbox)', 'woo-paynow' ),
			'type'     => 'password',
			'default'  => '',
		],
		'production_api_key'       => [
			'title'    => __( 'Api Key (Production)', 'woo-paynow' ),
			'type'     => 'password',
			'default'  => '',
		],
		'production_signature_key' => [
			'title'    => __( 'Signature Key (Production)', 'woo-paynow' ),
			'type'     => 'password',
			'default'  => '',
		],
	]
);
