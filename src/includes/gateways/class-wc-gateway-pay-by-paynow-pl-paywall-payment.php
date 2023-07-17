<?php
defined( 'ABSPATH' ) || exit();

class WC_Gateway_Pay_By_Paynow_PL_Paywall_Payment extends WC_Gateway_Pay_By_Paynow_PL {

	public function __construct() {
		$this->id                = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'paywall';
		$this->title             = __( 'Paynow', 'pay-by-paynow-pl' );
		$this->icon              = 'https://static.paynow.pl/brand/paynow_logo_black.png';
		$this->payment_method_id = null;
		parent::__construct();
	}

	public function is_available(): bool {
		$is_paynow_enabled       = false;
		$paynow_payment_gateways = wc_pay_by_paynow()->payment_gateways();

		foreach ( WC()->payment_gateways()->payment_gateways() as $payment_gateway ) {
			if ( in_array( get_class( $payment_gateway ), $paynow_payment_gateways, true ) && 'yes' === $payment_gateway->enabled ) {
				$is_paynow_enabled = true;
				break;
			}
		}

		return ! $this->show_payment_methods && $is_paynow_enabled;
	}
}
