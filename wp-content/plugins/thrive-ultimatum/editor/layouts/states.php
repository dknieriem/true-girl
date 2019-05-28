<?php
$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
if ( ! $is_ajax && ! tve_ult_is_editor_page() ) {
	return;
}
global $design; // this is the main variation (variation parent)
if ( ! isset( $current_design ) ) {
	$current_design = $design; // this is the variation being edited now
}
/**
 * Shows a bar at the bottom of the page having all of the states defined for this form
 */
$states = tve_ult_get_related_states( $design );
?>
<?php if ( empty( $do_not_wrap ) ) : ?>
	<div class="tve_event_root">
	<div class="tl-form-states-container" id="tu-form-states">
<?php endif ?>
	<div class="multistep-lightbox"<?php echo ! empty( $_COOKIE['tve_ult_state_collapse'] ) ? ' style="display:none"' : '' ?>>
		<div class="multistep-lightbox-heading">
			<button
				data-ctrl="function:ext.tve_ult.state.toggle_manager"
				title="<?php echo __( 'Minimize', TVE_Ult_Const::T ) ?>" class="tve_click multistep-lightbox-minimize">
				<span class="tve_icm tve-ic-backward"></span>
			</button>
		</div>
		<div class="multistep-lightbox-steps-body">
			<ul class="multistep-lightbox-steps<?php echo count( $states ) > 6 ? ' tl-smaller' : '' ?>">
				<?php foreach ( $states as $index => $s ) : ?>
					<li
						data-ctrl="function:ext.tve_ult.state.state_click"
						data-id="<?php echo $s['id'] ?>"
						class="tve_click<?php echo $s['id'] == $current_design['id'] ? ' lightbox-step-active' : '' ?>">

						<button
							data-ctrl="function:ext.tve_ult.state.duplicate"
							data-id="<?php echo $s['id'] ?>"
							title="<?php echo __( 'Duplicate state', TVE_Ult_Const::T ) ?>"
							class="lightbox-step-duplicate tve_click"></button>

						<?php if ( $index > 0 ) : ?>
							<button
								data-ctrl="function:ext.tve_ult.state.remove"
								data-id="<?php echo $s['id'] ?>"
								title="<?php echo __( 'Delete state', TVE_Ult_Const::T ) ?>"
								class="lightbox-step-delete tve_click"></button>
							<button
									data-btn-hide="1"
									data-ctrl="function:ext.tve_ult.state.add_edit"
									data-lb="lb_ult_state_name"
									data-id="<?php echo $s['id'] ?>"
									title="<?php echo __( 'Rename', TVE_Ult_Const::T ) ?>"
									class="lightbox-step-rename tve_click"></button>
						<?php endif ?>

						<span
							class="lightbox-step-name"><?php echo $s['post_title'] . ( empty( $s['parent_id'] ) ? '<strong> (' . __( 'Main', TVE_Ult_Const::T ) . ')</strong>' : '' ) ?></span>
					</li>
				<?php endforeach ?>
				<li data-btn-hide="1"
					data-ctrl="function:ext.tve_ult.state.add_edit"
					data-lb="lb_ult_state_name"
					class="lightbox-step-add tve_click">
					<span class="lightbox-step-add-title"><?php echo __( 'Add', TVE_Ult_Const::T ) ?></span>
				</li>
			</ul>
		</div>
	</div>
<?php
if ( empty( $do_not_wrap ) ) :
	$position = isset( $_COOKIE['tve_ult_states_pos'] ) ? json_decode( stripslashes( $_COOKIE['tve_ult_states_pos'] ), true ) : array();
	?>
	</div>
	<div
		class="tl-state-minimized"<?php echo ! empty( $position['top'] ) && ! empty( $position['left'] ) ? sprintf( ' style="right:auto;top:%spx;left:%spx"', (int) $position['top'], (int) $position['left'] ) : '' ?>>
		<div class="multistep-lightbox-heading">
			<button
				data-ctrl="function:ext.tve_ult.state.toggle_manager"
				data-expand="1"
				title="<?php echo __( 'Restore', TVE_Ult_Const::T ) ?>" class="tve_click multistep-lightbox-minimize">
				<span class="tve_icm tve-ic-forward"></span>
			</button>
		</div>
		<div class="multistep-lightbox-steps-body">
			<div class="tl-body-shadow">
				<span class="tve_icm tve-ic-my-library-books"></span> <?php echo __( 'States', TVE_Ult_Const::T ) ?>
			</div>
		</div>
	</div>
	</div>
<?php endif ?>