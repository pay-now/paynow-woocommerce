<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Exception\PaynowException;
use Paynow\Model\PaymentMethods\Type;

class WC_Payment_Gateway_Pay_By_Paynow_PL_Pbl extends WC_Gateway_Pay_By_Paynow_PL {
	public function __construct() {
		$this->id                 = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'pbl';
		$this->title              = __( 'Online transfer payment', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - Online payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept online transfer payments with paynow.pl', 'pay-by-paynow-pl' );
		$this->icon               = 'https://static.paynow.pl/brand/paynow_logo_black.png';
		$this->has_fields         = true;
		parent::__construct();
	}

	public function payment_fields() {
		wp_enqueue_style( $this->id . '_styles', WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS_PATH . 'css/front.css', array(), wc_pay_by_paynow_pl_plugin_version() );
		try {
			$method_block = 'pbls';
			$methods = $this->get_only_payment_methods_for_type(Type::PBL);
			$notices = $this->gateway->gdpr_notices();
			include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH . 'pbl_payment.phtml';
		} catch ( PaynowException $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error( $exception->getMessage() );
		}
	}

	public function validate_fields(): bool {
		$payment_method_id = filter_input( INPUT_POST, 'paymentMethodId' );
		if ( empty( $payment_method_id ) ) {
			wc_add_notice( __( 'Please choose bank from the list below to make the payment', 'pay-by-paynow-pl' ), 'error' );

			return false;
		}

		return parent::validate_fields();
	}
}
