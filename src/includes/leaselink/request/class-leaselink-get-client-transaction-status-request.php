<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Get_Client_Transaction_Status_Request
 */
class Leaselink_Get_Client_Transaction_Status_Request extends Leaselink_Request {
    protected $response_model = Leaselink_Get_Client_Transaction_Status_Response::class;

    protected $endpoint = 'ClientTransactionStatus';

    protected $method = 'get';

    public function __construct($transaction_id) {
        $this->endpoint .= '/' . $transaction_id;
    }

    public function add_auth_token(string $token) {
        $this->set_header('Authorization', 'Bearer ' . $token);
    }
}
