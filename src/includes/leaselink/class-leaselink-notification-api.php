<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Notification_Api
 */
class Leaselink_Notification_Api {

    private const NAMESPACE = 'leaselink';
    private const PATH = 'notification/';

    private $setting_manager;

    public function __construct(Paynow_Settings_Manager $settings_manager) {
        $this->setting_manager = $settings_manager;

        add_action( 'rest_api_init', array($this, 'register_route') );

        $this->setting_manager->set_notification_url(get_rest_url() . self::NAMESPACE . '/' . self::PATH . md5($this->setting_manager->get_leaselink_api_key()));
    }

    public function register_route()
    {
        register_rest_route(
            self::NAMESPACE,
            self::PATH . md5($this->setting_manager->get_leaselink_api_key()),
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'process'),
                'permission_callback' => '__return_true',
            )
        );
    }

    public function process(WP_REST_Request $request)
    {
        $result = $request->get_param('Result');
        if (empty($result) || empty($result['TransactionId'] ?? null)) {
            return new WP_Error( 'no_transaction_id', 'Invalid body - transaction id not found.', array( 'status' => 404 ) );
        }

        $transaction_id = $result['TransactionId'];
        $orders = wc_get_orders([
            'meta_key' => '_leaselink_number',
            'meta_value' => $transaction_id,
            'meta_compare' => '=',
        ]);

        if (empty($orders) || empty($orders[0])) {
            return new WP_Error( 'no_order', 'Invalid transaction id - cannot get order by transaction id.', array( 'status' => 404 ) );
        }

        $order = $orders[0];

        if (!empty($result['StatusName'])) {
            $order->update_meta_data('_leaselink_status', $result['StatusName']);
            $order->save();
        }

        if (!empty($result['InvoiceVatCompanyName'] ?? null)) {
            $order->set_billing_first_name($result['InvoiceVatCompanyName']);
            $order->set_billing_last_name('');
            $order->set_billing_company($result['InvoiceVatIdentificationNumber'] ?? '');
            $order->set_billing_city($result['InvoiceVatAddressCity'] ?? '');
            $order->set_billing_postcode($result['InvoiceVatAddressZipCode'] ?? '');
            $order->set_billing_address_1($result['InvoiceVatAddressStreetName'] ?? '');
            $order->set_billing_address_2($result['InvoiceVatAddressStreetNumber'] ?? '');
            $order->save();
        }

        switch ($result['StatusName']) {
            case 'CANCELLED':
                $order->update_status('cancelled');
                break;
            case 'SIGN_CONTRACT':
            case 'SEND_ASSET':
                $order->payment_complete();
                $order->reduce_order_stock();
                break;
        }
    }
}
