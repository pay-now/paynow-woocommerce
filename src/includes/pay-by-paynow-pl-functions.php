<?php
defined( 'ABSPATH' ) || exit();

function wc_pay_by_paynow_pl_payment_gateways( $gateways ) {
	return array_merge( pay_by_paynow_wc()->payment_gateways(), $gateways );
}

function wc_pay_by_paynow_pl_plugin_version() {
	$plugin_data = get_file_data( WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'pay-by-paynow-pl.php', array( 'Version' => 'Version' ), false );

	return $plugin_data['Version'];
}

function wc_pay_by_paynow_pl_gateway_check_status( $data ): WP_REST_Response {
	return ( new WC_Gateway_Pay_By_Paynow_PL_Status_Handler() )->get_rest_status( $data['orderId'], $data['token'] );
}

function wc_pay_by_paynow_pl_gateway_rest_status_init() {
	register_rest_route(
		'paynow',
		'status',
		array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => 'wc_pay_by_paynow_pl_gateway_check_status',
		)
	);
}

function wc_pay_by_paynow_pl_gateway_content_thankyou( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order->get_payment_method() === WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'blik' ) {
		$paynow_blik = new WC_Payment_Gateway_Pay_By_Paynow_PL_Blik();
		if ( ! $order->has_status( wc_get_is_paid_statuses() ) && (int) filter_input( INPUT_GET, 'confirmBlik' ) === 1 ) {
			$rest_api_status_url = WC_Gateway_Pay_By_Paynow_PL_Status_Handler::get_rest_api_status_url() . '?' . http_build_query(
				array(
					'orderId' => $order_id,
					'token'   => WC_Gateway_Pay_By_Paynow_PL_Status_Handler::get_token_hash( $paynow_blik->gateway->get_signature_key(), array( 'orderId' => (int) $order_id ) ),
				)
			);

			include 'templates/' . WC_Payment_Gateway_Pay_By_Paynow_PL_Blik::BLIK_CONFIRM_TEMPLATE_NAME . '.phtml';
			wp_enqueue_script( 'paynow-confirm-blik', WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS_PATH . 'js/confirm-blik.js', array( 'jquery' ), wc_pay_by_paynow_pl_plugin_version(), true );
		}
	}
}
