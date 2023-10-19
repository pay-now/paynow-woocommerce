<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Offer_For_Client_Response
 */
class Leaselink_Offer_For_Client_Response extends Leaselink_Response {

    public function get_available_closing_payment () {
        $closing_payment = $this->get_options_from_offer_items('ClosingPaymentPct');
        sort($closing_payment, SORT_NUMERIC);

        return $closing_payment;
    }

    public function get_available_entry_payment () {
        $entry_payment = $this->get_options_from_offer_items('InitialPaymentPct');
        sort($entry_payment, SORT_NUMERIC);

        return $entry_payment;
    }

    public function get_available_rates_number() {
        $rates = $this->get_options_from_offer_items('NumberOfRates');
        sort($rates, SORT_NUMERIC);

        return $rates;
    }

    public function get_available_financial_operations() {
        return $this->get_options_from_offer_items('FinancialOperationType');
    }

    public function get_calculation_id() {
        return $this->get_from_result('AggragatedCalulationId');
    }

    public function get_client_offer_url() {
        return $this->get_from_result('ClientOfferUrl');
    }

    public function get_first_offer_closing_net_payment() {
        return $this->get_from_result('OfferItems.0.ClosingNetPayment');
    }

    public function get_first_offer_closing_net_payment_percent() {
        return $this->get_from_result('OfferItems.0.ClosingPaymentPct');
    }

    public function get_first_offer_entry_net_payment() {
        return $this->get_from_result('OfferItems.0.EntryNetPayment');
    }

    public function get_first_offer_entry_net_payment_percent() {
        return $this->get_from_result('OfferItems.0.InitialPaymentPct');
    }

    public function get_first_offer_financial_operation_type() {
        return $this->get_from_result('OfferItems.0.FinancialOperationType');
    }

    public function get_first_offer_number_of_rates() {
        return $this->get_from_result('OfferItems.0.NumberOfRates');
    }

    public function get_first_offer_monthly_rate_net_value() {
        return $this->get_from_result('OfferItems.0.MonthlyRateNetValue');
    }

    public function get_offer_items() {
        return $this->get_from_result('OfferItems');
    }

    public function to_array() {
        return parent::to_array();
    }

    private function get_options_from_offer_items($key) {
        $offer_items = $this->get_offer_items();
        $options = [];

        foreach ($offer_items as $offer) {
            if (array_key_exists($key, $offer) && !in_array($offer[$key], $options)) {
                $options[] = $offer[$key];
            }
        }

        return $options;
    }
}
