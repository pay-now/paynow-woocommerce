<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Model\PaymentMethods\Type;

class WC_Payment_Gateway_Pay_By_Paynow_PL_Blik extends WC_Gateway_Pay_By_Paynow_PL {

	const BLIK_CONFIRM_PAGE_NAME = 'blik_confirm_page';

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
		$blik_payment_methods = $this->get_only_payment_methods_for_type(Type::BLIK);
		if ($blik_payment_methods && $this->isWhiteLabelEnabled($blik_payment_methods)) {
			$method_block = 'blik';
			$notices = $this->gateway->gdpr_notices();
			include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH . 'blik_payment.phtml';
		}
	}

	public function validate_fields(): bool {
		$payment_authorization_code = (int) preg_replace( '/\s+/', '', filter_input( INPUT_POST, 'authorizationCode' ) );
		$blik_payment_methods = $this->get_only_payment_methods_for_type(Type::BLIK);
		if ( $blik_payment_methods &&
		     $this->isWhiteLabelEnabled( $blik_payment_methods ) &&
		     (empty( $payment_authorization_code ) || strlen( $payment_authorization_code )!==6)) {
			wc_add_notice( __( 'Please enter correct BLIK code', 'pay-by-paynow-pl' ), 'error' );

			return false;
		}

		return parent::validate_fields();
	}

	private function isWhiteLabelEnabled($payment_method): bool {
		return !empty($payment_method[0]) && Paynow\Model\PaymentMethods\AuthorizationType::CODE === $payment_method[0]->getAuthorizationType();
	}
}
