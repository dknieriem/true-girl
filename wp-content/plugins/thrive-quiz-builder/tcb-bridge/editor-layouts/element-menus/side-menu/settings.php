<div class="tve_cpanel_sec tve_lp_sub">
	<div class="tve_option_separator tve_dropdown_submenu tve_drop_style">
		<div class="tve_ed_btn tve_btn_text" style="display: block">
			<span id="sub_02" class="tve_caret tve_icm tve_right tve_sub_btn tve_expanded"
				  style="margin-top: -3px; margin-left: 4px;"></span>
			<span class="tve_expanded">
				<?php echo __( 'Thrive Quiz Builder', Thrive_Quiz_Builder::T ) ?>
			</span>
			<span class="tve_icm tve-ic-cog tve_collapsed"></span>

			<div class="tve_clear"></div>
		</div>
		<div class="tve_sub_btn">
			<div class="tve_sub" id="tve-ult-page-tpl-options" style="bottom: auto;top: 30px;width: 159px;">
				<ul>
					<li class="tve_click" id="tqb-tpl-chooser"
						data-ctrl="controls.lb_open" data-load="1"
						data-wpapi="tqb_templates"
						data-btn-hide="1">
						<?php echo __( 'Choose Template', Thrive_Quiz_Builder::T ) ?>
					</li>
					<li class="tve_click" data-ctrl="function:ext.tqb.open_page_settings"><?php echo __( 'Page Settings', Thrive_Quiz_Builder::T ) ?></li>
					<li class="tve_click"
						data-ctrl="function:ext.tqb.custom_buttons.lean_modal_trigger"
						data-modal-action="function:ext.tqb.template.reset"
						data-modal-button-text="<?php echo __( 'Reset to default content', Thrive_Quiz_Builder::T ); ?>"
						data-modal-text="<?php echo __( 'Are you sure you want to reset this variation to the default template? This action cannot be undone', Thrive_Quiz_Builder::T ); ?>"
						data-modal-title="<?php echo __( 'Reset to default content', Thrive_Quiz_Builder::T ) ?>"
						style="color: red;">
						<?php echo __( 'Reset to default content', Thrive_Quiz_Builder::T ) ?>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="tve_clear"></div>
</div>
