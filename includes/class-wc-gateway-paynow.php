<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Paynow\Environment;
use Paynow\Exception\PaynowException;

class WC_Gateway_Paynow extends WC_Payment_Gateway {

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
	 * @var \Paynow\Client
	 */
	private $api_client;

	public function __construct() {
		$this->id                 = 'paynow';
		$this->has_fields         = false;
		$this->method_title       = __( 'Paynow', 'woocommerce-gateway-paynow' );
		$this->method_description = __( 'Accepts payments by Paynow', 'woocommerce-gateway-paynow' );
		$this->icon               = apply_filters( 'woocommerce_paaynow_icon', WC_PAYNOW_PLUGIN_URL . '/assets/images/logo.png' );
		$this->supports           = [
			'products'
		];

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get setting values.
		$this->title         = __( 'Pay by Paynow', 'woocommerce-gateway-paynow' );
		$this->description   = __( 'Secure BLIK payments and fast online transfers', 'woocommerce-gateway-paynow' );
		$this->enabled       = $this->get_option( 'enabled' );
		$this->sandbox       = $this->get_option( 'sandbox' );
		$this->api_key       = $this->get_option( 'sandbox' ) ? $this->get_option( 'sandbox_api_key' ) : $this->get_option( 'production_api_key' );
		$this->signature_key = $this->get_option( 'sandbox' ) ? $this->get_option( 'sandbox_signature_key' ) : $this->get_option( 'production_signature_key' );

		// Load API Client
		$this->init_paynow_client();

		// Hooks
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		add_action( 'woocommerce_api_wc_gateway_' . $this->id, [ $this, 'notification_webhook' ] );
	}

	public function process_admin_options() {
		parent::process_admin_options();

		// update shop configuration
		try {
			$shop_configuration = new \Paynow\Service\ShopConfiguration( $this->api_client );
			$shop_configuration->changeUrls( $this->get_return_url(), WC_Paynow_Helper::get_notification_url() );
		} catch ( PaynowException $exception ) {
			wc_add_notice( $exception->getMessage(), 'notice' );
		}
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = require( dirname( __FILE__ ) . '/admin/paynow-settings.php' );
	}

	public function display_admin_settings_webhook_description() {
		return WC_Paynow_Helper::get_notification_url();
	}

	private function init_paynow_client() {
		$user_agent       = 'Wordpress-' . get_bloginfo( 'version' ) . '/WooCommerce-' . WC()->version . '/Plugin-' . WC_PAYNOW_PLUGIN_VERSION;
		$this->api_client = new \Paynow\Client(
			$this->api_key,
			$this->signature_key,
			$this->sandbox ? Environment::SANDBOX : Environment::PRODUCTION,
			$user_agent
		);
	}

	public function send_payment_request( $order ) {
		$currency     = $order->get_currency();
		$order_id     = $order->get_id();
		$billing_data = $order->get_address();
		$payment_data = [
			'amount'      => WC_Paynow_Helper::get_amount( $order->get_total() ),
			'currency'    => $currency,
			'externalId'  => $order_id,
			'description' => __( 'Order No: ', 'woocommerce-gateway-paynow' ) . $order_id,
			'buyer'       => [
				'email' => $billing_data['email']
			],
			'continueUrl' => $this->get_return_url($order)
		];
		$payment      = new \Paynow\Service\Payment( $this->api_client );

		return $payment->authorize( $payment_data );
	}

	function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		try {
			// This will throw exception if not valid.
			$this->validate_minimum_payment_amount( $order );

			$payment_data = $this->send_payment_request( $order );
			add_post_meta( $order_id, '_transaction_id', $payment_data->id, true );

			// add paymentId to order
			if ( WC_Paynow_Helper::is_old_wc_version() ) {
				update_post_meta( $order_id, '_transaction_id', $payment_data->paymentId );
			} else {
				$order->set_transaction_id( $payment_data->paymentId );
			}

			// Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'on-hold', __( 'Awaiting Paynow payment', 'woocommerce-gateway-paynow' ) );

			// Reduce stock levels
			$this->reduce_stock( $order );

			// Remove cart
			WC()->cart->empty_cart();

			$order->save();

			return [
				'result'   => 'success',
				'redirect' => $payment_data->redirectUrl
			];
		} catch ( PaynowException $exception ) {
			wc_add_notice( $exception->getMessage(), 'error' );
			$order->add_order_note( $exception->getMessage() );

			return false;
		}
	}

	/**
	 * @param WC_Order $order
	 */
	private function reduce_stock( $order ) {
		if ( WC_Paynow_Helper::is_old_wc_version() ) {
			$order->reduce_order_stock();
		} else {
			wc_reduce_stock_levels( $order->get_id() );
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
		if ( WC_Paynow_Helper::get_amount( $order->get_total() ) < WC_Paynow_Helper::get_minimum_amount() ) {
			throw new PaynowException( sprintf( __( 'Sorry, the minimum allowed order total is %1$s to use this payment method.', 'woocommerce-gateway-paynow' ), wc_price( WC_Paynow_Helper::get_minimum_amount() / 100 ) ) );
		}
	}
}