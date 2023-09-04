<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class Leaselink_Order_Status_Processor
 */
class Leaselink_Order_Status_Processor {

    public static function process($order_id, $attempt) {
        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        $number = $order->get_meta('_leaselink_number');

        if (!$number) {
            return;
        }

        /** @var \Leaselink_Get_Client_Transaction_Status_Response $response */
        $response = wc_pay_by_paynow()->leaselink()->client()->get_client_transaction_status($number);

        if (!$response->is_success()) {
            if ($attempt < 5) {
                $attempt++;
                self::schedule_leaselink_status_check($order_id, $attempt);
            }

            return;
        }

        switch ($response->get_status_name()) {
            case 'CANCELLED':
                $order->update_status('cancelled');
                break;
            case 'FINISHED':
                $order->payment_complete();
                $order->reduce_order_stock();
                break;
            default:
                self::schedule_leaselink_status_check($order_id);
        }
    }

    public static function schedule_leaselink_status_check($order_id, $attempt = 0) {
        $timestamp_to_check_status = (new DateTime())->add(new DateInterval('PT1H'))->getTimestamp();
        as_schedule_single_action($timestamp_to_check_status, 'leaselink_process_order_status', [$order_id, $attempt]);
    }
}
