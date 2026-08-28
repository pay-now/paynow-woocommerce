jQuery( document ).ready(function () {
	addApplePayEnabledToCookie();
	addFingerprintToCardPayment();

	jQuery( 'body' ).on('click', '.paynow-data-processing-info-less .expand', toggleProcessingInfo);

	jQuery( 'body' ).on( 'updated_checkout', function () {
		addFingerprintToCardPayment();
	});

	jQuery(document).on('click', '.paynow-payment-card-menu .paynow-payment-card-menu-button', function (e) {
		jQuery(e.currentTarget).siblings().toggleClass('--hidden');
	});

	jQuery(document).on('click', function (e) {
		if (!jQuery(e.target).is('[data-remove-saved-instrument]') && !jQuery(e.target).is('.paynow-payment-card-menu .paynow-payment-card-menu-button')) {
			jQuery('[data-remove-saved-instrument]').addClass('--hidden')
		}
	});

	jQuery(document).on('click', '[data-remove-saved-instrument]', function (e) {
		const target = jQuery(e.currentTarget);
		const savedInstrument = target.data('removeSavedInstrument');
		const nonce = target.data('nonce');
		const errorMessage = target.data('errorMessage');
		const cardMethodOption = jQuery('#wrapper-' + savedInstrument);

		cardMethodOption.addClass('loading');
		jQuery.ajax(target.data('action'), {
			method: 'POST', type: 'POST',
			data: {
				'savedInstrumentToken': savedInstrument,
				'_wpnonce': nonce,
			},
		}).success(function (data, textStatus, jqXHR) {
			if (data.success === true) {
				cardMethodOption.remove();
			} else {
				cardMethodOption.removeClass('loading');
				showRemoveSavedInstrumentErrorMessage(savedInstrument, errorMessage);
			}
		}).error(function (jqXHR, textStatus, errorThrown) {
			cardMethodOption.removeClass('loading');
			showRemoveSavedInstrumentErrorMessage(savedInstrument, errorMessage);
		});
	});
});

function addApplePayEnabledToCookie() {
	let applePayEnabled = false;

	if (window.ApplePaySession) {
		applePayEnabled = window.ApplePaySession.canMakePayments();
	}

	document.cookie = 'applePayEnabled=' + (applePayEnabled ? '1' : '0');
}

function addFingerprintToCardPayment() {
	const input = jQuery('#payment-method-fingerprint');

	if (!input.length) {
		return;
	}

	try {
		const fpPromise = import('https://static.paynow.pl/scripts/PyG5QjFDUI.min.js')
			.then(FingerprintJS => FingerprintJS.load())

		fpPromise
			.then(fp => fp.get())
			.then(result => {
				input.val(result.visitorId);
			})
	} catch (e) {
		console.error('Cannot get fingerprint');
	}
}

function showRemoveSavedInstrumentErrorMessage(savedInstrument, errorMessage) {
	const errorMessageWrapper = jQuery('#wrapper-' + savedInstrument + ' .paynow-payment-card-error');

	errorMessageWrapper.text(errorMessage);

	setTimeout(() => {
		errorMessageWrapper.text('');
	}, 5000)
}

function toggleProcessingInfo() {
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
