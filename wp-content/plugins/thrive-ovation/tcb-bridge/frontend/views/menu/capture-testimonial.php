<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tvo-preview-menu" class="tvo-menu-onpage">
	<div class="tvo-menu-onpage-container">

		<div class="tvo-menu-relative tvo-menu-inline-block">
			<span class="tvo_toggle_fields tvo_menu_item tvo_icon_box" data-func="toggle_fields" data-toggle=".tvo_color_box">
				<i class="tvo-f-icon-color-palette"></i>
				<?php echo __( 'Color', TVO_TRANSLATE_DOMAIN ) ?>
			</span>

			<div class="tvo_toggle_fields_box tvo_color_box tvo-showhide-fields" style="display: none;">
				<?php echo __( 'Template Color', TVO_TRANSLATE_DOMAIN ) ?>
				<ul class="tvo_template_colors">
					<li class="tve_black"><span></span></li>
					<li class="tve_blue"><span></span></li>
					<li class="tve_green"><span></span></li>
					<li class="tve_orange"><span></span></li>
					<li class="tve_purple"><span></span></li>
					<li class="tve_red"><span></span></li>
					<li class="tve_teal"><span></span></li>
					<li class="tve_white"><span></span></li>
				</ul>
			</div>
		</div>

		<div class="tvo_form_settings tvo_menu_item" data-func="form_settings">Form settings</div>
		<div class="tvo_change_template tvo_menu_item" data-func="change_template">Change template</div>
		<span class="tvo_save_changes tvo_menu_item" data-func="save">Save changes</span>
	</div>
</div>
