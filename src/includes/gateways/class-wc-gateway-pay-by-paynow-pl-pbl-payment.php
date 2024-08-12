<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Exception\PaynowException;
use Paynow\Model\PaymentMethods\Type;

class WC_Gateway_Pay_By_Paynow_PL_Pbl_Payment extends WC_Gateway_Pay_By_Paynow_PL {

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
		echo  esc_html( __( 'You will be redirected to payment provider page.', 'pay-by-paynow-pl' ) );
		try {
			$method_block    = 'pbls';
			$methods         = $this->get_only_payment_methods_for_type( array( Type::PBL ) );
			$idempotency_key = WC_Pay_By_Paynow_PL_Keys_Generator::generate_idempotency_key(
				WC_Pay_By_Paynow_PL_Keys_Generator::generate_external_id_from_cart()
			);
			$notices         = $this->gateway->gdpr_notices( $idempotency_key );
			include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH . 'pbl_payment.php';
		} catch ( PaynowException $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error( $exception->getMessage() );
		}
	}

	public function validate_fields(): bool {
		$payment_method_id = $this->get_payment_method_id_from_posted_data();
		if ( empty( $payment_method_id ) ) {
			wc_add_notice( __( 'Please choose bank from the list below to make the payment', 'pay-by-paynow-pl' ), 'error' );

			return false;
		}

		return parent::validate_fields();
	}

	/**
	 * Returns true if payment method is available
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		if ( ! is_admin() ) {
			$payment_methods          = $this->get_only_payment_methods_for_type( array( Type::PBL ) );
			$filtered_payment_methods = array_filter(
				$payment_methods,
				function ( $payment_method ) {
					return $payment_method->isEnabled();
				}
			);

			return parent::is_available() && ! empty( $filtered_payment_methods ) && $this->show_payment_methods;
		}

		return parent::is_available();
	}
}
