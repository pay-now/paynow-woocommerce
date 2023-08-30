<?php
defined( 'ABSPATH' ) || exit();

return array(
	'enabled'                        => array(
		'title'       => __( 'Enable/Disable', 'pay-by-paynow-pl' ),
		'label'       => __( 'Enable gateway', 'pay-by-paynow-pl' ),
		'type'        => 'checkbox',
		'description' => '',
		'default'     => 'no',
	),
	'information'                    => array(
		'title'       => __( 'Informations', 'pay-by-paynow-pl' ),
		'type'        => 'title',
		'description' => __( 'If you do not have an account in the Paynow system yet, <a href="https://paynow.pl/boarding" target="_blank">register in the Production</a> or <a href="https://panel.sandbox.paynow.pl/auth/register" target="_blank">Sandbox environment</a>.<br /> If you have any problem with configuration, please find the manual <a href="https://github.com/pay-now/paynow-woocommerce/blob/master/README.EN.md" target="_blank">here</a>.', 'pay-by-paynow-pl' ),
	),
	'debug_logs'                     => array(
		'title'       => __( 'Debug', 'pay-by-paynow-pl' ),
		'label'       => __( 'Enable logs', 'pay-by-paynow-pl' ),
		'type'        => 'checkbox',
		'description' => __( 'Save debug messages to the WooCommerce System Status log.', 'pay-by-paynow-pl' ),
		'default'     => 'no',
		'desc_tip'    => true,
	),
	'send_order_items'               => array(
		'title'   => __( 'Send order items', 'pay-by-paynow-pl' ),
		'label'   => __( 'Enable sending ordered products information: name, categories, quantity and unit price', 'pay-by-paynow-pl' ),
		'type'    => 'checkbox',
		'default' => 'no',
	),
	'use_payment_validity_time_flag' => array(
		'title'   => __( 'Use payment validity time', 'pay-by-paynow-pl' ),
		'label'   => __( 'Enable to limit the validity of the payment.', 'pay-by-paynow-pl' ),
		'type'    => 'checkbox',
		'default' => 'no',
	),
	'payment_validity_time'          => array(
		'title'       => __( 'Payment validity time', 'pay-by-paynow-pl' ),
		'type'        => 'number',
		'description' => __( 'Determines how long it will be possible to pay for the order from the moment the payment link is generated. Value expressed in seconds. The value must be between 60 and 86400 seconds.', 'pay-by-paynow-pl' ),
		'default'     => 86400,
		'desc_tip'    => true,
	),
	'support'                        => array(
		'title'       => __( 'Support', 'pay-by-paynow-pl' ),
		'type'        => 'title',
		'description' => __( 'If you have any questions or issues, please contact our support at <a href="mailto:support@paynow.pl">support@paynow.pl</a>', 'pay-by-paynow-pl' ),
	),
);
