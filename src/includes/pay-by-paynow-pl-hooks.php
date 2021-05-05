<?php
defined( 'ABSPATH' ) || exit();

add_filter( 'woocommerce_payment_gateways', 'wc_pay_by_paynow_pl_payment_gateways' );