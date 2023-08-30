<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Process_Client_Decision_Response
 */
class Leaselink_Process_Client_Decision_Response extends Leaselink_Response {

    public function get_transaction_status() {
        return $this->get_from_result('StatusName');
    }
}
