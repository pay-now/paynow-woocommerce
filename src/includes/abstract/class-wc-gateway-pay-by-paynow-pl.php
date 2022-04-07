<?php
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

use Paynow\Exception\PaynowException;
use Paynow\Model\Payment\Status;

abstract class WC_Gateway_Pay_By_Paynow_PL extends WC_Payment_Gateway {
	protected $payment_method_id;

	protected $payment_gateway_options = array(
		'enabled',
	);

	/**
	 * @var Paynow_Gateway
	 */
	public $gateway;

	public function __construct() {
		$this->init_form_fields();
		$this->init_settings();
		$this->hooks();
		$this->init_supports();

		if ( $this->payment_method_id ) {
			$this->icon = 'https://static.paynow.pl/payment-method-icons/' . $this->payment_method_id . '.png';
		}

		$this->gateway = new Paynow_Gateway( $this->settings );
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
        include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH . 'payment_processor_info.phtml';
    }

	public function process_payment( $order_id ): array {
		$order    = wc_get_order( $order_id );
		$response = array();

		try {
			WC_Pay_By_Paynow_PL_Helper::validate_minimum_payment_amount( $order->get_total() );

			$payment_method_id  = filter_input( INPUT_POST, 'paymentMethodId' );
			$authorization_code = preg_replace( '/\s+/', '', filter_input( INPUT_POST, 'authorizationCode' ) );

			$payment_data = $this->gateway->payment_request(
				$order,
				$this->get_return_url( $order ),
				$payment_method_id ? intval( $payment_method_id ) : $this->payment_method_id,
				$authorization_code ?? null
			);
			add_post_meta( $order_id, '_transaction_id', $payment_data->getPaymentId(), true );

			if ( WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
				update_post_meta( $order_id, '_transaction_id', $payment_data->getPaymentId() );
			} else {
				$order->set_transaction_id( $payment_data->getPaymentId() );
			}

			WC()->cart->empty_cart();

			if ( is_callable( array( $order, 'save' ) ) ) {
				$order->save();
			}

			if ( ! in_array(
				$payment_data->getStatus(),
				array(
					Paynow\Model\Payment\Status::STATUS_NEW,
					Paynow\Model\Payment\Status::STATUS_PENDING,
				),
				true
			) ) {
				$response['result'] = 'failure';
			} else {
				$response['result'] = 'success';
				if ( $payment_data->getRedirectUrl() ) {
					$response['redirect'] = $payment_data->getRedirectUrl();
				} else {
					$response['redirect'] = $this->get_return_url( $order ) . ( $authorization_code ? '&' . http_build_query(
						array(
							'paymentId'   => $payment_data->getPaymentId(),
							'confirmBlik' => 1,
						)
					) : '' );
				}
			}

			return $response;
		} catch ( PaynowException $exception ) {
			$errors = $exception->getErrors();
			WC_Pay_By_Paynow_PL_Logger::error( $exception->getMessage() . ' {orderId={}}', array( $order_id ) );
			if ( $errors ) {
				foreach ( $errors as $error ) {
					WC_Pay_By_Paynow_PL_Logger::error(
						$error->getType() . ' - ' . $error->getMessage() . ' {orderId={}, paymentMethodId={}}',
						array(
							$order_id,
							$this->payment_method_id,
						)
					);
				}

				if ( $exception->getErrors() && $exception->getErrors()[0] ) {
					WC_Pay_By_Paynow_PL_Logger::error( $exception->getMessage() );
					switch ( $exception->getErrors()[0]->getType() ) {
						case 'AUTHORIZATION_CODE_INVALID':
							wc_add_notice( __( 'Wrong BLIK code', 'pay-by-paynow-pl' ), 'error' );
							break;
						case 'AUTHORIZATION_CODE_EXPIRED':
							wc_add_notice( __( 'BLIK code has expired', 'pay-by-paynow-pl' ), 'error' );
							break;
						case 'AUTHORIZATION_CODE_USED':
							wc_add_notice( __( 'BLIK code already used', 'pay-by-paynow-pl' ), 'error' );
							break;
						default:
							wc_add_notice( __( 'An error occurred during the payment process and the payment could not be completed.', 'pay-by-paynow-pl' ), 'error' );
					}
				}
			}

			$order->add_order_note( $exception->getMessage() );

			return array();
		}
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
				$order->add_order_note( sprintf( __( 'Refund request processed correctly - %s' ), $refund_data->getRefundId() ) );

				return true;
			}

