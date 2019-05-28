<div id="tvo_capture_testimonials_menu">
	<span class="tve_options_headline"><span class="tve_icm tve-ic-move"></span><?php echo __( 'Capture testimonial options', TVO_TRANSLATE_DOMAIN ) ?></span>
	<ul class="tve_menu">

		<?php $has_custom_colors = true; ?>

		<?php include $menu_path . '_custom_colors.php' ?>

		<?php include $menu_path . '_margin.php'; ?>
		
		<li id="tvo_capture_testimonial_settings" class="tve_ed_btn tve_btn_text tve_click" data-ctrl="function:tvo.capture.open_lb" data-wpapi="capture-form-settings" data-btn-hide="1" data-multiple-hide>
			<?php echo __( 'Form Settings', TVO_TRANSLATE_DOMAIN ) ?>
		</li>

		<li id="tvo_capture_testimonial_template" class="tve_ed_btn tve_btn_text tve_click" data-ctrl="function:tvo.capture.open_lb" data-wpapi="capture-testimonial-templates" data-btn-hide="1" data-multiple-hide>
			<?php echo __( 'Change Template', TVO_TRANSLATE_DOMAIN ) ?>
		</li>
	</ul>
</div>
