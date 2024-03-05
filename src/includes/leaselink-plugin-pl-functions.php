<?php

defined( 'ABSPATH' ) || exit();

function wc_leaselink_payment_gateways( $gateways ) {

	return array_merge( wc_leaselink_plugin()->payment_gateways(), $gateways );
}

function wc_leaselink_plugin_version() {

	$plugin_data = get_file_data( WC_LEASELINK_PLUGIN_FILE_PATH . 'pay-by-paynow-pl.php', array( 'Version' => 'Version' ), false );

	return $plugin_data['Version'];
}

function wc_leaselink_plugin_gateway_check_status( $data ): WP_REST_Response {

	return ( new WC_Gateway_Leaselink_Plugin_PL_Paynow_Status_Handler() )->get_rest_status( $data['orderId'], $data['token'] );
}

function wc_leaselink_plugin_gateway_remove_saved_instrument( $data ): WP_REST_Response {

	return ( new WC_Gateway_Leaselink_Plugin_PL_Paynow_Remove_Instrument_Handler() )->remove_instrument( $data['savedInstrumentToken'] ?? '' );
}

function wc_leaselink_plugin_gateway_rest_init() {

	register_rest_route(
		'paynow',
		'status',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'wc_leaselink_plugin_gateway_check_status',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'paynow',
		'instrument-remove',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => 'wc_leaselink_plugin_gateway_remove_saved_instrument',
			'permission_callback' => '__return_true',
		)
	);
}

function wc_leaselink_plugin_gateway_content_thankyou( $order_id ) {

	$order = wc_get_order( $order_id );
	if ( $order->get_payment_method() === WC_LEASELINK_PLUGIN_PREFIX . 'blik' ) {
		$paynow_blik = new WC_Gateway_Leaselink_Plugin_PL_Paynow_Blik_Payment();
		if ( ! $order->has_status( wc_get_is_paid_statuses() ) && (int) filter_input( INPUT_GET, 'confirmBlik' ) === 1 ) {
			$rest_api_status_url  = WC_Gateway_Leaselink_Plugin_PL_Paynow_Status_Handler::get_rest_api_status_url();
			$rest_api_status_url .= ( strpos( $rest_api_status_url, '?' ) !== false ? '&' : '?' )
				. http_build_query(
					array(
						'orderId' => $order_id,
						'token'   => WC_Gateway_Leaselink_Plugin_PL_Paynow_Status_Handler::get_token_hash( $paynow_blik->gateway->get_signature_key(), array( 'orderId' => (int) $order_id ) ),
					)
				);

			include 'templates/' . WC_Gateway_Leaselink_Plugin_PL_Paynow_Blik_Payment::BLIK_CONFIRM_TEMPLATE_NAME . '.php';
			wp_enqueue_script( 'paynow-confirm-blik', WC_LEASELINK_PLUGIN_ASSETS_PATH . 'js/confirm-blik.js', array( 'jquery' ), wc_leaselink_plugin_version(), true );
		}
	}
}

function wc_pay_by_paynow_leaselink_render_widget($products = null) {
    wc_leaselink_plugin()->leaselink()->render_widget($products);
}
