<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Pay_By_Paynow_PL_Logger {

	public static $logger;
	const WC_LOG_FILENAME = 'pay-by-paynow-pl';

	public static function log( $message ) {
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

			$settings = get_option( 'woocommerce_paynow_settings' );
			if ( empty( $settings ) || isset( $settings['debug_logs'] ) && 'yes' !== $settings['debug_logs'] ) {
				return;
			}

			if ( WC_Pay_By_Paynow_PL_Helper::is_old_wc_version() ) {
				self::$logger->add( self::WC_LOG_FILENAME, $message );
			} else {
				self::$logger->debug( $message, [ 'source' => self::WC_LOG_FILENAME ] );
			}
		}
	}
}