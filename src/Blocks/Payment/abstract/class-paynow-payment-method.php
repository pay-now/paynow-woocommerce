<?php

namespace PayByPaynowPl\Blocks\Payments;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Class PaynowPaymentMethod
 */
class Paynow_Payment_Method extends AbstractPaymentMethodType {

	/**
	 * @var null|WC_Gateway_Pay_By_Paynow_PL
	 */
	protected $payment_method = null;

	/**
	 * Initializes block.
	 *
	 * @return void
	 */
	public function initialize() {
		$payment_methods = WC()->payment_gateways()->payment_gateways();

		foreach ( $payment_methods as $key => $gateway ) {
			if ( $key === $this->name ) {
				$this->payment_method = $gateway;
				break;
			}
		}
	}

	/**
	 * Checks if the payment method is active or not.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return 'yes' === ( $this->payment_method ? $this->payment_method->enabled : 'no' );
	}

	/**
	 * @return false|string|null
	 */
	protected function get_payment_fields() {
		if ( empty( $this->payment_method ) ) {
			return null;
		}

		ob_start();
		$this->payment_method->payment_fields();
		$fields = ob_get_clean();

		return $fields;
	}

	/**
	 * @return bool
	 */
	protected function is_available() {
		if ( empty( $this->payment_method ) ) {
			return false;
		}

		return $this->payment_method->is_available();
	}
}
