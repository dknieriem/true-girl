<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/4/2016
 * Time: 11:48 AM
 */

$variation        = tqb_get_variation( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] );
$page_type_name   = tqb()->get_style_page_name( $variation['post_type'] );
$current_template = ! empty( $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) ? $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] : '';
$templates        = TQB_Template_Manager::get_templates( $variation['post_type'], $variation['quiz_id'] );
?>
<div class="tve_large_lightbox">
	<h4 class="tve-with-filter">
		<?php echo sprintf( __( 'Choose the %s template you would like to use for this design', Thrive_Quiz_Builder::T ), $page_type_name ) ?>
		<span class="tve-quick-filter tve_lb_fields">
			<input class="tve_keyup tve_lightbox_input" data-ctrl="controls.filter_lp"
				   type="text" style="width: 170px"
				   placeholder="<?php echo __( 'Quick filter...', Thrive_Quiz_Builder::T ) ?>" value=""
				   id="tve_landing_page_filter">
		</span>
	</h4>

	<div class="tve_tl_tpl <?php echo $current_template ? 'thrv_columns' : '' ?> tve_clearfix" id="tqb-tpl">
		<?php if ( $current_template ) : /* display the "Save" button just if there is some content in the form */ ?>
			<div class="tve_colm tve_foc tve_df tve_ofo">
				<div class="tve_message tve_warning" id="tve_landing_page_msg">
					<h6><?php echo __( 'Warning - your changes will be lost', Thrive_Quiz_Builder::T ) ?></h6>

					<p>
						<?php echo __( "If you change your the template without saving the current revision, you won't be able to revert back to it later.", Thrive_Quiz_Builder::T ) ?>
					</p>

					<input id="tve_landing_page_name" class="tve_lightbox_input" type="text" value=""
						   placeholder="<?php echo __( 'Template Name', Thrive_Quiz_Builder::T ) ?>">
					<br><br>
					<a data-ctrl="function:ext.tqb.template.save"
					   class="tve_click tve_editor_button tve_update"
					   href="javascript:void(0)"><?php echo __( 'Save As Template', Thrive_Quiz_Builder::T ) ?></a>
				</div>
			</div>
		<?php endif ?>
		<div class="<?php if ( $current_template ) : ?>tve_colm tve_tfo tve_df tve_lst<?php endif ?>">
			<div class="tve_grid tve_landing_pages" id="tve_landing_page_selector">
				<div class="tve_scT tve_green">
					<ul class="tve_clearfix">
						<li class="tve_tS tve_click">
							<span class="tve_scTC1"><?php echo __( 'Quiz Builder Templates', Thrive_Quiz_Builder::T ) ?></span>
						</li>
						<li data-ctrl-mousedown="function:ext.tqb.template.user_tab_clicked"
							class="tve_click tve_mousedown">
							<span class="tve_scTC2"><?php echo sprintf( __( 'Saved Templates', Thrive_Quiz_Builder::T ), $page_type_name ) ?></span>
						</li>
					</ul>
					<div class="tve_scTC tve_scTC1" style="display: block">
						<div class="tve_clear" style="height: 5px;"></div>
						<div class="tve_overflow_y">
							<?php foreach ( $templates as $data ) : ?>
								<span class="tve_grid_cell tve_landing_page_template tve_click<?php echo $current_template == $data['key'] ? ' tve_cell_selected' : '' ?>">
									<input type="hidden" class="lp_code" value="<?php echo $data['key'] ?>">
									<img src="<?php echo $data['thumbnail'] ?>" width="166" height="140">
									<span class="tve_cell_caption_holder">
										<span class="tve_cell_caption"><?php echo $data['name'] ?></span>
									</span>
									<span class="tve_cell_check tve_icm tve-ic-checkmark"></span>
								</span>
							<?php endforeach ?>
						</div>
						<div class="tve_clear" style="height: 5px;"></div>
					</div>
					<div class="tve_scTC tve_scTC2" style="display: none;">
						<a href="javascript:void(0)" data-ctrl="function:ext.tqb.template.delete_saved"
						   class="tve_click tve_editor_button tve_editor_button_cancel tve_right">
							<?php echo __( 'Delete template', Thrive_Quiz_Builder::T ) ?>
						</a>
						<h6><?php echo __( 'Choose from your saved templates', Thrive_Quiz_Builder::T ) ?></h6>
						<?php if ( $current_template ) : ?>
							<div class="tve_lightbox_input_holder">
								<input type="checkbox" id="tqb-user-current-templates"
									   data-ctrl="function:ext.tqb.template.get_saved" class="tve_change"
									   value="1"/>
								<label for="tqb-user-current-templates">
									<?php echo __( 'Show only saved versions of the current template', Thrive_Quiz_Builder::T ) ?>
								</label>
							</div>
						<?php endif ?>
						<div class="tve_clear" style="height: 15px;"></div>
						<div class="tve_overflow_y" style="max-height: 380px" id="tqb-saved-templates">
							<p class="tqb-tpl-loading"><?php echo __( 'Fetching saved templates...', Thrive_Quiz_Builder::T ) ?></p>
						</div>
					</div>
					<div class="tve_clear" style="height: 15px;"></div>
					<div class="tve_landing_pages_actions">
						<div class="tve_editor_button tve_right tve_click"
							 data-ctrl="function:ext.tqb.template.choose">
							<div class="tve_update">
								<?php echo __( 'Choose template', Thrive_Quiz_Builder::T ) ?>
							</div>
						</div>
						<?php if ( ! empty( $current_template ) ) : ?>
							<div style="margin-right: 20px;"
								 class="tve_editor_button tve_right tve_click"
								 data-ctrl="function:ext.tqb.template.reset">
								<div class="tve_preview">
									<?php echo __( 'Reset contents', Thrive_Quiz_Builder::T ) ?>
								</div>
							</div>
						<?php endif ?>
					</div>
					<div class="tve_clear"></div>
				</div>
			</div>
		</div>
	</div>
</div>
