<?php

use Paynow\Exception\PaynowException;

defined( 'ABSPATH' ) || exit();

class WC_Payment_Gateway_Pay_By_Paynow_PL_Pbl extends WC_Gateway_Pay_By_Paynow_PL {
	public function __construct() {
		$this->id                 = 'pay_by_paynow_pl_pbl';
		$this->title              = __( 'Online transfer payment', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - Online payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept online transfer payments with paynow.pl', 'pay-by-paynow-pl' );
		$this->icon               = 'https://static.paynow.pl/brand/paynow_logo_black.png';
		$this->has_fields         = true;
		parent::__construct();
	}

	public function payment_fields() {
		wp_enqueue_style( $this->id . '_styles', WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS . 'css/front.css', [], wc_pay_by_paynow_pl_plugin_version() );
		try {
			$methods = $this->gateway->payment_methods();
			include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/templates/pbls.phtml';
		} catch ( PaynowException $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error( $exception->getMessage() );
		}
	}

	public function process_payment( $order_id ) {
		$payment_method_id = filter_input( INPUT_POST, 'paymentMethodId' );
		if ( empty( $payment_method_id ) ) {
			wc_add_notice( __( 'Please choose bank from the list to make payment', 'pay-by-paynow-pl' ), 'error' );

			return;
		}

		return parent::process_payment( $order_id );
	}
}