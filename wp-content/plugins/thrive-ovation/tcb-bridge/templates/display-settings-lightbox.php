<div class="tvo-editor-lightbox">
	<div class="tvo-frontend-modal">
		<h4>
			<?php echo __( 'Testimonial Display Settings', TVO_TRANSLATE_DOMAIN ); ?>
		</h4>
		<hr class="tve_lightbox_line"/>
		<div id="tvo-testimonial-display" class="tvo-break-word">
		</div>
		<div class="tvo-footer tvo-row tvo-no-mb">
			<div class="tvo-col tvo-s6">
				<a class="tvo-back tve_editor_button tve_editor_button_default">
					<?php echo __( 'Back', TVO_TRANSLATE_DOMAIN ); ?>
				</a>
			</div>
			<div class="tvo-col tvo-s6">
				<a class="tvo-next tve_editor_button tve_editor_button_success tvo-right">
					<?php echo __( 'Continue', TVO_TRANSLATE_DOMAIN ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
<?php
$templates = tve_dash_get_backbone_templates( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/backbone', 'backbone' );
tve_dash_output_backbone_templates( $templates );
?>
<?php if ( ! empty( $_POST['ajax_load'] ) ) : ?>
	<script type="text/javascript">
		jQuery( document ).ready( function () {
			Backbone.history.stop();
			Backbone.history.start();

			if ( typeof TVO_Front.active_element === 'undefined' ) {
				TVO_Front.router.navigate( '#start', {trigger: true} );
			} else {
				TVO_Front.router.navigate( '#pre-select', {trigger: true} );
			}

			if ( ! TVO_Front.data ) {
				TVO_Front.data = {};
				TVO_Front.data.tags = <?php echo json_encode( tvo_get_formatted_tags() ); ?>;
				TVO_Front.data.testimonials = <?php echo json_encode( tvo_get_formatted_testimonials() ); ?>;
			}
		} );
	</script>
<?php endif; ?>
