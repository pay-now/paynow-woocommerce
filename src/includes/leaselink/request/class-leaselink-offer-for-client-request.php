<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Offer_For_Client_Request
 */
class Leaselink_Offer_For_Client_Request extends Leaselink_Request {
    protected $response_model = Leaselink_Offer_For_Client_Response::class;

    protected $endpoint = 'OfferForClient';

    protected $method = 'post';

    public function __construct(
        string $partner_name,
        string $partner_user_guid,
        string $partner_user_name,
        string $customer_external_document,
        string $save_data_email = '',
        string $save_data_phone = '',
        bool $full_recalculation = true,
        bool $save_in_process = false,
        bool $multi_offer = false,
        bool $simulation = true
    ) {
        $this->set('PartnerName', $partner_name);
        $this->set('PartnerUserGuid', $partner_user_guid);
        $this->set('PartnerUserName', $partner_user_name);
        $this->set('CustomerExternalDocument', $customer_external_document);
        $this->set('FullRecalculation', $full_recalculation);
        $this->set('SaveInProcess', $save_in_process);
        $this->set('MultiOffer', $multi_offer);
        $this->set('Simulation', $simulation);

        if ($save_data_email) {
            $this->set('SaveDataEmail', $save_data_email);
        }

        if ($save_data_phone) {
            $this->set('SaveDataPhone', $save_data_phone);
        }
    }

    public function add_auth_token(string $token) {
        $this->set_header('Authorization', 'Bearer ' . $token);
    }

    public function add_requested_item(
        $tax_code,
        $tax,
        $quantity,
        $name,
        $category_level_1,
        $net_price,
        $net_value,
        $gross_price,
        $gross_value,
        $tax_value
    ) {
        $this->add_to_data_array('RequestItems', [
            'TaxCode' => $tax_code,
            'Tax'=> $tax,
            'Quantity'=> $quantity,
            'Name'=> $name,
            'CategoryLevel1'=> $category_level_1,
            'NetPrice'=> $net_price,
            'NetValue'=> $net_value,
            'GrossPrice'=> $gross_price,
            'GrossValue'=> $gross_value,
            'TaxValue'=> $tax_value
        ]);
    }

    public function add_requestor_by_nip(string $nip, string $name) {
        $this->add_to_data_array('Requestors', [
            'IdentificationNumberType1' => '1',
            'IdentificationNumber1' => $nip,
            'RequestorName' => $name
        ]);
    }

    public function add_requestor_by_regon(string $regon, string $name) {
        $this->add_to_data_array('Requestors', [
            'IdentificationNumberType1' => '2',
            'IdentificationNumber1' => $regon,
            'RequestorName' => $name
        ]);
    }
}
