<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Model\PaymentMethods\Type;

class WC_Gateway_Pay_By_Paynow_PL_Paypo_Payment extends WC_Gateway_Pay_By_Paynow_PL {

	public function __construct() {
		$this->id                 = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'paypo';
		$this->title              = __( 'PayPo - buy now, pay later', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - PayPo payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept online transfer payments with paynow.pl', 'pay-by-paynow-pl' );
		$this->payment_method_id  = 3000;
		$this->icon               = 'https://static.paynow.pl/payment-method-icons/3000.png';
		parent::__construct();
	}

	public function payment_fields() {
		$method_block    = 'paypo';
		$idempotency_key = WC_Pay_By_Paynow_PL_Keys_Generator::generate_idempotency_key(
			WC_Pay_By_Paynow_PL_Keys_Generator::generate_external_id_from_cart()
		);
		$notices         = $this->gateway->gdpr_notices( $idempotency_key );

		include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH . 'paypo_payment.php';
	}

	/**
	 * Returns true if payment method is available
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return $this->is_payment_method_available( array( Type::PAYPO ) );
	}
}
