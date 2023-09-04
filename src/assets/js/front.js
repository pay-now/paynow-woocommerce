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

	jQuery('[data-leaselink-widget-button]').on(
		'click',
		function () {
			jQuery('[data-leaselink-calculator]').removeClass('paynow-leaselink__calculator__hidden');
		}
	);

	jQuery('[data-leaselink-calculator-close]').on(
		'click',
		function () {
			jQuery('[data-leaselink-calculator]').addClass('paynow-leaselink__calculator__hidden');
		}
	);

	jQuery(
		'.paynow-leaselink__calculator input[type=radio][name="rates"], .paynow-leaselink__calculator input[type=radio][name="entry-payment"], .paynow-leaselink__calculator input[type=radio][name="closing-payment"]'
	).change(
		function (ev) {
			const calculator_element = jQuery('[data-leaselink-calculator] .paynow-leaselink__calculator');
			calculator_element.addClass( 'processing' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			const products = calculator_element.data('products');
			const number_of_rates = jQuery('[data-leaselink-calculator] input[name="rates"]:checked').val();
			const entry_payment_percent = jQuery('[data-leaselink-calculator] input[name="entry-payment"]:checked').val();
			const closing_payment_percent = jQuery('[data-leaselink-calculator] input[name="closing-payment"]:checked').val();
			jQuery.ajax({
				url: woocommerce_params ? woocommerce_params.ajax_url : '/wp-admin/admin-ajax.php',
				type: 'GET',
				data: {
					action: 'leaselink_get_offer_for_client',
					products: products ? products : null,
					number_of_rates: number_of_rates ? number_of_rates : 60,
					entry_payment_percent: entry_payment_percent ? entry_payment_percent : 1,
					closing_payment_percent: closing_payment_percent ? closing_payment_percent : 1,
				},
				success: function (response) {
					if (response.entry_payment) {
						jQuery('[data-leaselink-calculator] [data-entry-netto-payment]').html(response.entry_payment);
					}

					if (response.closing_payment) {
						jQuery('[data-leaselink-calculator] [data-closing-netto-payment]').html(response.closing_payment);
					}

					if (response.financial_product) {
						jQuery('[data-leaselink-calculator] [data-financial-product-name]').html(response.financial_product);
					}

					if (response.monthly_rate) {
						jQuery('[data-leaselink-calculator] [data-monthly-netto-payment]').html(response.monthly_rate);
					}

					if (response.number_of_rates) {
						jQuery('[data-leaselink-calculator] input[name="rates"][value="' + response.number_of_rates + '"]').prop("checked", true);
					}
				},
				complete: function() {
					calculator_element.removeClass( 'processing' ).unblock();
				}
			});
		}
	);
});
