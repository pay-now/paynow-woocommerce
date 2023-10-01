<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class WC_Gateway_Pay_By_Paynow_PL_Remove_Instrument_Handler
 */
class WC_Gateway_Pay_By_Paynow_PL_Remove_Instrument_Handler extends WC_Gateway_Pay_By_Paynow_PL {

	/**
	 * @param string $instrument
	 *
	 * @return WP_REST_Response
	 */
	public function remove_instrument( string $instrument ): WP_REST_Response {

		$response = array(
            'success' => true,
            'token' => $instrument,
        );

		return new WP_REST_Response( $response );
	}

	/**
	 * @return string
	 */
	public static function get_rest_api_remove_instrument_url(): string {

		return get_rest_url() . 'paynow/instrument-remove/';
	}
}
