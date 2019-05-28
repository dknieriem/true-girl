<?php $templates = tvo_get_testimonial_templates( 'capture' ); ?>
<?php $selected_template = empty( $_POST['template'] ) ? 'default' : $_POST['template']; ?>
<div class="tvo-frontend-modal">
	<h4>
		<?php echo __( 'Testimonial Capture Templates', TVO_TRANSLATE_DOMAIN ) ?>
	</h4>
	<hr class="tve_lightbox_line">
	<div class="tvo_capture_templates tvo_templates">
		<div class="tve_overflow_y">
			<?php foreach ( $templates as $template ) : ?>
				<div class="tve_grid_cell tve_landing_page_template tve_click <?php echo ( $template['file'] == $selected_template ) ? 'tve_cell_selected' : ''; ?>">
					<input type="hidden" class="lp_code" value="<?php echo $template['file'] ?>">
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
	<input type="button" class="tve_editor_button tve_editor_button_success tve_click tve_right" data-ctrl="function:tvo.capture.save_capture_testimonial" value="Save">
</div>
