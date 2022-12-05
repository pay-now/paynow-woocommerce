<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Model\Payment\Status;
use Paynow\Notification;

class WC_Gateway_Pay_By_Paynow_PL_Notification_Handler extends WC_Gateway_Pay_By_Paynow_PL {

	const ALLOWED_WC_API_PARAM_VALUES = array(
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
			$this->bad_request_response( 'Wrong request' );
			exit;
		}

		$payload           = trim( file_get_contents( 'php://input' ) );
		$headers           = WC_Pay_By_Paynow_PL_Helper::get_request_headers();
		$notification_data = json_decode( $payload, true );

		WC_Pay_By_Paynow_PL_Logger::info(
			'Received payment status notification {orderId={}, paymentId={}, status={}}',
			array(
				$notification_data['externalId'],
				$notification_data['paymentId'],
				$notification_data['status'],
			)
		);

		try {
			new Notification( $this->gateway->get_signature_key(), $payload, $headers );
			$order = wc_get_order( $notification_data['externalId'] );

			if ( ! $order ) {
				WC_Pay_By_Paynow_PL_Logger::error(
					'Order was not found {orderId={}, paymentId={}}',
					array(
						$notification_data['externalId'],
						$notification_data['paymentId'],
					)
				);
				$this->bad_request_response( 'Order not found' );
			}

			if ( strpos( $order->get_payment_method(), WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX ) === false ) {
				WC_Pay_By_Paynow_PL_Logger::error(
					'Other payment gateway is already selected {orderId={}, paymentId={}}',
					array(
						$notification_data['externalId'],
						$notification_data['paymentId'],
					)
				);
				$this->bad_request_response( 'Other payment gateway is already selected' );
			}

			if ( ! $order->has_status( wc_get_is_paid_statuses() ) && ( $order->get_transaction_id() === $notification_data['paymentId'] || Status::STATUS_NEW === $notification_data['status'] ) ) {
				$this->process_order_status_change( $order, $notification_data['paymentId'], $notification_data['status'] );
			} else {
				WC_Pay_By_Paynow_PL_Logger::info(
					'Order has one of paid statuses. Skipped notification processing {orderId={}, orderStatus={}, payment={}}',
					array(
						$notification_data['externalId'],
						$order->get_status(),
						$notification_data['paymentId'],
					)
				);
			}
		} catch ( Exception $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error(
				$exception->getMessage() . ' {orderId={}, paymentId={}}',
				array(
					$notification_data['externalId'],
					$notification_data['paymentId'],
				)
			);
			$this->bad_request_response( $exception->getMessage() );
		}

		status_header( 202 );
		exit;
	}

	private function bad_request_response( $reason ) {
		header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		status_header( 400 );
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
