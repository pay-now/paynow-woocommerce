<input type="hidden" name="paymentMethodFingerprint" id="payment-method-fingerprint" value="">
<?php if ( !empty( $instruments ) ): ?>
    <p><?php echo __( 'Select a saved card or enter new card details:', 'pay-by-paynow-pl' ); ?></p>
    <div class="paynow-payment-option-pbls">
		<?php foreach ( $instruments as $instrument ):?>
            <div class="paynow-payment-card-option" id="wrapper-<?php echo esc_attr( $instrument->getToken() ); ?>">
                <input type="radio" name="paymentMethodToken" value="<?php echo esc_attr( $instrument->getToken() ); ?>" id="<?php echo esc_attr( $instrument->getToken() ); ?>" <?php echo $instrument->isExpired() ? 'disabled' : ''; ?>>
                <label for="<?php echo esc_attr( $instrument->getToken() ); ?>">
                    <div class="paynow-payment-card-image">
                        <img src="<?php echo esc_attr( $instrument->getImage() ); ?>" alt="<?php echo esc_attr( $instrument->getBrand() ); ?>">
                    </div>
                    <div class="paynow-payment-card-details">
                        <?php if ( $instrument->isExpired() ): ?>
                            <p class="paynow-payment-card-details-card-name paynow-expired"><?php echo __( 'Card:', 'pay-by-paynow-pl' ); ?> <?php echo esc_attr( $instrument->getName() ); ?></p>
                            <p class="paynow-payment-card-details-expiration paynow-expired"><?php echo __( 'Expired:', 'pay-by-paynow-pl' ); ?> <?php echo esc_attr( $instrument->getExpirationDate() ); ?></p>
                        <?php else: ?>
                            <p class="paynow-payment-card-details-card-name"><?php echo __( 'Card:', 'pay-by-paynow-pl' ); ?> <?php echo esc_attr( $instrument->getName() ); ?></p>
                            <p class="paynow-payment-card-details-expiration"><?php echo __( 'Expires:', 'pay-by-paynow-pl' ); ?> <?php echo esc_attr( $instrument->getExpirationDate() ); ?></p>
                        <?php endif; ?>
                    </div>
                </label>
                <div class="paynow-payment-card-menu">
                    <button class="paynow-payment-card-menu-button" type="button">
                        <?php echo __( 'remove', 'pay-by-paynow-pl' ); ?>
                    </button>
                    <button
                            class="paynow-payment-card-remove --hidden" type="button"
                            data-remove-saved-instrument="<?php echo esc_attr( $instrument->getToken() ); ?>"
                            data-action="<?php echo $remove_saved_instrument_action; ?>"
                            data-nonce="<?php echo wp_create_nonce( 'wp_rest' ); ?>"
                            data-error-message="<?php echo __( 'An error occurred while deleting the saved card.', 'pay-by-paynow-pl' ); ?>"
                    >
                        <?php echo __( 'Remove card', 'pay-by-paynow-pl' ); ?>
                    </button>
                </div>
                <span class="paynow-payment-card-error"></span>
            </div>
        <?php endforeach; ?>
        <div class="paynow-payment-card-option">
            <input type="radio" name="paymentMethodToken" value="" id="paymentMethodToken-default">
            <label for="paymentMethodToken-default">
                <div class="paynow-payment-card-image">
                    <img src="<?php echo WC_PAY_BY_PAYNOW_PL_PLUGIN_ASSETS_PATH . 'images/card-default.svg'; ?>" alt="Card default icon">
                </div>
                <div class="paynow-payment-card-details">
                    <p class="paynow-payment-card-details-card-name"><?php echo __( 'Enter your new card details', 'pay-by-paynow-pl' ); ?></p>
                    <p class="paynow-payment-card-details-expiration"><?php echo __( 'You can save it in the next step', 'pay-by-paynow-pl' ); ?></p>
                </div>
            </label>
        </div>
    </div>
	<?php include( 'data_processing_info.php' ); ?>
<?php else: ?>
    <p><?php echo __(  'You will be redirected to payment provider page.', 'pay-by-paynow-pl' ); ?></p>
    <div class="paynow-data-processing-info">
        <p><?php echo __(  'Secure and fast payments provided by paynow.pl', 'pay-by-paynow-pl' ); ?></p>
    </div>
<?php endif; ?>
