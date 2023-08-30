<?php

defined( 'ABSPATH' ) || exit();

use GuzzleHttp\Client;

/**
 * Class Leaselink_HTTP_Client
 */
class Leaselink_HTTP_Client {

    private $client;

    private $config;

    public function __construct(Leaselink_Configuration $config) {
        $this->config = $config;

        $this->client = new Client([
            'base_uri' => $this->config->get_api_url(),
            'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ),
        ]);
    }

    /**
     * @param \Leaselink_Request $request
     *
     * @return \Leaselink_Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function call(Leaselink_Request $request) {
        $response = $this->client->request($request->get_method(), $request->get_endpoint(), [
            'headers' => $this->prepare_headers($request),
            'json' => $request->to_array(),
        ]);

        return $request->create_response($response);
    }

    private function prepare_headers(Leaselink_Request $request): array {
        return array_merge([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ], $request->get_headers());
    }
}
