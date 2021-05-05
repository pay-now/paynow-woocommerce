<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Client;
use Paynow\Environment;
use Paynow\Exception\PaynowException;
use Paynow\Response\Payment\Authorize;
use Paynow\Response\PaymentMethods\PaymentMethods;
use Paynow\Response\Refund\Status;
use Paynow\Service\Payment;
use Paynow\Service\Refund;
use Paynow\Service\ShopConfiguration;

class Paynow_Gateway {

	protected $client;

	/**
	 * Api access Signature Key
	 * @var string
	 */
	protected $signature_key;

	public function __construct( $api_key, $signature_key, $is_sandbox = false ) {
		$this->signature_key = $signature_key;
		$this->client        = $this->client = new Client(
			$api_key,
			$this->signature_key,
			$is_sandbox ? Environment::SANDBOX : Environment::PRODUCTION,
			'Wordpress-' . get_bloginfo( 'version' ) . '/WooCommerce-' . WC()->version . '/Plugin-' . wc_pay_by_paynow_pl_plugin_version() );
	}

	/**
	 * Sends payment request
	 *
	 * @param $order
	 * @param $return_url
	 * @param null $payment_method_id
	 *
	 * @return Authorize
	 * @throws PaynowException
	 */
	public function payment_request( $order, $return_url, $payment_method_id = null ) {
		$currency     = WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ? $order->get_order_currency() : $order->get_currency();
		$order_id     = WC_Pay_By_Paynow_PL_Helper::get_order_id( $order );
		$billing_data = $order->get_address();
		$payment_data = [
			'amount'      => WC_Pay_By_Paynow_PL_Helper::get_amount( $order->get_total() ),
			'currency'    => strtoupper( $currency ),
			'externalId'  => $order_id,
			'description' => __( 'Order No: ', 'pay-by-paynow-pl' ) . $order->get_order_number(),
			'buyer'       => [
				'email'     => $billing_data['email'],
				'firstName' => $billing_data['first_name'],
				'lastName'  => $billing_data['last_name']
			],
			'continueUrl' => $return_url
		];
		if ( ! empty( $payment_method_id ) ) {
			$payment_data['paymentMethodId'] = $payment_method_id;
		}
		$idempotency_key = uniqid( $order_id, true );
		$payment         = new Payment( $this->client );

		return $payment->authorize( $payment_data, $idempotency_key );
	}

	/**
	 * Sends refund request
	 *
	 * @param $order_id
	 * @param $payment_id
	 * @param $amount
	 *
	 * @return Status
	 * @throws PaynowException
	 */
	public function refund_request( $order_id, $payment_id, $amount ) {
		$refund = new Refund( $this->client );

		return $refund->create(
			$payment_id,
			uniqid( $order_id, true ),
			WC_Pay_By_Paynow_PL_Helper::get_amount( $amount )
		);
	}

	/**
	 * Sends shop urls configuration
	 *
	 * @param $return_url
	 */
	public function send_shop_urls_configuration_request( $return_url ) {
		try {
			$shop_configuration = new ShopConfiguration( $this->client );
			$shop_configuration->changeUrls( $return_url, WC_Pay_By_Paynow_PL_Helper::get_notification_url() );
		} catch ( PaynowException $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error( $exception->getMessage() );
		}
	}

	/**
	 * @return string
	 */
	public function get_signature_key(): string {
		return $this->signature_key;
	}

	/**
	 * @return PaymentMethods
	 * @throws PaynowException
	 */
	public function payment_methods() {
		$payment = new Payment( $this->client );

		return $payment->getPaymentMethods( get_woocommerce_currency(), WC_Pay_By_Paynow_PL_Helper::get_amount( WC()->cart->total ) );
	}

}