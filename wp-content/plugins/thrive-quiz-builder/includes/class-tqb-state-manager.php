<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/5/2016
 * Time: 4:13 PM
 */

/**
 * Handles AJAX calls related to variation states
 *
 * Class TQB_State_Manager
 */
class TQB_State_Manager extends TQB_Request_Handler {

	/**
	 * @var $instance self
	 */
	protected static $instance = null;

	/**
	 * Row from DB
	 *
	 * @var $variation array always the main (default) state for a variation
	 */
	protected $variation = null;

	/**
	 * TU_State_Manager constructor.
	 *
	 * @param $variation
	 */
	private function __construct( $variation ) {
		$this->variation = $variation;
	}

	/**
	 * Returns the instance of the variation
	 *
	 * @param $variation array the variation being edited - this is always the main (default) state for a variation
	 *
	 * @return self
	 */
	public static function get_instance( $variation ) {

		if ( ! empty( self::$instance ) && self::$instance->variation['id'] === $variation['id'] ) {
			return self::$instance;
		}

		if ( ! empty( $variation ) && ! empty( $variation['id'] ) ) {
			$variation = tqb_get_variation( $variation['id'] );
		}

		return new self( $variation );
	}

	/**
	 * Compose all the data that's required on a page after the content has been changed
	 * (editor content / CSS links / fonts etc)
	 *
	 * @param array $current_variation
	 *
	 * @return array
	 */
	public function state_data( $current_variation ) {
		global $variation;
		$variation = $this->variation;
		$state_bar = $this->state_bar( $variation );

		ob_start();
		$variation = $current_variation;
		include tqb()->plugin_path( 'tcb-bridge/editor-layouts/element-menus/side-menu/settings.php' );
		$page_buttons = ob_get_contents();
		ob_end_clean();

		/** $css is an array with 2 keys fonts and css which need to be included in the page, if they do not already exist */
		$css_links        = array();
		$enqueued_scripts = tqb_enqueue_variation_scripts( $current_variation );

		foreach ( $enqueued_scripts ['fonts'] as $_id => $_font ) {
			$css_links[ $_id ] = $_font;
		}

		foreach ( $enqueued_scripts ['css'] as $_id => $_css ) {
			$css_links[ $_id ] = $_css;
		}

		/** javascript global page data (that will overwrite parts of the global tve_path_params variable) */
		$javascript_data = array(
			'custom_post_data' => array(
				Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME => $current_variation['id'],
				'variation_id'                                => $current_variation['id'],
			),
		);

		/** javascript global page data for the TQB - editor part */
		$editor_js = array(
			'variation_id'       => $current_variation['id'],
			'allow_tqb_advanced' => ( ! empty( $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) && TCB_Hooks::enable_tqb_advanced_menu( $variation['post_type'] ) ) ? true : false,
		);

		ob_start();
		TCB_Hooks::tqb_editor_output_custom_css( $current_variation, false );
		$custom_css = ob_get_contents();
		ob_end_clean();

		$return = array(
			'state_bar'         => $state_bar,
			'main_page_content' => trim( $this->render_ajax_content( $current_variation ) ),
			'custom_css'        => $custom_css,
			'css'               => $css_links,
			'page_buttons'      => $page_buttons,
			'tve_path_params'   => $javascript_data,
			'tqb_page_data'     => $editor_js,
		);

		return $return;
	}

