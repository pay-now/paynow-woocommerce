<?php
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

use Paynow\Exception\PaynowException;

abstract class WC_Gateway_Pay_By_Paynow_PL extends WC_Payment_Gateway {
	protected $payment_method_id;

	protected $payment_gateway_options = [
		'enabled'
	];

	/**
	 * @var Paynow_Gateway
	 */
	protected $gateway;

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
		$this->supports = [
			'products',
			'refunds'
		];
	}

	public function init_settings() {
		parent::init_settings();
		$options = get_option( $this->get_api_option_key_name(), null );

		$this->settings = array_merge( $this->settings, ! empty( $options ) ? $options : [] );

		if ( is_array( $options ) && in_array( 'enabled', $options ) ) {
			$this->enabled = $options['enabled'];
		} else {
			$this->enabled = ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
		}
	}

	public function process_admin_options() {
		$this->init_settings();
		$api_settings            = [];
		$payment_method_settings = [];

		$post_data = $this->get_post_data();

		foreach ( $this->get_form_fields() as $key => $field ) {
			if ( 'title' !== $this->get_field_type( $field ) ) {
				try {
					if ( in_array( $key, $this->payment_gateway_options ) ) {
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

        $this->display_errors();

		$this->gateway->send_shop_urls_configuration_request( $this->get_return_url() );
	}

	public function update_option( $key, $value = '' ) {
		if ( empty( $this->settings ) ) {
			$this->init_settings();
		}

		$plugin_settings = [];
		foreach ( $this->settings as $key => $val ) {
			if ( in_array( $key, $this->payment_gateway_options ) ) {
				$plugin_settings[ $key ] = $value;
			}
		}

		return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $plugin_settings ), 'yes' );
	}

	public function init_form_fields() {
        $this->form_fields = include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/settings/pay-by-paynow-pl-settings.php';
	}

    public function validate_payment_validity_time_field( $key, $value ){
        if((int)$value < 1 || (int)$value > 86400 ){
            $this->add_error(__('Payment validity time must be greater than 0 and less than 86400 seconds', 'pay-by-paynow-pl'));
    } else {
            return $value;
        }
    }

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		try {
			// throw exception if not valid
			WC_Pay_By_Paynow_PL_Helper::validate_minimum_payment_amount( $order->get_total() );

			$payment_method_id = filter_input( INPUT_POST, 'paymentMethodId' );

			$payment_data = $this->gateway->payment_request(
				$order,
				$this->get_return_url( $order ),
				$payment_method_id ? intval( $payment_method_id ) : $this->payment_method_id
			);
			add_post_meta( $order_id, '_transaction_id', $payment_data->getPaymentId(), true );

			// add paymentId to order
			if ( WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
				update_post_meta( $order_id, '_transaction_id', $payment_data->getPaymentId() );
			} else {
				$order->set_transaction_id( $payment_data->getPaymentId() );
			}

			// Remove cart
			WC()->cart->empty_cart();

			if ( is_callable( [ $order, 'save' ] ) ) {
				$order->save();
			}

			return [
				'result'   => 'success',
				'redirect' => $payment_data->getRedirectUrl()
			];
		} catch ( PaynowException $exception ) {
			$errors = $exception->getErrors();
			if ( $errors ) {
				foreach ( $errors as $error ) {
					WC_Pay_By_Paynow_PL_Logger::error( $exception->getMessage() . ' {orderId={}}', [ $order_id ] );
					WC_Pay_By_Paynow_PL_Logger::error( $error->getType() . ' - ' . $error->getMessage() . ' {orderId={}, paymentMethodId={}}', [
						$order_id,
						$this->payment_method_id
					] );
				}
			}
			wc_add_notice( __( 'Error occurred during the payment process and the payment could not be completed.', 'pay-by-paynow-pl' ), 'error' );
			$order->add_order_note( $exception->getMessage() );

			return false;
		}
	}

	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order      = wc_get_order( $order_id );
		$payment_id = $order->get_transaction_id();

		if ( ! $this->check_can_make_refund( $order, WC_Pay_By_Paynow_PL_Helper::get_amount( $amount ) ) ) {
			return new WP_Error( 'error', __( 'Refund can\'t be processed. Please check logs for more information', 'pay-by-paynow-pl' ) );
		}

		WC_Pay_By_Paynow_PL_Logger::debug( 'Processing refund request {orderId={}, paymentId={}, amount={}}', [
			$order_id,
			$payment_id,
			$amount
		] );

		try {
			$refund_data = $this->gateway->refund_request(
				$order_id,
				$payment_id,
				$amount
			);

			WC_Pay_By_Paynow_PL_Logger::info( 'Refund has been created successfully {orderId={}, paymentId={}, refundId={}, amount={}}', [
				$order_id,
				$payment_id,
				$refund_data->getRefundId(),
				$amount
			] );

			if ( ! empty( $refund_data->getRefundId() ) ) {
				$order->add_order_note( 'Refund request processed correctly - ' . $refund_data->getRefundId() );

				return true;
			}

			return false;
		} catch ( PaynowException $exception ) {
			$errors = $exception->getErrors();
			if ( $errors ) {
				foreach ( $errors as $error ) {
					$order->add_order_note( 'Error occurred during the refund process - ' . $error->getMessage() );
					WC_Pay_By_Paynow_PL_Logger::error( $error->getType() . ' - ' . $error->getMessage() . ' {orderId={}, paymentId={}}', [
						$order_id,
						$payment_id
					] );

					return new WP_Error( 'error', __( 'Refund process failed. Please check logs for more information', 'pay-by-paynow-pl' ) );
				}
			}
		}

		return false;
	}

	public function check_can_make_refund( $order, $amount ) {
		if ( ! $this->can_refund_order( $order ) ) {
			return false;
		}

		$order_id = WC_Pay_By_Paynow_PL_Helper::get_order_id( $order );

		if ( empty( $order_id ) ) {
			WC_Pay_By_Paynow_PL_Logger::warning( 'Order was not found to make a refund {orderId={}}', [ $order_id ] );

			return false;
		}

		if ( empty( $order->get_transaction_id() ) ) {
			WC_Pay_By_Paynow_PL_Logger::warning( 'The order has no payment to make a refund {orderId={}}', [ $order_id ] );

			return false;
		}

		if ( empty( $amount ) ) {
			WC_Pay_By_Paynow_PL_Logger::warning( 'The amount of the refund must be above zero {orderId={}}', [ $order_id ] );

			return false;
		}

		if ( ! $order->has_status( wc_get_is_paid_statuses() ) ) {
			WC_Pay_By_Paynow_PL_Logger::warning( 'Status of the order must be in paid status {orderId={}, status={}}', [
				$order_id,
				$order->get_status()
			] );

			return false;
		}

		if ( empty( $order->get_remaining_refund_amount() ) ) {
			WC_Pay_By_Paynow_PL_Logger::warning( 'There is no more remaining amount to refund {orderId={}, amount={}}', [
				$order_id,
				$order->get_remaining_refund_amount()
			] );
		}

		return true;
	}

	/**
	 * @param WC_Order $order
	 */
	protected function increase_stock( $order ) {
		if ( ! WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
			wc_increase_stock_levels( $order );
		}
	}

	public function is_available() {
		if ( ! is_admin() ) {
			$available = true;
			try {
				WC_Pay_By_Paynow_PL_Helper::validate_minimum_payment_amount( WC()->cart->total );
			} catch ( PaynowException $exception ) {
				$available = false;
			}

			return parent::is_available() && $available;
		}

		return parent::is_available();
	}

	protected function hooks() {
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	private function get_api_option_key_name() {
		return $this->plugin_id . WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'settings';
	}
}
