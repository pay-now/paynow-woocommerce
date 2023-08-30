<?php

defined( 'ABSPATH' ) || exit();

use Psr\Http\Message\ResponseInterface;

/**
 * Class Leaselink_Request
 */
class Leaselink_Request {
    protected $response_model;

    protected $data = [];

    protected $headers = [];

    protected $endpoint;

    protected $method;

    public function create_response(ResponseInterface $response) {
        return $this->response_model::create_from_guzzle_response($response);
    }

    public function get_endpoint() {
        return $this->endpoint;
    }

    public function get_headers() {
        return $this->headers;
    }

    public function get_method() {
        return $this->method;
    }

    public function to_array() {
        return $this->data;
    }

    protected function add_to_data_array(string $key, $data) {
        $this->data[$key][] = $data;
    }

    protected function set(string $key, $data) {
        $this->data[$key] = $data;
    }

    protected function set_header(string $header, string $value) {
        $this->headers[$header] = $value;
    }
}
