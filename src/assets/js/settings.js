jQuery( document ).ready(function () {
	let $paymentValidityTimeInput    = jQuery( 'input[id$=\'payment_validity_time\']' );
	let $paymentValidityTimeCheckbox = jQuery( 'input[id$=\'use_payment_validity_time_flag\']' );

	if ( ! $paymentValidityTimeCheckbox.is( ':checked' )) {
		$paymentValidityTimeInput.prop( 'disabled', true );
	}

	$paymentValidityTimeCheckbox.change(
		function () {
			if (jQuery( this ).is( ':checked' )) {
				$paymentValidityTimeInput.prop( 'disabled', false );
			} else {
				$paymentValidityTimeInput.prop( 'disabled', true );
			}
		}
	);
});
