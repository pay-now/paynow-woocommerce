<?php

/**
 * Class NotificationRetryProcessing
 *
 * @package Paynow\PaymentGateway\Helper\Exception
 */
class WC_Leaselink_Plugin_Pl_Notification_Retry_Processing_Exception extends Exception {
	public $log_message;
	public $log_context;

	/**
	 * @param string $message
	 * @param array $context
	 */
	public function __construct( $message, $context ) {

		$this->log_message = $message;
		$this->log_context = $context;

		parent::__construct( $message );
	}
}
