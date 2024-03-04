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
			'success' => false,
		);

		try {
			$success = $this->gateway->remove_saved_instrument( $instrument );

			$response['success'] = $success;
		} catch ( Exception $e ) {
			$response['error'] = __( 'An error occurred while deleting the saved card.', 'pay-by-paynow-pl' );

			WC_Pay_By_Paynow_PL_Logger::error(
				'Remove saved instrument failed',
				array(
					'service' => 'Remove instrument handler',
					'action'  => 'remove_saved_instrument',
					'message' => $e->getMessage(),
					'trace'   => $e->getTraceAsString(),
				)
			);
		}

		return new WP_REST_Response( $response );
	}

	/**
	 * @return string
	 */
	public static function get_rest_api_remove_instrument_url(): string {

		return get_rest_url() . 'paynow/instrument-remove/';
	}
}
