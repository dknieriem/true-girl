<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/12/2016
 * Time: 2:33 PM
 */

/**
 * Displays the Thrive Ovation link to admin menu
 *
 * @param array $menus
 *
 * @return array
 */
function tvo_admin_menu( $menus = array() ) {

	$menus['tvo'] = array(
		'parent_slug' => 'tve_dash_section',
		'page_title'  => __( 'Thrive Ovation', TVO_TRANSLATE_DOMAIN ),
		'menu_title'  => __( 'Thrive Ovation', TVO_TRANSLATE_DOMAIN ),
		'capability'  => 'manage_options',
		'menu_slug'   => 'tvo_admin_dashboard',
		'function'    => 'tvo_admin_dashboard',
	);

	return $menus;
}

/**
 * Display Thrive Ovation Dashboard - the main plugin page
 */
function tvo_admin_dashboard() {

	if ( ! tvo_check_license() ) {
		return tvo_license_warning();
	}

	include dirname( __FILE__ ) . '/views/dashboard.php';
}

/**
 * Enqueue admin scripts and styles
 *
 * @param $hook string
 *
 * @return mixed
 *
 */
function tvo_admin_enqueue_scripts( $hook ) {

	/* first, the license check */
	if ( ! tvo_check_license() ) {
		return;
	}

	if ( $hook == 'edit-comments.php' ) {
		tvo_enqueue_style( 'tvo-admin-styles', TVO_ADMIN_URL . '/css/comments-styles.css' );
		tvo_enqueue_script( 'tvo-comments', TVO_ADMIN_URL . '/js/comments.js' );
		tvo_enqueue_script( 'tvo-velocity-functions', TVO_ADMIN_URL . '/js/velocity_functions.js' );
		tvo_enqueue_script( 'tvo-admin-tooltip', TVO_ADMIN_URL . '/js/libs/tooltip.min.js' );
		tvo_enqueue_script( 'tvo-admin-velocity', TVO_ADMIN_URL . '/js/libs/velocity.min.js' );

		tvo_enqueue_style( 'tvo-modal-style', TVE_DASH_URL . '/css/modal.css' );
		tvo_enqueue_script( 'tvo-modal-script', TVO_ADMIN_URL . '/js/libs/leanmodal.min.js' );

		tvo_enqueue_style( 'tvo-preloader', TVE_DASH_URL . '/css/preloader.css' );

		wp_localize_script( 'tvo-comments', 'ThriveOvation', tvo_get_localization_parameters() ); // To be visible in comments.js
	}

	$accepted_hooks = apply_filters( 'tvo_accepted_admin_pages', array(
		'admin_page_tvo_admin_dashboard',
		'thrive-dashboard_page_tvo_admin_dashboard',
	) );

	if ( ! in_array( $hook, $accepted_hooks ) ) {
		return;
	}

	wp_enqueue_media();
	tve_dash_enqueue();

	tvo_enqueue_style( 'admin-styles', tvo_plugin_url( 'admin/css/styles.css' ) );
	tve_dash_enqueue_style( 'tve-dash-styles-css', TVE_DASH_URL . '/css/styles.css' );


	tvo_enqueue_script( 'tvo-admin-js', tvo_plugin_url( '/admin/js/admin.min.js' ), array(
		'jquery',
		'backbone',
		'tve-dash-main-js',
	), false, true );

	tvo_enqueue_script( 'tvo-admin-global', tvo_plugin_url( '/admin/js/global.js' ), array( 'jquery' ), false, true );
	wp_enqueue_script( 'editor' );
	wp_enqueue_script( 'editor-functions' );
	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'tiny_mce' );

	wp_localize_script( 'tvo-admin-js', 'ThriveOvation', tvo_get_localization_parameters() );

	add_action( 'admin_print_footer_scripts', 'tvo_backbone_templates' );
}

/**
 * get the javascript localization parameters
 *
 * @return array
 */
