<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

use Paynow\Exception\PaynowException;
use Paynow\Model\Payment\Status;

abstract class WC_Gateway_Pay_By_Paynow_PL extends WC_Payment_Gateway {

	const ORDER_META_STATUS_FIELD_NAME    = '_pay_by_paynow_pl_status';
	const ORDER_META_MODIFIED_AT_KEY      = '_pay_by_paynow_pl_modified_at';
	const ORDER_META_NOTIFICATION_HISTORY = '_pay_by_paynow_pl_notification_history';

	protected $payment_method_id = null;

	protected $show_payment_methods = true;

	protected $payment_gateway_options
		= array(
			'enabled',
		);

	public const BLIK_PAYMENT = 0;

	public const PBL_PAYMENT = 1;

	public const CARD_PAYMENT = 2;

	public const DIGITAL_WALLETS_PAYMENT = 3;

	public const PAYNOW_PAYMENT_GATEWAY
		= array(
			self::BLIK_PAYMENT            => WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'blik',
			self::PBL_PAYMENT             => WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'pbl',
			self::CARD_PAYMENT            => WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'card',
			self::DIGITAL_WALLETS_PAYMENT => WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'digital_wallets',
		);

	/**
	 * @var Paynow_Gateway
	 */
	public $gateway;

	/**
	 * @var WC_Pay_By_Paynow_PL_Locking_Mechanism
	 */
	private $locking_mechanism;

	public function __construct() {

		$this->init_form_fields();
		$this->init_settings();
		$this->hooks();
		$this->init_supports();

		if ( $this->payment_method_id ) {
			$this->icon = 'https://static.paynow.pl/payment-method-icons/' . $this->payment_method_id . '.png';
		}

		$this->gateway           = new Paynow_Gateway( $this->settings );
		$this->locking_mechanism = new WC_Pay_By_Paynow_PL_Locking_Mechanism();
	}

	public function init_supports() {

		$this->supports = array(
			'products',
			'refunds',
		);
	}

	public function init_settings() {

		parent::init_settings();
		$options = get_option( $this->get_api_option_key_name(), null );

		$this->settings = array_merge( $this->settings, ! empty( $options ) ? $options : array() );

		if ( is_array( $options ) && in_array( 'enabled', $options, true ) ) {
			$this->enabled = $options['enabled'];
		} else {
			$this->enabled = ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
		}

		$this->show_payment_methods = ( 'yes' === ( $this->settings['show_payment_methods'] ?? 'yes' ) );
	}

