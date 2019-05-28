<?php

/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/4/2016
 * Time: 4:28 PM
 */
class TQB_Template_Manager extends TQB_Request_Handler {

	const OPTION_TPL_META = 'tqb_saved_tpl_meta';
	const OPTION_TPL_CONTENT = 'tqb_saved_tpl';

	/**
	 * @var $instance TQB_Template_Manager
	 */
	protected static $instance = null;

	/**
	 * Row from DB
	 *
	 * @var $variation array
	 */
	protected $variation;

	/**
	 * Templates config for variation templates
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * Variation Manager
	 *
	 * @var null|TQB_Variation_Manager
	 */
	protected $variation_manager = null;

	/**
	 * TQB_Template_Manager constructor.
	 *
	 * @param $variation
	 */
	protected function __construct( $variation ) {
		$this->variation          = $variation;
		$this->variation['style'] = TQB_Post_meta::get_quiz_style_meta( $variation['quiz_id'] );
		$this->variation_manager  = new TQB_Variation_Manager( $variation['quiz_id'], $variation['page_id'] );
		$this->config();
	}

	protected function config() {
		$this->config = TQB_Template_Manager::get_default_templates();
	}

	public static function get_default_templates() {
		return include tqb()->plugin_path( 'tcb-bridge/editor-templates/config.php' );
	}

	/**
	 * Returns the instance of the design
	 *
	 * @param null $variation
	 *
	 * @return TQB_Template_Manager
	 */
	public static function get_instance( $variation = null ) {

		if ( ! is_array( $variation ) || empty( $variation ) ) {
			if ( ! empty( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
				$variation = tqb_get_variation( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] );
			} else {
				$variation = array();
			}
		}

		if ( ! self::$instance ) {
			self::$instance = new self( $variation );
		}

		if ( isset( $variation['id'] ) && self::$instance->variation['id'] != $variation['id'] ) {
			self::$instance = new self( $variation );
		}

		return self::$instance;
	}

	/**
	 * header bars and footer bars should have the same templates
	 *
	 * @param string $tpl
	 *
	 * @return string
	 */
	public static function type( $tpl ) {
		if ( empty( $tpl ) ) {
			return '';
		}
		$parts = explode( '|', $tpl );

		return $parts[0];
	}

	/**
	 * get the type and tpl name from a design template name
	 *
	 * @param string $tpl
	 *
	 * @return array
	 */
	public static function tpl_type_key( $tpl ) {
		if ( empty( $tpl ) ) {
			return array( '', '' );
		}

		list( $type, $key ) = explode( '|', $tpl );

		return array(
			$type,
			$key,
		);
	}

	/**
	 * Returns the variation templates set in config.php for a specific design type
	 *
	 * @param $variation_type
	 * @param int $quiz_id
	 *
	 * @return array
	 */
	public static function get_templates( $variation_type = '', $quiz_id = 0 ) {

		$all_default_templates = TQB_Template_Manager::get_default_templates();
		$quiz_style            = TQB_Post_meta::get_quiz_style_meta( $quiz_id );
		$templates             = $all_default_templates[ $variation_type ];

		foreach ( $templates as $tpl => $tpl_data ) {
			$templates[ $tpl ]['key']       = $variation_type . '|' . $tpl;
			$templates[ $tpl ]['thumbnail'] = tqb()->plugin_url( 'tcb-bridge/editor-templates/' . $variation_type . '/thumbnails/style-' . $quiz_style . '/' . $tpl . '.png' );
		}

		return $templates;
	}

	/**
	 * API entry point for templates
	 *
	 * @param $action
	 *
	 * @return false|string json
	 */
	public function api( $action ) {
		$method = 'api_' . strtolower( $action );

		if ( ! method_exists( $this, $method ) ) {
			return false;
		}

		$result = call_user_func( array( $this, $method ) );

		exit( json_encode( $result ) );
	}

