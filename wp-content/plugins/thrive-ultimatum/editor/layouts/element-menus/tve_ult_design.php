<div id="ult_design_menu">
	<span class="tve_options_headline"><span class="tve_icm tve-ic-move"></span><?php echo __( 'Element options', TVE_Ult_Const::T ) ?></span>
	<ul class="tve_menu">
		<?php $has_custom_colors = 1;
		include $menu_path . '_custom_colors.php' ?>
		<li class="tve_ed_btn tve_btn_text">
			<div class="tve_option_separator">
				<span class="tve_ind tve_left" data-default="<?php echo __('Border Type', TVE_Ult_Const::T) ?>"><?php echo __( 'Border Type', TVE_Ult_Const::T ) ?></span><span
					class="tve_caret tve_icm tve_left"></span>

				<div class="tve_clear"></div>
				<div class="tve_sub_btn">
					<div class="tve_sub active_sub_menu">
						<ul>
							<li id="tve_brdr_none" class="tve_click" data-ctrl="controls.click.add_class" data-border="1"><?php echo __( 'none', TVE_Ult_Const::T ) ?></li>
							<li id="tve_brdr_dotted" class="tve_click" data-ctrl="controls.click.add_class" data-border="1"><?php echo __( 'dotted', TVE_Ult_Const::T ) ?></li>
							<li id="tve_brdr_dashed" class="tve_click" data-ctrl="controls.click.add_class" data-border="1"><?php echo __( 'dashed', TVE_Ult_Const::T ) ?></li>
							<li id="tve_brdr_solid" class="tve_click" data-ctrl="controls.click.add_class" data-border="1"><?php echo __( 'solid', TVE_Ult_Const::T ) ?></li>
							<li id="tve_brdr_double" class="tve_click" data-ctrl="controls.click.add_class" data-border="1"><?php echo __( 'double', TVE_Ult_Const::T ) ?></li>
							<li id="tve_brdr_groove" class="tve_click" data-ctrl="controls.click.add_class" data-border="1"><?php echo __( 'groove', TVE_Ult_Const::T ) ?></li>
							<li id="tve_brdr_ridge" class="tve_click" data-ctrl="controls.click.add_class" data-border="1"><?php echo __( 'ridge', TVE_Ult_Const::T ) ?></li>
							<li id="tve_brdr_inset" class="tve_click" data-ctrl="controls.click.add_class" data-border="1"><?php echo __( 'inset', TVE_Ult_Const::T ) ?></li>
							<li id="tve_brdr_outset" class="tve_click" data-ctrl="controls.click.add_class" data-border="1"><?php echo __( 'outset', TVE_Ult_Const::T ) ?></li>
						</ul>
					</div>
				</div>
			</div>
		</li>
		<li class="tve_ed_btn_text clearfix">
			<input class="tve_change tu-border-width" value="0" type="text" size="3" data-ctrl="function:ext.tve_ult.border_width"
				       data-css-property="border-width" data-suffix="px" data-size="1"> px
		</li>
		<?php include $menu_path . '_margin.php' ?>
		<?php
		global $page_section_patterns, $template_uri; ?>
		<li class="tve_firstOnRow tve_ed_btn tve_btn_text tve_click" data-ctrl="function:ext.tve_ult.open_bg_media">
			<?php echo __( 'Background image...', TVE_Ult_Const::T ) ?>
		</li>
		<?php if ( ! empty( $page_section_patterns ) ) : ?>
			<li class="tve_firstOnRow tve_ed_btn tve_btn_text">
				<div class="tve_option_separator">
					<span class="tve_ind tve_left"><?php echo __( 'Background pattern', TVE_Ult_Const::T ) ?></span>
					<span class="tve_caret tve_icm tve_left" id="sub_02"></span>

					<div class="tve_clear"></div>
					<div class="tve_sub_btn" style="width: 715px;">
						<div class="tve_sub active_sub_menu" style="width: 100%">
							<ul class="tve_clearfix">
								<?php foreach ( $page_section_patterns as $i => $_image ) : ?>
									<?php $_uri = $template_uri . '/images/patterns/' . $_image . '.png' ?>
									<li class="tve_ed_btn tve_btn_text tve_left clearfix tve_click" data-fn="bgPattern" data-plugin="tve_ult_design">
										<span class="tve_section_colour tve_left" style="background:url('<?php echo $_uri ?>')"></span>
										<span class="tve_left"><?php echo 'pattern' . ( $i + 1 ); ?></span>
										<input type="hidden" data-image="<?php echo $_uri; ?>"/>
									</li>
								<?php endforeach ?>
							</ul>
						</div>
					</div>
				</div>
			</li>
		<?php endif ?>
		<li class="tve_firstOnRow tve_ed_btn tve_btn_text tve_click" data-plugin="tve_ult_design" data-fn="clearBgColor">
			<?php echo __( 'Clear background color', TVE_Ult_Const::T ) ?>
		</li>
		<li class="tve_firstOnRow tve_ed_btn tve_btn_text tve_click" data-plugin="tve_ult_design" data-fn="clearBgImage">
			<?php echo __( 'Clear background pattern / image', TVE_Ult_Const::T ) ?>
		</li>
	</ul>
</div>