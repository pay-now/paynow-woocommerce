<?php

defined( 'ABSPATH' ) || exit();

use Paynow\Util\ClientExternalIdCalculator;

/**
 * Provides static methods as helpers.
 */
class WC_Pay_By_Paynow_PL_Keys_Generator {

	/**
	 * @param $external_id
	 * @return false|string
	 */
	public static function generate_idempotency_key( $external_id ) {

		return substr( uniqid( $external_id, true ), 0, 45 );
	}

	/**
	 * @return string
	 */
	public static function generate_external_id_from_cart(): string {

		if ( empty( WC()->cart ) ) {
			return '';
		}

		return uniqid( WC()->cart->get_cart_hash() . '_' );
	}

	/**
	 * @param $customer_id
	 * @param $signature_key
	 * @return string
	 */
	public static function generate_buyer_external_id( $customer_id, $signature_key ): string {

		return ClientExternalIdCalculator::calculate( "$customer_id", $signature_key );
	}
}
