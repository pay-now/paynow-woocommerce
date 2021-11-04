<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Exception\PaynowException;
use Paynow\Model\Payment\Status;

/**
 * Provides static methods as helpers.
 */
class WC_Pay_By_Paynow_PL_Helper {
	/**
	 * Get amount to pay
	 *
	 * @param float $total Amount due
	 *
	 * @return int
	 */
	public static function get_amount( $total ) {
		return absint( wc_format_decimal( ( (float) $total * 100 ), wc_get_price_decimals() ) ); // In cents.
	}

	/**
	 * Returns ID of order
	 *
	 * @param $order
	 *
	 * @return mixed
	 */
	public static function get_order_id( $order ) {
		return self::is_old_wc_version() ? $order->id : $order->get_id();
	}

	/**
	 * Get minimum payment amount value
	 *
	 * @return int
	 */
	public static function get_minimum_amount() {
		return 100;
	}

	/**
	 * Gets the notification URL for payment status update.
	 *
	 * @return string
	 */
	public static function get_notification_url() {
		return add_query_arg( 'wc-api', 'WC_Gateway_Pay_By_Paynow_PL', home_url( '/' ) );
	}

	/**
	 * Get request headers
	 *
	 * @return array|false
	 */
	public static function get_request_headers() {
		if ( ! function_exists( 'apache_request_headers' ) ) {
			$headers = array();
			foreach ( $_SERVER as $key => $value ) {
				if ( substr( $key, 0, 5 ) == 'HTTP_' ) {
					$subject                                      = ucwords( str_replace( '_', ' ', strtolower( substr( $key, 5 ) ) ) );
					$headers[ str_replace( ' ', '-', $subject ) ] = $value;
				}
			}

			return $headers;
		}

		return apache_request_headers();
	}

	public static function is_old_wc_version() {
		return version_compare( WC_VERSION, '3.0', '<' );
	}

	/**
	 * Validate minimum payment amount
	 *
	 * @param $order
	 *
	 * @throws PaynowException
	 */
	public static function validate_minimum_payment_amount( $amount ) {
		if ( self::get_amount( $amount ) < self::get_minimum_amount() ) {
			throw new PaynowException( sprintf( __( 'Sorry, the minimum allowed order total is %1$s to use this payment method.', 'pay-by-paynow-pl' ), wc_price( self::get_minimum_amount() / 100 ) ) );
		}
	}

	/**
	 * Returns list of product categories
	 *
	 * @param $product_id
	 *
	 * @return string|null
	 */
	public static function get_product_categories( $product_id ) {
		$terms = get_the_terms( $product_id, 'product_cat' );

		$categories = array();
		foreach ( $terms as $term ) {
			$categories[] = $term->name;
		}

		return implode( ', ', $categories );
	}

	/**
	 * @return int
	 */
	public static function get_payment_amount() {
		$amount = 0;

		// checkout page
		if ( isset( WC()->cart ) ) {
			$amount = WC()->cart->total;
		}

		// order-pay page
		if ( get_query_var( 'order-pay' ) ) {
			$order  = new WC_Order( get_query_var( 'order-pay' ) );
			$amount = $order->get_total();
		}

		return $amount;
	}

	/**
	 * @param $order
	 * @return bool
	 */
	public static function is_paynow_order( $order ) {
		if ( self::is_old_wc_version() ) {
			$paymentMethod = get_post_meta( $order->id, '_payment_method', true );
		} else {
			$paymentMethod = $order->get_payment_method();
		}
        if (strpos( $paymentMethod, WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX ) !== false) {
            return true;
        } else {
            return false;
        }
	}
}
