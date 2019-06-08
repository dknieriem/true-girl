<?php

/**
 * Editor class for the Layouts plugin.
 *
 * Handles all the functionality needed to allow the Layouts plugin to work with Content Template editing.
 *
 * @since 3.2.1
 */
class Toolset_User_Editors_Editor_Layouts
	extends Toolset_User_Editors_Editor_Abstract {

	const LAYOUTS_SCREEN_ID = 'layouts';
	const LAYOUTS_BUILDER_OPTION_NAME = '_private_layouts_template_in_use';
	const LAYOUTS_BUILDER_OPTION_VALUE = 'yes';
	const LAYOUTS_BUILDER_PRIVATE_LAYOUT_SETTINGS_OPTION_NAME = '_dd_layouts_settings';

	/**
	 * @var WPDD_Layouts
	 */
	private $layouts;

	/**
	 * @var Toolset_Condition_Plugin_Layouts_Active
	 */
	private $layouts_is_active;

	/**
	 * @var string
	 */
	protected $id = self::LAYOUTS_SCREEN_ID;

	/**
	 * @var string
	 */
	protected $name = 'Layouts';

	/**
	 * @var string
	 */
	protected $option_name = '_toolset_user_editors_layouts_template';

	/**
	 * @var string
	 */
	protected $logo_class = 'icon-layouts-logo';

	public function set_layouts_is_active( \Toolset_Condition_Plugin_Layouts_Active $is_layouts_active ) {
		$this->layouts_is_active = $is_layouts_active;
	}

	public function set_layouts( \WPDD_Layouts $layouts ) {
		$this->layouts = $layouts;
	}

	public function initialize() {
		$this->layouts_is_active = new Toolset_Condition_Plugin_Layouts_Active();
		$this->layouts = $this->layouts_is_active->is_met() ? WPDD_Layouts::getInstance() : null;

		add_action( 'toolset_update_layouts_builder_post_meta', array( $this, 'update_layouts_builder_post_meta' ), 10, 2 );

		if (
			isset( $this->medium )
			&& $this->medium->get_id()
		) {
			// Layouts meta updating filter is initialized on "init", so we need to move the "update_layouts_builder_post_meta"
			// call on "init" and not before that. The current call happens on "before_init".
			// This can be safely removed once Layouts will be initialized on "after_theme_setup".
			add_action( 'init', array( $this, 'update_layouts_builder_post_meta_on_init' ) );
		}
	}

	public function update_layouts_builder_post_meta_on_init() {
		$this->update_layouts_builder_post_meta( $this->medium->get_id(), 'ct_editor_choice' );
	}

	public function update_layouts_builder_post_meta( $post_id, $key ) {
		if ( ! array_key_exists( $key, $_REQUEST ) ) {
			return;
		}

		if ( $this->get_id() !== sanitize_text_field( $_REQUEST[ $key ] ) ) {
			delete_post_meta( $post_id, self::LAYOUTS_BUILDER_OPTION_NAME );
			delete_post_meta( $post_id, self::LAYOUTS_BUILDER_PRIVATE_LAYOUT_SETTINGS_OPTION_NAME );
			return;
		}

		update_post_meta( $post_id, self::LAYOUTS_BUILDER_OPTION_NAME, sanitize_text_field( self::LAYOUTS_BUILDER_OPTION_VALUE ) );

		$layout_type = 'fluid';
		$layouts = $this->layouts;
		$default_private_layout_setting = call_user_func_array( array( $layouts, 'load_layout'), array( $this->constants->constant( 'WPDDL_PRIVATE_EMPTY_PRESET' ), $layout_type ) );

		// Get the current Content Template content and if the content exists and is not empty, create a new visual
		// editor cell and place the existing Content Template content there.
		$post = get_post( $post_id );

		/* translators: Prefix for the Content Template name that is built using Layouts. */
		$default_private_layout_setting['name'] = __( 'Layout for', 'wpv-views' ) . ' ' . $post->post_title;
		$default_private_layout_setting['type'] = $layout_type;
		$default_private_layout_setting['layout_type']  = 'private';
		$default_private_layout_setting['owner_kind']  = 'view_template';

		if (
			property_exists( $post, 'post_content' ) &&
			'' !== $post->post_content &&
			is_callable(
				array(
					'WPDD_Utils',
					'create_cell',
				)
			)
		) {
			$default_private_layout_setting['Rows'][0] = array(
				'kind' => 'Row',
				'Cells' => array(
					WPDD_Utils::create_cell(
						'Post Content Cell',
						1,
						'cell-text', array(
							'content' => array(
								'content' => sanitize_textarea_field( $post->post_content ),
							),
							'width'   => 12,
						)
					),
				),
				'cssClass' => 'row-fluid',
				'name' => 'Post content row',
				'additionalCssClasses' => '',
				'row_divider' => 1,
				'layout_type' => 'fluid',
				'mode' => 'full-width',
				'cssId' => '',
				'tag' => 'div',
				'width' => 1,
				'editorVisualTemplateID' => '',
			);
		}
		/**
		 * Handles the saving of the Content Layout.
		 *
		 * The return value from this filter is not assigned to a variable because it won't be used at all until the end
		 * of the call. This filter is only called to just save the Layout settings.
		 *
		 * @param int   $post_id
		 * @param array $default_private_layout_setting
		 *
		 * @return int
		 *
		 * @since 3.2.3
		 */
		apply_filters( 'ddl-save_layout_settings', $post_id, $default_private_layout_setting );
	}

	public function required_plugin_active() {
		if ( ! apply_filters( 'toolset_is_views_available', false ) ) {
			return false;
		}

		if ( $this->layouts_is_active->is_met() ) {
			/* translators: The name of the editor that edits Content Templates using Layouts. */
			$this->name = __( 'Layouts', 'wpv-views' );
			return true;
		}

		return false;
	}

	public function run() {}
}
