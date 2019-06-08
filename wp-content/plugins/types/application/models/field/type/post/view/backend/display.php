<?php

/**
 * Class Types_Field_Type_Post_View_Backend_Creation
 *
 * @since 2.3
 */
class Types_Field_Type_Post_View_Backend_Display {
	const SELECT2_POSTS_PER_LOAD = 10;

	/**
	 * @var Types_Field_Type_Post
	 */
	private $field;

	/**
	 * Types_Field_Type_Post_View_Backend_Display constructor.
	 *
	 * @param Types_Field_Type_Post $field
	 */
	public function __construct( Types_Field_Type_Post $field ){
		$this->field = $field;
	}

	/**
	 *
	 */
	public function prepare() {
		add_action( 'admin_enqueue_scripts', array( $this, 'on_admin_enqueue_scripts' ) );
		add_action( 'admin_print_scripts', array( $this, 'print_js_data' ) );

		// Fix for GUTENBERG, which has already triggered 'admin_enqueue_scripts' add this point
		// It's a known issue see: https://github.com/WordPress/gutenberg/issues/4929
		if( did_action( 'admin_enqueue_scripts' ) ) {
			$this->on_admin_enqueue_scripts();
		}

		// also admin_print_scripts is already triggered by GUTENBERG out of order
		if( did_action( 'admin_print_scripts' ) ) {
			add_action( 'admin_footer', array( $this, 'print_js_data' ) );
		}
	}

	/**
	 * Scritps and Styles
	 */
	public function on_admin_enqueue_scripts() {
		if ( function_exists( 'wpcf_edit_post_screen_scripts' ) ) {
			wpcf_edit_post_screen_scripts();
		}

		WPToolset_Field_File::file_enqueue_scripts();

		$main_handle = 'types-post-reference-field';

		/*
		wp_enqueue_style(
			$main_handle,
			TYPES_RELPATH . '/public/page/edit_post/rfg.css',
			array(),
			TYPES_VERSION
		);
		*/

		wp_enqueue_script(
			$main_handle,
			TYPES_RELPATH . '/public/page/edit_post/post-reference-field.js',
			array(
				'jquery',
				'underscore',
				Types_Asset_Manager::SCRIPT_KNOCKOUT,
				Types_Asset_Manager::SCRIPT_UTILS,
				Types_Asset_Manager::SCRIPT_POINTER
			),
			TYPES_VERSION
		);
	}

	/**
	 * Print JS
	 */
	public function print_js_data() {
		echo '<script id="types_post_reference_model_data" type="text/plain">' . base64_encode( wp_json_encode( $this->build_js_data() ) ) . '</script>';
	}

	/**
	 * Build data to be passed to JavaScript.
	 *
	 * @return array
	 */
	private function build_js_data() {

		$types_settings_action = Types_Ajax::get_instance()->get_action_js_name( Types_Ajax::CALLBACK_POST_REFERENCE_FIELD );

		$post_id = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : false;

		if( ! $post_id ) {
			// this happens on new post, but the post id is already resevered and stored in global $post_ID
			global $post_ID;
			$post_id = $post_ID ?: 0;
		}

		return array(
			'post_id' => $post_id,
			'action'  => array(
				'name'  => $types_settings_action,
				'nonce' => wp_create_nonce( $types_settings_action ),
			),
			'select2' => array(
				'posts_per_load' => self::SELECT2_POSTS_PER_LOAD
			)
		);
	}

	/**
	 * This renders the container for the repeatable group.
	 *
	 * The items of the repeatable group will be loaded via ajax, this way we not slowing down
	 * the initial load of the post edit screen.
	 *
	 * @param Types_Field_Type_Post $field
	 *
	 * @param string $additional_css_classes - used only for legacy conditions
	 *
	 * @return string
	 */
	public function render( Types_Field_Type_Post $field, $additional_css_classes = '' ) {
		ob_start();
		$is_wpml_active = Toolset_WPML_Compatibility::get_instance()->is_wpml_active_and_configured();
		$is_default_language = Toolset_WPML_Compatibility::get_instance()->is_current_language_default();
		$disabled = $is_wpml_active && ! $is_default_language && ! $this->has_default_language_translation();
		include( TYPES_ABSPATH . '/application/views/field/post-reference/container.phtml' );
		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}


	/**
	 * Returns if the post is translated in the default language
	 *
	 * @param int $post_id Post ID.
	 * @return boolean
	 * @since m2m
	 */
	private function has_default_language_translation() {
		$post_id = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : 0;
		if ( ! $post_id ) {
			return false;
		}
		return Toolset_Wpml_Utils::has_default_language_translation( $post_id );
	}
}
