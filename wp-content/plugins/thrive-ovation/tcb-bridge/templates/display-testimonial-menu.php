<div id="tvo_display_testimonials_menu">
	<span class="tve_options_headline"><span class="tve_icm tve-ic-move"></span><?php echo __( 'Display testimonial options', TVO_TRANSLATE_DOMAIN ) ?></span>
	<ul class="tve_menu">

		<?php $has_custom_colors = true; ?>

		<?php include $menu_path . '_custom_colors.php' ?>

		<?php include $menu_path . '_margin.php'; ?>

		<li id="tvo_display_testimonial_settings" class="tve_ed_btn tve_btn_text tve_click" data-ctrl="function:tvo.display.open_lb" data-wpapi="display-settings" data-btn-hide="1" data-multiple-hide>
			<?php echo __( 'Display Settings', TVO_TRANSLATE_DOMAIN ) ?>
		</li>

		<li id="tvo_display_testimonial_template" class="tve_ed_btn tve_btn_text tve_click" data-ctrl="function:tvo.display.open_lb" data-next="update" data-wpapi="display-testimonial-templates" data-btn-hide="1" data-multiple-hide>
			<?php echo __( 'Change Template', TVO_TRANSLATE_DOMAIN ) ?>
		</li>

		<li class="tve_ed_btn tve_btn_text">
			<div class="tve_option_separator">
				<span class="tve_ind tve_left" data-default="<?php echo __( 'Show/Hide Fields', TVO_TRANSLATE_DOMAIN ) ?>"><?php echo __( 'Show/Hide Fields', TVO_TRANSLATE_DOMAIN ) ?></span>
				<span class="tve_caret tve_left tve_icm" id="sub_02"></span>

				<div class="tve_clear"></div>
				<div class="tve_sub_btn">
					<div class="tve_sub active_sub_menu tve_medium">
						<ul class="tve_font_list">
							<li class="tve_no_click">
								<label>
									<input class="tve_change tvo_show_role" data-ctrl="tvo.display.toggle_field" type="checkbox" value="role"> <?php echo __( 'Role/Ocupation', TVO_TRANSLATE_DOMAIN ) ?>
								</label>
							</li>
							<li class="tve_no_click">
								<label>
									<input class="tve_change tvo_show_site" data-ctrl="tvo.display.toggle_field" type="checkbox" value="site"> <?php echo __( 'Site URL', TVO_TRANSLATE_DOMAIN ) ?>
								</label>
							</li>
							<li class="tve_no_click">
								<label>
									<input class="tve_change tvo_show_title" data-ctrl="tvo.display.toggle_field" type="checkbox" value="title"> <?php echo __( 'Title', TVO_TRANSLATE_DOMAIN ) ?>
								</label>
							</li>
						</ul>
						<div class="tve_clear"></div>
					</div>
				</div>
			</div>
		</li>
	</ul>
</div>
