jQuery(function ($) {
    $('body').on('DOMSubtreeModified', '#payment', function () {
        $('input[name="payment_method"]').on('change', function () {
            if ('pay_by_paynow_pl_pbl' === $('input[name="payment_method"]:checked').val()) {
                $('button#place_order').prop('disabled', true);
            } else {
                $('button#place_order').prop('disabled', false);
            }
        });
        $('input[name="paymentMethodId"]').on('change', function () {
            $('button#place_order').prop('disabled', false);
        });
    });
});