	/**
	 * Renders the html contents for a new design to replace the previously edited one
	 *
	 * @param $current_variation array
	 *
	 * @return string html
	 */
	public function render_ajax_content( $current_variation = array() ) {
		global $variation;

		$variation = $current_variation;
		ob_start();
		$is_ajax_render = true;
		include tqb()->plugin_path( 'tcb-bridge/editor/page/' . TQB_Template_Manager::type( $current_variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) . '.php' );
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * get the html for the state bar
	 *
	 * @param array $current_variation the design currently being displayed
	 *
	 * @return string
	 */
	protected function state_bar( $current_variation ) {
		global $variation;
		ob_start();

		include tqb()->plugin_path( 'tcb-bridge/editor/page/states.php' );
		$state_bar = ob_get_contents();

		ob_end_clean();

		return $state_bar;
	}

	/**
	 * API-calls after this point
	 * --------------------------------------------------------------------
	 */

	/**
	 * Update states when user resize the states
	 *
	 * @return array
	 */
	public function api_update() {
		$min           = $this->param( 'min' );
		$max           = $this->param( 'max' );
		$child_id      = $this->param( 'child_id' );
		$child_prev_id = $this->param( 'child_prev_id' );
		$child_next_id = $this->param( 'child_next_id' );
		$quiz_min_val  = $this->param( 'quiz_min' );
		$quiz_max_val  = $this->param( 'quiz_max' );

		if ( empty( $child_id ) && ! is_numeric( $child_id ) || ! is_numeric( $min ) || ! is_numeric( $max ) ) {
			return false;
		}

		$child_variation    = TQB_Variation_Manager::get_variation( $child_id );
		$child_max_min_diff = intval( $child_variation['tcb_fields']['max'] - $child_variation['tcb_fields']['min'] );

		if ( $quiz_max_val - $quiz_min_val + 1 < Thrive_Quiz_Builder::STATES_MAXIMUM_NUMBER_OF_INTERVALS && $child_max_min_diff === 0 ) {
			$child_width_multiply_param = 1;
		} else {
			$child_width_multiply_param = $max - $min;
		}

		if ( $child_max_min_diff === 0 ) {
			$child_max_min_diff ++;  // if 0 make it 1. division with 0 => not possible
		}

		$child_width = intval( $child_width_multiply_param * $child_variation['tcb_fields']['width'] / $child_max_min_diff );
		if ( $child_width % 10 !== 0 ) {
			$child_width = round( $child_width / 10 ) * 10;
		}

		/*Check if new width is less then minimum width size*/
		if ( $child_width < Thrive_Quiz_Builder::STATES_MINIMUM_WIDTH_SIZE ) {
			$child_width = Thrive_Quiz_Builder::STATES_MINIMUM_WIDTH_SIZE;
		}

		$aux = $child_variation['tcb_fields']['width'] - $child_width; // $child_width - can be positive or negative

		$diff_width = 0;
		if ( $min != $child_variation['tcb_fields']['min'] && $max == $child_variation['tcb_fields']['max'] ) {
			$diff_width += (int) $this->update_child_variation_for_update( $child_prev_id, $min, null, $aux );
		}

		if ( $max != $child_variation['tcb_fields']['max'] && $min == $child_variation['tcb_fields']['min'] ) {
			$diff_width += (int) $this->update_child_variation_for_update( $child_next_id, null, $max, $aux );
		}

		/*Is somewhere between*/
		if ( $min != $child_variation['tcb_fields']['min'] && $max != $child_variation['tcb_fields']['max'] ) {
			$diff_width += (int) $this->update_child_variation_for_update( $child_prev_id, $min, null, $aux / 2 );
			$diff_width += (int) $this->update_child_variation_for_update( $child_next_id, null, $max, $aux / 2 );
		}

		TQB_Variation_Manager::save_child_variation( array(
			'id'         => $child_variation['id'],
			'parent_id'  => $child_variation['parent_id'],
			'tcb_fields' => array( 'min' => $min, 'max' => $max, 'width' => $child_width - $diff_width ),
			'post_title' => $min . ' - ' . $max,
		) );


		return $this->state_data( $this->variation );
	}

	/**
	 * Updates custom a child variation
	 *
	 * @param int $child_id
	 * @param $add_min
	 * @param $add_max
	 * @param $with_to_add
	 *
	 * @return int|null
	 */
	private function update_child_variation_for_update( $child_id = 0, $add_min, $add_max, $with_to_add ) {
		$child_variation = TQB_Variation_Manager::get_variation( $child_id );
		if ( ! is_numeric( $add_max ) ) {
			$new_min = $child_variation['tcb_fields']['min'];
		} else {
			$new_min = $add_max + 1;
		}

		if ( ! is_numeric( $add_min ) ) {
			$new_max = $child_variation['tcb_fields']['max'];
		} else {
			$new_max = $add_min - 1;
		}

		// We test also that the new with is not lower then the minimum allowed width
		$aux_with = $child_variation['tcb_fields']['width'] + $with_to_add;
		if ( $aux_with < Thrive_Quiz_Builder::STATES_MINIMUM_WIDTH_SIZE ) {
			$new_child_with = Thrive_Quiz_Builder::STATES_MINIMUM_WIDTH_SIZE;
		} else {
			$new_child_with = $aux_with;
		}

		TQB_Variation_Manager::save_child_variation( array(
			'id'         => $child_variation['id'],
			'parent_id'  => $child_variation['parent_id'],
			'tcb_fields' => array( 'min' => $new_min, 'max' => $new_max, 'width' => $new_child_with ),
			'post_title' => $new_min . ' - ' . $new_max,
		) );

		return ( $new_child_with == Thrive_Quiz_Builder::STATES_MINIMUM_WIDTH_SIZE ) ? $new_child_with - $aux_with : null;
	}

	/**
	 * Updates child variation with absolute values
	 *
	 * @param int $child_id
	 * @param $min
	 * @param $max
	 * @param $with_to_add
	 */
	private function update_child_variation_absolute_values( $child_id = 0, $min, $max, $with_to_add ) {
		$child_variation = TQB_Variation_Manager::get_variation( $child_id );

		if ( empty( $min ) ) {
			$min = $child_variation['tcb_fields']['min'];
		}

		if ( empty( $max ) ) {
			$max = $child_variation['tcb_fields']['max'];
		}

		TQB_Variation_Manager::save_child_variation( array(
			'id'         => $child_variation['id'],
			'parent_id'  => $child_variation['parent_id'],
			'tcb_fields' => array( 'min' => $min, 'max' => $max, 'width' => $child_variation['tcb_fields']['width'] + $with_to_add ),
			'post_title' => $min . ' - ' . $max,
		) );
	}

	/**
	 * Builds child variation array
	 *
	 * @param array $child_variation
	 *
	 * @return array
	 */
	public function build_child_variation_arr( $child_variation = array() ) {
		return array(
			'quiz_id'     => $child_variation['quiz_id'],
			'page_id'     => $child_variation['page_id'],
			'parent_id'   => $child_variation['id'],
			'post_status' => $child_variation['post_status'],
			'content'     => Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN . '<div class="tve_content_inner tqb-content-inner">' . Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_DEFAULT . '</div>' . Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN,
		);
	}

	/**
	 * Generate result intervals (when the user selects the state interval from the state dropdown)
	 *
	 * @return array|bool
	 */
	public function api_set_result_intervals() {
		$result_interval = $this->param( 'result_interval' );

		if ( empty( $result_interval ) || ! is_numeric( $result_interval ) || $result_interval > Thrive_Quiz_Builder::STATES_MAXIMUM_NUMBER_OF_INTERVALS ) {
			return false;
		}

		$variation       = empty( $this->variation['id'] ) ? $this->variation : tqb_get_variation( $this->variation['id'] );
		$absolute_limits = tqb_compute_quiz_absolute_max_min_values( $variation['quiz_id'], true );

		$intervals_width_arr = tqb_compute_result_intervals_width( $result_interval );
		$intervals           = tqb_compute_results_intervals_limits_from_with( $intervals_width_arr, $absolute_limits['min'], $absolute_limits['max'] );

		// Delete the child variation
		TQB_Variation_Manager::delete_variation( array( 'parent_id' => $variation['id'] ) );

		$child = $this->build_child_variation_arr( $variation );

		foreach ( $intervals as $interval ) {
			TQB_Variation_Manager::save_child_variation( array_merge( $child, array(
				'post_title' => $interval['min'] . ' - ' . $interval['max'],
				'tcb_fields' => $interval,
			) ) );
		}

		return TQB_State_Manager::get_instance( $variation )->state_data( $variation );
	}

	/**
	 * Import state content from other states
	 *
	 * @return bool
	 */
	public function api_import() {
		$import_to   = $this->param( 'import_to' );
		$import_from = $this->param( 'import_from' );

		if ( empty( $import_to ) || ! is_numeric( $import_to ) ) {
			return false;
		}

		$from_variation = TQB_Variation_Manager::get_variation( $import_from );
		if ( $from_variation['parent_id'] != $this->variation['id'] ) {
			return false;
		}

		TQB_Variation_Manager::save_child_variation( array(
			'id'        => $import_to,
			'parent_id' => $from_variation['parent_id'],
			'content'   => $from_variation['content'],
		) );

		return $from_variation['content'];
	}

	/**
	 * Equalize states
	 *
	 * @return array|bool
	 */
	public function api_equalize() {

		$variation_manager = new TQB_Variation_Manager( $this->variation['quiz_id'], $this->variation['page_id'] );
		$child_variations  = $variation_manager->get_page_variations( array( 'parent_id' => $this->variation['id'] ) );

		//Sort the intervals array
		$flag = array();
		foreach ( $child_variations as $key => $variation ) {
			$flag[ $key ] = $variation['tcb_fields']['min'];
		}
		array_multisort( $flag, SORT_ASC, $child_variations );

		$absolute_limits = tqb_compute_quiz_absolute_max_min_values( $this->variation['quiz_id'], true );

		$intervals_width_arr = tqb_compute_result_intervals_width( count( $child_variations ) );
		$intervals           = tqb_compute_results_intervals_limits_from_with( $intervals_width_arr, $absolute_limits['min'], $absolute_limits['max'] );

		foreach ( $child_variations as $key => $child ) {
			TQB_Variation_Manager::save_child_variation( array_merge( $child, array(
				'id'         => $child['id'],
				'post_title' => $intervals[ $key ]['min'] . ' - ' . $intervals[ $key ]['max'],
				'tcb_fields' => $intervals[ $key ],
			) ) );
		}

		return TQB_State_Manager::get_instance( $this->variation )->state_data( $this->variation );
	}

	/**
	 * Split one state into 2 equal states
	 *
	 * @return array|bool
	 */
	public function api_split() {
		$child_id = $this->param( 'child_id' );

		if ( empty( $child_id ) && ! is_numeric( $child_id ) ) {
			return false;
		}

		$child_variation = TQB_Variation_Manager::get_variation( $child_id );

		$number_to_add = floor( ( $child_variation['tcb_fields']['max'] - $child_variation['tcb_fields']['min'] ) / 2 );
		$new_child_max = $child_variation['tcb_fields']['min'] + $number_to_add;
		list( $new_child_width, $child_next_width ) = array( $child_variation['tcb_fields']['width'] / 2, $child_variation['tcb_fields']['width'] / 2 );
		if ( $new_child_width % 10 != 0 ) {
			$new_child_width -= 5;
			$child_next_width += 5;
		}

		TQB_Variation_Manager::save_child_variation( array(
			'id'         => $child_variation['id'],
			'parent_id'  => $child_variation['parent_id'],
			'tcb_fields' => array( 'min' => $child_variation['tcb_fields']['min'], 'max' => $new_child_max, 'width' => $new_child_width ),
			'post_title' => $child_variation['tcb_fields']['min'] . ' - ' . $new_child_max,
		) );

		$child = $this->build_child_variation_arr( $child_variation );

		TQB_Variation_Manager::save_child_variation( array_merge( $child, array(
			'parent_id'  => $child_variation['parent_id'],
			'tcb_fields' => array( 'min' => $new_child_max + 1, 'max' => $child_variation['tcb_fields']['max'], 'width' => $child_next_width ),
			'post_title' => $new_child_max + 1 . ' - ' . $child_variation['tcb_fields']['max'],
			'content'    => $child_variation['content'],
		) ) );

		return $this->state_data( $this->variation );
	}

	/**
	 * Remove a state
	 */
	public function api_remove() {
		$child_id      = $this->param( 'child_id' );
		$child_prev_id = $this->param( 'child_prev_id' );
		$child_next_id = $this->param( 'child_next_id' );

		if ( empty( $child_id ) && ! is_numeric( $child_id ) ) {
			return false;
		}

		$child_variation = TQB_Variation_Manager::get_variation( $child_id );

		// prev child not exists => we put the with to the next child
		if ( empty( $child_prev_id ) ) {
			$this->update_child_variation_absolute_values( $child_next_id, $child_variation['tcb_fields']['min'], null, $child_variation['tcb_fields']['width'] );
		}

		// next child not exists => we put the with to the prev child
		if ( empty( $child_next_id ) ) {
			$this->update_child_variation_absolute_values( $child_prev_id, null, $child_variation['tcb_fields']['max'], $child_variation['tcb_fields']['width'] );
		}

		// we split the space between
		if ( ! empty( $child_prev_id ) && ! empty( $child_next_id ) ) {
			list( $child_prev_width, $child_next_width ) = array( $child_variation['tcb_fields']['width'] / 2, $child_variation['tcb_fields']['width'] / 2 );
			if ( $child_prev_width % 10 != 0 ) {
				$child_prev_width -= 5;
				$child_next_width += 5;
			}
			$number_to_add = ( $child_variation['tcb_fields']['max'] - $child_variation['tcb_fields']['min'] + 1 ) / 2;

			if ( $number_to_add % 2 == 0 ) {
				list( $child_prev_add_max, $child_next_add_min ) = array( ceil( $number_to_add ), floor( $number_to_add ) );
			} else {
				list( $child_prev_add_max, $child_next_add_min ) = array( ceil( $number_to_add ), floor( $number_to_add ) );
			}

			$this->update_child_variation_absolute_values( $child_prev_id, null, $child_variation['tcb_fields']['min'] - 1 + $child_prev_add_max, $child_prev_width );
			$this->update_child_variation_absolute_values( $child_next_id, $child_variation['tcb_fields']['max'] + 1 - $child_next_add_min, null, $child_next_width );
		}

		// Delete the child variation
		TQB_Variation_Manager::delete_variation( array( 'id' => $child_id ) );

		return $this->state_data( $this->variation );
	}

	/**
	 * Generate a b c child variations
	 *
	 * @return array
	 */
	public function api_generate_personality_child_variations() {
		$child        = $this->build_child_variation_arr( $this->variation );
		$quiz_manager = new TQB_Quiz_Manager( $this->variation['quiz_id'] );
		$results      = $quiz_manager->get_results();

		foreach ( $results as $key => $result ) {
			TQB_Variation_Manager::save_child_variation( array_merge( $child, array(
				'post_title' => $result['text'],
				'tcb_fields' => array(
					'result_id' => $result['id'],
				),
			) ) );
		}

		return $this->state_data( $this->variation );
	}

	/**
	 * Gets child variation
	 *
	 * @return array|bool|null|object|void
	 */
	public function api_get_child_variation() {
		$child_variation_id = $this->param( 'child_variation' );
		if ( ! empty( $child_variation_id ) && is_numeric( $child_variation_id ) ) {
			$child_variation            = TQB_Variation_Manager::get_variation( $child_variation_id );
			$child_variation['content'] = tve_thrive_shortcodes( $child_variation['content'], true );

			return $child_variation;
		}

		return false;
	}

	/**
	 * Fetch social share badge selected template
	 *
	 * @return string
	 */
	public function api_get_social_share_badge_template() {
		$template = $this->param( 'template' );
		$base     = tqb()->plugin_path( 'tcb-bridge/editor-templates/social-share-badge' );

		if ( empty( $template ) || ! is_file( $base . '/' . $template . '.php' ) || empty( $this->variation ) ) {
			return '';
		}

		$tie_image     = new TIE_Image( $this->variation['page_id'] );
		$tie_image_url = $tie_image->get_image_url();
		if ( empty( $tie_image_url ) ) {
			$tie_image_url = tqb()->plugin_url( 'tcb-bridge/assets/images/share-badge-default.png' );
		}

		ob_start();
		include $base . '/' . $template . '.php';
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Delete dynamic content
	 */
	public function api_delete_dynamic_content() {
		//Delete Child Variations
		return TQB_Variation_Manager::delete_variation( array( 'parent_id' => $this->variation['id'] ) );
	}

	/**
	 * Copy the dynamic content from similar pages that have dynamic content (ex: Result, Optin)
	 *
	 * @return array|bool
	 */
	public function api_copy_similar_dynamic_content() {

		$child_variations = tqb_has_similar_dynamic_content( $this->variation );

		if ( $child_variations === false ) {
			return false;
		}
		// Delete previous child variations
		TQB_Variation_Manager::delete_variation( array( 'parent_id' => $this->variation['id'] ) );

		$child = $this->build_child_variation_arr( $this->variation );
		foreach ( $child_variations as $child_var ) {
			TQB_Variation_Manager::save_child_variation( array_merge( $child, array(
				'post_title' => $child_var['post_title'],
				'tcb_fields' => $child_var['tcb_fields'],
			) ) );
		}

		return TQB_State_Manager::get_instance( $this->variation )->state_data( $this->variation );;
	}
}

