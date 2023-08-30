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
        $this->description = 'Platność za pomocą leaselink opis';
        $this->method_title = 'paynow.pl - Leaselink';
        $this->method_description = 'Platność za pomocą leaselink opis metody';
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
            throw new Exception('Cannot get offer for client.');
        }

        /** @var \Leaselink_Process_Client_Decision_Response $decision */
        $decision = wc_pay_by_paynow()->leaselink()->client()->process_client_decision($response->get_calculation_id(), $partner_site->get_token());

        $order->update_meta_data('_leaselink_status', $decision->get_transaction_status());
        $order->update_meta_data('_leaselink_number', $response->get_calculation_id());
        $order->update_meta_data('_leaselink_form', $response->get_financial_product_name());

        $order->save();

        $woocommerce->cart->empty_cart();

        wp_schedule_event();

        return array(
            'result' => 'success',
            'redirect' => wc_pay_by_paynow()->leaselink()->client()->config()->get_url() . $response->get_client_offer_url(),
        );
    }
}
