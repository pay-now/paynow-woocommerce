<?php

use Paynow\Model\PaymentMethods\Type;

defined( 'ABSPATH' ) || exit();

class WC_Gateway_Pay_By_Paynow_PL_Card_Payment extends WC_Gateway_Pay_By_Paynow_PL {

	public function __construct() {
		$this->id                 = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'card';
		$this->title              = __( 'Card payment', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - Card payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept card payments with paynow.pl', 'pay-by-paynow-pl' );
		$this->payment_method_id  = 2002;
		$this->icon               = 'https://static.paynow.pl/brand/paynow_logo_black.png';
		parent::__construct();
	}

	public function payment_fields() {
		$card_payment_methods = $this->get_only_payment_methods_for_type( array( Type::CARD ) );
		$card_payment_method  = $card_payment_methods[0] ?? null;
		if ( $card_payment_method ) {
			$method_block                   = 'card';
			$idempotency_key                = WC_Pay_By_Paynow_PL_Keys_Generator::generate_idempotency_key(
				WC_Pay_By_Paynow_PL_Keys_Generator::generate_external_id_from_cart()
			);
			$notices                        = $this->gateway->gdpr_notices( $idempotency_key );
			$instruments                    = $card_payment_method->getSavedInstruments();
			$remove_saved_instrument_action = WC_Gateway_Pay_By_Paynow_PL_Remove_Instrument_Handler::get_rest_api_remove_instrument_url();
			include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH . 'card_payment.php';
		} else {
			parent::payment_fields();
		}
	}

	/**
	 * Returns true if payment method is available
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return $this->is_payment_method_available( array( Type::CARD ) );
	}
}
