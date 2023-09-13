<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Client
 */
class Leaselink_Client {

    private $config;

    private $http;

    public function __construct(Paynow_Settings_Manager $settings_manager) {
        $this->config = new Leaselink_Configuration($settings_manager->leaselink_is_sandbox(), $settings_manager->get_leaselink_api_key());
        $this->http = new Leaselink_HTTP_Client($this->config);
    }

    public function config() {
        return $this->config;
    }

    public function get_client_transaction_status($transaction_id, $token = null) {
        if (empty($token)) {
            $site = $this->register_partner_site();
            $token = $site->get_token();
        }

        $request = new Leaselink_Get_Client_Transaction_Status_Request($transaction_id);
        $request->add_auth_token($token);

        return $this->http->call($request);
    }

    public function get_offer_for_client($products = [], $data = [], $register_partner_response = null) {
        if (empty($register_partner_response)) {
            $register_partner_response = $this->register_partner_site();
        }

        $request = new Leaselink_Offer_For_Client_Request(
            $register_partner_response->get_partner_name(),
            $register_partner_response->get_partner_user_guid(),
            $register_partner_response->get_partner_user_name(),
            '00001',
            !empty($data['save_data_email']) ? $data['save_data_email'] : '',
            !empty($data['save_data_phone']) ? $data['save_data_phone'] : '',
            array_key_exists('full_recalculation', $data) ? $data['full_recalculation'] : true,
            array_key_exists('save_in_process', $data) ? $data['save_in_process'] : false,
            array_key_exists('multi_offer', $data) ? $data['multi_offer'] : false,
            array_key_exists('simulation', $data) ? $data['simulation'] : true
        );
        $request->add_auth_token($register_partner_response->get_token());

        foreach ($products as $product) {
            $quantity = is_array($product) ? $product['qty'] : 1;
            $net_price = is_array($product) ? $product['net_price'] : wc_get_price_excluding_tax($product);
            $gross_price = is_array($product) ? $product['gross_price'] : wc_get_price_including_tax($product);
            $request->add_requested_item(
                is_array($product) ? $product['tax_code'] : $product->get_tax_class(),
                is_array($product) ? $product['tax'] : $product->get_tax_class(),
                $quantity,
                is_array($product) ? $product['name'] : $product->get_title(),
                is_array($product) ? $product['category'] : 'kategoria123',
                $net_price,
                $quantity * $net_price,
                $gross_price,
                $quantity * $gross_price,
                $quantity * ($gross_price - $net_price)
            );
        }

        return $this->http->call($request);
    }

    public function process_client_decision($transaction_id, $token) {
        $request = new Leaselink_Process_Client_Decision_Request($transaction_id);
        $request->add_auth_token($token);

        return $this->http->call($request);
    }

    public function register_partner_site() {
        $request = new Leaselink_Register_Partner_Site_Request($this->config->get_api_key());

        /** @var \Leaselink_Register_Partner_Site_Response $response */
        $response = $this->http->call($request);

        return $response;
    }
}
