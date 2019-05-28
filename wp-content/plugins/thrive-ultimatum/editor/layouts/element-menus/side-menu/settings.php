<div class="tve_cpanel_sec tve_lp_sub">
	<div class="tve_option_separator tve_dropdown_submenu tve_drop_style">
		<div class="tve_ed_btn tve_btn_text" style="display: block">
			<span id="sub_02" class="tve_caret tve_icm tve_right tve_sub_btn tve_expanded"
			      style="margin-top: -3px; margin-left: 4px;"></span>
			<span class="tve_expanded">
				<?php echo __( 'Thrive Ultimatum', TVE_Ult_Const::T ) ?>
			</span>
			<span class="tve_icm tve-ic-cog tve_collapsed"></span>

			<div class="tve_clear"></div>
		</div>
		<div class="tve_sub_btn">
			<div class="tve_sub" id="tve-ult-page-tpl-options" style="bottom: auto;top: 30px;width: 159px;">
				<ul>
					<li class="tve_click" id="tvu-tpl-chooser"
					    data-ctrl="controls.lb_open" data-load="1"
					    data-wpapi="tve_ult_templates"
					    data-btn-hide="1">
						<?php echo __( 'Choose Template', TVE_Ult_Const::T ) ?>
					</li>
					<li class="tve_click"
					    data-element-selector="<?php echo $design_details['edit_selector'] ?>"
					    data-ctrl="function:ext.tve_ult.open_cpanel">
						<?php echo sprintf( __( '%s Settings', TVE_Ult_Const::T ), $design_details['name']) ?>
					</li>
					<li class="tve_click" data-ctrl="function:ext.tve_ult.template.reset" style="color: red;">
						<?php echo __( 'Reset To Default Content', TVE_Ult_Const::T ) ?>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="tve_clear"></div>
</div>
