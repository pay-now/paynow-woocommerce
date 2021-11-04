(function ($) {
	$( document ).ready(
		function () {

			var paymentValidityTimeInput    = $( 'input[id$=\'payment_validity_time\']' );
			var paymentValidityTimeCheckbox = $( 'input[id$=\'use_payment_validity_time_flag\']' );

			if ( ! paymentValidityTimeCheckbox.is( ':checked' )) {
				paymentValidityTimeInput.prop( 'disabled', true );
			}

			paymentValidityTimeCheckbox.change(
				function() {
					if ($( this ).is( ':checked' )) {
						paymentValidityTimeInput.prop( 'disabled', false );
					} else {
						paymentValidityTimeInput.prop( 'disabled', true );
					}
				}
			);

		}
	);
})( jQuery );
