<?php if ( !empty( $notices ) ) { ?>
	<div class="paynow-data-processing-info">
		<?php foreach ( $notices as $notice ) { ?>
			<div class="paynow-data-processing-info-less">
				<?php echo esc_html( $notice->getTitle() ); ?>
				<?php if ( $notice->getContent() ) { ?>&nbsp;
					<span class="expand" data-toggle="collapse" data-target="#paynow_disclaimer_<?php echo esc_attr( $method_block ); ?>" data-expanded-text="<?php echo __( 'Collapse', 'leaselink-plugin-pl' ); ?>" data-collapsed-text="<?php echo __( 'Read more', 'leaselink-plugin-pl' ); ?>">
						<?php echo __( 'Read more', 'leaselink-plugin-pl' ); ?>
                    </span>
				<?php } ?>
			</div>
			<?php if ( $notice->getContent() ) { ?>
				<div class="paynow-data-processing-info-more collapse" id="paynow_disclaimer_<?php echo esc_attr( $method_block ); ?>">
					<?php echo strip_tags( $notice->getContent(), "<p><br><ul><ol><li><strong><a>" ); ?>
				</div>
			<?php } ?>
		<?php } ?>
	</div>
<?php } ?>