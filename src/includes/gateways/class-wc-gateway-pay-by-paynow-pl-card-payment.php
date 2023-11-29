<?php

use Paynow\Model\PaymentMethods\Type;

defined( 'ABSPATH' ) || exit();

class WC_Gateway_Pay_By_Paynow_PL_Card_Payment extends WC_Gateway_Pay_By_Paynow_PL {

	public function __construct() {
		$this->id                 = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'card';
		$this->title              = __( 'Card payment', 'leaselink-plugin-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'leaselink-plugin-pl' );
		$this->method_title       = __( 'LeaseLink - Card payments', 'leaselink-plugin-pl' );
		$this->method_description = __( 'Accept card payments with paynow.pl', 'leaselink-plugin-pl' );
		$this->payment_method_id  = 2002;
		$this->icon               = 'https://static.paynow.pl/brand/paynow_logo_black.png';
		parent::__construct();
	}

	/**
	 * Returns true if payment method is available
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return $this->is_payment_method_available( Type::CARD );
	}
}
