<?php
/**
 * Paynow payment block for WooCommerce
 *
 * @package WooCommerce/Blocks
 */

namespace PayByPaynowPl\Blocks\Payments;

/**
 * Class PaynowApplePayPayment
 */
class PaynowApplePayPayment extends PaynowPaymentMethod {
	/**
	 * Payment method name. Matches gateway ID.
	 *
	 * @var string
	 */
	protected $name = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'apple_pay';

	/**
	 * Loads the payment method scripts.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$version      = wc_pay_by_paynow_pl_plugin_version();
		$path         = plugins_url( 'build/paynow-apple-pay-block.js', __FILE__ );
		$handle       = 'paynow-apple-pay-checkout-block';
		$dependencies = [ 'wp-hooks' ];

		wp_register_script( $handle, $path, $dependencies, $version, true );

		return [ 'paynow-apple-pay-checkout-block' ];
	}

	/**
	 * Gets the payment method data to load into the frontend.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'title'       => __( 'Apple Pay', 'pay-by-paynow-pl' ),
			'description' => __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' ),
			'iconurl'     => 'https://static.paynow.pl/payment-method-icons/2004.png',
			'available'   => $this->is_available(),
		];
	}
}
