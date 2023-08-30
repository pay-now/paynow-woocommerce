<?php

defined( 'ABSPATH' ) || exit();

use Psr\Http\Message\ResponseInterface;

/**
 * Class Leaselink_Response
 */
class Leaselink_Response {

    protected $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public static function create_from_guzzle_response(ResponseInterface $response) {
        return new static(json_decode($response->getBody(), true));
    }

    public function get_guid() {
        return $this->data['Guid'] ?? null;
    }

    public function get_raw_data() {
        return $this->data;
    }

    public function get_status_code() {
        return $this->data['StatusCode'] ?? null;
    }

    public function is_success() {
        return !empty($this->get_status_code()) && $this->get_status_code() === 200;
    }

    public function to_array() {
        return [
            'status_code' => $this->get_status_code(),
            'guid' => $this->get_guid(),
        ];
    }

    protected function get_from_result(string $key) {
        return $this->get('Result.' . $key);
    }

    protected function get(string $key) {
        $keys = explode('.', $key);

        $current = $this->data;
        foreach ($keys as $currentKey) {
            if (!empty($current[$currentKey])) {
                $current = $current[$currentKey];
            } else {
                return null;
            }
        }

        return $current;
    }
}
