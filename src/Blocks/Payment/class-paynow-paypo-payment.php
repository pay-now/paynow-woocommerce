<?php
/**
 * Paynow payment block for WooCommerce
 *
 * @package WooCommerce/Blocks
 */

namespace PayByPaynowPl\Blocks\Payments;

/**
 * Class Paynow_PayPo_Payment
 */
class Paynow_PayPo_Payment extends Paynow_Payment_Method {
	/**
	 * Payment method name. Matches gateway ID.
	 *
	 * @var string
	 */
	protected $name = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'paypo';

	/**
	 * Loads the payment method scripts.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$version      = wc_pay_by_paynow_pl_plugin_version();
		$path         = plugins_url( 'build/paynow-paypo-block.js', __FILE__ );
		$handle       = 'paynow-paypo-checkout-block';
		$dependencies = array( 'wp-hooks' );

		wp_register_script( $handle, $path, $dependencies, $version, true );

		return array( $handle );
	}

	/**
	 * Gets the payment method data to load into the frontend.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return array(
			'title'       => __( 'Buy now, pay later', 'pay-by-paynow-pl' ),
			'description' => __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' ),
			'iconurl'     => 'https://static.paynow.pl/payment-method-icons/3000.png',
			'available'   => $this->is_available(),
			'fields'      => $this->get_payment_fields(),
		);
	}
}
