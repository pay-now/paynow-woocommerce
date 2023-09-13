<?php

defined( 'ABSPATH' ) || exit();

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
     * Settings manager instance
     *
     * @var \Paynow_Settings_Manager
     */
    private $settings_manager;

    /**
     * Leaselink facade
     *
     * @var \Paynow_Leaselink
     */
    private $leaselink;

	/**
	 * Constructor of WC_Pay_By_Paynow_Pl_Manager
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 10 );
		add_action( 'woocommerce_init', array( $this, 'woocommerce_dependencies' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'rest_api_init', 'wc_pay_by_paynow_pl_gateway_rest_status_init' );
		add_action( 'wp_enqueue_scripts', array( $this, 'wc_pay_by_paynow_pl_gateway_front_resources' ) );
		add_action( 'woocommerce_before_thankyou', 'wc_pay_by_paynow_pl_gateway_content_thankyou', 10, 1 );

        $this->setup_settings_manager();
        $this->setup_leaselink();
	}

	/**
	 * Hook invoked after plugin loaded. Adds translations
	 */
	public function plugins_loaded() {

		load_plugin_textdomain( 'pay-by-paynow-pl', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages' );
	}

	/**
	 * Loads dependencies
	 */
	public function woocommerce_dependencies() {

		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/pay-by-paynow-pl-functions.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-paynow-gateway.php';

		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-helper.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-logger.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-locking-mechanism.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-notification-retry-processing-exception.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-pay-by-paynow-pl-notification-stop-processing-exception.php';

		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/abstract/class-wc-gateway-pay-by-paynow-pl.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-gateway-pay-by-paynow-pl-notification-handler.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-wc-gateway-pay-by-paynow-pl-status-handler.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-pay-by-paynow-pl-blik-payment.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-pay-by-paynow-pl-card-payment.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-pay-by-paynow-pl-google-pay-payment.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-pay-by-paynow-pl-leaselink.php';
		include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-gateway-pay-by-paynow-pl-pbl-payment.php';

		$this->payment_gateways = apply_filters(
			'wc_pay_by_paynow_pl_payment_gateways',
			array(
				'WC_Gateway_Pay_By_Paynow_PL_Blik_Payment',
				'WC_Gateway_Pay_By_Paynow_PL_Pbl_Payment',
				'WC_Gateway_Pay_By_Paynow_PL_Card_Payment',
				'WC_Gateway_Pay_By_Paynow_PL_Google_Pay_Payment',
                'WC_Gateway_Pay_By_Paynow_PL_Leaselink',
			)
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

    public function settings(): Paynow_Settings_Manager {

        return $this->settings_manager;
    }

    private function setup_leaselink(): void {

        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/abstract/class-leaselink-request.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/abstract/class-leaselink-response.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/request/class-leaselink-get-client-transaction-status-request.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/request/class-leaselink-offer-for-client-request.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/request/class-leaselink-process-client-decision-request.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/request/class-leaselink-register-partner-site-request.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/response/class-leaselink-get-client-transaction-status-response.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/response/class-leaselink-offer-for-client-response.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/response/class-leaselink-process-client-decision-response.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/response/class-leaselink-register-partner-site-response.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/class-leaselink-client.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/class-leaselink-configuration.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/class-leaselink-http-client.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/class-leaselink-order-status-processor.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/leaselink/class-leaselink-widget.php';
        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-paynow-leaselink.php';

        $this->leaselink = new Paynow_Leaselink($this->settings_manager);
    }

    private function setup_settings_manager(): void {

        include_once WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'includes/class-paynow-settings-manager.php';

        $this->settings_manager = new Paynow_Settings_Manager();
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
