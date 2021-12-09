<?php

use Paynow\Model\PaymentMethods\Type;

defined( 'ABSPATH' ) || exit();

class WC_Payment_Gateway_Pay_By_Paynow_PL_Blik extends WC_Gateway_Pay_By_Paynow_PL {
	public function __construct() {
		$this->id                 = 'pay_by_paynow_pl_blik';
		$this->title              = __( 'BLIK payment', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - BLIK payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept BLIK payments with paynow.pl', 'pay-by-paynow-pl' );
		$this->payment_method_id  = 2007;
		parent::__construct();
	}

	public function payment_fields() {
		$payment_methods = $this->gateway->payment_methods();
		$blik_payment_methods = $this->get_only_blik_payment_methods($payment_methods);
		if ($blik_payment_methods && $this->isWhiteLabelEnabled($blik_payment_methods)) {
			$method_block = 'blik';
			$notices = $this->gateway->gdpr_notices();
			include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/templates/blik.phtml';
		}
	}

	private function isWhiteLabelEnabled($payment_method): bool {
		return !empty($payment_method[0]) && Paynow\Model\PaymentMethods\AuthorizationType::CODE === $payment_method[0]->getAuthorizationType();
	}

	public function process_payment( $order_id ) {
		$payment_authorization_code = filter_input( INPUT_POST, 'authorizationCode' );
		$payment_methods = $this->gateway->payment_methods();
		$blik_payment_methods = $this->get_only_blik_payment_methods($payment_methods);
		if ( $blik_payment_methods &&
			$this->isWhiteLabelEnabled( $blik_payment_methods ) &&
			empty( $payment_authorization_code )) {
			wc_add_notice( __( 'Please enter correct BLIK code', 'pay-by-paynow-pl' ), 'error' );

			return;
		}

		return parent::process_payment( $order_id );
	}

	private function get_only_blik_payment_methods($payment_methods): array {
		$blik_payment_methods = [];
		if (! empty($payment_methods)) {
			foreach ($payment_methods as $item) {
				if (Type::BLIK === $item->getType()) {
					$blik_payment_methods[] = $item;
				}
			}
		}

		return $blik_payment_methods;
	}
}
