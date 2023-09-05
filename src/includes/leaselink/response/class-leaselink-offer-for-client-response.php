<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Offer_For_Client_Response
 */
class Leaselink_Offer_For_Client_Response extends Leaselink_Response {

    public function get_calculation_id()
    {
        return $this->get_from_result('AggragatedCalulationId');
    }

    public function get_client_offer_url()
    {
        return $this->get_from_result('ClientOfferUrl');
    }

    public function get_closing_net_payment()
    {
        return $this->get_from_result('OfferItems.0.ClosingNetPayment');
    }

    public function get_entry_net_payment()
    {
        return $this->get_from_result('OfferItems.0.EntryNetPayment');
    }

    public function get_financial_operation_type_name()
    {
        return $this->get_from_result('FinancialOperationTypeName');
    }

    public function get_financial_product_name()
    {
        return $this->get_from_result('FinancialProductName');
    }

    public function get_monthly_rate_net_value()
    {
        $offer_items = $this->get_from_result('OfferItems');
        $value = 0;

        foreach ($offer_items as $item) {
            if (!empty($item['MonthlyRateNetValue'])) $value += $item['MonthlyRateNetValue'];
        }

        return $value;
    }

    public function get_number_of_rates()
    {
        return $this->get_from_result('OfferItems.0.NumberOfRates');
    }

    public function to_array()
    {
        return parent::to_array();
    }
}
