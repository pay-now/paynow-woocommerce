jQuery( document ).ready(function () {
	setTimeout(
		function () {
			jQuery( '.paynow-data-processing-info-less .expand' ).on(
				'click',
				function () {
					let $target = jQuery( jQuery( this ).data( 'target' ) );
					if ( ! $target.hasClass( 'show' )) {
						$target.slideDown();
						$target.addClass( 'show' );
						jQuery( this ).text( jQuery( this ).data( 'expanded-text' ) );
					} else {
						$target.slideUp();
						$target.removeClass( 'show' );
						jQuery( this ).text( jQuery( this ).data( 'collapsed-text' ) );
					}
				}
			);
		},
		1000
	);

	addApplePayEnabledToCookie();
});

function addApplePayEnabledToCookie() {
	let applePayEnabled = false;

	if (window.ApplePaySession) {
		applePayEnabled = window.ApplePaySession.canMakePayments();
	}

	document.cookie = 'applePayEnabled=' + (applePayEnabled ? '1' : '0');
}
