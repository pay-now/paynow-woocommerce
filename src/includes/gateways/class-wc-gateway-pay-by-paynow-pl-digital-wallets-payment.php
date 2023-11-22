<?php

use Paynow\Model\PaymentMethods\Type;

defined( 'ABSPATH' ) || exit();

class WC_Gateway_Pay_By_Paynow_PL_Digital_Wallets_Payment extends WC_Gateway_Pay_By_Paynow_PL {

	public function __construct() {
		$this->id                 = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'digital_wallets';
		$this->title              = __( 'Digital wallets', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - Digital wallets payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept Digital wallets payments with paynow.pl', 'pay-by-paynow-pl' );
		parent::__construct();
	}

	public function is_available(): bool {
		return false;
	}
}
