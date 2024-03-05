<?php
/**
 * Leaselink payment block for WooCommerce
 *
 * @package WooCommerce/Blocks
 */

namespace LeaselinkPluginPl\Blocks\Payments;

/**
 * Class Leaselink_Apple_Pay_Payment
 */
class Leaselink_Apple_Pay_Payment extends Leaselink_Payment_Method {
	/**
	 * Payment method name. Matches gateway ID.
	 *
	 * @var string
	 */
	protected $name = WC_LEASELINK_PLUGIN_PREFIX . 'apple_pay';

	/**
	 * Loads the payment method scripts.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$version      = wc_leaselink_plugin_version();
		$path         = plugins_url( 'build/paynow-apple-pay-block.js', __FILE__ );
		$handle       = 'paynow-apple-pay-checkout-block';
		$dependencies = array( 'wp-hooks' );

		wp_register_script( $handle, $path, $dependencies, $version, true );

		return array( 'paynow-apple-pay-checkout-block' );
	}

	/**
	 * Gets the payment method data to load into the frontend.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return array(
			'title'       => __( 'Apple Pay', 'leaselink-plugin-pl' ),
			'description' => __( 'Secure and fast payments provided by paynow.pl', 'leaselink-plugin-pl' ),
			'iconurl'     => 'https://static.paynow.pl/payment-method-icons/2004.png',
			'available'   => $this->is_available(),
		);
	}
}
