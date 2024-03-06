<?php

use Paynow\Model\PaymentMethods\Type;

defined( 'ABSPATH' ) || exit();

class WC_Gateway_Leaselink_Plugin_PL_Paynow_Google_Pay_Payment extends WC_Gateway_Leaselink_Plugin_PL_Paynow {

	public function __construct() {
		$this->id                 = WC_LEASELINK_PLUGIN_PREFIX . 'google_pay';
		$this->title              = __( 'Google Pay', 'leaselink-plugin-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'leaselink-plugin-pl' );
		$this->method_title       = __( 'LeaseLink - Google Pay payments', 'leaselink-plugin-pl' );
		$this->method_description = __( 'Accept Google Pay payments with paynow.pl', 'leaselink-plugin-pl' );
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
			WC_Leaselink_Plugin_PL_Helper::is_payment_method_available( WC_Gateway_Leaselink_Plugin_PL_Paynow_Digital_Wallets_Payment::class );
	}
}