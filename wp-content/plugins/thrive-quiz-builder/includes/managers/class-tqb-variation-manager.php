<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Class TQB_Variation_Manager
 *
 * Handles Variation operations
 */
class TQB_Variation_Manager {

	/**
	 * @var TQB_Variation_Manager $instance
	 */
	protected $quiz_id;

	protected $page_id;

	/**
	 * TQB_Variation_Manager constructor.
	 */
	public function __construct( $quiz_id, $page_id = null ) {
		$this->quiz_id = $quiz_id;
		$this->page_id = $page_id;
	}

	/**
	 * Get single quiz variation.
	 */
	public static function get_variation( $id ) {
		global $tqbdb;

		$variation = $tqbdb->get_variation( $id );
		$variation = _unserialize_fields( $variation, array( 'tcb_fields' ) );
		$variation = _assign_fields( $variation, array( 'tcb_fields' ) );

		return $variation;
	}

	/**
	 * Get all page variations according to filter
	 *
	 * @param array $filters
	 *
	 * @return array|null|object|void
	 */
	public function get_page_variations( $filters = array() ) {
		global $tqbdb;

		$filters = array_merge( $filters, array( 'post_id' => $this->page_id ) );

		return $tqbdb->get_page_variations( $filters );
	}

	/**
	 * Validate variation data
	 *
	 * @param array $model
	 *
	 * @return array $model
	 */
	public function validate_variation( $model = array() ) {
		if ( empty( $model['page_id'] ) ) {
			$structure_manager = new TQB_Structure_Manager( $model['quiz_id'] );
			$model['page_id']  = $structure_manager->save_structure_item( $model['type'], true );
		}

		if ( empty( $model['page_id'] ) ) {
			return false;
		}

		return $model;
	}

	/**
	 * Prepare the data for the variation insertion / update into the database
	 *
	 * @param array $model
	 * @param bool $skip_tcb
	 *
	 * @return array|false model of variation or false
	 */
	public function save_variation( $model = array(), $skip_tcb = false ) {

		$model = $this->validate_variation( $model );

		if ( empty( $model['page_id'] ) ) {
			return false;
		}

		$post = get_post( $model['page_id'] );
		if ( empty( $post ) || is_wp_error( $post ) ) {
			return false;
		}

		if ( $skip_tcb === false ) {
			foreach ( Thrive_Quiz_Builder::editor_fields() as $field ) {
				$model['tcb_fields'][ $field ] = isset( $model[ $field ] ) ? $model[ $field ] : '';
			}
		}

		global $tqbdb;

		$defaults = array(
			'date_added'    => date( 'Y-m-d H:i:s' ),
			'date_modified' => date( 'Y-m-d H:i:s' ),
		);

		$filters     = array_merge( $defaults, $model );
		$model['id'] = $tqbdb->save_variation( $filters );

		return $model;
	}

	/**
	 * Saves child variation
	 * parent_id -> REQUIRED
	 *
	 * @param array $model
	 *
	 * @return array|bool
	 */
	public static function save_child_variation( $model = array() ) {
		if ( empty( $model['parent_id'] ) ) {
			return false;
		}
		global $tqbdb;
		$defaults    = array(
			'date_added'    => date( 'Y-m-d H:i:s' ),
			'date_modified' => date( 'Y-m-d H:i:s' ),
		);
		$filters     = array_merge( $defaults, $model );
		$model['id'] = $tqbdb->save_variation( $filters );

		return $model;
	}

	/**
	 * Changes the control with the first variation non control.
	 *
	 * @param int $page_id
	 *
	 * @return bool|int
	 */
	public function change_control( $page_id = 0 ) {
		global $tqbdb;

		if ( empty( $page_id ) ) {
			return false;
		}

		$variations = $tqbdb->get_page_variations( array(
			'post_status' => Thrive_Quiz_Builder::VARIATION_STATUS_PUBLISH,
			'post_id'     => $page_id,
			'is_control'  => 0,
		), OBJECT );

		if ( empty( $variations ) ) {
			return false;
		}

		$first_variation = current( $variations );

		return $tqbdb->save_variation( array(
			'id'         => $first_variation->id,
			'page_id'    => $first_variation->page_id,
			'is_control' => 1,
		) );
	}

