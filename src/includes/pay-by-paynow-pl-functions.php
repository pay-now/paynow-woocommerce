<?php
defined( 'ABSPATH' ) || exit();

function wc_pay_by_paynow_pl_payment_gateways( $gateways ) {
	return array_merge( pay_by_paynow_wc()->payment_gateways(), $gateways );
}

function wc_pay_by_paynow_pl_plugin_version() {
	$plugin_data = get_file_data( WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'pay-by-paynow-pl.php', array( 'Version' => 'Version' ), false );

	return $plugin_data['Version'];
}
