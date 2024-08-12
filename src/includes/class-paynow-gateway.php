<?php

defined( 'ABSPATH' ) || exit();

use Paynow\Client;
use Paynow\Configuration;
use Paynow\Environment;
use Paynow\Exception\ConfigurationException;
use Paynow\Exception\PaynowException;
use Paynow\Model\PaymentMethods\PaymentMethod;
use Paynow\Response\Payment\Authorize;
use Paynow\Response\Refund\Status;
use Paynow\Model\Payment\Status as PaymentStatus;
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
			$is_sandbox          = 'yes' === $this->settings['sandbox'];
			$api_key             = $is_sandbox ? $this->settings['sandbox_api_key'] : $this->settings['production_api_key'];
			$this->signature_key = $is_sandbox ? $this->settings['sandbox_signature_key'] : $this->settings['production_signature_key'];

			if ( $api_key && $this->signature_key ) {
				$this->client = new Client(
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
	 * @param $return_url
	 * @param $payment_method_id
	 * @param $authorization_code
	 * @param $payment_method_token
	 * @param $payment_method_fingerprint
	 * @return array|array[]|void
	 * @throws ConfigurationException
	 */
	public function payment_request( WC_Order $order, $return_url, $payment_method_id = null, $authorization_code = null, $payment_method_token = null, $payment_method_fingerprint = null ) {

		if ( ! $this->client ) {
			return;
		}

		$return_url = rtrim( $return_url, '?' );

		$currency     = WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ? $order->get_order_currency() : $order->get_currency();
		$order_id     = WC_Pay_By_Paynow_PL_Helper::get_order_id( $order );
		$customer_id  = get_current_user_id() > 0 ? get_current_user_id() : null;
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

		try {
			$payment_data['buyer']['address'] = array(
				'billing' => array(
					'street'          => $order->get_billing_address_1(),
					'houseNumber'     => $order->get_billing_address_2(),
					'apartmentNumber' => '',
					'zipcode'         => $order->get_billing_postcode(),
					'city'            => $order->get_billing_city(),
					'county'          => $order->get_billing_state(),
					'country'         => $order->get_billing_country(),
				),
			);

			if ( $order->has_shipping_address() ) {
				$payment_data['buyer']['address']['shipping'] = array(
					'street'          => $order->get_shipping_address_1(),
					'houseNumber'     => $order->get_shipping_address_2(),
					'apartmentNumber' => '',
					'zipcode'         => $order->get_shipping_postcode(),
					'city'            => $order->get_shipping_city(),
					'county'          => $order->get_shipping_state(),
					'country'         => $order->get_shipping_country(),
				);
			} else {
				$payment_data['buyer']['address']['shipping'] = $payment_data['buyer']['address']['billing'];
			}
		} catch ( Throwable $e ) {
			WC_Pay_By_Paynow_PL_Logger::error( 'Cannot add addresses to payment data', array( 'msg' => $e->getMessage() ) );
		}

		if ( ! empty( $customer_id ) ) {
			$payment_data['buyer']['externalId'] = WC_Pay_By_Paynow_PL_Keys_Generator::generate_buyer_external_id( $customer_id, $this->signature_key );
		}

		$logger_context = array(
			WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_EXTERNAL_ID_FIELD_NAME => $order_id,
		);

		if ( ! empty( $payment_method_id ) ) {
			$payment_data['paymentMethodId'] = $payment_method_id;
		}

		$is_blik = ! empty( $authorization_code );
		if ( $is_blik ) {
			$payment_data['authorizationCode'] = $authorization_code;
		}

		if ( ! empty( $payment_method_token ) ) {
			$payment_data['paymentMethodToken'] = $payment_method_token;
		}

		if ( ! empty( $payment_method_fingerprint ) ) {
			$payment_data['buyer']['deviceFingerprint'] = $payment_method_fingerprint;
		}

		if ( 'yes' === $this->settings['send_order_items'] ) {
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

			$order_items = array_values(
				array_filter(
					$order_items,
					function ( $item ) {

						return ! empty( $item['category'] );
					}
				)
			);

			if ( ! empty( $order_items ) ) {
				$payment_data['orderItems'] = $order_items;
			}
		}

		if ( 'yes' === $this->settings['use_payment_validity_time_flag'] ) {
			$payment_data['validityTime'] = $this->settings['payment_validity_time'];
		}

		$idempotency_key = WC_Pay_By_Paynow_PL_Keys_Generator::generate_idempotency_key( $order_id );
		$payment         = new Payment( $this->client );

		try {
			$api_response_object = $payment->authorize( $payment_data, $idempotency_key );

			$redirect_url = $api_response_object->getRedirectUrl() ?? $return_url;
			$redirect_url = rtrim( $redirect_url, '?' );
			if ( $is_blik ) {
				$redirect_url .= ( strpos( $redirect_url, '?' ) !== false ? '&' : '?' )
					. http_build_query(
						array(
							'paymentId'   => $api_response_object->getPaymentId(),
							'confirmBlik' => 1,
						)
					);
			}

			$payment_data = array(
				WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_STATUS_FIELD_NAME       => $api_response_object->getStatus(),
				WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_PAYMENT_ID_FIELD_NAME   => $api_response_object->getPaymentId(),
				WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_REDIRECT_URL_FIELD_NAME => $redirect_url,
			);

			$cache_key = 'paynow_payment_methods__' . md5( substr( $this->get_signature_key(), 0, 8 ) . '_' . $currency . '_' . WC_Pay_By_Paynow_PL_Helper::get_amount( $order->get_total() ) );
			delete_transient( $cache_key );

			WC_Pay_By_Paynow_PL_Logger::debug( 'Retrieved authorization response', array_merge( $logger_context, $payment_data ) );

			return $payment_data;
		} catch ( PaynowException $exception ) {
			$errors = array();

			foreach ( $exception->getErrors() as $e ) {
				$errors[] = array(
					'message' => $e->getMessage(),
					'type'    => $e->getType(),
				);
			}

			WC_Pay_By_Paynow_PL_Logger::error(
				'Authorization failed',
				array_merge(
					$logger_context,
					array(
						'service' => 'Payment',
						'action'  => 'authorize',
						'message' => $exception->getMessage(),
						'errors'  => $errors,
					)
				)
			);

			return array( 'errors' => $exception->getErrors() );
		} catch ( \Exception $exception ) {
			if ( $is_blik && ( $exception->getCode() === 504 || ! ( strpos( $exception->getMessage(), 'cURL error 28' ) === false ) ) ) {
				$payment_data = array(
					WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_STATUS_FIELD_NAME       => PaymentStatus::STATUS_NEW,
					WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_PAYMENT_ID_FIELD_NAME   => $order_id . '_UNKNOWN',
					WC_Pay_By_Paynow_PL_Helper::NOTIFICATION_REDIRECT_URL_FIELD_NAME => $return_url
					. ( strpos( $return_url, '?' ) !== false ? '&' : '?' )
						. http_build_query(
							array(
								'paymentId'   => $order_id . '_UNKNOWN',
								'confirmBlik' => 1,
							)
						),
				);
				WC_Pay_By_Paynow_PL_Logger::error( 'Authorization response timeout', array_merge( $logger_context, $payment_data ) );
				return $payment_data;
			} else {
				WC_Pay_By_Paynow_PL_Logger::error(
					'Authorization failed',
					array_merge(
						$logger_context,
						array(
							'service' => 'Payment',
							'action'  => 'authorize',
							'message' => $exception->getMessage(),
							'trace'   => $exception->getTraceAsString(),
						)
					)
				);
				return array( 'errors' => array() );
			}
		}
	}

	/**
	 * Sends refund request
	 *
	 * @param int $order_id Order ID.
	 * @param string $payment_id Payment ID
	 * @param int $amount Refund amount.
	 *
	 * @return Status|null
	 * @throws PaynowException Thrown in Paynow SDK during request processing
	 */
	public function refund_request( int $order_id, string $payment_id, int $amount ): ?Status {

		if ( ! $this->client ) {
			return null;
		}
		$refund = new Refund( $this->client );

		return $refund->create(
			$payment_id,
			substr( uniqid( $order_id, true ), 0, 36 ),
			$amount
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

		$amount = WC_Pay_By_Paynow_PL_Helper::get_amount( WC_Pay_By_Paynow_PL_Helper::get_payment_amount() );

		if ( ! $this->client || did_action( 'wp_loaded' ) === 0 ) {
			return null;
		}

		$payment_methods = array();
		try {
			$currency  = get_woocommerce_currency();
			$cache_key = 'paynow_payment_methods__' . md5( substr( $this->get_signature_key(), 0, 8 ) . '_' . $currency . '_' . $amount );

			$apple_pay_enabled = sanitize_text_field( wp_unslash( $_COOKIE['applePayEnabled'] ?? '0' ) ) === '1';
			$payment_methods   = get_transient( $cache_key );
			if ( false === $payment_methods ) {
				WC_Pay_By_Paynow_PL_Logger::info(
					'Retrieving payment methods {currency={}, amount={}, force={}}',
					array(
						$currency,
						$amount,
					)
				);
				$idempotency_key   = WC_Pay_By_Paynow_PL_Keys_Generator::generate_idempotency_key(
					WC_Pay_By_Paynow_PL_Keys_Generator::generate_external_id_from_cart()
				);
				$current_user_id   = get_current_user_id();
				$buyer_external_id = $current_user_id > 0 ? WC_Pay_By_Paynow_PL_Keys_Generator::generate_buyer_external_id( $current_user_id, $this->signature_key ) : null;
				$payment_methods   = ( new Payment( $this->client ) )->getPaymentMethods( $currency, $amount, $apple_pay_enabled, $idempotency_key, $buyer_external_id )->getAll();
				// replace null value to string for caching
				if ( null === $payment_methods ) {
					$payment_methods = 'null';
				}

				set_transient( $cache_key, $payment_methods, 3600 );
			}
		} catch ( PaynowException $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error( $exception->getMessage() );
		}

		// replace string 'null' into real null
		// and false in case when get_transient returns false and then an exception will be thrown
		if ( 'null' === $payment_methods || false === $payment_methods ) {
			$payment_methods = null;
		}
		return $payment_methods;
	}

	/**
	 * @param $token
	 * @return bool
	 * @throws ConfigurationException
	 * @throws PaynowException
	 */
	public function remove_saved_instrument( $token ): bool {
		try {
			$amount    = WC_Pay_By_Paynow_PL_Helper::get_amount( WC_Pay_By_Paynow_PL_Helper::get_payment_amount() );
			$currency  = get_woocommerce_currency();
			$cache_key = 'paynow_payment_methods__' . md5( substr( $this->get_signature_key(), 0, 8 ) . '_' . $currency . '_' . $amount );
			delete_transient( $cache_key );

			$idempotency_key   = WC_Pay_By_Paynow_PL_Keys_Generator::generate_idempotency_key(
				WC_Pay_By_Paynow_PL_Keys_Generator::generate_external_id_from_cart()
			);
			$buyer_external_id = WC_Pay_By_Paynow_PL_Keys_Generator::generate_buyer_external_id( get_current_user_id(), $this->signature_key );

			( new Payment( $this->client ) )->removeSavedInstrument( $buyer_external_id, $token, $idempotency_key );

			return true;
		} catch ( PaynowException $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error(
				'Remove saved instrument failed',
				array(
					'service' => 'Payment',
					'action'  => 'removeSavedInstrument',
					'message' => $exception->getMessage(),
					'trace'   => $exception->getTraceAsString(),
				)
			);

			return false;
		}
	}

	/**
	 * @param int $order_id Order ID.
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

			$idempotency_key = WC_Pay_By_Paynow_PL_Keys_Generator::generate_idempotency_key( $order_id );
			$response        = $payment->status( $payment_id, $idempotency_key );
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
	 * @param string $idempotency_key
	 * @return array|null
	 */
	public function gdpr_notices( string $idempotency_key ): ?array {

		$notices = array();
		$locale  = $this->get_locale();
		try {
			$cache_key = strtolower( 'paynow_gdpr_notices_' . substr( $this->get_signature_key(), 0, 8 ) . '_' . str_replace( '-', '_', $locale ) );
			if ( ! is_null( WC()->session ) && ! empty( WC()->session->get( $cache_key ) ) ) {
				$notices = WC()->session->get( $cache_key );
			} else {
				WC_Pay_By_Paynow_PL_Logger::info( 'Retrieving GDPR notices' );
				$notices = ( new Paynow\Service\DataProcessing( $this->client ) )->getNotices( $locale, $idempotency_key )->getAll();
				if ( ! is_null( WC()->session ) ) {
					WC()->session->set( $cache_key, $notices );
				}
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