			return false;
		} catch ( PaynowException $exception ) {
			$errors = $exception->getErrors();
			if ( $errors ) {
				foreach ( $errors as $error ) {
					/* translators: %s: Error message */
					$order->add_order_note( sprintf( __( 'An error occurred during the refund process - %s' ), $error->getMessage() ) );
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
	 * @param int   $amount
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
	 * @param string $type Payment method Type
	 *
	 * @return bool
	 */
	protected function is_payment_method_available( string $type ): bool {
		if ( ! is_admin() && parent::is_available() ) {
			$payment_method = $this->get_only_payment_methods_for_type( $type );
			return ! empty( $payment_method ) && reset( $payment_method )->isEnabled();
		}

		return parent::is_available();
	}

	/**
	 * @param WC_order $order
	 * @param string   $payment_id
	 * @param string   $notification_status
	 *
	 * @throws Exception
	 */
	public function process_order_status_change( WC_order $order, string $payment_id, string $notification_status ) {

		if ( ! $this->is_correct_status( $order->get_status(), $notification_status ) ) {
			throw new Exception( 'Order status transition from ' . $order->get_status() . ' to ' . $notification_status . ' is incorrect' );
		}

		WC_Pay_By_Paynow_PL_Logger::info(
			'Order status transition is correct {orderId={}, paymentId={}, orderStatus={}, paymentStatus={}}',
			array(
				WC_Pay_By_Paynow_PL_Helper::get_order_id( $order ),
				$order->get_transaction_id(),
				$order->get_status(),
				$notification_status,
			)
		);

		switch ( $notification_status ) {
			case Status::STATUS_NEW:
				$this->process_new_status( $order, $payment_id );
				break;
			case Status::STATUS_REJECTED:
				/* translators: %s: Payment ID */
				$order->update_status( 'failed', sprintf( __( 'Payment has not been authorized by the buyer - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
				break;
			case Status::STATUS_CONFIRMED:
				$order->payment_complete( $payment_id );
				/* translators: %s: Payment ID */
				$order->add_order_note( sprintf( __( 'Payment has been authorized by the buyer - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
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
	}

	/**
	 * @param $previous_status
	 * @param $next_status
	 *
	 * @return bool
	 */
	public static function is_correct_status( $previous_status, $next_status ): bool {
		$payment_status_flow    = array(
			'pending' => array(
				Status::STATUS_NEW,
				Status::STATUS_PENDING,
				Status::STATUS_ERROR,
				Status::STATUS_CONFIRMED,
				Status::STATUS_REJECTED,
				Status::STATUS_EXPIRED,
				Status::STATUS_ABANDONED,
			),
			'failed'  => array(
				Status::STATUS_NEW,
				Status::STATUS_CONFIRMED,
				Status::STATUS_ERROR,
				Status::STATUS_REJECTED,
				Status::STATUS_ABANDONED,
			),
		);
		$previous_status_exists = isset( $payment_status_flow[ $previous_status ] );
		$is_change_possible     = in_array( $next_status, $payment_status_flow[ $previous_status ], true );

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
			$status     = $this->gateway->payment_status( $order_id, $payment_id );
			if ( $status ) {
				WC_Pay_By_Paynow_PL_Logger::info(
					'Received payment status from API {orderId={}, paymentId={}, status={}}',
					array(
						$order->get_id(),
						$payment_id,
						$status,
					)
				);

				if ( ! $order->has_status( wc_get_is_paid_statuses() ) && $order->get_transaction_id() === $payment_id ) {
					$this->process_order_status_change( $order, $payment_id, $status );
				} else {
					WC_Pay_By_Paynow_PL_Logger::info(
						'Order has one of paid statuses. Skipped notification processing {orderId={}, orderStatus={}, payment={}}',
						array(
							$order->get_id(),
							$order->get_status(),
							$payment_id,
						)
					);
				}
			}

			wp_safe_redirect( $order->get_checkout_order_received_url() );
			exit();
		}
	}

	public function allow_payment_without_login( $allcaps, $caps, $args ) {
		if ( ! isset( $caps[0] ) || 'pay_for_order' !== $caps[0] ) {
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

		if ( $order_key == $order_key_check && WC_Pay_By_Paynow_PL_Helper::is_paynow_order( $order ) ) {
			$allcaps['pay_for_order'] = true;
		}

		return $allcaps;
	}

	/**
	 * @param WC_order $order
	 * @param string   $payment_id
	 *
	 * @throws WC_Data_Exception
	 */
	private function process_new_status( WC_order $order, string $payment_id ) {
		$order_id = WC_Pay_By_Paynow_PL_Helper::get_order_id( $order );
		if ( ! empty( $order->get_transaction_id() ) && $order->get_transaction_id() !== $payment_id ) {
			WC_Pay_By_Paynow_PL_Logger::info(
				'The order has already a payment. Attaching new payment {orderId={}, newPaymentId={}}',
				array(
					$order_id,
					$payment_id,
				)
			);
		}

		if ( WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
			update_post_meta( $order_id, '_transaction_id', $payment_id );
		} else {
			$order->set_transaction_id( $payment_id );
			$order->save();
		}

		/* translators: %s: Payment ID */
		$order->update_status( 'pending', sprintf( __( 'Awaiting payment authorization - %s.', 'pay-by-paynow-pl' ), $order->get_transaction_id() ) );
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

	protected function get_only_payment_methods_for_type( $type ): array {
		$payment_methods = $this->gateway->payment_methods();

		if ( ! empty( $payment_methods ) && is_array( $payment_methods ) ) {
			return array_filter(
				$payment_methods,
				function ( $payment_method ) use ( $type ) {
					return $type === $payment_method->getType();
				}
			);
		}

		return array();
	}
}
