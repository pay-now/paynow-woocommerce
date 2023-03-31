<?php

use Paynow\Util\SignatureCalculator;

defined( 'ABSPATH' ) || exit();

/**
 * Class WC_Gateway_Pay_By_Paynow_PL_Status_Handler
 */
class WC_Gateway_Pay_By_Paynow_PL_Status_Handler extends WC_Gateway_Pay_By_Paynow_PL {

	/**
	 * @param string $order_id Order ID.
	 * @param string $token Individual request token.
	 *
	 * @return WP_REST_Response
	 */
	public function get_rest_status( string $order_id, string $token ): WP_REST_Response {

		$order      = wc_get_order( $order_id );
		$response   = array();
		$return_url = rtrim( $this->get_return_url( $order ), '?' );

		$return_url .= strpos( $return_url, '?' ) !== false ? '&' : '?';

		if ( $order->get_transaction_id() === $order_id . '_UNKNOWN' ) {
			$response = array(
				'order_status'   => $order->get_status(),
				'payment_status' => \Paynow\Model\Payment\Status::STATUS_PENDING,
				'redirect_url'   => $return_url . http_build_query( array( 'paymentId' => $order->get_transaction_id() ) ),
			);
		} elseif ( self::get_token_hash( $this->gateway->get_signature_key(), array( 'orderId' => (int) $order_id ) ) === $token ) {
			$status   = $this->gateway->payment_status( $order_id, $order->get_transaction_id() );
			$response = array(
				'order_status'   => $order->get_status(),
				'payment_status' => $status,
				'redirect_url'   => $return_url . http_build_query( array( 'paymentId' => $order->get_transaction_id() ) ),
			);
		}

		return new WP_REST_Response( $response );
	}

	/**
	 * @param string $signature_key Paynow signature key.
	 * @param array $data Data to generate hash
	 *
	 * @return string
	 */
	public static function get_token_hash( string $signature_key, array $data ): string {

		return hash( 'sha256', ( new SignatureCalculator( $signature_key, wp_json_encode( $data ) ) ) );
	}

	/**
	 * Returns REST url for status check
	 *
	 * @return string
	 */
	public static function get_rest_api_status_url(): string {

		return get_rest_url() . 'paynow/status/';
	}
}
