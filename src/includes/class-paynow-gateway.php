<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Client;
use Paynow\Environment;
use Paynow\Exception\ConfigurationException;
use Paynow\Exception\PaynowException;
use Paynow\Model\PaymentMethods\PaymentMethod;
use Paynow\Response\Payment\Authorize;
use Paynow\Response\Refund\Status;
use Paynow\Service\Payment;
use Paynow\Service\Refund;
use Paynow\Service\ShopConfiguration;

/**
 * Class Paynow_Gateway
 */
class Paynow_Gateway {

	protected $settings;

	protected $client;

	/**
	 * Api access Signature Key
	 *
	 * @var string
	 */
	protected $signature_key;

	public function __construct( array $settings ) {
		$this->settings = $settings;
		if ( ! empty( $this->settings ) && isset( $this->settings['sandbox'] ) && ( isset( $this->settings['sandbox_api_key'] ) || isset( $this->settings['production_api_key'] ) ) ) {
			$is_sandbox          = $this->settings['sandbox'] === 'yes';
			$api_key             = $is_sandbox ? $this->settings['sandbox_api_key'] : $this->settings['production_api_key'];
			$this->signature_key = $is_sandbox ? $this->settings['sandbox_signature_key'] : $this->settings['production_signature_key'];

			if ( $api_key && $this->signature_key ) {
				$this->client = $this->client = new Client(
					$api_key,
					$this->signature_key,
					$is_sandbox ? Environment::SANDBOX : Environment::PRODUCTION,
					'Wordpress-' . get_bloginfo( 'version' ) . '/WooCommerce-' . WC()->version . '/Plugin-' . wc_pay_by_paynow_pl_plugin_version()
				);
			}
		}
	}

	/**
	 * Sends payment request
	 *
	 * @param WC_Order $order
	 * @param string   $return_url Return URL after payment
	 * @param null     $payment_method_id
	 * @param null     $authorization_code
	 *
	 * @return Authorize|void
	 * @throws PaynowException
	 * @throws ConfigurationException
	 */
	public function payment_request( WC_Order $order, $return_url, $payment_method_id = null, $authorization_code = null ) {
		if ( ! $this->client ) {
			return;
		}

		$currency     = WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ? $order->get_order_currency() : $order->get_currency();
		$order_id     = WC_Pay_By_Paynow_PL_Helper::get_order_id( $order );
		$billing_data = $order->get_address();
		$payment_data = array(
			'amount'      => WC_Pay_By_Paynow_PL_Helper::get_amount( $order->get_total() ),
			'currency'    => strtoupper( $currency ),
			'externalId'  => $order_id,
			'description' => __( 'Order No: ', 'pay-by-paynow-pl' ) . $order->get_order_number(),
			'buyer'       => array(
				'email'     => $billing_data['email'],
				'firstName' => $billing_data['first_name'],
				'lastName'  => $billing_data['last_name'],
				'locale'    => $this->get_locale(),
			),
			'continueUrl' => $return_url,
		);

		if ( ! empty( $payment_method_id ) ) {
			$payment_data['paymentMethodId'] = $payment_method_id;
		}

		if ( ! empty( $authorization_code ) ) {
			$payment_data['authorizationCode'] = $authorization_code;
		}

		if ( $this->settings['send_order_items'] === 'yes' ) {
			$order_items = array();
			foreach ( $order->get_items() as $item ) {
				$product       = $item->get_product();
				$order_items[] = array(
					'name'     => $product->get_title(),
					'category' => WC_Pay_By_Paynow_PL_Helper::get_product_categories( $product->get_id() ),
					'quantity' => $item->get_quantity(),
					'price'    => WC_Pay_By_Paynow_PL_Helper::get_amount( WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ? wc_price( wc_get_price_including_tax( $product ) ) : $product->get_price_including_tax() ),
				);
			}

			$order_items = array_filter(
				$order_items,
				function ( $item ) {
					return ! empty( $item['category'] );
				}
			);

			if ( ! empty( $order_items ) ) {
				$payment_data['orderItems'] = $order_items;
			}
		}

		if ( $this->settings['use_payment_validity_time_flag'] === 'yes' ) {
			$payment_data['validityTime'] = $this->settings['payment_validity_time'];
		}

		$idempotency_key = substr( uniqid( $order_id, true ), 0, 45 );
		$payment         = new Payment( $this->client );

		return $payment->authorize( $payment_data, $idempotency_key );
	}

