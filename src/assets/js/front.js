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
		function (ev) {
			jQuery('[data-leaselink-calculator]').removeClass('paynow-leaselink__calculator__hidden');
			const products = jQuery(ev.currentTarget).data('products');
			jQuery.ajax({
				url: woocommerce_params ? woocommerce_params.ajax_url : '/wp-admin/admin-ajax.php',
				type: 'GET',
				data: {
					action: 'leaselink_get_offer_for_client',
					products: products ? products : null
				},
				success: function (response) {
				}
			});
		}
	);

	jQuery('[data-leaselink-calculator-close]').on(
		'click',
		function () {
			jQuery('[data-leaselink-calculator]').addClass('paynow-leaselink__calculator__hidden');
		}
	);

	jQuery(
		'.paynow-leaselink__calculator input[type=radio][name="rates"], .paynow-leaselink__calculator input[type=radio][name="first-pay"], .paynow-leaselink__calculator input[type=radio][name="buy-for"]'
	).change(
		function () {
		}
	);
});
