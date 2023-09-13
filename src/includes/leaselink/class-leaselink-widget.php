<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Widget
 */
class Leaselink_Widget {

    const FINANCIAL_OPERATION_NAME_MAP = [
        0 => 'Leasing',
        2 => 'Pożyczka'
    ];

    const WIDGET_COLOR_SETTING_MAP = [
        Paynow_Settings_Manager::SETTING_WIDGET_COLOR_BLACK => [
            'image' => 'arrow-purple.svg',
            'class' => 'paynow-leaselink__color-black',
        ],
        Paynow_Settings_Manager::SETTING_WIDGET_COLOR_WHITE => [
            'image' => 'arrow-purple.svg',
            'class' => 'paynow-leaselink__color-white',
        ],
        Paynow_Settings_Manager::SETTING_WIDGET_COLOR_BLACK_GHOST => [
            'image' => 'arrow-black.svg',
            'class' => 'paynow-leaselink__color-black-ghost',
        ],
        Paynow_Settings_Manager::SETTING_WIDGET_COLOR_WHITE_GHOST => [
            'image' => 'arrow-white.svg',
            'class' => 'paynow-leaselink__color-white-ghost',
        ]
    ];

    private $client;

    private $setting_manager;

    public function __construct(Leaselink_Client $client, Paynow_Settings_Manager $settings_manager) {
        $this->client = $client;
        $this->setting_manager = $settings_manager;
    }

    public function render($products = null) {
        $products_ids = $this->get_products_ids_as_array($products);
        $products = $this->get_products($products_ids);

        if (empty($products)) {
            return;
        }

        /** @var \Leaselink_Offer_For_Client_Response $offer */
        $offer = $this->get_offer_for_client($products);

        if (!$offer->is_success()) {
            return;
        }

        $data = $this->prepare_data_from_offer($offer, $products);
        $data = array_merge($data, self::WIDGET_COLOR_SETTING_MAP[$this->setting_manager->get_leaselink_widget_color()]);

        include WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . WC_PAY_BY_PAYNOW_PL_PLUGIN_TEMPLATES_PATH . 'leaselink_widget.phtml';
    }

    private function get_products_ids_as_array($products) {
        if (empty($products)) {
            $product = wc_get_product();

            return $product ? [$product->get_id()] : [];
        } else {
            return is_array($products) ? $products : explode(',', $products);
        }
    }

    private function get_products($ids) {
        $products = [];

        foreach ($ids as $id) {
            $product = wc_get_product($id);

            if ($product) {
                $products[] = $product;
            }
        }

        return $products;
    }

    private function get_offer_for_client($products) {
        return $this->client->get_offer_for_client($products, [
            'multi_offer' => true
        ]);
    }

    /**
     * @param \Leaselink_Offer_For_Client_Response $offer
     */
    private function prepare_data_from_offer($offer, $products) {
        return [
            'rates' => $offer->get_available_rates_number(),
            'entry_payment_options' => $offer->get_available_entry_payment(),
            'closing_payment_options' => $offer->get_available_closing_payment(),
            'widget_products' => $products,
            'widget_products_sum' => $this->calculate_products_sum($products),
            'widget_net_value' => $offer->get_first_offer_monthly_rate_net_value(),
            'checked_rate' => $offer->get_first_offer_number_of_rates(),
            'entry_net_payment' => $offer->get_first_offer_entry_net_payment(),
            'entry_net_payment_percent' => $offer->get_first_offer_entry_net_payment_percent(),
            'closing_net_payment' => $offer->get_first_offer_closing_net_payment(),
            'closing_net_payment_percent' => $offer->get_first_offer_closing_net_payment_percent(),
            'financial_product_name' => self::FINANCIAL_OPERATION_NAME_MAP[$offer->get_first_offer_financial_operation_type()] ?? self::FINANCIAL_OPERATION_NAME_MAP[0],
            'help_tooltip_class' => $this->get_help_tooltip_class($offer->get_available_financial_operations()),
            'offers' => $this->get_mapped_offer_items($offer),
        ];
    }

    private function calculate_products_sum($products) {
        $sum = 0;

        foreach ($products as $product) {
            $price = wc_get_price_excluding_tax($product);

            if ($price) {
                $sum += $price;
            }
        }

        return $sum;
    }

    private function get_help_tooltip_class($operations) {
        if (count($operations) !== 1) {
            return '';
        }

        return ($operations[0] ?? 0) === 0 ? '--only-leasing' : '--only-loan';
    }

    /**
     * @param \Leaselink_Offer_For_Client_Response $offer
     */
    private function get_mapped_offer_items($offer) {
        $offer_items = $offer->get_offer_items();
        $mapped_offers = [];

        foreach ($offer_items as $offer) {
            $mapped_offers[] = [
                'rates' => $offer['NumberOfRates'],
                'entry_payment' => wc_price($offer['EntryNetPayment']) . ' (netto)',
                'entry_payment_percent' => $offer['EntryPaymentPct'],
                'closing_payment' => wc_price($offer['ClosingNetPayment']) . ' (netto)',
                'closing_payment_percent' => $offer['ClosingPaymentPct'],
                'financial_operation_name' => self::FINANCIAL_OPERATION_NAME_MAP[$offer['FinancialOperationType']] ?? self::FINANCIAL_OPERATION_NAME_MAP[0],
                'monthly_net_value' => wc_price($offer['MonthlyRateNetValue']),
            ];
        }

        return $mapped_offers;
    }
}
