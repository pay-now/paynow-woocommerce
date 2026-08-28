<div class="paynow-confirm-blik">
    <h2><?php echo __( 'Confirm BLIK payment', 'pay-by-paynow-pl' ); ?></h2>
    <img src="<?php echo WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS_PATH . 'images/blik-confirm.png'; ?>"
         alt="<?php echo __( 'Confirm the payment using the app on your phone.', 'pay-by-paynow-pl' ); ?>">
</div>
<script>
    let paynow_status_rest_api = '<?php echo esc_url( $rest_api_status_url, null, null );?>';
    let paynow_order_confirmed = '<?php echo esc_attr( $order->get_checkout_order_received_url() );?>'
</script>