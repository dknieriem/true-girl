<?php
global $variation;

// Show the advanced Thrive Quiz Builder menu only for results and opt-in page
if ( empty( $variation ) || ! TCB_Hooks::enable_tqb_advanced_menu( $variation['post_type'] ) ) {
	return;
}
$variation_manager = new TQB_Variation_Manager( $variation['quiz_id'], $variation['page_id'] );
$intervals         = $variation_manager->get_page_variations( array( 'parent_id' => $variation['id'] ) );

$tie_image     = new TIE_Image( $variation['page_id'] );
$tie_image_url = $tie_image->get_image_url();
if ( empty( $tie_image_url ) ) {
	$tie_image_url = tqb()->plugin_url( 'tcb-bridge/assets/images/share-badge-default.png' );
}
?>

<div id="tqb-menu-item" class="tve_option_separator tve_clearfix" title="<?php echo __( 'Thrive Quiz Builder', Thrive_Quiz_Builder::T ) ?>">
	<div class="icon-tqb tve_left" onclick="jQuery('.tve_cpanel_options').animate({scrollTop: jQuery('.tve_cpanel_options').prop('scrollHeight')});"></div>
	<span class="tve_expanded tve_left" onclick="jQuery('.tve_cpanel_options').animate({scrollTop: jQuery('.tve_cpanel_options').prop('scrollHeight')});"><?php echo __( 'Thrive Quiz Builder', Thrive_Quiz_Builder::T ) ?></span>
	<span class="tve_caret tve_icm tve_sub_btn tve_right tve_expanded"></span>

	<div class="tve_clear"></div>
	<div class="tve_sub_btn" title="<?php echo __( 'Thrive Quiz Builder', Thrive_Quiz_Builder::T ) ?>">
		<div class="tve_sub">
			<ul>
				<li class="cp_draggable sc_tqb_dynamic_content" title="<?php echo __( 'Dynamic Content', Thrive_Quiz_Builder::T ) ?>" data-elem="sc_tqb_dynamic_content" style="<?php if ( count( $intervals ) > 0 ) { ?> display: none; <?php } ?>">
					<a href="javascript:void(0)" data-ctrl="controls.lb_open" data-wpapi="tqb_compute_result_page_states"></a>
					<div class="tve_icm tve-ic-plus"></div><?php echo __( 'Dynamic Content', Thrive_Quiz_Builder::T ) ?>
				</li>
				<?php if ( $variation['post_type'] === Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ) : ?>
					<li class="cp_draggable" title="<?php echo __( 'Social Share Badge', Thrive_Quiz_Builder::T ) ?>" data-elem="sc_tqb_social_share_badge_container" style="<?php if ( $variation[ Thrive_Quiz_Builder::FIELD_SOCIAL_SHARE_BADGE ] ) { ?> display: none; <?php } ?>">
						<div class="tve_icm tve-ic-plus"></div><?php echo __( 'Social Share Badge', Thrive_Quiz_Builder::T ) ?>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
