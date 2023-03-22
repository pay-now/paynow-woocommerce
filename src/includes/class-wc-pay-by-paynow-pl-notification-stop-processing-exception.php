<?php

/**
 * Class NotificationRetryProcessing
 *
 * @package Paynow\PaymentGateway\Helper\Exception
 */
class WC_Pay_By_Paynow_Pl_Notification_Stop_Processing_Exception extends Exception {

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
