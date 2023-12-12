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

	onWidgetConfigurationChange: function () {
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
	}
}

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

	leaselink.onWidgetConfigurationChange();
});

function addApplePayEnabledToCookie() {
	let applePayEnabled = false;

	if (window.ApplePaySession) {
		applePayEnabled = window.ApplePaySession.canMakePayments();
	}

	document.cookie = 'applePayEnabled=' + (applePayEnabled ? '1' : '0');
}
