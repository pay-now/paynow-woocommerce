<?php

use Paynow\Model\PaymentMethods\Type;

defined( 'ABSPATH' ) || exit();

class WC_Gateway_Leaselink_Plugin_PL_Paynow_Digital_Wallets_Payment extends WC_Gateway_Leaselink_Plugin_PL_Paynow {

	public function __construct() {
		$this->id                 = WC_LEASELINK_PLUGIN_PREFIX . 'digital_wallets';
		$this->title              = __( 'Digital wallets', 'leaselink-plugin-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'leaselink-plugin-pl' );
		$this->method_title       = __( 'paynow.pl - Digital wallets payments', 'leaselink-plugin-pl' );
		$this->method_description = __( 'Accept Digital wallets payments with paynow.pl', 'leaselink-plugin-pl' );
		parent::__construct();
	}

	public function is_available(): bool {
		return false;
	}
}
