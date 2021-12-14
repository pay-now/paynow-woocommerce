<?php
defined( 'ABSPATH' ) || exit();

class WC_Payment_Gateway_Pay_By_Paynow_PL_Google_Pay extends WC_Gateway_Pay_By_Paynow_PL {
	public function __construct() {
		$this->id                 = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'google_pay';
		$this->title              = __( 'Google Pay', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - Google Pay payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept Google Pay payments with paynow.pl', 'pay-by-paynow-pl' );
		$this->payment_method_id  = 2003;
		parent::__construct();
	}
}
