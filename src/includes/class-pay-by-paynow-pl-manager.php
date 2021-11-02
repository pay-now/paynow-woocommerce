<?php
defined( 'ABSPATH' ) || exit();

class WC_Pay_By_Paynow_Pl_Manager {
	public static $_instance;

	public static function instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private $payment_gateways;

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 10 );
		add_action( 'woocommerce_init', array( $this, 'woocommerce_dependencies' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	public function plugins_loaded() {
		load_plugin_textdomain( 'pay-by-paynow-pl', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages' );
	}

	public function woocommerce_dependencies() {
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/pay-by-paynow-pl-functions.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-paynow-gateway.php';

		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-helper.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-logger.php';

		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/abstract/abstract-wc-gateway-pay-by-paynow-pl.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-notification-handler.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-pay-by-paynow-pl-blik.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-pay-by-paynow-pl-card.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-pay-by-paynow-pl-google-pay.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-pay-by-paynow-pl-pbl.php';

		$this->payment_gateways = apply_filters(
			'wc_pay_by_paynow_pl_payment_gateways',
			array(
				'WC_Payment_Gateway_Pay_By_Paynow_PL_Blik',
				'WC_Payment_Gateway_Pay_By_Paynow_PL_Pbl',
				'WC_Payment_Gateway_Pay_By_Paynow_PL_Card',
				'WC_Payment_Gateway_Pay_By_Paynow_PL_Google_Pay',
			)
		);
	}

	public function enqueue_admin_scripts( $hook ) {
		wp_enqueue_script( 'settings', WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS . 'js/settings.js', array( 'jquery' ), wc_pay_by_paynow_pl_plugin_version() );
	}

	public function payment_gateways() {
		return $this->payment_gateways;
	}
}

function pay_by_paynow_wc() {
	return WC_Pay_By_Paynow_Pl_Manager::instance();
}

pay_by_paynow_wc();
