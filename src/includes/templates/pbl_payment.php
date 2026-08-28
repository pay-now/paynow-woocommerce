<?php
if ( ! empty( $methods ) ) {
	?>
    <p><?php echo __( 'Choose bank:', 'pay-by-paynow-pl' ); ?></p>
    <div class="paynow-payment-option-pbls">
		<?php
		foreach ( $methods as $method ) {
			?>
            <div class="paynow-payment-option-pbl<?php echo ! $method->isEnabled() ? ' disabled' : ''; ?>">
                <input type="radio" name="paymentMethodId" value="<?php echo esc_attr( $method->getId() ); ?>"
                       id="paynow_method_<?php echo esc_attr( $method->getId() ); ?>"<?php echo ! $method->isEnabled() ? ' disabled' : ''; ?> />
                <label for="paynow_method_<?php echo esc_attr( $method->getId() ); ?>">
                    <img src="<?php echo esc_attr( $method->getImage() ); ?>" alt="<?php echo esc_attr( $method->getDescription() ); ?>"/>
                </label>
            </div>
			<?php
		}
		?>
    </div>
	<?php
    include( 'data_processing_info.php' );
}
?>