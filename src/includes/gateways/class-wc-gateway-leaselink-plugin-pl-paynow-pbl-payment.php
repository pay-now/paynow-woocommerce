<?php
defined( 'ABSPATH' ) || exit();

use Paynow\Exception\PaynowException;
use Paynow\Model\PaymentMethods\Type;

class WC_Gateway_Leaselink_Plugin_PL_Paynow_Pbl_Payment extends WC_Gateway_Leaselink_Plugin_PL_Paynow {

	public function __construct() {
		$this->id                 = WC_LEASELINK_PLUGIN_PREFIX . 'pbl';
		$this->title              = __( 'Online transfer payment', 'leaselink-plugin-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'leaselink-plugin-pl' );
		$this->method_title       = __( 'LeaseLink - Online payments', 'leaselink-plugin-pl' );
		$this->method_description = __( 'Accept online transfer payments with paynow.pl', 'leaselink-plugin-pl' );
		$this->icon               = 'https://static.paynow.pl/brand/paynow_logo_black.png';
		$this->has_fields         = true;
		parent::__construct();
	}

	public function payment_fields() {
		echo  esc_html( __( 'You will be redirected to payment provider page.', 'leaselink-plugin-pl' ) );
		try {
			$method_block    = 'pbls';
			$methods         = $this->get_only_payment_methods_for_type( Type::PBL );
			$idempotency_key = WC_Leaselink_Plugin_PL_Keys_Generator::generate_idempotency_key(
				WC_Leaselink_Plugin_PL_Keys_Generator::generate_external_id_from_cart()
			);
			$notices         = $this->gateway->gdpr_notices( $idempotency_key );
			include WC_LEASELINK_PLUGIN_FILE_PATH . WC_LEASELINK_PLUGIN_TEMPLATES_PATH . 'pbl_payment.php';
		} catch ( PaynowException $exception ) {
			WC_Leaselink_Plugin_PL_Logger::error( $exception->getMessage() );
		}
	}

	public function validate_fields(): bool {
		$payment_method_id = $this->get_payment_method_id_from_posted_data();
		if ( empty( $payment_method_id ) ) {
			wc_add_notice( __( 'Please choose bank from the list below to make the payment', 'leaselink-plugin-pl' ), 'error' );

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
			$payment_methods          = $this->get_only_payment_methods_for_type( Type::PBL );
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