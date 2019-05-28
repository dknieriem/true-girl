<h4 class="tve-with-filter">
	<?php echo __( 'Social share badge template', Thrive_Quiz_Builder::T ); ?>
</h4>
<div class="tve_grid tve_landing_pages" id="tqb-social-share-badge-tpl">
	<?php
	$style_templates = tqb()->get_tcb_social_share_badge_templates();
	foreach ( $style_templates as $key => $template ) :
		?>
		<span class="tve_grid_cell tve_landing_page_template tve_click <?php if ( ! empty( $_POST['tqb_social_sharing_badge_template'] ) && $_POST['tqb_social_sharing_badge_template'] == $template['file'] ) : echo ' tve_cell_selected'; endif; ?>">
				<input type="hidden" class="tqb-social-share-badge-file" value="<?php echo $template['file'] ?>">
				<img src="<?php echo $template['image']; ?>">
				<span class="tve_cell_caption_holder">
					<span class="tve_cell_caption"><?php echo $template['name']; ?></span>
				</span>
				<span class="tve_cell_check tve_icm tve-ic-checkmark"></span>
			</span>
		<?php
	endforeach;
	?>
</div>
<div class="tve_editor_button tve_right tve_update tve_click tqb-social-share-badge-template-action"
	 data-ctrl="function:ext.tqb.social_share_badge.choose_template"
<div class="tve_update">
	<?php echo __( 'Choose Template', Thrive_Quiz_Builder::T ) ?>
</div>
</div>
<style>
	#tve_lightbox_buttons {
		display: none !important;
	}
</style>

