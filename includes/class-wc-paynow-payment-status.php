<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Paynow_Payment_Status {
	const STATUS_NEW = "NEW";
	const STATUS_PENDING = "PENDING";
	const STATUS_REJECTED = "REJECTED";
	const STATUS_CONFIRMED = "CONFIRMED";
	const STATUS_ERROR = "ERROR";
}