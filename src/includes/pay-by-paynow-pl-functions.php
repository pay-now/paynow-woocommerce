<?php
defined( 'ABSPATH' ) || exit();

function wc_pay_by_paynow_pl_payment_gateways( $gateways ) {
	return array_merge( pay_by_paynow_wc()->payment_gateways(), $gateways );
}

function wc_pay_by_paynow_pl_plugin_version() {
	$plugin_data = get_file_data( WC_PAY_BY_PAYNOW_PL_PLUGIN_FILE_PATH . 'pay-by-paynow-pl.php', array( 'Version' => 'Version' ), false );

	return $plugin_data['Version'];
}

function wc_pay_by_paynow_pl_gateway_content( $content ) {
	global $wp_query;

	if ( ! empty( $wp_query->post->ID ) &&
	     $wp_query->post->ID == (int) get_option( WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . WC_Pay_By_Paynow_Pl_Page::CONFIRM_BLIK_PAYMENT_ID . '_id' ) ) {
		wp_enqueue_script( 'paynow-confirm-blik', WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS_PATH . 'js/confirm-blik.js', array( 'jquery' ), wc_pay_by_paynow_pl_plugin_version() );
		return render_template( WC_Pay_By_Paynow_Pl_Page::CONFIRM_BLIK_PAYMENT_ID );
	}

	return $content;
}

function wc_pay_by_paynow_pl_gateway_hide_pages( $pages ) {
	$blik_confirm_page_id = (int) get_option( WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . WC_Pay_By_Paynow_Pl_Page::CONFIRM_BLIK_PAYMENT_ID . '_id' );
	foreach ( $pages as $index => $page ) {
		if ( $page->ID == $blik_confirm_page_id || $page->object_id == $blik_confirm_page_id ) {
			unset( $pages[ $index ] );
		}
	}

	return $pages;
}

function render_template($name): string {
	ob_start();
	include 'templates/' . $name . '.phtml';
	return ob_get_clean();
}

function paynow_check_payment_status( $data ): WP_REST_Response {
	return (new WC_Gateway_Pay_By_Paynow_PL_Status_Handler())->get_rest_status($data['orderId'], $data['token']);
}

function paynow_rest_status_init()
{
	register_rest_route('paynow', 'status', array(
		'methods'   => WP_REST_Server::READABLE,
		'callback'  => 'paynow_check_payment_status'
	));
}