	public function process_admin_options() {

		$this->init_settings();
		$api_settings            = array();
		$payment_method_settings = array();

		$post_data = $this->get_post_data();

		foreach ( $this->get_form_fields() as $key => $field ) {
			if ( 'title' !== $this->get_field_type( $field ) ) {
				try {
					if ( in_array( $key, $this->payment_gateway_options, true ) ) {
						$payment_method_settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
					} else {
						$api_settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
					}
				} catch ( Exception $e ) {
					$this->add_error( $e->getMessage() );
				}
			}
		}

		update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $payment_method_settings ), 'yes' );
		update_option( $this->get_api_option_key_name(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $api_settings ), 'yes' );

		$this->gateway->send_shop_urls_configuration_request( $this->get_return_url() );
	}

	public function update_option( $key, $value = '' ): bool {

		if ( empty( $this->settings ) ) {
			$this->init_settings();
		}

		$plugin_settings = array();
		foreach ( $this->settings as $key => $val ) {
			if ( in_array( $key, $this->payment_gateway_options, true ) ) {
				$plugin_settings[ $key ] = $value;
			}
		}
		return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $plugin_settings ), 'yes' );
	}

	public function init_form_fields() {

		$this->form_fields = include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/settings/pay-by-paynow-pl-settings.php';
	}

	public function payment_fields() {

		include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH . 'payment_processor_info.php';
	}

	public function process_payment( $order_id ): array {

		$order    = wc_get_order( $order_id );
		$response = array();

		try {
			WC_Pay_By_Paynow_PL_Helper::validate_minimum_payment_amount( (float) $order->get_total() );
		} catch ( Exception $e ) {
			WC_Pay_By_Paynow_PL_Logger::error( $e->getMessage(), array( WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_EXTERNAL_ID_FIELD_NAME => $order_id ) );
			return $response;
		}

		$payment_method     = $this->get_payment_method_from_posted_data();
		$payment_method_id  = null;
		$authorization_code = null;
		if ( in_array( $payment_method, array( self::PAYNOW_PAYMENT_GATEWAY[ self::PBL_PAYMENT ], self::PAYNOW_PAYMENT_GATEWAY[ self::DIGITAL_WALLETS_PAYMENT ] ), true ) ) {
			$payment_method_id = $this->get_payment_method_id_from_posted_data();
		} elseif ( self::PAYNOW_PAYMENT_GATEWAY[ self::BLIK_PAYMENT ] === $payment_method ) {
			$authorization_code = preg_replace( '/\s+/', '', $this->get_authorization_code_from_posted_data() );
		} elseif ( self::PAYNOW_PAYMENT_GATEWAY[ self::CARD_PAYMENT ] === $payment_method ) {
			$payment_method_token       = $this->get_payment_method_token_from_posted_data();
			$payment_method_fingerprint = $this->get_payment_method_fingerprint_from_posted_data();
		}

		$payment_data = $this->gateway->payment_request(
			$order,
			$this->get_return_url( $order ),
			! empty( $payment_method_id ) ? intval( $payment_method_id ) : $this->payment_method_id,
			$authorization_code,
			! empty( $payment_method_token ) ? $payment_method_token : null,
			! empty( $payment_method_fingerprint ) ? $payment_method_fingerprint : null
		);
		if ( isset( $payment_data['errors'] ) ) {
			$error_type = null;
			$message    = null;
			if ( isset( $payment_data['errors'] [0] ) && $payment_data['errors'][0] instanceof \Paynow\Exception\Error ) {
				$error_type = $payment_data['errors'][0]->getType();
				$message    = $payment_data['errors'][0]->getMessage();
			}
			switch ( $error_type ) {
				case 'AUTHORIZATION_CODE_INVALID':
					wc_add_notice( __( 'Wrong BLIK code', 'pay-by-paynow-pl' ), 'error' );
					break;
				case 'AUTHORIZATION_CODE_EXPIRED':
					wc_add_notice( __( 'BLIK code has expired', 'pay-by-paynow-pl' ), 'error' );
					break;
				case 'AUTHORIZATION_CODE_USED':
					wc_add_notice( __( 'BLIK code already used', 'pay-by-paynow-pl' ), 'error' );
					break;
				case 'VALIDATION_ERROR':
					wc_add_notice( $this->get_validation_errors_message( $message ), 'error' );
					break;
				default:
					wc_add_notice( __( 'An error occurred during the payment process and the payment could not be completed.', 'pay-by-paynow-pl' ), 'error' );
			}
			return $response;
		}

		if ( WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
			add_post_meta( $order_id, '_transaction_id', $payment_data[ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_PAYMENT_ID_FIELD_NAME ], true );
		} else {
			$order->add_meta_data( '_transaction_id', $payment_data[ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_PAYMENT_ID_FIELD_NAME ], true );
		}

		if ( WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
			update_post_meta( $order_id, '_transaction_id', $payment_data [ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_PAYMENT_ID_FIELD_NAME ] );
		} else {
			$order->set_transaction_id( $payment_data[ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_PAYMENT_ID_FIELD_NAME ] );
		}

		WC()->cart->empty_cart();

		if ( is_callable( array( $order, 'save' ) ) ) {
			$order->save();
		}

		if ( ! in_array(
			$payment_data[ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_STATUS_FIELD_NAME ],
			array(
				Status::STATUS_NEW,
				Status::STATUS_PENDING,
			),
			true
		) ) {
			$response['result'] = 'failure';
		} else {
			$response['result']   = 'success';
			$response['redirect'] = $payment_data[ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_REDIRECT_URL_FIELD_NAME ];
		}

		return $response;
	}

	protected function get_validation_errors_message( $message = '' ) {
		if ( strpos( $message, 'buyer.email' ) !== false ) {
			return __( 'Invalid email address entered. Check the correctness of the entered data', 'pay-by-paynow-pl' );
		}

		return __( 'A data validation error occurred. Check the correctness of the entered data', 'pay-by-paynow-pl' );
	}

	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		$order      = wc_get_order( $order_id );
		$payment_id = $order->get_transaction_id();

		$refund_amount = WC_Pay_By_Paynow_PL_Helper::get_amount( $amount );
		if ( ! $this->check_can_make_refund( $order, $refund_amount ) ) {
			return new WP_Error( 'error', __( 'Refund can\'t be processed. Please check logs for more information', 'pay-by-paynow-pl' ) );
		}

		WC_Pay_By_Paynow_PL_Logger::debug(
			'Processing refund request {orderId={}, paymentId={}, amount={}}',
			array(
				$order_id,
				$payment_id,
				$refund_amount,
			)
		);

		try {
			$refund_data = $this->gateway->refund_request(
				$order_id,
				$payment_id,
				$refund_amount
			);

			WC_Pay_By_Paynow_PL_Logger::info(
				'Refund has been created successfully {orderId={}, paymentId={}, refundId={}, amount={}}',
				array(
					$order_id,
					$payment_id,
					$refund_data->getRefundId(),
					$refund_amount,
				)
			);

			if ( ! empty( $refund_data->getRefundId() ) ) {
				/* translators: %s: Payment ID */
				$order->add_order_note( sprintf( __( 'Refund request processed correctly - %s', 'pay-by-paynow-pl' ), $refund_data->getRefundId() ) );

				return true;
			}

			return false;
		} catch ( PaynowException $exception ) {
			$errors = $exception->getErrors();
			if ( $errors ) {
				foreach ( $errors as $error ) {
					/* translators: %s: Error message */
					$order->add_order_note( sprintf( __( 'An error occurred during the refund process - %s', 'pay-by-paynow-pl' ), $error->getMessage() ) );
					WC_Pay_By_Paynow_PL_Logger::error(
						$error->getType() . ' - ' . $error->getMessage() . ' {orderId={}, paymentId={}, amount={}}',
						array(
							$order_id,
							$payment_id,
							$refund_amount,
						)
					);

					return new WP_Error( 'error', __( 'Refund process failed. Please check logs for more information', 'pay-by-paynow-pl' ) );
				}
			}
		}

		return false;
	}

	/**
	 * @param $order
	 * @param int $amount
	 *
	 * @return bool
	 */
	public function check_can_make_refund( $order, int $amount ): bool {

		if ( ! $this->can_refund_order( $order ) ) {
			return false;
		}

		$order_id = WC_Pay_By_Paynow_PL_Helper::get_order_id( $order );

		if ( empty( $order_id ) ) {
			WC_Pay_By_Paynow_PL_Logger::warning( 'Order was not found to make a refund {orderId={}}', array( $order_id ) );

			return false;
		}

		if ( empty( $order->get_transaction_id() ) ) {
			WC_Pay_By_Paynow_PL_Logger::warning( 'The order has no payment to make a refund {orderId={}}', array( $order_id ) );

			return false;
		}

		if ( empty( $amount ) ) {
			WC_Pay_By_Paynow_PL_Logger::warning( 'The amount of the refund must be above zero {orderId={}}', array( $order_id ) );

			return false;
		}

		if ( ! $order->has_status( wc_get_is_paid_statuses() ) ) {
			WC_Pay_By_Paynow_PL_Logger::warning(
				'Status of the order must be in paid status {orderId={}, status={}}',
				array(
					$order_id,
					$order->get_status(),
				)
			);

			return false;
		}

		if ( empty( $order->get_remaining_refund_amount() ) ) {
			WC_Pay_By_Paynow_PL_Logger::warning(
				'There is no more remaining amount to refund {orderId={}, amount={}}',
				array(
					$order_id,
					$order->get_remaining_refund_amount(),
				)
			);
		}

		return true;
	}

	public function is_available(): bool {

		if ( ! is_admin() ) {
			$available = true;
			try {
				WC_Pay_By_Paynow_PL_Helper::validate_minimum_payment_amount( WC_Pay_By_Paynow_PL_Helper::get_payment_amount() );
			} catch ( PaynowException $exception ) {
				$available = false;
			}

			return parent::is_available() && $available;
		}

		return parent::is_available();
	}

	/**
	 * @param array $types Payment method Type
	 *
	 * @return bool
	 */
	protected function is_payment_method_available( array $types ): bool {

		if ( ! is_admin() && parent::is_available() ) {
			$payment_method = $this->get_only_payment_methods_for_type( $types );
			return ! empty( $payment_method ) && reset( $payment_method )->isEnabled() && $this->show_payment_methods;
		}

		return parent::is_available();
	}

	/**
	 * @param $payment_id
	 * @param $status
	 * @param $external_id
	 * @param $modified_at
	 * @param $force
	 * @return void
	 * @throws WC_Pay_By_Paynow_Pl_Notification_Retry_Processing_Exception
	 * @throws WC_Pay_By_Paynow_Pl_Notification_Stop_Processing_Exception
	 */
	public function process_notification( $payment_id, $status, $external_id, $modified_at = '', $force = false ) {

		// phpcs:ignore
		set_time_limit(30);

		$context = array(
			WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_EXTERNAL_ID_FIELD_NAME => $external_id,
			WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_PAYMENT_ID_FIELD_NAME  => $payment_id,
			WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_STATUS_FIELD_NAME      => $status,
			WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_MODIFIED_AT_FIELD_NAME => $modified_at,
		);

		if ( $this->locking_mechanism->check_and_create( $external_id ) ) {
			for ( $i = 1; $i <= 3; $i++ ) {
				// phpcs:ignore
				sleep( 1 );
				$is_notification_locked = $this->locking_mechanism->check_and_create( $external_id );
				if ( false === $is_notification_locked ) {
					break;
				} elseif ( 3 === $i ) {
					throw new WC_Pay_By_Paynow_Pl_Notification_Retry_Processing_Exception( 'Skipped processing. Previous notification is still processing.', $context );
				}
			}
		}

		WC_Pay_By_Paynow_PL_Logger::info( 'Lock passed successfully, notification validation starting.', $context );

		$is_new       = Status::STATUS_NEW === $status;
		$is_confirmed = Status::STATUS_CONFIRMED === $status;

		$order = wc_get_order( $external_id );

		if ( ! $order ) {
			$this->locking_mechanism->delete( $external_id );
			throw new WC_Pay_By_Paynow_Pl_Notification_Retry_Processing_Exception(
				'Skipped processing. Order not found.',
				$context
			);
		}

		if ( strpos( $order->get_payment_method(), WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX ) === false ) {
			$this->locking_mechanism->delete( $external_id );
			throw new WC_Pay_By_Paynow_Pl_Notification_Stop_Processing_Exception( 'Other payment gateway is already selected', $context );
		}

		$order_payment_id          = $order->get_transaction_id();
		$order_payment_status      = $order->get_meta( self::ORDER_META_STATUS_FIELD_NAME );
		$order_payment_status_date = $order->get_meta( self::ORDER_META_MODIFIED_AT_KEY );
		$order_processed           = in_array( $order->get_status(), wc_get_is_paid_statuses(), true );

		$context += array(
			'orderPaymentId'         => $order_payment_id,
			'orderPaymentStatus'     => $order_payment_status,
			'orderPaymentStatusDate' => $order_payment_status_date,
		);

		if ( $order_processed ) {
			if ( $is_confirmed && ! ( $order_payment_id === $payment_id ) ) {
				$order->add_order_note( __( 'Transaction confirmed, but order already paid. Transaction ID: ', 'pay-by-paynow-pl' ) . $payment_id );
			}
			$this->locking_mechanism->delete( $external_id );
			throw new WC_Pay_By_Paynow_Pl_Notification_Stop_Processing_Exception( 'Skipped processing. Order has paid status.', $context );
		}

		if ( $order_payment_status === $status && $order_payment_id === $payment_id ) {
			$this->locking_mechanism->delete( $external_id );
			throw new WC_Pay_By_Paynow_Pl_Notification_Stop_Processing_Exception( sprintf( 'Skipped processing. Transition status (%s) already consumed.', $status ), $context );
		}

		if ( ! ( $order_payment_id === $payment_id ) && ! $is_new && ! $force && ! $is_confirmed ) {
			$this->retry_processing_n_times( $order, 'Skipped processing. Order has another active payment.', $context );
		}

		if ( ! empty( $order_payment_status_date ) && $order_payment_status_date > $modified_at && ! $is_confirmed ) {
			if ( ! $is_new || $order_payment_id === $payment_id ) {
				$this->locking_mechanism->delete( $external_id );
				throw new WC_Pay_By_Paynow_Pl_Notification_Stop_Processing_Exception(
					'Skipped processing. Order has newer status. Time travels are prohibited.',
					$context
				);
			}
		}

		if ( ! $this->is_correct_status( $order_payment_status, $status ) && ! $is_new && ! $force && ! $is_confirmed ) {
			$this->retry_processing_n_times(
				$order,
				sprintf(
					'Order status transition from %s to %s is incorrect.',
					$order_payment_status,
					$status
				),
				$context
			);
		}

		if ( WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
			$order_id = WC_Pay_By_Paynow_PL_Helper::get_order_id( $order );
			add_post_meta( $order_id, self::ORDER_META_STATUS_FIELD_NAME, $status, true );
			add_post_meta( $order_id, self::ORDER_META_MODIFIED_AT_KEY, $modified_at, true );
		} else {
			$order->add_meta_data( self::ORDER_META_STATUS_FIELD_NAME, $status, true );
			$order->add_meta_data( self::ORDER_META_MODIFIED_AT_KEY, $modified_at, true );
		}

		WC_Pay_By_Paynow_PL_Logger::info( 'Order status transition is correct.', $context );

		switch ( $status ) {
			case Status::STATUS_NEW:
				$this->process_new_status( $order, $payment_id, $context );
				break;
			case Status::STATUS_PENDING:
				/* translators: %s: Payment ID */
				$order->update_status( 'pending', sprintf( __( 'Awaiting payment authorization - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
				break;
			case Status::STATUS_REJECTED:
				/* translators: %s: Payment ID */
				$order->update_status( 'failed', sprintf( __( 'Payment has not been authorized by the buyer - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
				break;
			case Status::STATUS_CONFIRMED:
				$this->process_confirm_status( $order, $payment_id, $context );
				break;
			case Status::STATUS_ERROR:
				/* translators: %s: Payment ID */
				$order->update_status( 'failed', sprintf( __( 'An error occurred during the payment process and the payment could not be completed - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
				break;
			case Status::STATUS_EXPIRED:
				/* translators: %s: Payment ID */
				$order->update_status( 'failed', sprintf( __( 'Payment has been expired - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
				break;
			case Status::STATUS_ABANDONED:
				/* translators: %s: Payment ID */
				$order->update_status( 'pending', sprintf( __( 'Payment has been abandoned - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
				break;
		}
		$order->save();
		$this->locking_mechanism->delete( $external_id );
		WC_Pay_By_Paynow_PL_Logger::info( 'Notification processed successfully', $context );
	}


	/**
	 * @param WC_Order $order
	 * @param $message
	 * @param $context
	 * @param $counter
	 * @return mixed
	 * @throws WC_Pay_By_Paynow_Pl_Notification_Retry_Processing_Exception
	 * @throws WC_Pay_By_Paynow_Pl_Notification_Stop_Processing_Exception
	 */
	private function retry_processing_n_times( WC_Order $order, $message, $context = array(), $counter = 3 ) {

		$history = $order->get_meta( self::ORDER_META_NOTIFICATION_HISTORY );

		if ( ! is_array( $history ) ) {
			$history = array();
		}

		$history_key = sprintf( '%s:%s', $context[ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_PAYMENT_ID_FIELD_NAME ], $context[ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_STATUS_FIELD_NAME ] );
		if ( ! isset( $history[ $history_key ] ) ) {
			$history[ $history_key ] = 0;
		}
		$history[ $history_key ] = (int) $history[ $history_key ] + 1;

		if ( WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
			$order_id = WC_Pay_By_Paynow_PL_Helper::get_order_id( $order );
			update_post_meta( $order_id, self::ORDER_META_NOTIFICATION_HISTORY, $history );
		} else {
			$order->add_meta_data( self::ORDER_META_NOTIFICATION_HISTORY, $history, true );
			$order->save();
		}

		$context['statusCounter'] = $history[ $history_key ];

		$this->locking_mechanism->delete( $context[ WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_EXTERNAL_ID_FIELD_NAME ] );
		if ( $history[ $history_key ] >= $counter ) {
			throw new WC_Pay_By_Paynow_Pl_Notification_Stop_Processing_Exception( $message, $context );
		} else {
			throw new WC_Pay_By_Paynow_Pl_Notification_Retry_Processing_Exception( $message, $context );
		}
	}

	/**
	 * @param $previous_status
	 * @param $next_status
	 *
	 * @return bool
	 */
	public static function is_correct_status( $previous_status, $next_status ): bool {

		$payment_status_flow = array(
			Status::STATUS_NEW       => array(
				Status::STATUS_PENDING,
				Status::STATUS_ERROR,
				Status::STATUS_EXPIRED,
				Status::STATUS_CONFIRMED,
				Status::STATUS_REJECTED,
			),
			Status::STATUS_PENDING   => array(
				Status::STATUS_CONFIRMED,
				Status::STATUS_REJECTED,
				Status::STATUS_EXPIRED,
				Status::STATUS_ABANDONED,
			),
			Status::STATUS_REJECTED  => array(
				Status::STATUS_ABANDONED,
				Status::STATUS_CONFIRMED,
			),
			Status::STATUS_CONFIRMED => array(),
			Status::STATUS_ERROR     => array(
				Status::STATUS_CONFIRMED,
				Status::STATUS_REJECTED,
				Status::STATUS_ABANDONED,
				Status::STATUS_NEW,
			),
			Status::STATUS_EXPIRED   => array(),
			Status::STATUS_ABANDONED => array(),
		);

		$previous_status_exists = isset( $payment_status_flow[ $previous_status ] );
		$is_change_possible     = in_array( $next_status, $payment_status_flow[ $previous_status ] ?? array(), true );
		if ( ! $previous_status_exists && Status::STATUS_NEW === $next_status ) {
			return true;
		}
		return $previous_status_exists && $is_change_possible;
	}

	public function redirect_order_received_page() {

		if ( ! is_wc_endpoint_url( 'order-received' ) ||
			! filter_input( INPUT_GET, 'key' ) ||
			! filter_input( INPUT_GET, 'paymentId' ) ||
			filter_input( INPUT_GET, 'confirmBlik' ) ) {
			return;
		}

		$order_id = wc_get_order_id_by_order_key( filter_input( INPUT_GET, 'key' ) );
		$order    = wc_get_order( $order_id );

		if ( WC_Pay_By_Paynow_PL_Helper::is_paynow_order( $order ) ) {
			$payment_id = $order->get_transaction_id();
			if ( $payment_id === $order_id . '_UNKNOWN' ) {
				$status = Status::STATUS_PENDING;
			} else {
				$status = $this->gateway->payment_status( $order_id, $payment_id );
			}
			$logger_context = array(
				'externalId' => $order_id,
				'paymentId'  => $payment_id,
				'status'     => $status,
			);
			if ( $status ) {
				WC_Pay_By_Paynow_PL_Logger::info( 'Received payment status from API. ', $logger_context );
				try {
					$this->process_notification( $payment_id, $status, $order_id, gmdate( 'Y-m-d\TH:i:s' ), true );
				} catch ( Error | Exception $e ) {
					WC_Pay_By_Paynow_PL_Logger::error( $e->getMessage(), $logger_context );
				}
			}
			wp_safe_redirect( $order->get_checkout_order_received_url() );
			exit();
		}
	}

	public function allow_payment_without_login( $allcaps, $caps, $args ) {

		if ( ! isset( $caps[0] ) || ! ( 'pay_for_order' === $caps[0] ) ) {
			return $allcaps;
		}
		if ( ! filter_input( INPUT_GET, 'key' ) ) {
			return $allcaps;
		}

		$order = wc_get_order( $args[2] );
		if ( ! $order ) {
			return $allcaps;
		}

		$order_key       = $order->get_order_key();
		$order_key_check = filter_input( INPUT_GET, 'key' );

		if ( $order_key === $order_key_check && WC_Pay_By_Paynow_PL_Helper::is_paynow_order( $order ) ) {
			$allcaps['pay_for_order'] = true;
		}

		return $allcaps;
	}

	/**
	 * @param WC_Order $order
	 * @param string $payment_id
	 * @param $context
	 * @return void
	 */
	private function process_new_status( WC_Order $order, string $payment_id, $context ) {

		if ( ! empty( $order->get_transaction_id() ) && ! ( $order->get_transaction_id() === $payment_id ) ) {
			WC_Pay_By_Paynow_PL_Logger::info( 'The order has already a payment. Attaching new payment.', $context );
		}

		if ( WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
			$order_id = WC_Pay_By_Paynow_PL_Helper::get_order_id( $order );
			update_post_meta( $order_id, '_transaction_id', $payment_id );
		} else {
			$order->set_transaction_id( $payment_id );
			$order->save();
		}

		/* translators: %s: Payment ID */
		$order->update_status( 'pending', sprintf( __( 'New payment created for order - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
	}

	/**
	 * @param WC_Order $order
	 * @param string $payment_id
	 * @param $context
	 * @return void
	 */
	private function process_confirm_status( WC_Order $order, string $payment_id, $context ) {

		if ( ! empty( $order->get_transaction_id() ) && ! ( $order->get_transaction_id() === $payment_id ) ) {
			WC_Pay_By_Paynow_PL_Logger::info( 'The order has already a payment. Attaching new payment.', $context );
			$this->process_new_status( $order, $payment_id, $context );
		}
		$order->payment_complete( $payment_id );
		/* translators: %s: Payment ID */
		$order->add_order_note( sprintf( __( 'Payment has been authorized by the buyer - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
	}

	private function get_api_option_key_name(): string {

		return $this->plugin_id . WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'settings';
	}

	protected function hooks() {

		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);
		add_action( 'template_redirect', array( $this, 'redirect_order_received_page' ) );
		add_filter( 'user_has_cap', array( $this, 'allow_payment_without_login' ), 10, 3 );
		add_filter( 'woocommerce_payment_gateways', 'wc_pay_by_paynow_pl_payment_gateways' );
	}

	protected function get_only_payment_methods_for_type( $types ): array {

		$payment_methods = $this->gateway->payment_methods();

		if ( ! empty( $payment_methods ) && is_array( $payment_methods ) ) {
			return array_values(
				array_filter(
					$payment_methods,
					function ( $payment_method ) use ( $types ) {

						return in_array( $payment_method->getType(), $types, true );
					}
				)
			);
		}

		return array();
	}

	public function validate_production_api_key_field( $key, $value ) {
		return self::api_credentials_validator( $value, __( 'Incorrect API key format (production)', 'pay-by-paynow-pl' ) );
	}

	public function validate_production_signature_key_field( $key, $value ) {
		return self::api_credentials_validator( $value, __( 'Incorrect API signature key format (production)', 'pay-by-paynow-pl' ) );
	}

	public function validate_sandbox_api_key_field( $key, $value ) {
		return self::api_credentials_validator( $value, __( 'Incorrect API key format (sandbox)', 'pay-by-paynow-pl' ) );
	}

	public function validate_sandbox_signature_key_field( $key, $value ) {
		return self::api_credentials_validator( $value, __( 'Incorrect API signature key format (sandbox)', 'pay-by-paynow-pl' ) );
	}

	private static function api_credentials_validator( $value, $message ) {
		if ( ! empty( $value ) && ! preg_match( '/^[[:xdigit:]]{8}(?:\-[[:xdigit:]]{4}){3}\-[[:xdigit:]]{12}$/i', $value ) ) {
			WC_Admin_Settings::add_error( $message );
			$value = '';
		}
		return $value;
	}

	protected function get_authorization_code_from_posted_data() {
		return filter_input( INPUT_POST, 'authorizationCode' ) ?? filter_var( wp_unslash( $_POST['authorizationcode'] ?? '' ) );
	}

	protected function get_payment_method_from_posted_data() {
		return filter_input( INPUT_POST, 'payment_method' ) ?? $this->id;
	}

	protected function get_payment_method_id_from_posted_data() {
		return filter_input( INPUT_POST, 'paymentMethodId' ) ?? filter_var( wp_unslash( $_POST['paymentmethodid'] ?? '' ) );
	}

	protected function get_payment_method_token_from_posted_data() {
		return filter_input( INPUT_POST, 'paymentMethodToken', FILTER_SANITIZE_STRING ) ?? filter_var( wp_unslash( $_POST['paymentmethodtoken'] ?? '' ), FILTER_SANITIZE_STRING );
	}

	protected function get_payment_method_fingerprint_from_posted_data() {
		return filter_input( INPUT_POST, 'paymentMethodFingerprint', FILTER_SANITIZE_STRING ) ?? filter_var( wp_unslash( $_POST['paymentmethodfingerprint'] ?? '' ), FILTER_SANITIZE_STRING );
	}
}
