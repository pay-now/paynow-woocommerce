<?php
defined( 'ABSPATH' ) || exit();

class WC_Payment_Gateway_Pay_By_Paynow_PL_Blik extends WC_Gateway_Pay_By_Paynow_PL {
	public function __construct() {
		$this->id                 = 'pay_by_paynow_pl_blik';
		$this->title              = __( 'BLIK payment', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - BLIK payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept BLIK payments with paynow.pl', 'pay-by-paynow-pl' );
		$this->payment_method_id  = 2007;
		parent::__construct();
	}
}