<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Model\PaymentMethods\Type;

class WC_Gateway_Pay_By_Paynow_PL_Click_To_Pay_Payment extends WC_Gateway_Pay_By_Paynow_PL {

	public function __construct() {
		$this->id                 = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'click_to_pay';
		$this->title              = __( 'Click to Pay', 'pay-by-paynow-pl' );
		$this->description        = __( 'Click To Pay - pay with pre-saved card', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - Click to Pay payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Click To Pay - pay with pre-saved card', 'pay-by-paynow-pl' );
		$this->payment_method_id  = 2005;
		parent::__construct();
		$this->icon = self::get_icon_url();
	}

	public static function get_icon_url(): string {
		return WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS_PATH . 'images/click-to-pay.svg';
	}

	public function is_available(): bool {
		return false;
	}

	public static function is_enabled(): bool {
		$options = get_option( 'woocommerce_' . WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'click_to_pay_settings' );
		if ( is_array( $options ) && array_key_exists( 'enabled', $options ) ) {
			return 'yes' === $options['enabled'];
		} else {
			return false;
		}
	}

}
