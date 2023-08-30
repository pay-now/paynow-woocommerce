<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Process_Client_Decision_Request
 */
class Leaselink_Process_Client_Decision_Request extends Leaselink_Request {
    protected $response_model = Leaselink_Process_Client_Decision_Response::class;

    protected $endpoint = 'ProcessClientDecision';

    protected $method = 'put';

    public function __construct($transaction_id) {
        $this->endpoint .= '/' . $transaction_id;
    }

    public function add_auth_token(string $token) {
        $this->set_header('Authorization', 'Bearer ' . $token);
    }
}