function tvo_get_localization_parameters() {

	$facebook = new Thrive_Dash_List_Connection_Facebook();
	$twitter  = new Thrive_Dash_List_Connection_Twitter();

	return array(
		'translations'                  => require TVO_ADMIN_PATH . 'i18n.php',
		'nonce'                         => wp_create_nonce( 'wp_rest' ),
		'routes'                        => array(
			'settings'     => tvo_get_route_url( 'settings' ),
			'testimonials' => tvo_get_route_url( 'testimonials' ),
			'tags'         => tvo_get_route_url( 'tags' ),
			'socialmedia'  => tvo_get_route_url( 'socialmedia' ),
			'comments'     => tvo_get_route_url( 'comments' ),
			'shortcodes'   => tvo_get_route_url( 'shortcodes' ),
			'postmeta'     => tvo_get_route_url( 'postmeta' ),
			'filters'      => tvo_get_route_url( 'filters' ),
		),
		'social_connections'            => array(
			'facebook' => $facebook->isConnected(),
			'twitter'  => $twitter->isConnected(),
		),
		'testimonial_image_placeholder' => tvo_get_default_image_placeholder(),
		'breadcrumbs'                   => tvo_get_default_breadcrumbs(),
		'const'                         => array(
			'toast_timeout'           => TVO_TOAST_TIMEOUT,
			'wp_content'              => rtrim( WP_CONTENT_URL, '/' ) . '/',
			'content_character_limit' => TVO_TESTIMONIAL_CONTENT_SUMMARY_LIMIT,
			'status'                  => array(
				'ready_for_display' => TVO_STATUS_READY_FOR_DISPLAY,
				'awaiting_approval' => TVO_STATUS_AWAITING_APPROVAL,
				'awaiting_review'   => TVO_STATUS_AWAITING_REVIEW,
				'rejected'          => TVO_STATUS_REJECTED,
			),
			'meta_key'                => array(
				'status' => TVO_STATUS_META_KEY,
			),
			'source'                  => array(
				'comments'       => TVO_SOURCE_COMMENTS,
				'social_media'   => TVO_SOURCE_SOCIAL_MEDIA,
				'direct_capture' => TVO_SOURCE_DIRECT_CAPTURE,
				'plugin'         => TVO_SOURCE_PLUGIN,
				'copy'           => TVO_SOURCE_COPY,
			),
		),
		'admin_url'                     => TVO_ADMIN_URL,
		'availableTags'                 => tvo_get_all_tags(),
		'apiConnections'                => tvo_dashboard_get_connections(),
		'apis'                          => tvo_dashboard_get_all_connections(),
	);
}

/**
 * Render backbone templates
 */
function tvo_backbone_templates() {
	$templates = tve_dash_get_backbone_templates( plugin_dir_path( __FILE__ ) . 'views/template', 'template' );
	tve_dash_output_backbone_templates( $templates );
}

/**
 * Load connected mail delivery apps
 *
 * @return array
 */
function tvo_dashboard_get_connections() {
	$connected_apis  = Thrive_List_Manager::getAvailableAPIsByType( true, array( 'email' ) );
	$structured_apis = array();
	foreach ( $connected_apis as $k => $v ) {
		$structured_apis[] = array(
			'connection'          => $k,
			'active'              => get_option( 'tvo_api_delivery_service' ),
			'connection_instance' => Thrive_List_Manager::credentials( $k ),
		);
	}

	return $structured_apis;
}

/**
 * All mail delivery connections
 */
function tvo_dashboard_get_all_connections() {
	$all_apis = Thrive_List_Manager::getAvailableAPIsByType( false, array( 'email' ) );
	$apis     = array();
	foreach ( $all_apis as $k => $api ) {
		/** @var Thrive_Dash_List_Connection_Abstract $api */
		$credentials = Thrive_List_Manager::credentials( $k );

		$apis[] = array(
			'connection'          => $k,
			'title'               => $api->getTitle(),
			'connected'           => $api->isConnected(),
			'connection_instance' => $credentials
		);
	}

	return $apis;
}

/**
 * Load all mail delivery apps
 *
 * @return array
 */
function tvo_dashboard_get_available_apis() {
	$connected_apis  = Thrive_List_Manager::getAvailableAPIsByType( false, array( 'email' ) );
	$structured_apis = array();
	foreach ( $connected_apis as $k => $v ) {
		$structured_apis[] = array(
			'connection'          => $k,
			'active'              => get_option( 'tvo_api_delivery_service' ),
			'connection_instance' => Thrive_List_Manager::credentials( $k ),
		);
	}

	return $structured_apis;
}

/**
 * Builds the testimonial iframe, includes the css and javascript files and display it on screen
 */
function tvo_display_testimonial_iframe() {
	set_current_screen( 'edit-comments' );
	tvo_admin_enqueue_scripts( 'admin_page_tvo_admin_dashboard' );
	tvo_enqueue_script( 'tvo-comments-modal', TVO_ADMIN_URL . '/js/modal_comment_edit.js' );
	wp_localize_script( 'tvo-comments-modal', 'ThriveOvation', tvo_get_localization_parameters() );

	iframe_header();

	if ( ! empty( $_GET['comment_id'] ) && is_numeric( $_GET['comment_id'] ) ) {
		$comment  = get_comment( $_GET['comment_id'], OBJECT );
		$settings = tvo_get_settings();
		if ( tvo_validate_gravatar( $comment->comment_author_email ) ) {
			$comment->comment_author_picture_url = get_avatar_url( $comment->comment_author_email, array( 'size' => 200 ) );
		} else {
			$comment->comment_author_picture_url = tvo_get_default_image_placeholder();
		}
		$delivery_service = get_option( 'tvo_api_delivery_service', false );

		$ask_permission_email_response = tvo_get_ask_permission_email_response( $delivery_service, array(
			'name'    => $comment->comment_author,
			'content' => $comment->comment_content,
		) );

		include TVO_ADMIN_PATH . 'views/comments/modal-comment-edit.php';
	}

	iframe_footer();
	exit();
}

/**
 *  Displays an error notice at the top of the screen
 */
function tvo_notice() {
	?>
	<div id="tvo_notice" class="is-dismissible notice hidden">
		<p class="tvo_notice_text"></p>
	</div>
	<?php
}
