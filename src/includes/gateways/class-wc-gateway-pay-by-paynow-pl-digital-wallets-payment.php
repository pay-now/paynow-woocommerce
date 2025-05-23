<?php

use Paynow\Model\PaymentMethods\Type;

defined( 'ABSPATH' ) || exit();

class WC_Gateway_Pay_By_Paynow_PL_Digital_Wallets_Payment extends WC_Gateway_Pay_By_Paynow_PL {

	public function __construct() {
		$this->id                 = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'digital_wallets';
		$this->title              = __( 'Digital wallets', 'pay-by-paynow-pl' );
		$this->description        = __( 'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' );
		$this->method_title       = __( 'paynow.pl - Digital wallets payments', 'pay-by-paynow-pl' );
		$this->method_description = __( 'Accept Digital wallets payments with paynow.pl', 'pay-by-paynow-pl' );
		parent::__construct();
	}

	public function payment_fields() {
		echo  esc_html( __( 'You will be redirected to payment provider page.', 'pay-by-paynow-pl' ) );
		try {
			$method_block = 'digital-wallets';

			$methods         = $this->get_available_methods();
			$idempotency_key = WC_Pay_By_Paynow_PL_Keys_Generator::generate_idempotency_key(
				WC_Pay_By_Paynow_PL_Keys_Generator::generate_external_id_from_cart()
			);
			$notices         = $this->gateway->gdpr_notices( $idempotency_key );
			include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH . 'digital_wallets_payment.php';
		} catch ( PaynowException $exception ) {
			WC_Pay_By_Paynow_PL_Logger::error( $exception->getMessage() );
		}
	}

	public function is_available(): bool {
		$payments = $this->get_available_methods();
		$payments = array_values(
			array_filter(
				$payments,
				function ( $payment ) {

					return $payment->isEnabled();
				}
			)
		);

		$this->icon = $this->generate_icon( $payments );

		return parent::is_available() && count( $payments ) > 0 && $this->show_payment_methods;
	}

	public function get_paynow_icon_url(): string {
		return $this->icon;
	}

	private function generate_icon( $payments ): string {
		if ( count( $payments ) === 1 ) {
			return $payments[0]->getImage();
		}

		$types = array_map(
			function( $dw ) {
				return strtolower( substr( $dw->getType(), 0, 1 ) );
			},
			$payments
		);

		sort( $types );
		$types = implode( '', $types );

		return WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS_PATH . 'images/digital-wallets-' . $types . '.svg';
	}

	private function get_available_methods(): array {
		$methods = array(
			Type::CLICK_TO_PAY => null,
			Type::GOOGLE_PAY   => null,
			Type::APPLE_PAY    => null,
		);

		if ( ! WC_Gateway_Pay_By_Paynow_PL_Click_To_Pay_Payment::is_available_for_digital_wallets() ) {
			unset( $methods[ Type::CLICK_TO_PAY ] );
		}

		$available_methods = $this->get_only_payment_methods_for_type( array_keys( $methods ) );
		foreach ( $available_methods as $method ) {
			$methods[ $method->getType() ] = $method;
		}

		return array_values( array_filter( $methods ) );
	}
}
