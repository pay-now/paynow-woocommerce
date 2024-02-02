<?php
/**
 * Paynow payment block for WooCommerce
 *
 * @package WooCommerce/Blocks
 */

namespace PayByPaynowPl\Blocks\Payments;

/**
 * Class PaynowPaywallPayment
 */
class PaynowPaywallPayment extends PaynowPaymentMethod {
	/**
	 * Payment method name. Matches gateway ID.
	 *
	 * @var string
	 */
	protected $name = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'paywall';

	/**
	 * Checks if the payment method is active or not.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return true;
	}

	/**
	 * Loads the payment method scripts.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$version      = wc_pay_by_paynow_pl_plugin_version();
		$path         = plugins_url( 'build/paynow-paywall-block.js', __FILE__ );
		$handle       = 'paynow-paywall-checkout-block';
		$dependencies = [ 'wp-hooks' ];

		wp_register_script( $handle, $path, $dependencies, $version, true );

		return [ 'paynow-paywall-checkout-block' ];
	}

	/**
	 * Gets the payment method data to load into the frontend.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'title'       => ($this->payment_method ? $this->payment_method->title : __( 'BLIK, online transfer and card payment', 'pay-by-paynow-pl' )),
			'description' => __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' ),
			'iconurl'     => 'https://static.paynow.pl/brand/paynow_logo_black.png',
			'available'   => $this->is_available(),
		];
	}
}
