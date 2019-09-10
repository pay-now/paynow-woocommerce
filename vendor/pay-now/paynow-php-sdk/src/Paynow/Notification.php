<?php

namespace Paynow;

use InvalidArgumentException;
use Paynow\Exception\SignatureVerificationException;
use Paynow\Util\SignatureCalculator;
use UnexpectedValueException;

/**
 * Class Notification
 *
 * @package Paynow
 */
class Notification
{
    /**
     * Notification constructor
     *
     * @param $signatureKey
     * @param $payload
     * @param $headers
     * @throws SignatureVerificationException
     */
    public function __construct($signatureKey, $payload, $headers)
    {
        if (!$payload) {
            throw new InvalidArgumentException("No payload has been provided");
        }

        if (!$headers) {
            throw new InvalidArgumentException("No headers have been provided");
        }

        $this->verify($signatureKey, $this->parsePayload($payload), $headers);
    }

    /**
     * Parse payload
     *
     * @param $payload
     * @return mixed
     */
    private function parsePayload($payload)
    {
        $data = json_decode(trim($payload), true);
        $error = json_last_error();

        if (!$data && $error !== JSON_ERROR_NONE) {
            throw new UnexpectedValueException("Invalid payload: $error");
        }

        return $data;
    }

    /**
     * Verify payload Signature
     *
     * @param $signatureKey
     * @param $data
     * @param array $headers
     * @return bool
     * @throws SignatureVerificationException
     */
    private function verify($signatureKey, $data, array $headers)
    {
        $calculatedSignature = (string)new SignatureCalculator($signatureKey, $data);
        var_dump($calculatedSignature);
        if ($calculatedSignature !== $this->getPayloadSignature($headers)) {
            throw new SignatureVerificationException("Signature mismatched for payload");
        }

        return true;
    }

    /**
     * Retrieve Signature from payload
     *
     * @param array $headers
     * @return mixed
     * @throws SignatureVerificationException
     */
    private function getPayloadSignature(array $headers)
    {
        if (!isset($headers['Signature']) || !$headers['Signature']) {
            throw new SignatureVerificationException("No signature was found for payload");
        }

        return $headers['Signature'];
    }
}
