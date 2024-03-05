<?php

use Paynow\Model\PaymentMethods\PaymentMethod;
use Paynow\Model\PaymentMethods\Status;
use Paynow\Model\PaymentMethods\Type;

defined( 'ABSPATH' ) || exit();

class WC_Gateway_Leaselink_Plugin_PL_Paynow_Paywall_Payment extends WC_Gateway_Leaselink_Plugin_PL_Paynow {

	public function __construct() {
		$this->id                = WC_LEASELINK_PLUGIN_PREFIX . 'paywall';
		$this->icon              = 'https://static.paynow.pl/brand/paynow_logo_black.png';
		$this->payment_method_id = null;
		parent::__construct();
		$this->title = $this->generate_title();
	}

	public function is_available(): bool {
		$is_paynow_enabled       = false;
		$paynow_payment_gateways = wc_leaselink_plugin()->payment_gateways();

		foreach ( WC()->payment_gateways()->payment_gateways() as $payment_gateway ) {
			if ( in_array( get_class( $payment_gateway ), $paynow_payment_gateways, true ) && 'yes' === $payment_gateway->enabled ) {
				$is_paynow_enabled = true;
				break;
			}
		}

		return ! $this->show_payment_methods && $is_paynow_enabled;
	}

	private function generate_title(): string {
		$payment_methods = $this->gateway->payment_methods();
		foreach ( $payment_methods ?? array() as $payment_method ) {
			/** @var $payment_method PaymentMethod */
			if ( Type::CARD === $payment_method->getType() && Status::ENABLED === $payment_method->getStatus() ) {
				return __( 'BLIK, online transfer and card payment', 'leaselink-plugin-pl' );
			}
		}
		return __( 'BLIK, online transfer payment', 'leaselink-plugin-pl' );
	}
}
