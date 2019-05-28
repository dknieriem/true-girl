<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Class TQB_Structure_Manager
 *
 * Handles Structure operations
 */
class TQB_Structure_Manager {

	/**
	 * @var TQB_Structure_Manager $instance
	 */
	protected $quiz_id;

	/**
	 * TQB_Structure_Manager constructor.
	 */
	public function __construct( $quiz_id ) {
		$this->quiz_id = $quiz_id;
	}

	/**
	 * Get quiz structure meta
	 *
	 * @return mixed
	 */
	public function get_quiz_structure_meta() {
		$structure = get_post_meta( $this->quiz_id, TQB_Post_meta::META_NAME_FOR_QUIZ_STRUCTURE, true );

		return $structure;
	}

	/**
	 * Updates the quiz structure
	 *
	 * @param $model
	 *
	 * @return false|int
	 */
	public function update_quiz_structure( $model ) {
		$old_structure = $this->get_quiz_structure_meta();
		$model         = $this->update_structure_item_posts( $model, $old_structure );
		$result        = $this->update_quiz_structure_meta( $model );

		return $model;
	}

	/**
	 * Updates the quiz structure page viewed status
	 *
	 * @param $type
	 *
	 * @return false|int
	 */
	public function update_quiz_viewed_status( $type, $value ) {
		$structure                    = $this->get_quiz_structure_meta();
		$structure['viewed'][ $type ] = $value;
		$result                       = $this->update_quiz_structure_meta( $structure );

		return $result;
	}

	/**
	 * Updates the quiz structure meta
	 *
	 * @param $model
	 *
	 * @return false|int
	 */
	public function update_quiz_structure_meta( $model ) {
		if ( isset( $model['running_tests'] ) ) {
			unset( $model['running_tests'] );
		}
		$result = update_post_meta( $this->quiz_id, TQB_Post_meta::META_NAME_FOR_QUIZ_STRUCTURE, $model );

		return $result;
	}

	/**
	 * Updates structure items posts
	 *
	 * @param $model
	 */
	function update_structure_item_posts( $model, $old_structure ) {
		$array = tqb()->get_structure_internal_identifiers();

		foreach ( $array as $value ) {
			if ( empty( $old_structure[ $value ] ) ) {
				$old_structure[ $value ] = false;
			}
			if ( $model[ $value ] !== $old_structure[ $value ] ) {
				$model[ $value ]           = $this->update_structure_item( $model[ $value ], $old_structure[ $value ], $value );
				$model['viewed'][ $value ] = false;
			}
		}

		return $model;
	}

	/**
	 * Updates structure item
	 *
	 * @param $new_value
	 * @param $old_value
	 * @param $type
	 *
	 * @return bool/int
	 */
	function update_structure_item( $new_value, $old_value, $type ) {

		if ( ! isset( $old_value ) ) {
			return $new_value;
		}
		if ( is_int( $old_value ) ) {
			$page_structure = new TQB_Page_Manager( $old_value );
			$page           = $page_structure->get_page();
			if ( empty( $new_value ) && ! empty( $page ) ) {
				$page_structure->delete_page();
			}
		} elseif ( $new_value && $type != 'qna' ) {
			$data      = $this->generate_first_variation( array( 'type' => $type, 'page_id' => null, 'quiz_id' => $this->quiz_id ) );
			$new_value = $data['page_id'];
		}

		return $new_value;
	}

	/**
	 * Generate first variation and/or first page
	 *
	 * @param $model
	 *
	 * @return array
	 */
	function generate_first_variation( $model ) {
		$variation = new TQB_Variation_Manager( $this->quiz_id, $model['page_id'] );
		if ( empty( $model['post_title'] ) ) {
			$model['post_title'] = __( 'Control', Thrive_Quiz_Builder::T );
		}
		if ( $model['page_id'] == 'false' ) {
			$model['page_id'] = null;
		}
		$model = $variation->validate_variation( $model );
		$model = $variation->get_default_variation_content( $model );

		if ( ! $variation->has_control( $model['page_id'] ) ) {
			$model['is_control'] = 1;
		}

		$model                   = $variation->save_variation( $model, false );
		$model['tcb_editor_url'] = TQB_Variation_Manager::get_editor_url( $model['page_id'], $model['id'] );

		return $model;
	}

	/**
	 * Update an individual structure item
	 *
	 * @param $type
	 * @param $value
	 *
	 * @return int|WP_Error
	 */
	function update_individual_structure_item( $type, $value ) {
		$structure          = $this->get_quiz_structure_meta();
		$structure[ $type ] = $value;
		$result             = $this->update_quiz_structure_meta( $structure );

		return $result;
	}

	/**
	 * Saved the page
	 *
	 * @param $type
	 *
	 * @return int|WP_Error
	 */
	function save_structure_item( $type ) {
		$page_structure = new TQB_Page_Manager();
		$post_id        = $page_structure->save_page( $type, $this->quiz_id );

		if ( $post_id ) {
			$this->update_individual_structure_item( $type, $post_id );
		}

		return $post_id;
	}

