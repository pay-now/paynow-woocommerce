<?php

use Paynow\Model\PaymentMethods\Type;

defined( 'ABSPATH' ) || exit();

class WC_Gateway_Pay_By_Paynow_PL_Apple_Pay_Payment extends WC_Gateway_Pay_By_Paynow_PL {

	public function __construct() {
		$this->id                 = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'apple_pay';
		$this->title              = __( 'Apple Pay', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - Apple Pay payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept Apple Pay payments with paynow.pl', 'pay-by-paynow-pl' );
		$this->payment_method_id  = 2004;
		parent::__construct();
	}

	/**
	 * Returns true if payment method is available
	 *
	 * @return bool
	 */
	public function is_available(): bool {
        $is_digital_wallets_enabled = false;

        foreach ( WC()->payment_gateways()->payment_gateways() as $payment_gateway ) {
            if ( get_class( $payment_gateway ) === WC_Gateway_Pay_By_Paynow_PL_Digital_Wallets_Payment::class && 'yes' === $payment_gateway->enabled ) {
                $is_digital_wallets_enabled = true;
                break;
            }
        }

		return $this->is_payment_method_available( Type::APPLE_PAY ) && $is_digital_wallets_enabled;
	}
}
