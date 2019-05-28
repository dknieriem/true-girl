<?php
global $variation;
$tie_image     = new TIE_Image( $variation['page_id'] );
$tie_image_url = $tie_image->get_image_url();
?>
<div id="social_share_badge_settings_menu">
	<span class="tve_options_headline"><span class="tve_icm tve-ic-move"></span><?php echo __( 'Social share badge options', Thrive_Quiz_Builder::T ) ?></span>
	<ul class="tve_menu">
		<?php if ( empty( $tie_image_url ) ) : ?>
			<li class="tve_ed_btn tve_btn_text tve_click" data-href="<?php echo admin_url( 'admin.php?page=tqb_admin_dashboard#dashboard/quiz/' . $variation['quiz_id'] ); ?>" data-ctrl="function:ext.tqb.state.redirect"><?php echo __( 'Create Social Share Badge', Thrive_Quiz_Builder::T ) ?></li>
		<?php else : ?>
			<li class="tve_btn_text">
				<label>
					<?php echo __( 'Font Size', Thrive_Quiz_Builder::T ) ?> <input class="tve_text tve_font_size tve_change tve_mousedown" data-ctrl-change="function:ext.tqb.social_share_badge.menu_font_size" data-ctrl-mousedown="controls.save_selection" data-key="textSel" type="text" size="4"/> px
				</label>
			</li>
			<li class="tve_ed_btn tve_btn_text tve_click" data-ctrl="function:ext.tqb.social_share_badge.menu_clear_font_size"><?php echo __( 'Clear font size', Thrive_Quiz_Builder::T ) ?></li>
			<?php include $menu_path . '/_custom_font.php' ?>
			<li class="tve_text tve_firstOnRow">
				<?php echo __( 'Align:', Thrive_Quiz_Builder::T ) ?>
			</li>
			<li id="tve_leftBtn" class="btn_alignment tve_alignment_left">
				<?php echo __( 'Left', Thrive_Quiz_Builder::T ) ?>
			</li>
			<li id="tve_centerBtn" class="btn_alignment tve_alignment_center">
				<?php echo __( 'Center', Thrive_Quiz_Builder::T ) ?>
			</li>
			<li id="tve_rightBtn" class="btn_alignment tve_alignment_right">
				<?php echo __( 'Right', Thrive_Quiz_Builder::T ) ?>
			</li>
			<li class="tve_clear"></li>
			<li class="tve_ed_btn tve_btn_text tve_click" data-ctrl="function:social.openOptions"><?php echo __( 'Social Options', Thrive_Quiz_Builder::T ) ?></li>
			<li class="tve_ed_btn tve_center tve_btn_text btn_alignment tve_click" data-ctrl="function:ext.tqb.social_share_badge.change_template"><?php echo __( 'Change Template', Thrive_Quiz_Builder::T ) ?></li>
			<li class="tve_ed_btn tve_btn_text tve_click" data-ctrl="function:social.enableSortable"><?php echo __( 'Modify Order of Buttons', Thrive_Quiz_Builder::T ) ?></li>
			<li class="tve_ed_btn tve_btn_text">
				<div class="tve_option_separator">
					<span class="tve_ind tve_left"><?php echo __( 'Total Share Count', Thrive_Quiz_Builder::T ) ?></span>
					<span class="tve_caret tve_icm tve_left" id="sub_02"></span>
					<div class="tve_clear"></div>
					<div class="tve_sub_btn">
						<div class="tve_sub active_sub_menu tve_text_left tve_no_click" style="min-width: 250px">
							<input type="checkbox" class="tve_change" id="tve-share-show-counts" data-ctrl="function:social.shareCount">
							<label for="tve-share-show-counts"><?php echo __( 'Show total share count', Thrive_Quiz_Builder::T ) ?></label>
							<br>
							<?php echo __( 'Set the minimum number of shares that should be reached before the total share count is displayed:', Thrive_Quiz_Builder::T ) ?>
							<br>
							<input type="text" class="tve_change" style="width: 50px !important" data-ctrl="function:social.shareCount" id="tve-share-min-shares" value="0"> <?php echo __( 'shares', Thrive_Quiz_Builder::T ) ?>
						</div>
					</div>
				</div>
			</li>
			<li class="">
				<input type="text" class="element_class tve_change" data-ctrl="controls.change.cls"
					   placeholder="<?php echo __( 'Custom class', Thrive_Quiz_Builder::T ) ?>">
			</li>
			<?php include $menu_path . '/_margin.php' ?>

			<li class="tve_ed_btn tve_btn_text">
				<div class="tve_option_separator">
					<span class="tve_ind tve_left"><?php echo sprintf( __( 'Style %s', Thrive_Quiz_Builder::T ), '1' ) ?></span>
					<span class="tve_caret tve_icm tve_left" id="sub_02"></span>
					<div class="tve_clear"></div>
					<div class="tve_sub_btn">
						<div class="tve_sub active_sub_menu">
							<ul>
								<li id="tve_style_1" class="tve_click" data-cls="tve_style_1" data-ctrl="function:social.add_class">
									<?php echo sprintf( __( 'Style %s', Thrive_Quiz_Builder::T ), '1' ) ?>
								</li>
								<li id="tve_style_2" class="tve_click" data-cls="tve_style_2" data-ctrl="function:social.add_class">
									<?php echo sprintf( __( 'Style %s', Thrive_Quiz_Builder::T ), '2' ) ?>
								</li>
								<li id="tve_style_3" class="tve_click" data-cls="tve_style_3" data-ctrl="function:social.add_class">
									<?php echo sprintf( __( 'Style %s', Thrive_Quiz_Builder::T ), '3' ) ?>
								</li>
								<li id="tve_style_4" class="tve_click" data-cls="tve_style_4" data-ctrl="function:social.add_class">
									<?php echo sprintf( __( 'Style %s', Thrive_Quiz_Builder::T ), '4' ) ?>
								</li>
								<li id="tve_style_5" class="tve_click" data-cls="tve_style_5" data-ctrl="function:social.add_class">
									<?php echo sprintf( __( 'Style %s', Thrive_Quiz_Builder::T ), '5' ) ?>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</li>
			<li class="tve_ed_btn tve_btn_text">
				<div class="tve_option_separator">
					<span class="tve_ind tve_left"><?php echo __( 'Icon + text', Thrive_Quiz_Builder::T ) ?></span>
					<span class="tve_caret tve_icm tve_left"></span>

					<div class="tve_clear"></div>
					<div class="tve_sub_btn">
						<div class="tve_sub active_sub_menu">
							<ul>
								<li id="tve_social_ib" class="tve_click" data-cls="tve_social_ib" data-layout="1" data-ctrl="function:social.add_class"><?php echo __( 'Icon only', Thrive_Quiz_Builder::T ) ?></li>
								<li id="tve_social_itb" class="tve_click" data-cls="tve_social_itb" data-layout="1" data-ctrl="function:social.add_class"><?php echo __( 'Icon + text', Thrive_Quiz_Builder::T ) ?></li>
								<li id="tve_social_cb" class="tve_click" data-cls="tve_social_cb" data-layout="1" data-ctrl="function:social.add_class"><?php echo __( 'Counter', Thrive_Quiz_Builder::T ) ?></li>
							</ul>
						</div>
					</div>
				</div>
			</li>
		<?php endif; ?>
	</ul>
</div>
