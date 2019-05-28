<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/25/2016
 * Time: 2:52 PM
 */
global $variation;

$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
if ( ! $is_ajax && ! tqb_is_editor_page() ) {
	return;
}

$is_enable_states = ( ! empty( $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) && TCB_Hooks::enable_tqb_advanced_menu( $variation['post_type'] ) ) ? true : false;
if ( ! $is_enable_states ) {
	return;
}

$quiz_type         = TQB_Post_meta::get_quiz_type_meta( $variation['quiz_id'] );
$variation_manager = new TQB_Variation_Manager( $variation['quiz_id'], $variation['page_id'] );
$intervals         = $variation_manager->get_page_variations( array( 'parent_id' => $variation['id'] ) );

if ( empty( $intervals ) || ! is_array( $intervals ) ) {
	echo '<div class="tve_event_root"><div id="tqb-form-states"></div></div>';

	return;
}

?>

<div class="tve_event_root">
	<div id="tqb-form-states">
		<?php if ( $quiz_type['type'] == Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY ) : ?>
			<div class="tve_left tqb-interval-info">
				<span class="icon-help_outline" data-toggle="tooltip" title="<?php echo __( 'For each category you can personalize the content that will be displayed.', Thrive_Quiz_Builder::T ); ?>"></span>
				<?php echo __( 'Results:', Thrive_Quiz_Builder::T ); ?>
			</div>
			<div class="tqb-tcb-row-container tqb-tcb-row-container-personality tve_left">
				<?php foreach ( $intervals as $interval ) : ?>
					<?php
					$interval['post_title'] = str_replace( array( "'", '"' ), '', $interval['post_title'] );
					$html                   = "<div class='tqb-personality-results-popover'><div class='tqb-personality-results-dark-holder'><span class='icon-download'></span><a class='tve_click' data-ctrl='function:ext.tqb.state.import_content_lightbox' href='javascript:void(0);'>" . __( 'Import Content', Thrive_Quiz_Builder::T ) . '</a></div>' . "<div class='tqb-personality-results-purple-holder'>" . $interval['post_title'] . '</div>' . '</div>';
					?>
					<div class="tve_click tqb-tcb-intervals-item tqb-tcb-intervals-item-personality"
						 data-toggle="popover"
						 data-placement="top"
						 data-content="<?php echo $html; ?>"
						 data-html="true"
						 data-ctrl="function:ext.tqb.state.state_click"
						 data-id="<?php echo $interval['id']; ?>">
						<span><?php echo $interval['post_title']; ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<?php
			//Sort the intervals array
			$flag = array();
			foreach ( $intervals as $key => $interval ) {
				$flag[ $key ] = $interval['tcb_fields']['min'];
			}
			array_multisort( $flag, SORT_ASC, $intervals );

			$intervals_size = count( $intervals );
			?>
			<div class="tve_left tqb-interval-info">
				<span class="icon-help_outline" data-toggle="tooltip" title="<?php echo __( 'All the possible results are split into intervals so you can display personalized content for each interval.', Thrive_Quiz_Builder::T ); ?>"></span>
				<?php echo __( 'Results Intervals:', Thrive_Quiz_Builder::T ); ?>
			</div>
			<div class="tqb-tcb-row-container tve_left tqb-intervals">
				<?php foreach ( $intervals as $key => $interval ) : ?>
					<?php
					$prev_min = ( ! empty( $intervals[ $key - 1 ] ) ) ? $intervals[ $key - 1 ]['tcb_fields']['min'] : null;
					$prev_id  = ( ! empty( $intervals[ $key - 1 ] ) ) ? $intervals[ $key - 1 ]['id'] : null;
					$next_max = ( ! empty( $intervals[ $key + 1 ] ) ) ? $intervals[ $key + 1 ]['tcb_fields']['max'] : null;
					$next_id  = ( ! empty( $intervals[ $key + 1 ] ) ) ? $intervals[ $key + 1 ]['id'] : null;
					$html     = "
						<input type='hidden' id='tqb-child-id' value='" . $interval['id'] . "' />
						<input type='hidden' id='tqb-child-prev-id' value='" . $prev_id . "' />
						<input type='hidden' id='tqb-child-next-id' value='" . $next_id . "' />
						<input type='hidden' id='tqb-prev-min' value='" . $prev_min . "'/> 
						<input type='hidden' id='tqb-next-max' value='" . $next_max . "'/>
						";
					$html .= "<div class='tqb-interval-actions-holder'>";
					if ( $intervals_size < Thrive_Quiz_Builder::STATES_MAXIMUM_NUMBER_OF_INTERVALS &&
					     ( $interval['tcb_fields']['max'] - $interval['tcb_fields']['min'] > 1 ) && // not equal and not consecutive values
					     $interval['tcb_fields']['width'] > Thrive_Quiz_Builder::STATES_MINIMUM_WIDTH_SIZE // width to be grater then minimum width
					) :
						$html .= "<a data-ctrl='function:ext.tqb.state.state_split' class='tve_click tqb-interval-action-button'  href='javascript:void(0);'><span class='icon-call_split'></span>Split</a>";
					endif;
					if ( $intervals_size > 1 ) :
						$html .= "
							<a data-ctrl='function:ext.tqb.state.import_content_lightbox' class='tve_click tqb-interval-action-button' href='javascript:void(0);'><span class='icon-download'></span>Import</a>

							<a href='javascript:void(0);'
								style='float: right;'
								class='tve_click tqb-interval-action-button'
								data-ctrl='function:ext.tqb.custom_buttons.lean_modal_trigger'
								data-modal-title='" . __( 'Confirmation', Thrive_Quiz_Builder::T ) . "'
								data-modal-text='" . __( 'Are you sure you want to delete this state?', Thrive_Quiz_Builder::T ) . "'
								data-modal-action='function:ext.tqb.state.remove_state'
								data-modal-button-text='" . __( 'Remove', Thrive_Quiz_Builder::T ) . "'>
								<span class='icon-delete_forever'></span>
							</a>";
					endif;
					$html .= '</div>';
					$html .= "
						<div class='tqb-purple-container'>
							<span class='tqb-intervals-info'>Range</span>
							<div class='tqb-input-range-holder'>
								<input type='text' class='tqb-input-range' id='tqb-range-min' value='" . $interval['tcb_fields']['min'] . "'/>
								<input type='text' class='tqb-input-range' id='tqb-range-max' value='" . $interval['tcb_fields']['max'] . "'/>
							</div>
							<a data-ctrl='function:ext.tqb.state.update_intervals' class='tve_click tqb-apply-intervals' href='javascript:void(0);'>" . __( 'Apply', Thrive_Quiz_Builder::T ) . '</a>
						</div>';
					?>
					<div class="tve_click tqb-tcb-intervals-item"
						 data-ctrl="function:ext.tqb.state.state_click"
						 data-toggle="popover"
						 data-placement="top"
						 data-content="<?php echo $html; ?>"
						 data-html="true"
						 data-min="<?php echo $interval['tcb_fields']['min']; ?>"
						 data-max="<?php echo $interval['tcb_fields']['max']; ?>"
						 data-id="<?php echo $interval['id']; ?>"
						 style="width: <?php echo $interval['tcb_fields']['width']; ?>px"></div>
				<?php endforeach; ?>
				<div class="tqb-tcb-row-container tve_left tqb-intervals-range">
					<div class="tve_left"><?php echo $intervals[0]['tcb_fields']['min']; ?></div>
					<?php foreach ( $intervals as $key => $interval ) : ?>
						<div style="width: <?php echo $interval['tcb_fields']['width']; ?>px"
							 class="tqb-tcb-numeric-range-preview"
							 data-id="<?php echo $interval['id']; ?>">
							<?php if ( $key != 0 ) : ?>
								<div class="tve_left"><?php echo $interval['tcb_fields']['min']; ?></div>
							<?php endif; ?>
							<?php if ( $key != $intervals_size - 1 ) : ?>
								<div class="tve_right"><?php echo $interval['tcb_fields']['max']; ?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					<div class="tve_left"><?php echo $intervals[ $intervals_size - 1 ]['tcb_fields']['max']; ?></div>
				</div>
			</div>
			<div class="tqb-tcb-row-container tve_left tqb-interval-actions">
				<a href='javascript:void(0);'
				   class="tve_click"
				   data-ctrl='function:ext.tqb.custom_buttons.lean_modal_trigger'
				   data-modal-title="<?php echo __( 'Confirmation', Thrive_Quiz_Builder::T ); ?>"
				   data-modal-text="<?php echo __( 'Are you sure you want to equalize all intervals?', Thrive_Quiz_Builder::T ); ?>"
				   data-modal-button-text="<?php echo __( 'Equalize sizes', Thrive_Quiz_Builder::T ); ?>"
				   data-modal-action='function:ext.tqb.state.state_equalize'>
					<span class="icon-view_column"></span>
					<span class="tqb-interval-actions-button"><?php echo __( 'Equalize sizes', Thrive_Quiz_Builder::T ); ?></span>
				</a>
				<div class="tve_click"
					 data-ctrl="function:ext.tqb.state.state_reset">
					<span class="icon-rotate"></span>
					<span class="tqb-interval-actions-button"><?php echo __( 'Reset all', Thrive_Quiz_Builder::T ); ?></span>
				</div>
			</div>

		<?php endif; ?>
		<a href="javascript:void(0)" class="tve_click" style="display: none;" id="tqb_delete_dynamic_content"
		   data-ctrl="function:ext.tqb.custom_buttons.lean_modal_trigger"
		   data-modal-title="Confirmation"
		   data-modal-text="<?php echo __( 'By deleting the Dynamic Content Element from the page you\'ll loose all the settings you\'ve made to the intervals. ', Thrive_Quiz_Builder::T ); ?>"
		   data-modal-button-text="<?php echo __( 'Delete Dynamic Content', Thrive_Quiz_Builder::T ); ?>"
		   data-modal-action="function:ext.tqb.state.lightbox_delete_all_states"></a>
	</div>
</div>
