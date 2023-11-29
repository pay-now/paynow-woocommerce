<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

class WC_Gateway_Pay_By_Paynow_PL_Leaselink extends WC_Payment_Gateway {
	public function __construct() {
        $this->id = WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'leaselink';
        $this->icon = 'https://leaselink.pl/app/themes/leaselink/images/logo_desktop.png';
        $this->title = __( 'Leasing and Installments for Companies', 'leaselink-plugin-pl' );
        $this->description = __('online 24/7, decision in 5 minutes', 'leaselink-plugin-pl');
        $this->method_title = __( 'Leasing and Installments for Companies', 'leaselink-plugin-pl' );
        $this->method_description = __('online 24/7, decision in 5 minutes', 'leaselink-plugin-pl');
        $this->enabled = $this->get_option( 'enabled' );

        // Method with all the options fields
        $this->init_form_fields();

        // This action hook saves the settings
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __( 'Enable/Disable', 'leaselink-plugin-pl' ),
                'label'       => __( 'Enable Leaselink Gateway', 'leaselink-plugin-pl' ),
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
            $product = $order_item->get_product();
            $products[] = [
                'qty' => $order_item->get_quantity(),
                'net_price' => $order->get_item_total($order_item),
                'gross_price' => $order->get_item_total($order_item, true),
                'tax_code' => $order_item->get_tax_class(),
                'tax' => $order_item->get_tax_class(),
                'name' => $order_item->get_name(),
                'categories' => WC_Pay_By_Paynow_PL_Helper::get_product_categories( $product->get_id() ),
            ];
        }

        $partner_site = wc_pay_by_paynow()->leaselink()->client()->register_partner_site();
        /** @var \Leaselink_Offer_For_Client_Response $response */
        $response = wc_pay_by_paynow()->leaselink()->client()->get_offer_for_client($products, [
            'customer_external_document' => sprintf('%s', $order->get_id()),
            'save_in_process' => true,
            'save_data_email' => $order->get_billing_email(),
            'save_data_phone' => $order->get_billing_phone(),
            'simulation' => false,
        ], $partner_site);

        if (!$response->is_success()) {
            throw new Exception(__('Cannot get offer for client. Please use other payment option.', 'leaselink-plugin-pl' ));
        }

        /** @var \Leaselink_Process_Client_Decision_Response $decision */
        $decision = wc_pay_by_paynow()->leaselink()->client()->process_client_decision($response->get_calculation_id(), $partner_site->get_token());

        $order->update_meta_data('_leaselink_status', $decision->get_transaction_status());
        $order->update_meta_data('_leaselink_number', $response->get_calculation_id());
        $order->update_meta_data('_leaselink_form', $response->get_first_offer_financial_operation_type() === 0 ? __('Leasing', 'leaselink-plugin-pl' ) : __('Loan', 'leaselink-plugin-pl' ));

        $order->update_status('on-hold');

        $order->save();

        $woocommerce->cart->empty_cart();

        return array(
            'result' => 'success',
            'redirect' => wc_pay_by_paynow()->leaselink()->client()->config()->get_url() . $response->get_client_offer_url(),
        );
    }
}
