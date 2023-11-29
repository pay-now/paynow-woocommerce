<?php

use Paynow\Model\PaymentMethods\Type;

defined( 'ABSPATH' ) || exit();

class WC_Gateway_Pay_By_Paynow_PL_Google_Pay_Payment extends WC_Gateway_Pay_By_Paynow_PL {

	public function __construct() {
		$this->id                 = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'google_pay';
		$this->title              = __( 'Google Pay', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - Google Pay payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept Google Pay payments with paynow.pl', 'pay-by-paynow-pl' );
		$this->payment_method_id  = 2003;
		parent::__construct();

		$this->enabled = 'yes';
	}

	/**
	 * Returns true if payment method is available
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return $this->is_payment_method_available( Type::GOOGLE_PAY ) &&
			WC_Pay_By_Paynow_PL_Helper::is_payment_method_available( WC_Gateway_Pay_By_Paynow_PL_Digital_Wallets_Payment::class );
	}
}