	/**
	 * @param int $page_id
	 *
	 * @return bool
	 */
	public function has_control( $page_id = 0 ) {
		global $tqbdb;

		if ( empty( $page_id ) ) {
			return false;
		}

		$contor = $tqbdb->count_page_variations( array(
			'post_status' => Thrive_Quiz_Builder::VARIATION_STATUS_PUBLISH,
			'post_id'     => $page_id,
			'is_control'  => 1,
		), OBJECT );

		if ( empty( $contor ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Prepare the data for variation delete.
	 *
	 * @param array $model
	 *
	 * @return bool|false|int
	 */
	public static function delete_variation( $model = array() ) {
		global $tqbdb;

		if ( ! empty( $model ) ) {
			return $tqbdb->delete_variations( $model );
		}

		return false;
	}

	/**
	 * @param int $post_id
	 * @param int $variation_key
	 *
	 * @return mixed|string
	 */
	public static function get_editor_url( $post_id = 0, $variation_key = 0 ) {
		$cache = isset( $GLOBALS['TQB_CACHE_PERMALINKS'] ) ? $GLOBALS['TQB_CACHE_PERMALINKS'] : array();
		if ( ! isset( $cache[ $post_id ] ) ) {
			$cache[ $post_id ]               = set_url_scheme( get_permalink( $post_id ) );
			$GLOBALS['TQB_CACHE_PERMALINKS'] = $cache;
		}

		$post        = get_post( $post_id );
		$editor_link = $cache[ $post_id ];
		$editor_link = esc_url( apply_filters( 'preview_post_link', add_query_arg( array(
			'tve'                                         => 'true',
			Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME => $variation_key,
			'r'                                           => uniqid(),
		), $editor_link ), $post ) );

		/**
		 * we need to make sure that if the admin is https, then the editor link is also https, otherwise any ajax requests through wp ajax api will not work
		 */
		$admin_ssl = strpos( admin_url(), 'https' ) === 0;

		return $admin_ssl ? str_replace( 'http://', 'https://', $editor_link ) : $editor_link;
	}

	/**
	 *
	 * get the TCB editor PREVIEW URL for a form variation
	 *
	 * @param int $post_id
	 * @param int $variation_key
	 *
	 * @return string the url to open the editor for this variation
	 */
	public static function get_preview_url( $post_id, $variation_key ) {
		$cache = isset( $GLOBALS['TQB_CACHE_PERMALINKS'] ) ? $GLOBALS['TQB_CACHE_PERMALINKS'] : array();
		if ( ! isset( $cache[ $post_id ] ) ) {
			$cache[ $post_id ]               = set_url_scheme( get_permalink( $post_id ) );
			$GLOBALS['TQB_CACHE_PERMALINKS'] = $cache;
		}

		/*
		 * We need the post to complete the full arguments
		 */
		$post        = get_post( $post_id );
		$editor_link = $cache[ $post_id ];
		$editor_link = esc_url( apply_filters( 'preview_post_link', add_query_arg( array(
			Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME => $variation_key,
			'r'                                           => uniqid(),
		), $editor_link ), $post ) );

		return $editor_link;
	}

	/**
	 * Determine which variation inside test to run
	 *
	 * @return bool|false|int
	 */
	public function determine_variation( $test ) {

		$variations = $this->get_page_variations( array( 'post_status' => 'publish' ) );
		/**
		 * if there's a previous cookie key setup for this variation and a test is running, then we should show the same variation to the user
		 */

		$same_variation_key = 'tqb_t_' . $test['id'] . '_p_' . $test['page_id'];
		if ( isset( $_COOKIE[ $same_variation_key ] ) && isset( $variations[ $_COOKIE[ $same_variation_key ] ] ) ) {
			$variation_index = $_COOKIE[ $same_variation_key ];
		} else {
			$variation_index = tqb_get_random_index( count( $variations ) );
		}
		$variation = isset( $variations[ $variation_index ] ) ? $variations[ $variation_index ] : $variations[0];
		if ( ! headers_sent() ) {
			setcookie( $same_variation_key, $variation_index, time() + 3600 * 30, '/' );
		} else {
			$cookies_to_set[ $same_variation_key ] = array(
				'value'   => $variation_index,
				'expires' => 30,
			);
			$variation['set_cookies']              = $cookies_to_set;
		}

		return $variation;
	}

	/**
	 * Logs social share conversion
	 *
	 * @param int $id
	 *
	 * @return bool|int
	 */
	public function update_social_share_conversion( $id = 0 ) {
		if ( ! empty( $id ) && ! empty( $this->quiz_id ) && ! empty( $this->page_id ) ) {
			global $tqbdb;
			$tqbdb->update_test_item_action_counter( array(
				'social_shares_conversions' => true,
				'variation_id'              => $id,
			) );

			return $tqbdb->update_variation_cached_counter( array(
				'social_conversion' => true,
				'variation_id'      => $id,
			) );
		}

		return false;
	}

	/**
	 * Remains only the content and tcb_fields. Nothing else!
	 *
	 * @param array $variation
	 *
	 * @return array
	 */
	public function prepare_variation_for_tcb_save( $variation = array() ) {

		unset( $variation['cache_impressions'] );
		unset( $variation['cache_optins'] );
		unset( $variation['cache_optins_conversions'] );
		unset( $variation['cache_social_shares'] );
		unset( $variation['cache_social_shares_conversions'] );
		unset( $variation['post_status'] );
		unset( $variation['is_control'] );
		unset( $variation['post_title'] );

		return $variation;
	}


	/**
	 * Clone variation
	 *
	 * @param array $variation
	 *
	 * @return array|bool
	 */
	public function clone_variation( $variation = array() ) {
		if ( empty( $variation['id'] ) && ! is_numeric( $variation['id'] ) ) {
			return false;
		}

		global $tqbdb;
		$inserted_id = $tqbdb->clone_variation( $variation );

		// Duplicate also the child variations
		$child_variations = $tqbdb->get_page_variations( array( 'parent_id' => $variation['id'] ) );
		if ( ! empty( $child_variations ) ) {

			foreach ( $child_variations as $child_variation ) {
				unset( $child_variation['id'] );
				$child_variation['parent_id']     = $inserted_id;
				$child_variation['date_added']    = date( 'Y-m-d H:i:s' );
				$child_variation['date_modified'] = date( 'Y-m-d H:i:s' );

				$tqbdb->save_variation( $child_variation );
			}
		}

		$variation['id'] = $inserted_id;

		return $variation;
	}

	/**
	 * Create default content for default variation
	 *
	 * @param array $model
	 *
	 * @return array
	 */
	public function get_default_variation_content( $model = array() ) {

		if ( ! empty( $model['type'] ) && ! empty( $model['quiz_id'] ) ) {
			$model['post_type'] = tqb()->get_structure_post_type_name( $model['type'] );
			$templates          = TQB_Template_Manager::get_templates( $model['post_type'], $model['quiz_id'] );
			$quiz_template_id   = TQB_Post_meta::get_quiz_tpl_meta( $model['quiz_id'] );
			$all_quiz_templates = tqb()->get_quiz_templates();
			$quiz_template      = null;

			foreach ( $all_quiz_templates as $qt ) {
				if ( $quiz_template_id == $qt['id'] ) {
					$quiz_template = $qt;
					break;
				}
			}

			if ( empty( $quiz_template ) ) {
				return $model;
			}

			if ( empty( $quiz_template['default_page_templates'][ $model['post_type'] ] ) ) {
				return $model;
			}

			if ( ! empty( $templates[ $quiz_template['default_page_templates'][ $model['post_type'] ] ]['key'] ) ) {

				$model[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] = $templates[ $quiz_template['default_page_templates'][ $model['post_type'] ] ]['key'];
				$model[ Thrive_Quiz_Builder::FIELD_CONTENT ]  = TCB_Hooks::tqb_editor_get_template_content( $model, $model[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] );
				$model[ Thrive_Quiz_Builder::FIELD_SOCIAL_SHARE_BADGE ] = ( strpos( $model[ Thrive_Quiz_Builder::FIELD_CONTENT ], '"tqb-social-share-badge-container' ) !== false ) ? 1 : 0;
			}
		}

		return $model;
	}
}
