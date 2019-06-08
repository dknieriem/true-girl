<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * Initialize the Auryn dependency injector and offer it through a toolset_dic filter and functions.
 *
 * @since 3.0.6
 */

namespace {

	/**
	 * @return \OTGS\Toolset\Common\Auryn\Injector
	 */
	function toolset_dic() {
		static $dic;

		if ( null === $dic ) {
			/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
			$dic = new \OTGS\Toolset\Common\Auryn\Injector();
		}

		return $dic;
	}

	/** @noinspection PhpDocMissingThrowsInspection */
	/**
	 * @param $class_name
	 * @param array $args
	 *
	 * @return mixed
	 * @deprecated See https://github.com/rdlowrey/auryn#example-use-cases
	 */
	function toolset_dic_make( $class_name, $args = array() ) {
		/** @noinspection PhpUnhandledExceptionInspection */
		return toolset_dic()->make( $class_name, $args );
	}


	add_filter( 'toolset_dic', function ( /** @noinspection PhpUnusedParameterInspection */ $ignored ) {
		return toolset_dic();
	} );

}


/**
 * Initialize the DIC for usage of Toolset Common classes.
 */
namespace OTGS\Toolset\Common\DicSetup {

	use OTGS\Toolset\Common\GuiBase\DialogBoxFactory;
	use OTGS\Toolset\Common\Utils\RequestMode;

	/** @var \OTGS\Toolset\Common\Auryn\Injector $dic */
	$dic = apply_filters( 'toolset_dic', null );

	// To expose existing singleton classes, use delegate callbacks. These callbacks will
	// be invoked only when the instance is actually needed, thus save performance.
	// Only after a delegate is used, we'll use the $injector->share() method to
	// provide the singleton instance directly and to improve performance a bit further.
	$singleton_delegates = array(
		'\Toolset_Ajax' => function() {
			return \Toolset_Ajax::get_instance();
		},
		'\Toolset_Assets_Manager' => function() {
			return \Toolset_Assets_Manager::get_instance();
		},
		'\Toolset_Output_Template_Repository' => function() {
			return \Toolset_Output_Template_Repository::get_instance();
		},
		'\Toolset_Post_Type_Repository' => function() {
			return \Toolset_Post_Type_Repository::get_instance();
		},
		'\Toolset_Relationship_Definition_Repository' => function() {
			do_action( 'toolset_do_m2m_full_init' );
			return \Toolset_Relationship_Definition_Repository::get_instance();
		},
		'\Toolset_Relationship_Migration_Controller' => function() {
			$relationship_controller = \Toolset_Relationship_Controller::get_instance();
			$relationship_controller->initialize_full();
			$relationship_controller->force_autoloader_initialization();
			return new \Toolset_Relationship_Migration_Controller();
		},
		'\Toolset_Renderer' => function() {
			return \Toolset_Renderer::get_instance();
		},
		'\Toolset_Constants' => function() {
			return new \Toolset_Constants();
		},
		'\Toolset_WPML_Compatibility' => function() {
			return \Toolset_WPML_Compatibility::get_instance();
		},
		'\Toolset_Field_Group_Post_Factory' => function() {
			return \Toolset_Field_Group_Post_Factory::get_instance();
		},
		'\OTGS\Toolset\Common\GuiBase\DialogBoxFactory' => function() {
			\Toolset_Common_Bootstrap::get_instance()->register_gui_base();
			return new DialogBoxFactory( \Toolset_Gui_Base::get_instance() );
		},
		'\wpdb' => function() {
			global $wpdb;
			return $wpdb;
		},
		'\Toolset_Field_Definition_Factory_Post' => function() {
			return \Toolset_Field_Definition_Factory_Post::get_instance();
		},
		'\Toolset_Field_Definition_Factory_User' => function() {
			return \Toolset_Field_Definition_Factory_User::get_instance();
		},
		'\Toolset_Field_Definition_Factory_Term' => function() {
			return \Toolset_Field_Definition_Factory_Term::get_instance();
		},
		'\Toolset_Condition_Plugin_Views_Active' => function() {
			return new \Toolset_Condition_Plugin_Views_Active();
		},
		'\Toolset_Condition_Plugin_Layouts_Active' => function() {
			return new \Toolset_Condition_Plugin_Layouts_Active();
		},
		'\Toolset_Common_Bootstrap' => function() {
			return \Toolset_Common_Bootstrap::get_instance();
		},
		'\WPCF_Roles' => function() {
			return \WPCF_Roles::getInstance();
		},
		'\WP_Views_plugin' => function() {
			global $WP_Views;
			return $WP_Views;
		},
	);

	foreach( $singleton_delegates as $class_name => $callback ) {
		/** @noinspection PhpUnhandledExceptionInspection */
		$dic->delegate( $class_name, function() use( $callback, $dic ) {
			$instance = $callback();
			$dic->share( $instance );
			return $instance;
		});
	}

	// Direct instances sharing; Use this *only* for classes that are used in 100% of requests.
	$dic->share( new RequestMode() );
}
