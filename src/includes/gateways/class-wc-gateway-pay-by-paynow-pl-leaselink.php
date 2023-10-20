<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

class WC_Gateway_Pay_By_Paynow_PL_Leaselink extends WC_Payment_Gateway {
	public function __construct() {
        $this->id = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'leaselink';
        $this->icon = 'https://leaselink.pl/app/themes/leaselink/images/logo_desktop.png';
        $this->title = __( 'Leaselink payment', 'pay-by-paynow-pl' );
        $this->description = 'Płatność za pomocą leaselink';
        $this->method_title = 'paynow.pl - Leaselink';
        $this->method_description = 'Płatność za pomocą leaselink';
        $this->enabled = $this->get_option( 'enabled' );

        // Method with all the options fields
        $this->init_form_fields();

        // This action hook saves the settings
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => 'Enable/Disable',
                'label'       => 'Enable Leaselink Gateway',
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no'
            )
        );
    }

    public function is_available()
    {
        if (!is_checkout() || !parent::is_available()) {
            return parent::is_available();
        }

        $items = WC()->cart->get_cart_contents() ?? [];

        if (empty($items)) {
            return false;
        }

        $products = [];
        foreach ($items as $item) {
            $product = wc_get_product($item['product_id'] ?? false);
            if ($product) {
                $products[] = $product;
            }
        }

        $partner_site = wc_pay_by_paynow()->leaselink()->client()->register_partner_site();
        /** @var \Leaselink_Offer_For_Client_Response $response */
        $response = wc_pay_by_paynow()->leaselink()->client()->get_offer_for_client($products, [], $partner_site);

        if (!$response->is_success()) {
            return false;
        }

        return true;
    }

    public function validate_fields() {
        return true;
    }

    public function process_payment( $order_id ) {
        global $woocommerce;

        $order = wc_get_order( $order_id );
        $order_items = $order->get_items();
        $products = [];

        foreach ($order_items as $order_item) {
            $products[] = [
                'qty' => $order_item->get_quantity(),
                'net_price' => $order->get_item_total($order_item),
                'gross_price' => $order->get_item_total($order_item, true),
                'tax_code' => $order_item->get_tax_class(),
                'tax' => $order_item->get_tax_class(),
                'name' => $order_item->get_name(),
                'category' => 'kategoria123',
            ];
        }

        $partner_site = wc_pay_by_paynow()->leaselink()->client()->register_partner_site();
        /** @var \Leaselink_Offer_For_Client_Response $response */
        $response = wc_pay_by_paynow()->leaselink()->client()->get_offer_for_client($products, [], $partner_site);

        if (!$response->is_success()) {
            throw new Exception('Cannot get offer for client. Please use other payment option.');
        }

        /** @var \Leaselink_Process_Client_Decision_Response $decision */
        $decision = wc_pay_by_paynow()->leaselink()->client()->process_client_decision($response->get_calculation_id(), $partner_site->get_token());

        $order->update_meta_data('_leaselink_status', $decision->get_transaction_status());
        $order->update_meta_data('_leaselink_number', $response->get_calculation_id());
        $order->update_meta_data('_leaselink_form', $response->get_first_offer_financial_operation_type() === 0 ? 'Leasing' : 'Pożyczka');

        $order->save();

        $woocommerce->cart->empty_cart();

        $timestamp_to_check_status = (new DateTime())->add(new DateInterval('PT1H'))->getTimestamp();
        as_schedule_single_action($timestamp_to_check_status, 'leaselink_process_order_status', [$order_id, 0]);

        return array(
            'result' => 'success',
            'redirect' => wc_pay_by_paynow()->leaselink()->client()->config()->get_url() . $response->get_client_offer_url(),
        );
    }
}
