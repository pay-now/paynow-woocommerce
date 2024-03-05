<?php

use Paynow\Model\PaymentMethods\Type;

defined( 'ABSPATH' ) || exit();

class WC_Gateway_Leaselink_Plugin_PL_Paynow_Apple_Pay_Payment extends WC_Gateway_Leaselink_Plugin_PL_Paynow {

	public function __construct() {
		$this->id                 = WC_LEASELINK_PLUGIN_PREFIX . 'apple_pay';
		$this->title              = __( 'Apple Pay', 'leaselink-plugin-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'leaselink-plugin-pl' );
		$this->method_title       = __( 'paynow.pl - Apple Pay payments', 'leaselink-plugin-pl' );
		$this->method_description = __( 'Accept Apple Pay payments with paynow.pl', 'leaselink-plugin-pl' );
		$this->payment_method_id  = 2004;
		parent::__construct();

		$this->enabled = 'yes';
	}

	/**
	 * Returns true if payment method is available
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return $this->is_payment_method_available( Type::APPLE_PAY ) &&
			WC_Leaselink_Plugin_PL_Helper::is_payment_method_available( WC_Gateway_Leaselink_Plugin_PL_Paynow_Digital_Wallets_Payment::class );
	}
}
