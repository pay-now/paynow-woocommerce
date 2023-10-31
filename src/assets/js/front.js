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
			}
		}).error(function (jqXHR, textStatus, errorThrown) {
			cardMethodOption.removeClass('loading');
		});
	});
});
