(function () {
	let pollPaymentStatus = setInterval(
		function () {
			checkPaymentStatus();
		},
		3000
	),

	checkPaymentStatus = function () {
		jQuery.ajax(
			{
				url: paynow_status_rest_api,
				dataType: 'json',
				type: 'get',
				success: function (message) {
					if (message.payment_status !== "PENDING") {
						clearInterval( pollPaymentStatus );
						window.location.replace( message.redirect_url );
					}
				},
				error: function () {
					window.location.replace( paynow_order_confirmed );
				}
			}
		);
	};

	setTimeout(
		() => {
			window.location.replace( paynow_order_confirmed );
		},
		60000
	);
})();
