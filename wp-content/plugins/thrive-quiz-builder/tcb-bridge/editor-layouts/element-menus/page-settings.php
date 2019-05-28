<div id="tqb_page_menu">
	<span class="tve_options_headline"><?php echo __( 'Page Settings', Thrive_Quiz_Builder::T ) ?></span>
	<span class="tve_options_headline" style="font-size: 13px;"><?php echo __( 'Background', Thrive_Quiz_Builder::T ) ?></span>
	<ul class="tve_menu">
		<?php $has_custom_colors = 1;
		$hide_default_colors     = 1;
		include $menu_path . '_custom_colors.php' ?>
		<li class="tve_clear"></li>
		<li class="tve_firstOnRow tve_ed_btn tve_btn_text tve_center tve_click" data-ctrl="function:ext.tqb.open_bg_media">
			<?php echo __( 'Background image...', Thrive_Quiz_Builder::T ) ?>
		</li>
		<li class="tve_clear"></li>
		<li class="tve_firstOnRow tve_ed_btn tve_btn_text tve_center tve_click" data-plugin="tqb_variation" data-fn="clearBgColor">
			<?php echo __( 'Clear background color', Thrive_Quiz_Builder::T ) ?>
		</li>
		<li class="tve_clear"></li>
		<li class="tve_firstOnRow tve_ed_btn tve_btn_text tve_center tve_click" data-plugin="tqb_variation" data-fn="clearBgImage">
			<?php echo __( 'Clear background image', Thrive_Quiz_Builder::T ) ?>
		</li>
		<li class="tve_clear"></li>
	</ul>
</div>

