<?php

use Paynow\Util\SignatureCalculator;

defined( 'ABSPATH' ) || exit();

class WC_Gateway_Pay_By_Paynow_PL_Status_Handler extends WC_Gateway_Pay_By_Paynow_PL {
	public function get_rest_status( string $orderId, string $token ) {
		$order    = wc_get_order( $orderId );
		$response = array();
		if ( $token === self::get_token_hash( $this->gateway->get_signature_key(), array( 'orderId' => (int) $orderId ) ) ) {
			$status   = $this->gateway->payment_status( $orderId, $order->get_transaction_id() );
			$response = array(
				'order_status'   => $order->get_status(),
				'payment_status' => $status,
				'redirect_url'   => $this->get_return_url( $order ) . '&' . http_build_query( array( 'paymentId' => $order->get_transaction_id() ) ),
			);
		}

		return new WP_REST_Response( $response );
	}

	public static function get_token_hash( string $signature_key, array $data ): string {
		return hash( 'sha256', ( new SignatureCalculator( $signature_key, json_encode( $data ) ) ) );
	}

	public static function get_rest_api_status_url() {
		return get_rest_url() . 'paynow/status/';
	}
}