	/**
	 * Get page html to display on frontend
	 *
	 * @return int|WP_Error
	 */
	function get_page_content( $page_type, $points = null, $post_id = 0 ) {
		$structure = $this->get_quiz_structure_meta();

		if ( ! is_numeric( $structure[ $page_type ] ) ) {
			return false;
		}

		$page_manager = new TQB_Page_Manager( $structure[ $page_type ] );
		if ( empty( $page_manager ) ) {
			return false;
		}

		$variation = $page_manager->get_page_display_html( $points );
		if ( empty( $variation[ Thrive_Quiz_Builder::FIELD_CONTENT ] ) ) {
			return false;
		}

		$tcb_fields = is_array( $variation['tcb_fields'] ) ? $variation['tcb_fields'] : unserialize( $variation['tcb_fields'] );

		if ( ! empty( $tcb_fields[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] ) ) { /* inline style rules = custom colors */
			$variation[ Thrive_Quiz_Builder::FIELD_CONTENT ] .= sprintf( '<style type="text/css" class="tve_custom_style">%s</style>', stripslashes( $tcb_fields[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] ) );
		}

		list( $variation_type, $key ) = TQB_Template_Manager::tpl_type_key( $tcb_fields[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] );
		$config = require tqb()->plugin_path( 'tcb-bridge/editor-templates/config.php' );

		$data['fonts'] = array();
		/*Include variation custom fonts*/
		if ( ! empty( $tcb_fields[ Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS ] ) ) {
			foreach ( $tcb_fields[ Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS ] as $variation_custom_font ) {
				$data['fonts'][] = $variation_custom_font;

			}
		}

		/*Include config fonts*/
		if ( ! empty( $config[ $variation_type ][ $key ] ) ) {
			$config = $config[ $variation_type ][ $key ];
			if ( ! empty( $config['fonts'] ) ) {
				foreach ( $config['fonts'] as $font ) {
					$data['fonts'][] = $font;
				}
			}
		}

		$quiz_style_meta   = TQB_Post_meta::get_quiz_style_meta( $variation['quiz_id'] );
		$template_css_file = tqb()->get_style_css( $quiz_style_meta );
		/* include also the CSS for each variation template */
		if ( ! empty( $template_css_file ) ) {
			$data['css'] = array(
				tqb()->plugin_url( 'tcb-bridge/editor-templates/css/' . TQB_Template_Manager::type( $variation['post_type'] ) . '/' . $template_css_file ),
			);
		}

		if ( ! empty( $post_id ) && is_numeric( $post_id ) ) {
			$GLOBALS['tcb_main_post_lightbox'] = get_post( $post_id );
		}

		$data['user_css']     = ( ! empty( $tcb_fields[ Thrive_Quiz_Builder::FIELD_USER_CSS ] ) ) ? $tcb_fields[ Thrive_Quiz_Builder::FIELD_USER_CSS ] : '';
		$data['html']         = do_shortcode( tve_do_wp_shortcodes( tve_thrive_shortcodes( $variation[ Thrive_Quiz_Builder::FIELD_CONTENT ] ) ) );
		$data['page_id']      = $structure[ $page_type ];
		$data['variation_id'] = $variation['id'];
		$data['quiz_id']      = $variation['quiz_id'];

		return $data;
	}

	/**
	 * Validate questions streak
	 *
	 * @return bool
	 */
	function validate_qna() {
		global $tgedb;

		$filters = array( 'quiz_id' => $this->quiz_id, 'start' => 1 );

		$first_question = $tgedb->get_quiz_questions( $filters, true );
		if ( empty( $first_question ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get preview error messages in case content is missing on pages
	 *
	 * @return array
	 */
	function get_display_availability() {
		$structure = $this->get_quiz_structure_meta();
		$pages     = tqb()->get_structure_internal_identifiers();
		$result    = array(
			'valid'          => true,
			'error_messages' => array(),
		);

		foreach ( $pages as $page ) {
			switch ( $page ) {
				case 'splash'://not mandatory

					if ( isset( $structure[ $page ] ) && $structure[ $page ] ) {

						if ( ! is_numeric( $structure[ $page ] ) ) {

							$result['error'][ $page ] = __( 'Your Splash Page is empty! Make sure you have at least one variation for it.', Thrive_Quiz_Builder::T );
							$result['notice']         = true;
						} else {
							$data = $this->get_page_content( $page );
							if ( ! $data ) {
								$result['error'][ $page ] = __( 'Your Splash Page is empty! Make sure you have at least one variation.', Thrive_Quiz_Builder::T );
								$result['notice']         = true;
							}
						}
					}
					break;
				case 'qna'://mandatory

					if ( ! $this->validate_qna() ) {
						$result['error'][ $page ] = __( 'Your quiz has no start question!', Thrive_Quiz_Builder::T );
						$result['valid']          = false;
					}
					break;
				case 'optin':

					if ( isset( $structure[ $page ] ) && $structure[ $page ] ) {
						if ( ! is_numeric( $structure[ $page ] ) ) {
							$result['error'][ $page ] = __( 'Your Opt-in Page is empty! Make sure you have at least one variation.', Thrive_Quiz_Builder::T );
							$result['notice']         = true;
						} else {
							$data = $this->get_page_content( $page );
							if ( ! $data ) {
								$result['error'][ $page ] = __( 'Your Opt-in Page is empty! Make sure you have at least one variation.', Thrive_Quiz_Builder::T );
								$result['notice']         = true;
							}
						}
					}
					break;
				case 'results'://mandatory

					if ( ! isset( $structure[ $page ] ) || ! is_numeric( $structure[ $page ] ) ) {
						$result['error'][ $page ] = __( 'Your Results Page is empty! Make sure you have at least one variation.', Thrive_Quiz_Builder::T );
						$result['valid']          = false;
					} else {
						$data = $this->get_page_content( $page );
						if ( ! $data ) {
							$result['error'][ $page ] = __( 'Your Results Page is empty! Make sure you have at least one variation.', Thrive_Quiz_Builder::T );
							$result['valid']          = false;
						}
					}
					break;
			}
		}

		return $result;
	}
}
