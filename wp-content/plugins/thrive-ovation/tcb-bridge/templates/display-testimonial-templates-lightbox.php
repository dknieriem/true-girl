<?php
$selected_template = empty( $_POST['template'] ) ? 'grid/default-template-grid' : $_POST['template'];
$next_button       = empty( $_POST['next'] ) ? 'update' : $_POST['next'];
$func              = ( $next_button == 'update' ? 'function:tvo.display.save_template' : 'controls.lb_open' );
$templates         = tvo_get_testimonial_templates( 'display' );
?>
<div class="tvo-frontend-modal">
	<h4>
		<?php echo __( 'Testimonial Display Templates', TVO_TRANSLATE_DOMAIN ) ?>
	</h4>
	<hr class="tve_lightbox_line">
	<div class="tvo_display_templates tvo_templates">
		<div class="tve_overflow_y">
			<?php foreach ( $templates as $file => $template ) : ?>
				<div class="tve_grid_cell tve_landing_page_template tve_click <?php echo ( $file == $selected_template ) ? 'tve_cell_selected' : ''; ?>">
					<input type="hidden" class="lp_code" value="<?php echo $file ?>">
					<img src="<?php echo $template['thumbnail'] ?>" alt="" width="166" height="140"/>
					<span class="tve_cell_caption_holder">
						<span class="tve_cell_caption">
							<?php echo $template['name'] ?>
						</span>
					</span>
					<div class="tve_cell_check tvo-f-icon-check"></div>
				</div>
			<?php endforeach ?>
		</div>
	</div>
	<div class="tve-sp"></div>
	<input onClick="javascript:tvo_save_template()" type="button" class="tvo_select_template tve_editor_button tve_editor_button_success tve_click tve_right" data-wpapi="display-settings"
	       data-btn-hide="1" data-multiple-hide data-ctrl="<?php echo $func; ?>" value="<?php echo ucfirst( $next_button ) ?>">
</div>

<script>
	function tvo_save_template() {
		TVO_Front.init_template = jQuery( '.tve_cell_selected input' ).val();
	}
</script>
