<?php
/**
 * Leaselink payment block for WooCommerce
 *
 * @package WooCommerce/Blocks
 */

namespace LeaselinkPluginPl\Blocks\Payments;

/**
 * Class Leaselink_Digital_Wallets_Payment
 */
class Leaselink_Digital_Wallets_Payment extends Leaselink_Payment_Method {
	/**
	 * Payment method name. Matches gateway ID.
	 *
	 * @var string
	 */
	protected $name = WC_LEASELINK_PLUGIN_PREFIX . 'digital_wallets';

	/**
	 * Loads the payment method scripts.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$version      = wc_leaselink_plugin_version();
		$path         = plugins_url( 'build/paynow-digital-wallets-block.js', __FILE__ );
		$handle       = 'leaselink-paynow-digital-wallets-checkout-block';
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
			'title'       => __( 'Digital wallets', 'leaselink-plugin-pl' ),
			'description' => __( 'Secure and fast payments provided by paynow.pl', 'leaselink-plugin-pl' ),
			'available'   => $this->is_available(),
		);
	}
}
