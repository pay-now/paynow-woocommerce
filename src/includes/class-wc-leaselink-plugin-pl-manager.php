<?php

defined( 'ABSPATH' ) || exit();

use LeaselinkPluginPl\Blocks\Payments\Leaselink_Payment;
use LeaselinkPluginPl\Blocks\Payments\Leaselink_Apple_Pay_Payment;
use LeaselinkPluginPl\Blocks\Payments\Leaselink_Blik_Payment;
use LeaselinkPluginPl\Blocks\Payments\Leaselink_Card_Payment;
use LeaselinkPluginPl\Blocks\Payments\Leaselink_Digital_Wallets_Payment;
use LeaselinkPluginPl\Blocks\Payments\Leaselink_Google_Pay_Payment;
use LeaselinkPluginPl\Blocks\Payments\Leaselink_Paywall_Payment;
use LeaselinkPluginPl\Blocks\Payments\Leaselink_Pbl_Payment;

/**
 * Class WC_Leaselink_Plugin_Manager
 */
class WC_Leaselink_Plugin_Manager {

	/**
	 * WC_Leaselink_Plugin_Manager Instance
	 *
	 * @var WC_Leaselink_Plugin_Manager
	 */
	public static $instance;

	/**
	 * Returns WC_Leaselink_Plugin_Manager instance
	 *
	 * @return WC_Leaselink_Plugin_Manager
	 */
	public static function instance(): WC_Leaselink_Plugin_Manager {

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
     * Settings manager instance
     *
     * @var \Leaselink_Settings_Manager
     */
    private $settings_manager;

    /**
     * Leaselink facade
     *
     * @var \Leaselink
     */
    private $leaselink;

	/**
	 * Constructor of WC_Leaselink_Plugin_Manager
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 10 );
		add_action( 'woocommerce_init', array( $this, 'woocommerce_dependencies' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'rest_api_init', 'wc_leaselink_plugin_gateway_rest_init' );
		add_action( 'wp_enqueue_scripts', array( $this, 'wc_pay_by_paynow_pl_gateway_front_resources' ) );
		add_action( 'woocommerce_before_thankyou', 'wc_leaselink_plugin_gateway_content_thankyou', 10, 1 );
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
		add_action( 'woocommerce_blocks_loaded', array( $this, 'register_payment_block' ) );

        $this->setup_settings_manager();
        $this->setup_leaselink();
	}

	/**
	 * Hook invoked after plugin loaded. Adds translations
	 */
	public function plugins_loaded() {

		load_plugin_textdomain( 'leaselink-plugin-pl', false, 'leaselink-plugin-pl/languages' );
	}

	/**
	 * Loads dependencies
	 */
	public function woocommerce_dependencies() {

		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-leaselink-paynow-gateway.php';

		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-wc-leaselink-plugin-pl-helper.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-wc-leaselink-plugin-pl-keys-generator.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-wc-leaselink-plugin-pl-logger.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-wc-leaselink-plugin-pl-locking-mechanism.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-wc-leaselink-plugin-pl-notification-retry-processing-exception.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-wc-leaselink-plugin-pl-notification-stop-processing-exception.php';

		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/abstract/class-wc-gateway-leaselink-plugin-pl-paynow.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-wc-gateway-leaselink-plugin-pl-paynow-notification-handler.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-wc-gateway-leaselink-plugin-pl-paynow-status-handler.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-wc-gateway-leaselink-plugin-pl-paynow-remove-instrument-handler.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-leaselink-plugin-pl-paynow-apple-pay-payment.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-leaselink-plugin-pl-paynow-blik-payment.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-leaselink-plugin-pl-paynow-card-payment.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-leaselink-plugin-pl-paynow-digital-wallets-payment.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-leaselink-plugin-pl-paynow-google-pay-payment.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-pay-by-paynow-pl-leaselink.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-leaselink-plugin-pl-paynow-pbl-payment.php';
		include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-leaselink-plugin-pl-paynow-paywall-payment.php';

		$payment_gateways = array(
			'WC_Gateway_Leaselink_Plugin_PL_Paynow_Blik_Payment',
			'WC_Gateway_Leaselink_Plugin_PL_Paynow_Pbl_Payment',
			'WC_Gateway_Leaselink_Plugin_PL_Paynow_Card_Payment',
			'WC_Gateway_Leaselink_Plugin_PL_Paynow_Digital_Wallets_Payment',
			'WC_Gateway_Pay_By_Paynow_PL_Leaselink',
		);

		if ( ! is_admin() ) {
			$payment_gateways = array_merge(
				$payment_gateways,
				array(
					'WC_Gateway_Leaselink_Plugin_PL_Paynow_Paywall_Payment',
					'WC_Gateway_Leaselink_Plugin_PL_Paynow_Google_Pay_Payment',
					'WC_Gateway_Leaselink_Plugin_PL_Paynow_Apple_Pay_Payment',
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

		wp_enqueue_script( WC_LEASELINK_PLUGIN_PREFIX . 'scripts', WC_LEASELINK_PLUGIN_ASSETS_PATH . 'js/front.js', array( 'jquery' ), wc_leaselink_plugin_version(), true );
		wp_enqueue_style( WC_LEASELINK_PLUGIN_PREFIX . 'styles', WC_LEASELINK_PLUGIN_ASSETS_PATH . 'css/front.css', array(), wc_leaselink_plugin_version() );
	}

	/**
	 * Adds settings.js to footer for admin
	 */
	public function enqueue_admin_scripts() {

		wp_enqueue_script( 'settings', WC_LEASELINK_PLUGIN_ASSETS_PATH . 'js/settings.js', array( 'jquery' ), wc_leaselink_plugin_version(), true );
	}

	/**
	 * Declare High-Performance Order Storage support by plugin
	 */
	public function declare_hpos_compatibility() {

		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WC_LEASELINK_PLUGIN_FILE, true );
		}
	}

	/**
	 * Register payment blocks
	 */
	public function register_payment_block() {
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			require_once WC_LEASELINK_PLUGIN_FILE_PATH . '/Blocks/Payment/abstract/class-leaselink-payment-method.php';
			require_once WC_LEASELINK_PLUGIN_FILE_PATH . '/Blocks/Payment/class-leaselink-payment.php';
			require_once WC_LEASELINK_PLUGIN_FILE_PATH . '/Blocks/Payment/class-leaselink-apple-pay-payment.php';
			require_once WC_LEASELINK_PLUGIN_FILE_PATH . '/Blocks/Payment/class-leaselink-blik-payment.php';
			require_once WC_LEASELINK_PLUGIN_FILE_PATH . '/Blocks/Payment/class-leaselink-card-payment.php';
			require_once WC_LEASELINK_PLUGIN_FILE_PATH . '/Blocks/Payment/class-leaselink-digital-wallets-payment.php';
			require_once WC_LEASELINK_PLUGIN_FILE_PATH . '/Blocks/Payment/class-leaselink-google-pay-payment.php';
			require_once WC_LEASELINK_PLUGIN_FILE_PATH . '/Blocks/Payment/class-leaselink-paywall-payment.php';
			require_once WC_LEASELINK_PLUGIN_FILE_PATH . '/Blocks/Payment/class-leaselink-pbl-payment.php';

			add_action(
				'woocommerce_blocks_payment_method_type_registration',
				function ( $registry ) {
					$registry->register( new Leaselink_Apple_Pay_Payment() );
					$registry->register( new Leaselink_Blik_Payment() );
					$registry->register( new Leaselink_Card_Payment() );
					$registry->register( new Leaselink_Digital_Wallets_Payment() );
					$registry->register( new Leaselink_Google_Pay_Payment() );
					$registry->register( new Leaselink_Paywall_Payment() );
					$registry->register( new Leaselink_Pbl_Payment() );
					$registry->register( new Leaselink_Payment() );
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

    public function leaselink() {

        return $this->leaselink;
    }

    public function settings(): Leaselink_Settings_Manager {

        return $this->settings_manager;
    }

    private function setup_leaselink(): void {

        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/abstract/class-leaselink-request.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/abstract/class-leaselink-response.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/request/class-leaselink-get-client-transaction-status-request.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/request/class-leaselink-offer-for-client-request.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/request/class-leaselink-process-client-decision-request.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/request/class-leaselink-register-partner-site-request.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/response/class-leaselink-get-client-transaction-status-response.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/response/class-leaselink-offer-for-client-response.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/response/class-leaselink-process-client-decision-response.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/response/class-leaselink-register-partner-site-response.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/class-leaselink-client.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/class-leaselink-configuration.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/class-leaselink-http-client.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/class-leaselink-notification-api.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/leaselink/class-leaselink-widget.php';
        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-leaselink.php';

        $this->leaselink = new Leaselink($this->settings_manager);
    }

    private function setup_settings_manager(): void {

        include_once WC_LEASELINK_PLUGIN_FILE_PATH . 'includes/class-leaselink-settings-manager.php';

        $this->settings_manager = new Leaselink_Settings_Manager();
    }
}

/**
 * Returns WC_Leaselink_Plugin_Manager instance
 *
 * @return WC_Leaselink_Plugin_Manager
 */
function wc_leaselink_plugin(): WC_Leaselink_Plugin_Manager {

	return WC_Leaselink_Plugin_Manager::instance();
}

wc_leaselink_plugin();
