<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Configuration
 */
class Leaselink_Configuration {
    public const PRODUCTION_URL = 'https://online.leaselink.pl';
    public const SANDBOX_URL = 'https://onlinetest.leaselink.pl';
    public const PRODUCTION_API_URL = self::PRODUCTION_URL . '/api/';
    public const SANDBOX_API_URL = self::SANDBOX_URL . '/api/';
    public const API_ENV_PRODUCTION = 'production';
    public const API_ENV_SANDBOX = 'sandbox';

    private $data = [];

    public function __construct(bool $is_sandbox, string $apiKey) {
        $this->data['env'] = $is_sandbox ? self::API_ENV_SANDBOX : self::API_ENV_PRODUCTION;
        $this->data['url'] = $is_sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
        $this->data['api_url'] = $is_sandbox ? self::SANDBOX_API_URL : self::PRODUCTION_API_URL;
        $this->data['api_key'] = $apiKey;
    }

    public function get_env(): string {
        return $this->get('env');
    }

    public function get_api_key(): ?string {
        return $this->get('api_key');
    }

    public function get_url(): ?string {
        return $this->get('url');
    }

    public function get_api_url(): ?string {
        return $this->get('api_url');
    }

    private function get(string $key) {
        return $this->data[$key] ?? null;
    }
}
