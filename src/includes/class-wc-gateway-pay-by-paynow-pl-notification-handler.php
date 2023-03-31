<?php

defined( 'ABSPATH' ) || exit();

use Paynow\Exception\SignatureVerificationException;
use Paynow\Model\Payment\Status;
use Paynow\Notification;

class WC_Gateway_Pay_By_Paynow_PL_Notification_Handler extends WC_Gateway_Pay_By_Paynow_PL {

	const ALLOWED_WC_API_PARAM_VALUES
		= array(
			'WC_Gateway_Pay_By_Paynow_PL_Notification_Handler',
			'WC_Gateway_Pay_By_Paynow_PL',
		);

	/**
	 * Constructor of WC_Gateway_Pay_By_Paynow_PL_Notification_Handler
	 */
	public function __construct() {

		parent::__construct();
		add_action( 'woocommerce_api_wc_gateway_pay_by_paynow_pl', array( $this, 'handle_notification' ) );
	}

	/**
	 * Handle notification request
	 */
	public function handle_notification() {

		if ( ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' !== $_SERVER['REQUEST_METHOD'] ) || ( ! in_array( filter_input( INPUT_GET, 'wc-api' ), self::ALLOWED_WC_API_PARAM_VALUES, true ) ) ) {
			$this->prepare_request_response( 'Wrong request' );
			exit;
		}
		$payload = trim( file_get_contents( 'php://input' ) );
		$headers = WC_Pay_By_Paynow_PL_Helper::get_request_headers();

		$notification_data = json_decode( $payload, true );

		WC_Pay_By_Paynow_PL_Logger::info(
			'Received payment status notification',
			$notification_data
		);

		try {
			new Notification( $this->gateway->get_signature_key(), $payload, $headers );
			$this->process_notification(
				$notification_data[ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_PAYMENT_ID_FIELD_NAME ],
				$notification_data[ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_STATUS_FIELD_NAME ],
				$notification_data[ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_EXTERNAL_ID_FIELD_NAME ],
				$notification_data[ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_MODIFIED_AT_FIELD_NAME ] ?? ''
			);

		} catch ( SignatureVerificationException $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error(
				'Error occurred handling notification: ' . $exception->getMessage(),
				$notification_data
			);
			$this->prepare_request_response( $exception->getMessage() );
		} catch ( WC_Pay_By_Paynow_Pl_Notification_Stop_Processing_Exception | WC_Pay_By_Paynow_Pl_Notification_Retry_Processing_Exception $exception ) {
			$response_code                          = ( $exception instanceof WC_Pay_By_Paynow_Pl_Notification_Stop_Processing_Exception ) ? 200 : 400;
			$exception->log_context['responseCode'] = $response_code;
			WC_Pay_By_Paynow_PL_Logger::error(
				$exception->log_message,
				$exception->log_context
			);
			$this->prepare_request_response( $exception->getMessage(), $response_code );
		} catch ( Error | Exception $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error(
				'Payment status notification processor -> unknown error' . $exception->getMessage(),
				$notification_data
			);
			$this->prepare_request_response( $exception->getMessage() );
		}

		status_header( 202 );
		exit;
	}

	private function prepare_request_response( $reason, $code = 400 ) {

		header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		status_header( $code );
		echo json_encode(
			array(
				'message' => 'An error occurred during processing notification',
				'reason'  => $reason,
			)
		);
		die;
	}
}

new WC_Gateway_Pay_By_Paynow_PL_Notification_Handler();
