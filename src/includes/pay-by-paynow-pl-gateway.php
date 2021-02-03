<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Paynow\Client;
use Paynow\Environment;
use Paynow\Exception\PaynowException;
use Paynow\Service\Payment;
use Paynow\Service\ShopConfiguration;

class WC_Gateway_Pay_By_Paynow_PL extends WC_Payment_Gateway {

	/**
	 * Is test mode active?
	 * @var bool
	 */
	public $sandbox;

	/**
	 * Api access Api Key
	 * @var string
	 */
	public $api_key;

	/**
	 * Api access Signature Key
	 * @var string
	 */
	public $signature_key;

	/**
	 * API Client
	 * @var Client
	 */
	private $api_client;

	public function __construct() {
		$this->id                 = 'pay_by_paynow_pl';
		$this->has_fields         = false;
		$this->method_title       = __( 'paynow.pl', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accepts secure BLIK, credit cards payments and fast online transfers by paynow.pl', 'pay-by-paynow-pl' );
		$this->icon               = apply_filters( 'woocommerce_' . $this->id . '_icon', WC_PAY_BY_PAYNOW_PL_PLUGIN_URL . '/assets/images/logo.png' );
		$this->supports           = [
			'products'
		];

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get setting values.
		$this->title         = __( 'Pay by paynow.pl', 'pay-by-paynow-pl' );
		$this->description   = __( 'Secure BLIK, credit cards payments and fast online transfers', 'pay-by-paynow-pl' );
		$this->enabled       = $this->get_option( 'enabled' );
		$this->sandbox       = $this->get_option( 'sandbox' ) === "yes";
		$this->api_key       = $this->sandbox ? $this->get_option( 'sandbox_api_key' ) : $this->get_option( 'production_api_key' );
		$this->signature_key = $this->sandbox ? $this->get_option( 'sandbox_signature_key' ) : $this->get_option( 'production_signature_key' );

		// Load API Client
		$this->init_paynow_client();

		// Hooks
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		add_action( 'woocommerce_api_wc_gateway_' . $this->id, [ $this, 'handle_notification' ] );
	}

	public function process_admin_options() {
		parent::process_admin_options();
		$this->init_paynow_client();
		// update shop configuration
		try {
			$shop_configuration = new ShopConfiguration( $this->api_client );
			$shop_configuration->changeUrls( $this->get_return_url(), WC_Pay_By_Paynow_PL_Helper::get_notification_url() );
		} catch ( PaynowException $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error( $exception->getMessage() );
		}
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = require( dirname( __FILE__ ) . '/admin/pay-by-paynow-pl-settings.php' );
	}

	public function display_admin_settings_webhook_description() {
		return WC_Pay_By_Paynow_PL_Helper::get_notification_url();
	}

	private function init_paynow_client() {
		$user_agent       = 'Wordpress-' . get_bloginfo( 'version' ) . '/WooCommerce-' . WC()->version . '/Plugin-' . WC_PAY_BY_PAYNOW_PL_PLUGIN_VERSION;
		$this->api_client = new Client(
			$this->api_key,
			$this->signature_key,
			$this->sandbox ? Environment::SANDBOX : Environment::PRODUCTION,
			$user_agent
		);
	}

	public function send_payment_request( $order ) {
		$currency        = WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ? $order->get_order_currency() : $order->get_currency();
		$order_id        = WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ? $order->id : $order->get_id();
		$billing_data    = $order->get_address();
		$payment_data    = [
			'amount'      => WC_Pay_By_Paynow_PL_Helper::get_amount( $order->get_total() ),
			'currency'    => strtoupper( $currency ),
			'externalId'  => $order_id,
			'description' => __( 'Order No: ', 'pay-by-paynow-pl' ) . $order->get_order_number(),
			'buyer'       => [
				'email'     => $billing_data['email'],
				'firstName' => $billing_data['first_name'],
				'lastName'  => $billing_data['last_name']
			],
			'continueUrl' => $this->get_return_url( $order )
		];
		$idempotency_key = uniqid( $order_id, true );
		$payment         = new Payment( $this->api_client );

		return $payment->authorize( $payment_data, $idempotency_key );
	}

	function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		try {
			// This will throw exception if not valid.
			$this->validate_minimum_payment_amount( $order );

			$payment_data = $this->send_payment_request( $order );
			add_post_meta( $order_id, '_transaction_id', $payment_data->id, true );

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
					WC_Pay_By_Paynow_PL_Logger::error( $error->getType() . ' - ' . $error->getMessage() . ' {orderId={}}', [ $order_id ] );
				}
			}
			wc_add_notice( __( 'Error occurred during the payment process and the payment could not be completed.', 'pay-by-paynow-pl' ), 'error' );
			$order->add_order_note( $exception->getMessage() );

			return false;
		}
	}

	/**
	 * @param WC_Order $order
	 */
	protected function increase_stock( $order ) {
		if ( ! WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
			wc_increase_stock_levels( $order );
		}
	}

	/**
	 * Validate minimum payment amount
	 *
	 * @param $order
	 *
	 * @throws PaynowException
	 */
	public function validate_minimum_payment_amount( $order ) {
		if ( WC_Pay_By_Paynow_PL_Helper::get_amount( $order->get_total() ) < WC_Pay_By_Paynow_PL_Helper::get_minimum_amount() ) {
			throw new PaynowException( sprintf( __( 'Sorry, the minimum allowed order total is %1$s to use this payment method.', 'pay-by-paynow-pl' ), wc_price( WC_Pay_By_Paynow_PL_Helper::get_minimum_amount() / 100 ) ) );
		}
	}
}