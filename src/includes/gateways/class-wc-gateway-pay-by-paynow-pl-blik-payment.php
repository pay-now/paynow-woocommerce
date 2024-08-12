<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Model\PaymentMethods\Type;

class WC_Gateway_Pay_By_Paynow_PL_Blik_Payment extends WC_Gateway_Pay_By_Paynow_PL {

	public const BLIK_CONFIRM_TEMPLATE_NAME = 'confirm_blik_payment';

	public function __construct() {
		$this->id                 = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'blik';
		$this->title              = __( 'BLIK payment', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - BLIK payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept BLIK payments with paynow.pl', 'pay-by-paynow-pl' );
		$this->payment_method_id  = 2007;
		parent::__construct();
	}

	public function payment_fields() {
		$blik_payment_methods = $this->get_only_payment_methods_for_type( array( Type::BLIK ) );
		if ( $blik_payment_methods && $this->isWhiteLabelEnabled( $blik_payment_methods ) ) {
			$method_block    = 'blik';
			$idempotency_key = WC_Pay_By_Paynow_PL_Keys_Generator::generate_idempotency_key(
				WC_Pay_By_Paynow_PL_Keys_Generator::generate_external_id_from_cart()
			);
			$notices         = $this->gateway->gdpr_notices( $idempotency_key );
			include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH . 'blik_payment.php';
		} else {
			parent::payment_fields();
		}
	}

	/**
	 * @return bool
	 */
	public function validate_fields(): bool {
		$payment_authorization_code = preg_replace( '/\s+/', '', $this->get_authorization_code_from_posted_data() );
		$blik_payment_methods       = $this->get_only_payment_methods_for_type( array( Type::BLIK ) );
		if ( $blik_payment_methods && $this->isWhiteLabelEnabled( $blik_payment_methods ) &&
			( empty( $payment_authorization_code ) || strlen( $payment_authorization_code ) !== 6 ) ) {
			wc_add_notice( __( 'Please enter correct BLIK code', 'pay-by-paynow-pl' ), 'error' );

			return false;
		}

		return parent::validate_fields();
	}

	/**
	 * @param $payment_method
	 *
	 * @return bool
	 */
	private function isWhiteLabelEnabled( $payment_method ): bool {
		return ! empty( $payment_method[0] ) && Paynow\Model\PaymentMethods\AuthorizationType::CODE === $payment_method[0]->getAuthorizationType();
	}

	/**
	 * Returns true if payment method is available
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return $this->is_payment_method_available( array( Type::BLIK ) );
	}
}
