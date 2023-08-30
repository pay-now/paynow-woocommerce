<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Register_Partner_Site_Request
 */
class Leaselink_Register_Partner_Site_Request extends Leaselink_Request {
    protected $response_model = Leaselink_Register_Partner_Site_Response::class;

    protected $endpoint = 'RegisterPartnerSite';

    protected $method = 'post';

    public function __construct(string $api_key, int $user = 0) {
        $this->set('ApiKey', $api_key);

        if ($user) {
            $this->set('UserId', $user);
        }
    }
}
