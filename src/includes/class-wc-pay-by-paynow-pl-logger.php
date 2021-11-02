<?php
defined( 'ABSPATH' ) || exit();

class WC_Pay_By_Paynow_PL_Logger {

	public static $logger;
	const WC_LOG_FILENAME = 'pay-by-paynow-pl';
	const DEBUG           = 'debug';
	const INFO            = 'info';
	const WARNING         = 'warning';
	const ERROR           = 'error';

	private static function add_log( $type, $message, $context = array() ) {
		if ( ! class_exists( 'WC_Logger' ) ) {
			return;
		}

		if ( apply_filters( 'wc_paynow_logging', true, $message ) ) {
			if ( empty( self::$logger ) ) {
				if ( WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
					self::$logger = new WC_Logger();
				} else {
					self::$logger = wc_get_logger();
				}
			}

			$settings = get_option( 'woocommerce_pay_by_paynow_pl_settings' );
			if ( empty( $settings ) || isset( $settings['debug_logs'] ) && 'yes' !== $settings['debug_logs'] ) {
				return;
			}

			if ( WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
				self::$logger->add( self::WC_LOG_FILENAME, $message );
			} else {
				self::$logger->{$type}( self::process_record( $message, $context ), array( 'source' => self::WC_LOG_FILENAME ) );
			}
		}
	}

	public static function info( $message, $context = array() ) {
		self::add_log( self::INFO, $message, $context );
	}

	public static function debug( $message, $context = array() ) {
		self::add_log( self::DEBUG, $message, $context );
	}

	public static function error( $message, $context = array() ) {
		self::add_log( self::ERROR, $message, $context );
	}

	public static function warning( $message, $context = array() ) {
		self::add_log( self::WARNING, $message, $context );
	}

	private static function process_record( $message, $context ) {
		$split_message      = explode( '{}', $message );
		$message_part_count = sizeof( $split_message );
		$result_message     = '';
		for ( $i = 0; $i < $message_part_count; $i ++ ) {
			if ( $i > 0 && sizeof( $context ) >= $i ) {
				$paramValue = $context[ $i - 1 ];
				if ( ! is_array( $paramValue ) ) {
					$result_message .= $paramValue;
				}
			}
			$messagePart     = $split_message[ $i ];
			$result_message .= $messagePart;
		}

		return $result_message;
	}
}
