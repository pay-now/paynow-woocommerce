const leaselink = {
	helpTooltip: {
		selector: '[data-leaselink-calculator-help]',
		hiddenClass: 'paynow-leaselink__calculator__help__hidden',
		show: function () {
			jQuery(leaselink.helpTooltip.selector).removeClass(leaselink.helpTooltip.hiddenClass);
		},
		hide: function () {
			jQuery(leaselink.helpTooltip.selector).addClass(leaselink.helpTooltip.hiddenClass);
		}
	},

	calculator: {
		selector: '[data-leaselink-calculator]',
		hiddenClass: 'paynow-leaselink__calculator__hidden',
		show: function () {
			jQuery(leaselink.calculator.selector).removeClass(leaselink.calculator.hiddenClass);
		},
		hide: function () {
			jQuery(leaselink.calculator.selector).addClass(leaselink.calculator.hiddenClass);
		}
	},

	attachListeners: function () {
		jQuery('[data-leaselink-calculator-help-open]').on('click', leaselink.helpTooltip.show);
		jQuery('[data-leaselink-calculator-help-close]').on('click', leaselink.helpTooltip.hide);

		jQuery('[data-leaselink-widget-button]').on('click', leaselink.calculator.show);
		jQuery('[data-leaselink-calculator-close]').on('click', leaselink.calculator.hide);

		jQuery('[data-leaselink-calculator]').on(
			'click',
			function (ev) {
				if (jQuery(ev.target).is('[data-leaselink-calculator]')) {
					leaselink.calculator.hide();
					leaselink.helpTooltip.hide();
				}
			}
		);

		jQuery('[data-leaselink-calculator-help]').on(
			'click',
			function (ev) {
				if (jQuery(ev.target).is('[data-leaselink-calculator-help]')) {
					leaselink.helpTooltip.hide();
				}
			}
		);

		jQuery(
			'.paynow-leaselink__calculator input[type=radio][name="rates"], .paynow-leaselink__calculator input[type=radio][name="entry-payment"], .paynow-leaselink__calculator input[type=radio][name="closing-payment"]'
		).change(leaselink.onWidgetConfigurationChange);
	},

	onWidgetConfigurationChange: function () {
		if (!window.leaselink_offers_json) {
			return;
		}

		const numberOfRates = parseInt(jQuery('[data-leaselink-calculator] input[name="rates"]:checked').val());
		const offersFilteredByRates = window.leaselink_offers_json.filter((offer) => offer.rates === numberOfRates);
		const availableEntryPayments = offersFilteredByRates.map((offer) => offer.entry_payment_percent).filter((entry, index, array) => array.indexOf(entry) === index);
		const availableClosingPayment = offersFilteredByRates.map((offer) => offer.closing_payment_percent).filter((closing, index, array) => array.indexOf(closing) === index);

		let entryPaymentPercent = parseFloat(jQuery('[data-leaselink-calculator] input[name="entry-payment"]:checked').val());
		entryPaymentPercent = availableEntryPayments.includes(entryPaymentPercent) ? entryPaymentPercent : availableEntryPayments[0];
		const offersFilteredByEntryPayment = offersFilteredByRates.filter((offer) => offer.entry_payment_percent === entryPaymentPercent);

		let closingPaymentPercent = parseFloat(jQuery('[data-leaselink-calculator] input[name="closing-payment"]:checked').val());
		closingPaymentPercent = availableClosingPayment.includes(closingPaymentPercent) ? closingPaymentPercent : availableClosingPayment[0];
		const offersFilteredByClosingPayment = offersFilteredByEntryPayment.filter((offer) => offer.closing_payment_percent === closingPaymentPercent);

		const offer = offersFilteredByClosingPayment[0];

		jQuery('[data-leaselink-calculator] [data-entry-netto-payment]').html(offer.entry_payment);
		jQuery('[data-leaselink-calculator] [data-closing-netto-payment]').html(offer.closing_payment);
		jQuery('[data-leaselink-calculator] [data-financial-product-name]').html(offer.financial_operation_name);
		jQuery('[data-leaselink-calculator] [data-monthly-netto-payment]').html(offer.monthly_net_value);
		jQuery('[data-leaselink-calculator] [data-monthly-payment-is-netto]').html(offer.is_netto ? 'netto' : 'brutto');

		jQuery('[data-leaselink-calculator] input[name="entry-payment"]').each(function () {
			const inputValue = parseFloat(jQuery(this).val());
			if (availableEntryPayments.includes(inputValue)) {
				jQuery(this).prop("disabled", false);
			} else {
				jQuery(this).prop("disabled", true);
			}

			if (inputValue === entryPaymentPercent) {
				jQuery(this).prop("checked", true);
			} else {
				jQuery(this).prop("checked", false);
			}
		});

		jQuery('[data-leaselink-calculator] input[name="closing-payment"]').each(function () {
			const inputValue = parseFloat(jQuery(this).val());
			if (availableClosingPayment.includes(inputValue)) {
				jQuery(this).prop("disabled", false);
			} else {
				jQuery(this).prop("disabled", true);
			}

			if (inputValue === closingPaymentPercent) {
				jQuery(this).prop("checked", true);
			} else {
				jQuery(this).prop("checked", false);
			}
		});
	},

	refreshWidget: function () {
		const productId = jQuery('input.variation_id').val();
		const ajaxUrl = woocommerce_params ? woocommerce_params.wc_ajax_url.replace('%%endpoint%%', 'get_leaselink_widget') : null;

		jQuery.post(ajaxUrl, { product_id: productId }).done(function (res) {
			jQuery('[data-leaselink-calculator]').remove();
			jQuery('.paynow-leaselink').replaceWith(res.html);

			leaselink.attachListeners();
			leaselink.onWidgetConfigurationChange();
		});
	}
}

jQuery( document ).ready(function () {
	addApplePayEnabledToCookie();
	addFingerprintToCardPayment();

	leaselink.attachListeners();
	leaselink.onWidgetConfigurationChange();

	jQuery('form.variations_form').on('woocommerce_variation_has_changed', leaselink.refreshWidget);

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