	/**
	 * Sends refund request
	 *
	 * @param int    $order_id Order ID.
	 * @param string $payment_id Payment ID
	 * @param float  $amount Refund amount.
	 *
	 * @return Status|null
	 * @throws PaynowException
	 */
	public function refund_request( int $order_id, string $payment_id, float $amount ): ?Status {
		if ( ! $this->client ) {
			return null;
		}
		$refund = new Refund( $this->client );

		return $refund->create(
			$payment_id,
			substr( uniqid( $order_id, true ), 0, 36 ),
			WC_Pay_By_Paynow_PL_Helper::get_amount( $amount )
		);
	}

	/**
	 * Sends shop urls configuration
	 *
	 * @param string $return_url Return URL.
	 */
	public function send_shop_urls_configuration_request( string $return_url ) {
		if ( ! $this->client ) {
			return;
		}
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
	 * Return available payment methods
	 *
	 * @return PaymentMethod[]|null
	 */
	public function payment_methods(): ?array {
		if ( ! $this->client ) {
			return null;
		}

		$payment_methods = array();
		try {
			$currency  = get_woocommerce_currency();
			$amount    = WC_Pay_By_Paynow_PL_Helper::get_amount( WC_Pay_By_Paynow_PL_Helper::get_payment_amount() );
			$cache_key = 'paynow_payment_methods_' . substr( $this->get_signature_key(), 0, 8 ) . '_' . $currency . '_' . $amount;
			if ( ! empty( WC()->session->get( $cache_key ) ) ) {
				$payment_methods = WC()->session->get( $cache_key );
			} else {
				WC_Pay_By_Paynow_PL_Logger::info(
					'Retrieving payment methods {currency={}, amount={}}',
					array(
						$currency,
						$amount,
					)
				);
				$payment_methods = ( new Payment( $this->client ) )->getPaymentMethods( $currency, $amount )->getAll();
				WC()->session->set( $cache_key, $payment_methods );
			}
		} catch ( PaynowException $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error( $exception->getMessage() );
		}

		return $payment_methods ?? null;
	}

	/**
	 * @param int    $order_id Order ID.
	 * @param string $payment_id Payment ID.
	 *
	 * @return string
	 */
	public function payment_status( int $order_id, string $payment_id ): ?string {
		if ( ! $this->client ) {
			return null;
		}

		try {
			$payment = new Payment( $this->client );

			$response = $payment->status( $payment_id );
			return $response->getStatus() ?? null;
		} catch ( PaynowException $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error(
				$exception->getMessage() . ' {orderId={}, paymentId={}}',
				array(
					$order_id,
					$payment_id,
				)
			);
		}

		return null;
	}

	/**
	 * Return GDPR notices
	 *
	 * @return array|null
	 */
	public function gdpr_notices(): ?array {
		$notices = array();
		$locale  = $this->get_locale();
		try {
			$cache_key = strtolower( 'paynow_gdpr_notices_' . substr( $this->get_signature_key(), 0, 8 ) . '_' . str_replace( '-', '_', $locale ) );
			if ( ! empty( WC()->session->get( $cache_key ) ) ) {
				$notices = WC()->session->get( $cache_key );
			} else {
				WC_Pay_By_Paynow_PL_Logger::info( 'Retrieving GDPR notices' );
				$notices = ( new Paynow\Service\DataProcessing( $this->client ) )->getNotices( $locale )->getAll();
				WC()->session->set( $cache_key, $notices );
			}
		} catch ( PaynowException $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error( $exception->getMessage() );
		}

		return $notices;
	}

	/**
	 * Return locale
	 *
	 * @return string
	 */
	private function get_locale(): string {
		return str_replace( '_', '-', get_user_locale() );
	}
}
