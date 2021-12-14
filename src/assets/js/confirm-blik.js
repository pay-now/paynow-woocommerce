/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License (MIT)
 * that is bundled with this package in the file LICENSE.md.
 *
 * @author mElements S.A.
 * @copyright mElements S.A.
 * @license   MIT License
 **/
(function ($) {
    let  pollPaymentStatus = setInterval(function () {
            checkPaymentStatus();
        }, 3000),

        checkPaymentStatus = function () {
            jQuery.ajax({
                url: paynow_status_rest_api,
                dataType: 'json',
                type: 'get',
                success: function (message) {
                    console.log(message)
                    if (message.payment_status !== "PENDING") {
                        clearInterval(pollPaymentStatus);
                        window.location.replace(message.redirect_url);
                    }
                },
                error: function () {

                }
            });
        };

    setTimeout(() => {
        redirectToReturn()
    }, 60000);
})();