/*!
 * Copyright 2014 Open Exchange Rates
 *
 * This source file is subject to the MIT License (MIT) that is bundled with this package in the file license.txt.
 */

jQuery( document ).ready(
	function () {
		setTimeout(
			function () {
				jQuery( '.paynow-data-processing-info-less .expand' ).on(
					'click',
					function () {
						let target = jQuery( jQuery( this ).data( 'target' ) );
						if ( ! target.hasClass( 'show' )) {
							target.slideDown();
							target.addClass( 'show' );
							jQuery( this ).text( jQuery( this ).data( 'expanded-text' ) );
						} else {
							target.slideUp();
							target.removeClass( 'show' );
							jQuery( this ).text( jQuery( this ).data( 'collapsed-text' ) );
						}
					}
				);
			},
			1000
		);
	}
);
