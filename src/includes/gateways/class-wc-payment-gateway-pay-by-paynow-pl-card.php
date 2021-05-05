<?php
defined( 'ABSPATH' ) || exit();

class WC_Payment_Gateway_Pay_By_Paynow_PL_Card extends WC_Gateway_Pay_By_Paynow_PL {
	public function __construct() {
		$this->id                 = 'pay_by_paynow_pl_card';
		$this->title              = __( 'Card payment', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure BLIK, credit cards payments and fast online transfers provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - Card payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept card payments with paynow.pl', 'pay-by-paynow-pl' );
		$this->payment_method_id  = 2002;
		$this->icon               = 'https://static.paynow.pl/brand/paynow_logo_black.png';
		parent::__construct();
	}
}