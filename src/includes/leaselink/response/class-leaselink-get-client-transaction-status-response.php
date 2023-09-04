<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Get_Client_Transaction_Status_Response
 */
class Leaselink_Get_Client_Transaction_Status_Response extends Leaselink_Response {

    public function get_status_name() {
        return $this->get_from_result('StatusName');
    }
}
