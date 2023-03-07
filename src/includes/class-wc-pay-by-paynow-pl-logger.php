<?php

defined( 'ABSPATH' ) || exit();

class WC_Pay_By_Paynow_PL_Logger {

	const WC_LOG_FILENAME = 'pay-by-paynow-pl';
	const DEBUG           = 'debug';
	const INFO            = 'info';
	const WARNING         = 'warning';
	const ERROR           = 'error';

	public static $logger;

	/**
	 * @param $type
	 * @param $message
	 * @param array $context
	 */
	private static function add_log( $type, $message, array $context = array() ) {

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

	/**
	 * @param $message
	 * @param array $context
	 */
	public static function info( $message, array $context = array() ) {

		self::add_log( self::INFO, $message, $context );
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public static function debug( $message, array $context = array() ) {

		self::add_log( self::DEBUG, $message, $context );
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public static function error( $message, array $context = array() ) {

		self::add_log( self::ERROR, $message, $context );
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public static function warning( $message, array $context = array() ) {

		self::add_log( self::WARNING, $message, $context );
	}

	/**
	 * @param string $message Message to log
	 * @param array $context Context of message
	 *
	 * @return string
	 */
	private static function process_record( string $message, array $context = array() ): string {

		$split_message      = explode( '{}', $message );
		$message_part_count = count( $split_message );
		if ( $message_part_count < 2 ) {
			$result_message = $message . ' : ' . json_encode( $context );
		} else {
			$result_message = '';
			for ( $i = 0; $i < $message_part_count; $i++ ) {
				if ( $i > 0 && count( $context ) >= $i ) {
					$value = $context[ $i - 1 ];
					if ( ! is_array( $value ) ) {
						$result_message .= $value;
					}
				}
				$result_message .= $split_message[ $i ];
			}
		}
		return $result_message;
	}
}
