<?php

/**
 * Main backend controller for Types.
 *
 * @since 2.0
 */
final class Types_Admin {
	/**
	 * Initialize Types for backend.
	 *
	 * This is expected to be called during init.
	 *
	 * @since 2.0
	 */
	public static function initialize() {
		new self();
	}


	private function __construct() {
		

		$this->on_init();
	}


	private function __clone() { }


	private function on_init() {

		Types_Upgrade::initialize();

		// Load menu - won't be loaded in embedded version.
		if( apply_filters( 'types_register_pages', true ) ) {
			Types_Admin_Menu::initialize();
		}

		$dic = toolset_dic();

		/** @var \OTGS\Toolset\Types\TypeRegistration\Controller $type_registration_controller */
		/** @noinspection PhpUnhandledExceptionInspection */
		$type_registration_controller = $dic->make( '\OTGS\Toolset\Types\TypeRegistration\Controller' );
		$type_registration_controller->initialize();

		$this->init_page_extensions();

		$this->register_types_style();
	}


	/**
	 * Add hooks for loading page extensions.
	 *
	 * @since 2.1
	 */
	private function init_page_extensions() {

		$load_add_or_edit_post_extension = function() {
			$dic = toolset_dic();
			/** @var \OTGS\Toolset\Types\Page\Extension\AddOrEditPost $add_or_edit_extension */
			$add_or_edit_extension = $dic->make( '\OTGS\Toolset\Types\Page\Extension\AddOrEditPost' );
			$add_or_edit_extension->initialize();
		};

		// extensions for post edit page
		add_action( 'load-post.php', function() use( $load_add_or_edit_post_extension ){
			$dic = toolset_dic();
			/** @var Types_Page_Extension_Edit_Post $edit_post_extension */
			$edit_post_extension = $dic->make( '\Types_Page_Extension_Edit_Post' );
			$edit_post_extension->initialize();

			$load_add_or_edit_post_extension();
		} );

		add_action( 'load-post-new.php', $load_add_or_edit_post_extension );

		// extension for post type edit page
		add_action( 'load-toolset_page_wpcf-edit-type', array( 'Types_Page_Extension_Edit_Post_Type', 'get_instance' ) );

		// extension for post fields edit page
		add_action( 'load-toolset_page_wpcf-edit', array( 'Types_Page_Extension_Edit_Post_Fields', 'get_instance' ) );

		// settings
		add_action( 'load-toolset_page_toolset-settings', array( $this, 'init_settings' ) );

		if( apply_filters( 'toolset_is_m2m_enabled', false ) ) {

			// Related posts in edit pages.
			add_action( 'add_meta_boxes', array( 'Types_Page_Extension_Meta_Box_Related_Content', 'initialize' ) );

		}

		// extension for cpt edit page
		add_action( 'load-toolset_page_wpcf-edit-type', function() {
			Toolset_Singleton_Factory::get( 'Types_Admin_Notices_Custom_Fields_For_New_Cpt' );
		} );
	}


	/**
	 * Initialize the extension for the Toolset Settings page.
	 *
	 * @since 2.1
	 */
	public function init_settings() {
		$settings = new Types_Page_Extension_Settings();
		$settings->build();
	}


	/**
	 * Registers Types style
	 * The goal for the future is to only have this Types css file.
	 */
	private function register_types_style(){
		wp_register_style(
			'toolset-types',
			TYPES_RELPATH . '/public/css/types.css',
			array(),
			TYPES_VERSION
		);
	}
}
