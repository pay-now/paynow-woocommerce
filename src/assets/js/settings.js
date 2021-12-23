/*!
 * Copyright 2014 Open Exchange Rates
 *
 * This source file is subject to the MIT License (MIT) that is bundled with this package in the file license.txt.
 */

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