	/**
	 * exchange data from $template to this->variation or vice-versa
	 *
	 * @param array $template
	 * @param string $dir can either be left-right or right-left
	 *
	 * @return array
	 */
	protected function interchange_data( $template, $dir = 'left -> right' ) {
		$fields = array(
			Thrive_Quiz_Builder::FIELD_CONTENT,
			Thrive_Quiz_Builder::FIELD_INLINE_CSS,
			Thrive_Quiz_Builder::FIELD_USER_CSS,
			Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS,
			Thrive_Quiz_Builder::FIELD_TYPEFOCUS,
			Thrive_Quiz_Builder::FIELD_MASONRY,
			Thrive_Quiz_Builder::FIELD_ICON_PACK,
		);

		foreach ( $fields as $field ) {
			if ( strpos( $dir, 'left' ) === 0 ) {
				$this->variation[ $field ] = $template[ $field ];
			} else {
				$template[ $field ] = $this->variation[ $field ];
			}
		}

		return $template;
	}

	/**
	 * --------------------------------------------------------------------
	 * -------------------- API-calls after this point -------------------- :)
	 * --------------------------------------------------------------------
	 */


	/**
	 * Choose a new template
	 */
	public function api_choose() {
		if ( ! ( $template = $this->param( 'tpl' ) ) ) {
			return false;
		}

		if ( strpos( $template, 'user-saved-template-' ) === 0 ) {
			/* at this point, the template is one of the previously saved templates (saved by the user) -
				it holds the index from the option array which needs to be loaded */

			$contents = get_option( self::OPTION_TPL_CONTENT );
			$meta     = get_option( self::OPTION_TPL_META );

			$template_index = intval( str_replace( 'user-saved-template-', '', $template ) );

			/* make sure we don't mess anything up */
			if ( empty( $contents ) || empty( $meta ) || ! isset( $contents[ $template_index ] ) ) {
				return '';
			}

			$tpl_data = $contents[ $template_index ];
			$template = $meta[ $template_index ]['tpl'];

			$this->interchange_data( $tpl_data, 'left -> right' );

			$this->variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] = $template;

			if ( ! empty( $meta[ $template_index ]['dynamic_content'] ) ) {
				$child = TQB_State_Manager::get_instance( $this->variation )->build_child_variation_arr( $this->variation );
				foreach ( $meta[ $template_index ]['dynamic_content'] as $i => $dynamic_content ) {
					TQB_Variation_Manager::save_child_variation( array_merge( $child, array(
						'post_title' => $dynamic_content['post_title'],
						'content'    => $dynamic_content['content'],
						'tcb_fields' => $dynamic_content['tcb_fields'],
					) ) );
				}
			}
		} else {
			TQB_Variation_Manager::delete_variation( array( 'parent_id' => $this->variation['id'] ) );

			$this->variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] = $template;
			$this->variation[ Thrive_Quiz_Builder::FIELD_CONTENT ]  = TCB_Hooks::tqb_editor_get_template_content( $this->variation, $template );
		}

		$this->variation_manager->save_variation( $this->variation );
		$variation = empty( $this->variation['id'] ) ? $this->variation : tqb_get_variation( $this->variation['id'] );

		return TQB_State_Manager::get_instance( $variation )->state_data( $this->variation );
	}

	/**
	 * reset contents for the current template
	 */
	public function api_reset() {

		$this->variation[ Thrive_Quiz_Builder::FIELD_CONTENT ] = TCB_Hooks::tqb_editor_get_template_content( $this->variation );

		//Delete Child Variations
		TQB_Variation_Manager::delete_variation( array( 'parent_id' => $this->variation['id'] ) );
		$this->variation_manager->save_variation( $this->variation );

		return TQB_State_Manager::get_instance( $this->variation )->state_data( $this->variation );
	}

	/**
	 * Save the current variation config and content as a template so that it can later be applied to other variation
	 */
	public function api_save() {
		$template_content = $this->interchange_data( array(), 'right -> left' );

		list( $type, $key ) = self::tpl_type_key( $this->variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] );


		$variation_manager = new TQB_Variation_Manager( $this->variation['quiz_id'], $this->variation['page_id'] );
		$template_meta     = array(
			'name'            => $this->param( 'name', '' ),
			'tpl'             => $this->variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ],
			'type'            => $type,
			'key'             => $key,
			'date'            => date( 'Y-m-d' ),
			'dynamic_content' => $variation_manager->get_page_variations( array( 'parent_id' => $this->variation['id'] ) ),
		);

		$templates_content = get_option( self::OPTION_TPL_CONTENT, array() );
		$templates_meta    = get_option( self::OPTION_TPL_META, array() );

		$templates_content [] = $template_content;
		$templates_meta []    = $template_meta;

		// make sure these are not autoloaded, as it is a potentially huge array
		add_option( self::OPTION_TPL_CONTENT, null, '', 'no' );

		update_option( self::OPTION_TPL_CONTENT, $templates_content );
		update_option( self::OPTION_TPL_META, $templates_meta );

		return array(
			'message' => __( 'Template saved.', Thrive_Quiz_Builder::T ),
			'list'    => $this->api_get_saved( true ),
		);
	}

	/**
	 * get user-saved templates
	 *
	 * @param bool $return whether or not to return the $html or output it and exit
	 *
	 * @return string
	 */
	public function api_get_saved( $return = false ) {
		$only_current_template = (int) $this->param( 'current_template' );
		$html                  = '';
		/**
		 * prepare for multiple templates applying to the same variation type
		 */
		if ( ! empty( $this->variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) ) {
			$types = array( self::type( $this->variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) );

			$templates = get_option( self::OPTION_TPL_META );
			$templates = empty( $templates ) ? array() : array_reverse( $templates, true ); // order by date DESC

			$input   = '<input type="hidden" class="lp_code" value="user-saved-template-%s"/>';
			$img     = '<img src="' . tqb()->plugin_url() . 'tcb-bridge/editor-templates/%s/thumbnails/style-' . $this->variation['style'] . '/%s" width="166" height="140"/>';
			$caption = '<span class="tve_cell_caption_holder"><span class="tve_cell_caption">%s</span></span><span class="tve_cell_check tve_icm tve-ic-checkmark"></span>';
			$item    = '<span class="tve_grid_cell tve_landing_page_template tve_click" title="Choose %s">%s</span>';

			foreach ( $templates as $index => $template ) {
				/* make sure we only load the same type, e.g. tqb_splash */
				if ( ! in_array( $template['type'], $types ) ) {
					continue;
				}

				if ( ! empty( $only_current_template ) && $this->variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] != $template[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) {
					continue;
				}

				$_content = sprintf( $input, $index ) .
				            sprintf( $img, $template['type'], $template['key'] . '.png' ) .
				            sprintf( $caption, $template['name'] . ' (' . strftime( '%d.%m.%y', strtotime( $template['date'] ) ) . ')' );
				$html .= sprintf( $item, $template['name'], $_content );
			}
		}

		$html = $html ? $html : __( 'No saved templates found', Thrive_Quiz_Builder::T );

		if ( $return ) {
			return $html;
		}
		echo $html;
		exit();
	}

	/**
	 * Deletes a template from the database.
	 *
	 * @return string
	 */
	public function api_delete() {
		$tpl_index = (int) str_replace( 'user-saved-template-', '', $this->param( 'tpl' ) );

		$contents = get_option( self::OPTION_TPL_CONTENT );
		$meta     = get_option( self::OPTION_TPL_META );

		if ( ! isset( $contents[ $tpl_index ] ) || ! isset( $meta[ $tpl_index ] ) ) {
			return $this->api_get_saved();
		}

		array_splice( $contents, $tpl_index, 1 );
		array_splice( $meta, $tpl_index, 1 );

		update_option( self::OPTION_TPL_CONTENT, array_values( $contents ) );
		update_option( self::OPTION_TPL_META, array_values( $meta ) );

		return $this->api_get_saved();
	}
}
