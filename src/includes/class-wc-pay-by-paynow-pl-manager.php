<?php

defined( 'ABSPATH' ) || exit();

use PayByPaynowPl\Blocks\Payments\Paynow_Blik_Payment;
use PayByPaynowPl\Blocks\Payments\Paynow_Card_Payment;
use PayByPaynowPl\Blocks\Payments\Paynow_Digital_Wallets_Payment;
use PayByPaynowPl\Blocks\Payments\Paynow_PayPo_Payment;
use PayByPaynowPl\Blocks\Payments\Paynow_Paywall_Payment;
use PayByPaynowPl\Blocks\Payments\Paynow_Pbl_Payment;

/**
 * Class WC_Pay_By_Paynow_Pl_Manager
 */
class WC_Pay_By_Paynow_Pl_Manager {

	/**
	 * WC_Pay_By_Paynow_Pl_Manager Instance
	 *
	 * @var WC_Pay_By_Paynow_Pl_Manager
	 */
	public static $instance;

	/**
	 * Returns WC_Pay_By_Paynow_Pl_Manager instance
	 *
	 * @return WC_Pay_By_Paynow_Pl_Manager
	 */
	public static function instance(): WC_Pay_By_Paynow_Pl_Manager {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Available payment gateways
	 *
	 * @var array
	 */
	private $payment_gateways = array();

	/**
	 * Constructor of WC_Pay_By_Paynow_Pl_Manager
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 10 );
		add_action( 'woocommerce_init', array( $this, 'woocommerce_dependencies' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'rest_api_init', 'wc_pay_by_paynow_pl_gateway_rest_init' );
		add_action( 'wp_enqueue_scripts', array( $this, 'wc_pay_by_paynow_pl_gateway_front_resources' ) );
		add_action( 'woocommerce_before_thankyou', 'wc_pay_by_paynow_pl_gateway_content_thankyou', 10, 1 );
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
		add_action( 'woocommerce_blocks_loaded', array( $this, 'register_payment_block' ) );
	}

	/**
	 * Hook invoked after plugin loaded. Adds translations
	 */
	public function plugins_loaded() {

		load_plugin_textdomain( 'pay-by-paynow-pl', false, WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'languages' );
	}

	/**
	 * Loads dependencies
	 */
	public function woocommerce_dependencies() {

		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-paynow-gateway.php';

		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-helper.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-keys-generator.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-logger.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-locking-mechanism.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-notification-retry-processing-exception.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-notification-stop-processing-exception.php';

		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/abstract/class-wc-gateway-pay-by-paynow-pl.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-gateway-pay-by-paynow-pl-notification-handler.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-gateway-pay-by-paynow-pl-status-handler.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-gateway-pay-by-paynow-pl-remove-instrument-handler.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-pay-by-paynow-pl-blik-payment.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-pay-by-paynow-pl-card-payment.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-pay-by-paynow-pl-digital-wallets-payment.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-pay-by-paynow-pl-paypo-payment.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-pay-by-paynow-pl-pbl-payment.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-pay-by-paynow-pl-paywall-payment.php';

		$payment_gateways = array(
			'WC_Gateway_Pay_By_Paynow_PL_Blik_Payment',
			'WC_Gateway_Pay_By_Paynow_PL_Pbl_Payment',
			'WC_Gateway_Pay_By_Paynow_PL_Card_Payment',
			'WC_Gateway_Pay_By_Paynow_PL_Digital_Wallets_Payment',
			'WC_Gateway_Pay_By_Paynow_PL_Paypo_Payment',
		);

		if ( ! is_admin() || is_admin() && isset( $_GET['page'] ) && 'wc-settings' !== $_GET['page'] ) {
			$payment_gateways = array_merge(
				$payment_gateways,
				array(
					'WC_Gateway_Pay_By_Paynow_PL_Paywall_Payment',
				)
			);
		}

		$this->payment_gateways = apply_filters(
			'wc_pay_by_paynow_pl_payment_gateways',
			$payment_gateways
		);
	}

	/**
	 * Adds js and css to front
	 */
	public function wc_pay_by_paynow_pl_gateway_front_resources() {

		wp_enqueue_script( WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'scripts', WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS_PATH . 'js/front.js', array( 'jquery' ), wc_pay_by_paynow_pl_plugin_version(), true );
		wp_enqueue_style( WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'styles', WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS_PATH . 'css/front.css', array(), wc_pay_by_paynow_pl_plugin_version() );
	}

	/**
	 * Adds settings.js to footer for admin
	 */
	public function enqueue_admin_scripts() {

		wp_enqueue_script( 'settings', WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS_PATH . 'js/settings.js', array( 'jquery' ), wc_pay_by_paynow_pl_plugin_version(), true );
	}

	/**
	 * Declare High-Performance Order Storage support by plugin
	 */
	public function declare_hpos_compatibility() {

		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE, true );
		}
	}

	/**
	 * Register payment blocks
	 */
	public function register_payment_block() {
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			require_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . '/Blocks/Payment/abstract/class-paynow-payment-method.php';
			require_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . '/Blocks/Payment/class-paynow-blik-payment.php';
			require_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . '/Blocks/Payment/class-paynow-card-payment.php';
			require_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . '/Blocks/Payment/class-paynow-digital-wallets-payment.php';
			require_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . '/Blocks/Payment/class-paynow-paypo-payment.php';
			require_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . '/Blocks/Payment/class-paynow-paywall-payment.php';
			require_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . '/Blocks/Payment/class-paynow-pbl-payment.php';

			add_action(
				'woocommerce_blocks_payment_method_type_registration',
				function ( $registry ) {
					$registry->register( new Paynow_Blik_Payment() );
					$registry->register( new Paynow_Card_Payment() );
					$registry->register( new Paynow_Digital_Wallets_Payment() );
					$registry->register( new Paynow_PayPo_Payment() );
					$registry->register( new Paynow_Paywall_Payment() );
					$registry->register( new Paynow_Pbl_Payment() );
				}
			);
		}
	}

	/**
	 * Returns available payment gateways
	 *
	 * @return array
	 */
	public function payment_gateways(): array {

		return $this->payment_gateways;
	}
}

/**
 * Returns WC_Pay_By_Paynow_Pl_Manager instance
 *
 * @return WC_Pay_By_Paynow_Pl_Manager
 */
function wc_pay_by_paynow(): WC_Pay_By_Paynow_Pl_Manager {

	return WC_Pay_By_Paynow_Pl_Manager::instance();
}

wc_pay_by_paynow();
