<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Register_Partner_Site_Response
 */
class Leaselink_Register_Partner_Site_Response extends Leaselink_Response {

    public function get_partner_name() {
        return $this->get_from_result('PartnerName');
    }

    public function get_partner_user_guid() {
        return $this->get_from_result('PartnerUserGuid');
    }

    public function get_partner_user_name() {
        return $this->get_from_result('PartnerUserName');
    }

    public function get_token() {
        return $this->get_from_result('Token');
    }

    public function to_array()
    {
        return array_merge([
            'partner_name' => $this->get_partner_name(),
            'partner_user_guid' => $this->get_partner_user_guid(),
            'partner_user_name' => $this->get_partner_user_name(),
            'token' => $this->get_token(),
        ], parent::to_array());
    }
}
