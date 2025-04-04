<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Model\PaymentMethods\Type;

class WC_Gateway_Pay_By_Paynow_PL_Click_To_Pay_Payment extends WC_Gateway_Pay_By_Paynow_PL {

	public function __construct() {
		$this->id                 = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'click_to_pay';
		$this->title              = __( 'Click to Pay', 'pay-by-paynow-pl' );
		$this->description        = __( 'lick To Pay - pay with pre-saved card', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - Click to Pay payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Click To Pay - pay with pre-saved card', 'pay-by-paynow-pl' );
		$this->payment_method_id  = 2005;
		$this->icon               = 'https://static.paynow.pl/payment-method-icons/2005.png';
		parent::__construct();
	}

	/**
	 * Returns true if payment method is available
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return false;
	}
}
