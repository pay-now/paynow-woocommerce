<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Model\Payment\Status;
use Paynow\Notification;

class WC_Gateway_Pay_By_Paynow_PL_Notification_Handler extends WC_Gateway_Pay_By_Paynow_PL {

	function __construct() {
		parent::__construct();
		add_action( 'woocommerce_api_wc_gateway_pay_by_paynow_pl', [ $this, 'handle_notification' ] );
	}

	/**
	 * Handle notification request
	 */
	public function handle_notification() {
		if ( ( 'POST' !== $_SERVER['REQUEST_METHOD'] )
		     || ! isset( $_GET['wc-api'] )
		     || ( 'WC_Gateway_Pay_By_Paynow_PL_Notification_Handler' !== $_GET['wc-api'] )
		) {
			status_header( 400 );
		}

		$payload           = trim( file_get_contents( 'php://input' ) );
		$headers           = WC_Pay_By_Paynow_PL_Helper::get_request_headers();
		$notification_data = json_decode( $payload, true );

		WC_Pay_By_Paynow_PL_Logger::info( 'Received payment notification {orderId={}, paymentId={}, status={}}', [
			$notification_data['externalId'],
			$notification_data['paymentId'],
			$notification_data['status']
		] );

		try {
			new Notification( $this->gateway->get_signature_key(), $payload, $headers );
			$order = wc_get_order( $notification_data['externalId'] );

			if ( ! $order ) {
				WC_Pay_By_Paynow_PL_Logger::error( 'Order was not found {orderId={}, paymentId={}}', [
					$notification_data['externalId'],
					$notification_data['paymentId']
				] );
				status_header( 400 );
				exit;
			}

			if ( strpos( $order->get_payment_method(), WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX ) === false ) {
				WC_Pay_By_Paynow_PL_Logger::error( 'Other payment gateway is already selected {orderId={}, paymentId={}}', [
					$notification_data['externalId'],
					$notification_data['paymentId']
				] );
				status_header( 400 );
				exit;
			}

			if ( ! $order->has_status( wc_get_is_paid_statuses() ) && $order->get_transaction_id() === $notification_data['paymentId'] ) {
				$this->process_notification( $order, $notification_data );
			} else {
				WC_Pay_By_Paynow_PL_Logger::info( 'Order has one of paid statuses. Skipped notification processing {orderId={}, orderStatus={}, payment={}}', [
					$notification_data['externalId'],
					$order->get_status(),
					$notification_data['paymentId']
				] );
			}
		} catch ( Exception $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error(
				$exception->getMessage() . ' {orderId={}, paymentId={}}',
				[
					$notification_data['externalId'],
					$notification_data['paymentId']
				]
			);
			status_header( 400 );
			exit;
		}

		status_header( 202 );
		exit;
	}

	/**
	 * @param WC_order $order
	 * @param array $notification_data
	 *
	 * @throws Exception
	 */
	private function process_notification( WC_order $order, array $notification_data ) {
		$notification_status = $notification_data['status'];

		if ( ! $this->is_correct_status( $order->get_status(), $notification_status ) ) {
            throw new Exception( 'Order status transition is incorrect '. $order->get_status() . ' - ' . $notification_status);
		}

		WC_Pay_By_Paynow_PL_Logger::info( 'Order status transition is correct {orderId={}, paymentId={}, orderStatus={}, paymentStatus={}}', [
			$notification_data['externalId'],
			$order->get_transaction_id(),
			$order->get_status(),
			$notification_status
		] );

		switch ( $notification_status ) {
			case Status::STATUS_NEW:
				$order->add_order_note( sprintf( __( 'Awaiting payment authorization - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
				break;
			case Status::STATUS_REJECTED:
				$order->update_status( 'failed', sprintf( __( 'Payment has not been authorized by the buyer - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
				break;
			case Status::STATUS_CONFIRMED:
				$order->payment_complete( $notification_data['paymentId'] );
				$order->add_order_note( sprintf( __( 'Payment has been authorized by the buyer - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
				break;
			case Status::STATUS_ERROR:
				$order->update_status( 'failed', sprintf( __( 'Error occurred during the payment process and the payment could not be completed - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
				break;
			case Status::STATUS_EXPIRED:
				$order->update_status( 'failed', sprintf( __( 'Payment has been expired - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
				break;
		}
	}

	/**
	 * @param $previous_status
	 * @param $next_status
	 *
	 * @return bool
	 */
	private function is_correct_status( $previous_status, $next_status ) {
		$payment_status_flow    = [
			'pending'              => [
				Status::STATUS_NEW,
				Status::STATUS_PENDING,
				Status::STATUS_ERROR,
				Status::STATUS_CONFIRMED,
				Status::STATUS_REJECTED,
				Status::STATUS_EXPIRED
			],
			'failed'               => [
				Status::STATUS_NEW,
				Status::STATUS_CONFIRMED,
				Status::STATUS_ERROR,
				Status::STATUS_REJECTED
			]
		];
		$previous_status_exists = isset( $payment_status_flow[ $previous_status ] );
		$is_change_possible     = in_array( $next_status, $payment_status_flow[ $previous_status ] );

		return $previous_status_exists && $is_change_possible;
	}
}

new WC_Gateway_Pay_By_Paynow_PL_Notification_Handler();
