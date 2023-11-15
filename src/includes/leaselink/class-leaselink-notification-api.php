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
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'process'),
                'permission_callback' => '__return_true',
            )
        );
    }

    public function process(WP_REST_Request $request)
    {
        $logger_context = [
            'service' => 'Leaselink notification api',
            'params' => $request->get_params(),
            'body' => $request->get_body(),
            'headers' => $request->get_headers(),
        ];
        WC_Pay_By_Paynow_PL_Logger::info('Processing notification', $logger_context);

        $transaction_id = $request->get_param('TransactionId');
        if (empty($transaction_id)) {
            WC_Pay_By_Paynow_PL_Logger::error('Invalid body - transaction id not found.', $logger_context);
            return new WP_Error( 'no_transaction_id', 'Invalid body - transaction id not found.', array( 'status' => 404 ) );
        }

        $logger_context['transaction_id'] = $transaction_id;
        $orders = wc_get_orders([
            'meta_key' => '_leaselink_number',
            'meta_value' => $transaction_id,
            'meta_compare' => '=',
        ]);

        if (empty($orders) || empty($orders[0])) {
            WC_Pay_By_Paynow_PL_Logger::error('Invalid transaction id - cannot get order by transaction id.', $logger_context);
            return new WP_Error( 'no_order', 'Invalid transaction id - cannot get order by transaction id.', array( 'status' => 404 ) );
        }

        $order = $orders[0];
        $logger_context['order'] = $order->get_id();

        $status = $request->get_param('StatusName');
        if (!empty($status)) {
            $order->update_meta_data('_leaselink_status', $status);
            $order->save();
        }

        if (!empty($request->get_param('InvoiceVatCompanyName'))) {
            $order->set_billing_first_name($request->get_param('InvoiceVatCompanyName'));
            $order->set_billing_last_name('');
            $order->set_billing_company($request->get_param('InvoiceVatIdentificationNumber') ?? '');
            $order->set_billing_city($request->get_param('InvoiceVatAddressCity') ?? '');
            $order->set_billing_postcode($request->get_param('InvoiceVatAddressZipCode') ?? '');
            $order->set_billing_address_1(($request->get_param('InvoiceVatAddressStreetName') ?? '') . ' ' . ($request->get_param('InvoiceVatAddressStreetNumber') ?? '') . ' ' . ($request->get_param('InvoiceVatAddressLocationNumber') ?? ''));
            $order->set_billing_address_2('');
            $order->save();
        }

        switch ($status) {
            case 'CANCELLED':
                $order->update_status('cancelled');
                break;
            case 'SIGN_CONTRACT':
                $order->payment_complete();
                wc_reduce_stock_levels($order->get_id());
                $order->add_order_note(sprintf(__('LeaseLink – The contract has been signed. Issue an invoice and send it to <a href="mailto:partner@leaselink.pl">partner@leaselink.pl</a>, providing the process number: %s', 'pay-by-paynow-pl'), $transaction_id));
                break;
            case 'SEND_ASSET':
                $order->payment_complete();
                wc_reduce_stock_levels($order->get_id());
                break;
        }

        WC_Pay_By_Paynow_PL_Logger::info('Notification processed successfully', $logger_context);

        return new WP_REST_Response();
    }
}
