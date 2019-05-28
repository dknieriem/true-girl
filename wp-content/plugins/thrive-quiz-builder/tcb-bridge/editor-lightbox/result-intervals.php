<?php
$variation       = tqb_get_variation( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] );
$absolute_limits = tqb_compute_quiz_absolute_max_min_values( $variation['quiz_id'], true );
?>

<?php if ( false === $absolute_limits['min'] && false === $absolute_limits['max'] ) : // No questions defined ?>
	<?php $quiz_post = get_post( $variation['quiz_id'] ); ?>
	<div class="tqb-tcb-container">
		<h4 class="tve-with-filter">
			<?php echo __( 'You have no questions defined', Thrive_Quiz_Builder::T ); ?>
		</h4>
		<hr>
		<span><?php echo __( 'There are no questions defined for this quiz so the dynamic content cannot be added!', Thrive_Quiz_Builder::T ); ?></span>
		<div class="tqb-tcb-row">
			<div class="tve_editor_button tve_right tve_click tqb-result-intervals-action"
				 data-href="<?php echo tge()->editor_url( $quiz_post ); ?>"
				 data-ctrl="function:ext.tqb.state.redirect">
				<div class="tve_update">
					<?php echo __( 'Add Questions', Thrive_Quiz_Builder::T ) ?>
				</div>
			</div>
		</div>
	</div>
<?php elseif ( is_numeric( $absolute_limits['min'] ) && $absolute_limits['min'] == $absolute_limits['max'] ) : // question min = question max; no branches in question editor. ?>
	<?php $quiz_post = get_post( $variation['quiz_id'] ); ?>

	<div class="tqb-tcb-container">
		<h4 class="tve-with-filter">
			<?php echo __( 'There seems to be a problem with your quiz', Thrive_Quiz_Builder::T ); ?>
		</h4>
		<span class="tqb-import-content-description"><?php echo __( 'The minimum and maximum result cannot be the same! First you need to add points to the quiz answers and then add the dynamic content in your page.', Thrive_Quiz_Builder::T ); ?></span>
		<div class="tqb-tcb-row">
			<div class="tve_editor_button tve_right tve_click tqb-result-intervals-action"
				 data-href="<?php echo tge()->editor_url( $quiz_post ); ?>"
				 data-ctrl="function:ext.tqb.state.redirect">
				<div class="tve_update">
					<?php echo __( 'Edit Questions', Thrive_Quiz_Builder::T ) ?>
				</div>
			</div>
		</div>
	</div>
<?php else : ?>
	<?php
	$max = Thrive_Quiz_Builder::STATES_MAXIMUM_NUMBER_OF_INTERVALS;
	$aux = $absolute_limits['max'] - $absolute_limits['min'];
	if ( $aux < $max ) {
		$max = $aux + 1;
	}
	?>
	<div class="tqb-tcb-container">
		<h4 class="tve-with-filter tqb-import-content-title">
			<?php echo __( 'Dynamic content intervals', Thrive_Quiz_Builder::T ); ?>
		</h4>
		<hr style="margin-top: 0;">

		<span class="tqb-import-content-description"><?php echo sprintf( __( 'Before adding your Dynamic Content, please choose how to split your results into intervals.<br> You can create a maximum of %s intervals', Thrive_Quiz_Builder::T ), $max ); ?></span>

		<div class="tqb-tcb-row tve_center">
			<span class="tqb-import-content-description tqb-action-required-text"><?php echo __( 'Split available results range into: ', Thrive_Quiz_Builder::T ); ?></span>
			<input id="tqb_result_intervals" type="number" min="1" max="<?php echo $max; ?>" step="1" onchange="TVE_Content_Builder.ext.tqb.state.lightbox_state_choose(this)">
			<span class="tqb-import-content-description tqb-action-required-text"><?php echo __( 'equal intervals', Thrive_Quiz_Builder::T ); ?></span>
		</div>
		<div id="tqb-intervals-preview" class="tqb-tcb-row"></div>
		<div class="tqb-tcb-row tve_center">
			<div class="tve_editor_button tve_click <?php if ( empty( $_REQUEST['tqb_child_variation_id'] ) ) : ?> tqb-result-intervals-action<?php endif; ?>"
				 data-ctrl="function:ext.tqb.state.lightbox_save_states_number">
				<div class="tve_update">
					<?php echo __( 'Create new dynamic content intervals', Thrive_Quiz_Builder::T ) ?>
				</div>
			</div>
		</div>
		<?php if ( tqb_has_similar_dynamic_content( $variation ) ) : ?>
			<?php

			switch ( $variation['post_type'] ) {
				case Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS:
					$searched_post_type = Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN;
					break;
				case Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN:
					$searched_post_type = Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS;
					break;
				default:
					$searched_post_type = '';
					break;
			}
			$page_to_import_from = tqb()->get_style_page_name( $searched_post_type );

			?>
			<div class="tqb-tcb-row tqb-line-with-or"></div>
			<div class="tqb-tcb-row tve_center">
				<span class="tqb-import-content-description"><?php echo sprintf( __( 'You can choose to import intervals from the %s.', Thrive_Quiz_Builder::T ), $page_to_import_from ); ?></span>
			</div>
			<div class="tqb-tcb-row tve_center" style="margin-top: 0;">
				<span class="tqb-import-content-description"><?php echo __( 'If you choose this, only the intervals ranges will be imported.', Thrive_Quiz_Builder::T ); ?></span>
			</div>
			<div class="tqb-tcb-row tve_center">
				<div class="tve_editor_button tve_click"
					 data-ctrl="function:ext.tqb.state.lightbox_copy_states_from_prev_page">
					<div class="tve_update tqb-blue">
						<span class="icon-transfer" style="color: #FFFFFF;"></span>
						<?php echo sprintf( __( 'Import intervals from %s', Thrive_Quiz_Builder::T ), $page_to_import_from ); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
<style>
	#tve_lightbox_buttons {
		display: none !important;
	}
</style>
