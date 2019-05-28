<?php
/**
 * Initializes Avada basic components.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      3.8
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Initializes Avada basic components.
 */
class Avada_Init {

	/**
	 * Constructor.
	 *
	 * @access  public
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'load_textdomain' ) );
		add_action( 'after_setup_theme', array( $this, 'set_builder_status' ), 10 );
		add_action( 'after_setup_theme', array( $this, 'add_theme_supports' ), 10 );
		add_action( 'after_setup_theme', array( $this, 'register_nav_menus' ) );
		add_action( 'after_setup_theme', array( $this, 'add_image_size' ) );
		add_action( 'after_setup_theme', array( $this, 'init_fb_demos_importer' ), 20 );
		add_action( 'wp_ajax_fusion_builder_load_demo', array( $this, 'init_fb_demos_importer' ), 20 );
		add_filter( 'image_size_names_choose', array( $this, 'add_image_sizes_to_media_library_dialog' ) );

		if ( class_exists( 'BuddyPress' ) && ! Avada_Helper::is_buddypress() ) {
			add_action( 'init', array( $this, 'remove_buddypress_redirection' ), 5 );
		}

		if ( class_exists( 'Convert_Plug' ) ) {
			add_action( 'init', array( $this, 'remove_convert_plus_notices' ) );
		}

		if ( class_exists( 'GF_User_Registration_Bootstrap' ) ) {
			add_action( 'init', array( $this, 'change_gravity_user_registration_priority' ) );
		}

		// Init FPO for Event Espresso plugin.
		if ( class_exists( 'EE_Calendar' ) ) {
			add_filter( 'fusion_page_options_init', array( $this, 'init_fusion_page_option_for_event_espresso' ) );
		}

		add_action( 'widgets_init', array( $this, 'widget_init' ) );

		// Allow shortcodes in widget text.
		add_filter( 'widget_text', 'do_shortcode' );

		add_filter( 'wp_nav_menu_args', array( $this, 'main_menu_args' ), 5 );
		add_action( 'after_switch_theme', array( $this, 'theme_activation' ) );
		add_action( 'switch_theme', array( $this, 'theme_deactivation' ) );

		// Term meta migration for WordPress 4.4.
		add_action( 'avada_before_main_content', array( $this, 'youtube_flash_fix' ) );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

		// Remove post_format from preview link.
		add_filter( 'preview_post_link', array( $this, 'remove_post_format_from_link' ), 9999 );

		add_filter( 'wp_tag_cloud', array( $this, 'remove_font_size_from_tagcloud' ) );

		// Add contact methods for author page.
		add_filter( 'user_contactmethods', array( $this, 'modify_contact_methods' ) );

		add_filter( 'wpcf7_form_response_output', array( $this, 'modify_wpcf7_notices' ), 10, 4 );

		if ( ! is_admin() ) {
			add_filter( 'pre_get_posts', array( $this, 'modify_search_filter' ) );
			add_filter( 'pre_get_posts', array( $this, 'empty_search_filter' ) );
			add_filter( 'posts_search', array( $this, 'limit_search_to_title_only' ), 500, 2 );
		}

		// Check if we've got a task to remove backup data.
		if ( false !== get_option( 'scheduled_avada_fusionbuilder_migration_cleanups', false ) ) {
			add_action( 'init', array( 'Fusion_Builder_Migrate', 'cleanup_backups' ) );
		}

		add_action( 'wp_footer', array( $this, 'add_wp_footer_scripts' ), 9999 );

		// Live Search.
		add_action( 'wp_ajax_live_search_retrieve_posts', array( $this, 'live_search_retrieve_posts' ) );
		add_action( 'wp_ajax_nopriv_live_search_retrieve_posts', array( $this, 'live_search_retrieve_posts' ) );
	}

	/**
	 * Load the theme textdomain.
	 *
	 * @access  public
	 */
	public function load_textdomain() {

		// Path: wp-content/theme/languages/en_US.mo.
		// Path: wp-content/languages/themes/Avada-en_US.mo.
		$loaded = load_theme_textdomain( 'Avada', Avada::$template_dir_path . '/languages' );

		// Path: wp-content/theme/languages/Avada-en_US.mo.
		if ( ! $loaded ) {
			add_filter( 'theme_locale', array( $this, 'change_locale' ), 10, 2 );
			$loaded = load_theme_textdomain( 'Avada', Avada::$template_dir_path . '/languages' );

			// Path: wp-content/theme/languages/avada-en_US.mo.
			// Path: wp-content/languages/themes/avada-en_US.mo.
			if ( ! $loaded ) {
				remove_filter( 'theme_locale', array( $this, 'change_locale' ) );
				add_filter( 'theme_locale', array( $this, 'change_locale_lowercase' ), 10, 2 );
				$loaded = load_theme_textdomain( 'Avada', Avada::$template_dir_path . '/languages' );

				// Path: wp-content/languages/Avada-en_US.mo.
				if ( ! $loaded ) {
					remove_filter( 'theme_locale', array( $this, 'change_locale_lowercase' ) );
					add_filter( 'theme_locale', array( $this, 'change_locale' ), 10, 2 );
					$loaded = load_theme_textdomain( 'Avada', dirname( dirname( Avada::$template_dir_path ) ) . '/languages' );

					// Path: wp-content/languages/themes/avada/en_US.mo.
					if ( ! $loaded ) {
						remove_filter( 'theme_locale', array( $this, 'change_locale' ) );
						load_theme_textdomain( 'Avada', dirname( dirname( Avada::$template_dir_path ) ) . '/languages/themes/avada' );
					}
				}
			}
		}
	}

	/**
	 * Formats the locale.
	 *
	 * @access  public
	 * @param  string $locale The language locale.
	 * @param  string $domain The textdomain.
	 * @return  string
	 */
	public function change_locale( $locale, $domain ) {
		return $domain . '-' . $locale;
	}

	/**
	 * Formats the locale using lowercase characters.
	 *
	 * @access  public
	 * @param  string $locale The language locale.
	 * @param  string $domain The textdomain.
	 * @return  string
	 */
	public function change_locale_lowercase( $locale, $domain ) {
		return strtolower( $domain ) . '-' . $locale;
	}

	/**
	 * Conditionally add theme_support for fusion_builder.
	 *
	 * @access  public
	 */
	public function set_builder_status() {
		$builder_settings = get_option( 'fusion_builder_settings' );

		if ( isset( $builder_settings['enable_builder_ui'] ) && $builder_settings['enable_builder_ui'] ) {
			add_theme_support( 'fusion_builder' );
		}
	}

	/**
	 * Conditionally init Fusion_Builder_Demos_Importer class.
	 *
	 * @since 5.8.2
	 * @access  public
	 */
	public function init_fb_demos_importer() {
		$post_type = false;

		if ( ! Avada_Helper::is_post_admin_screen() || ! current_theme_supports( 'fusion-builder-demos' ) || ! Avada()->registration->is_registered() || ! defined( 'FUSION_BUILDER_PLUGIN_DIR' ) || ( fusion_doing_ajax() && ! isset( $_POST['page_name'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		// Edit screen.
		$post_id = isset( $_GET['post'] ) ? sanitize_text_field( wp_unslash( $_GET['post'] ) ) : '';

		if ( '' !== $post_id ) {
			$post_type = get_post_type( $post_id );
		}

		// New post screen.
		if ( false === $post_type && isset( $_GET['post_type'] ) ) {
			$post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
		}

		// Fusion Builder is enabled for this post type.
		if ( in_array( $post_type, FusionBuilder::allowed_post_types(), true ) || ( fusion_doing_ajax() && isset( $_POST['page_name'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$fusion_builder_demo_importer = new Fusion_Builder_Demos_Importer();
		}
	}

	/**
	 * Add theme_supports.
	 *
	 * @access  public
	 */
	public function add_theme_supports() {

		// Default WP generated title support.
		add_theme_support( 'title-tag' );
		// Default RSS feed links.
		add_theme_support( 'automatic-feed-links' );
		// Default custom header.
		add_theme_support( 'custom-header' );
		// Default custom backgrounds.
		add_theme_support( 'custom-background' );
		// Woocommerce Support.
		add_theme_support( 'woocommerce' );

		add_theme_support( 'wc-product-gallery-slider' );

		if ( '1' === Avada()->settings->get( 'enable_woo_gallery_zoom' ) ) {
			add_theme_support( 'wc-product-gallery-zoom' );
		}

		if ( '1' !== Avada()->settings->get( 'disable_woo_gallery' ) ) {
			add_theme_support( 'wc-product-gallery-lightbox' );
		}

		// Post Formats.
		add_theme_support( 'post-formats', array( 'gallery', 'link', 'image', 'quote', 'video', 'audio', 'chat' ) );
		// Add post thumbnail functionality.
		add_theme_support( 'post-thumbnails' );

		// Add Fusion Builder Demos support.
		add_theme_support( 'fusion-builder-demos' );

	}

	/**
	 * Add image sizes.
	 *
	 * @access  public
	 */
	public function add_image_size() {
		add_image_size( 'blog-large', 669, 272, true );
		add_image_size( 'blog-medium', 320, 202, true );
		add_image_size( 'recent-posts', 700, 441, true );
		add_image_size( 'recent-works-thumbnail', 66, 66, true );

		// Image sizes used for grid layouts.
		add_image_size( '200', 200, '', false );
		add_image_size( '400', 400, '', false );
		add_image_size( '600', 600, '', false );
		add_image_size( '800', 800, '', false );
		add_image_size( '1200', 1200, '', false );
	}

	/**
	 * Add image sizes to WP Media Library Dialog.
	 *
	 * @since 5.3
	 * @access public
	 * @param array $sizes The image sizes already in the WP Meida Library Dialog.
	 * @return array Image sizes for WP Media Library Dialog.
	 */
	public function add_image_sizes_to_media_library_dialog( $sizes ) {
		/* translators: image size. */
		$sizes['200'] = sprintf( esc_attr__( 'Avada Grid %s', 'Avada' ), 200 );
		/* translators: image size. */
		$sizes['400'] = sprintf( esc_attr__( 'Avada Grid %s', 'Avada' ), 400 );
		/* translators: image size. */
		$sizes['600'] = sprintf( esc_attr__( 'Avada Grid %s', 'Avada' ), 600 );
		/* translators: image size. */
		$sizes['800'] = sprintf( esc_attr__( 'Avada Grid %s', 'Avada' ), 800 );
		/* translators: image size. */
		$sizes['1200'] = sprintf( esc_attr__( 'Avada Grid %s', 'Avada' ), 1200 );

		return $sizes;
	}

	/**
	 * Register navigation menus.
	 *
	 * @access  public
	 */
	public function register_nav_menus() {

		register_nav_menu( 'main_navigation', 'Main Navigation' );
		register_nav_menu( 'top_navigation', 'Top Navigation' );
		register_nav_menu( 'mobile_navigation', 'Mobile Navigation' );
		register_nav_menu( '404_pages', '404 Useful Pages' );
		register_nav_menu( 'sticky_navigation', 'Sticky Header Navigation' );

	}

	/**
	 * Theme activation actions.
	 *
	 * @access  public
	 */
	public function theme_activation() {

		update_option(
			'shop_catalog_image_size',
			array(
				'width'  => 500,
				'height' => '',
				0,
			)
		);
		update_option(
			'shop_single_image_size',
			array(
				'width'  => 700,
				'height' => '',
				0,
			)
		);
		update_option(
			'shop_thumbnail_image_size',
			array(
				'width'  => 120,
				'height' => '',
				0,
			)
		);

		update_option( 'woocommerce_single_image_width', 700 );
		update_option( 'woocommerce_thumbnail_image_width', 500 );
		update_option( 'woocommerce_thumbnail_cropping', 'uncropped' );

		// Delete the patcher caches.
		delete_site_transient( 'fusion_patcher_check_num' );
		// Delete compiled JS.
		avada_reset_all_caches();

	}

	/**
	 * Theme activation actions.
	 *
	 * @access  public
	 */
	public function theme_deactivation() {

		// Delete the patcher caches.
		delete_site_transient( 'fusion_patcher_check_num' );
		// Delete compiled JS.
		avada_reset_all_caches();

	}

	/*
	// WIP
	public function migrate_term_data() {
		$version = get_bloginfo( 'version' );
		$function_test = function_exists( 'add_term_meta' );

		if ( version_compare( $version, '4.4', '>=' ) && ! $function_test ) {}
	}
	*/

	/**
	 * Get the main menu arguments.
	 *
	 * @access public
	 * @param  array $args The arguments.
	 * @return  array The arguments modified.
	 */
	public function main_menu_args( $args ) {

		global $post;

		$c_page_id = Avada()->fusion_library->get_page_id();

		if ( get_post_meta( $c_page_id, 'pyre_displayed_menu', true ) &&
			'default' !== get_post_meta( $c_page_id, 'pyre_displayed_menu', true ) &&
			( 'main_navigation' === $args['theme_location'] || 'sticky_navigation' === $args['theme_location'] )
		) {
			$menu         = get_post_meta( $c_page_id, 'pyre_displayed_menu', true );
			$args['menu'] = $menu;
		}

		return $args;

	}

	/**
	 * Inject some HTML to fix a youtube flash bug.
	 *
	 * @access  public
	 */
	public function youtube_flash_fix() {
		echo '<div class="fusion-youtube-flash-fix">&shy;<style type="text/css"> iframe { visibility: hidden; opacity: 0; } </style></div>';
	}

	/**
	 * Register widgets.
	 *
	 * @access  public
	 */
	public function widget_init() {

		register_widget( 'Fusion_Widget_Ad_125_125' );
		register_widget( 'Fusion_Widget_Author' );
		register_widget( 'Fusion_Widget_Contact_Info' );
		register_widget( 'Fusion_Widget_Tabs' );
		register_widget( 'Fusion_Widget_Recent_Works' );
		register_widget( 'Fusion_Widget_Tweets' );
		register_widget( 'Fusion_Widget_Flickr' );
		register_widget( 'Fusion_Widget_Social_Links' );
		register_widget( 'Fusion_Widget_Facebook_Page' );
		register_widget( 'Fusion_Widget_Menu' );
		register_widget( 'Fusion_Widget_Vertical_Menu' );

	}

	/**
	 * Removes the post format from links.
	 *
	 * @access  public
	 * @param  string $url The URL to process.
	 * @return  string The URL with post_format stripped.
	 */
	public function remove_post_format_from_link( $url ) {
		$url = remove_query_arg( 'post_format', $url );
		return $url;
	}

	/**
	 * Removes font-sizes from the tagclouds.
	 *
	 * @param string $tagcloud The markup of tagclouds.
	 * @return string
	 */
	public function remove_font_size_from_tagcloud( $tagcloud ) {
		return preg_replace( '/ style=(["\'])[^\1]*?\1/i', '', $tagcloud, -1 );
	}

	/**
	 * Modifies user contact methods and adds some more social networks.
	 *
	 * @param array $profile_fields The profile fields.
	 * @return array The profile fields with additional contact methods.
	 */
	public function modify_contact_methods( $profile_fields ) {
		// Add new fields.
		$profile_fields['author_email']    = 'Email (Author Page)';
		$profile_fields['author_facebook'] = 'Facebook (Author Page)';
		$profile_fields['author_twitter']  = 'Twitter (Author Page)';
		$profile_fields['author_linkedin'] = 'LinkedIn (Author Page)';
		$profile_fields['author_dribble']  = 'Dribble (Author Page)';
		$profile_fields['author_gplus']    = 'Google+ (Author Page)';
		$profile_fields['author_whatsapp'] = 'WhatsApp (Author Page)';
		$profile_fields['author_custom']   = 'Custom Message (Author Page)';

		return $profile_fields;
	}

	/**
	 * Modifies the HTML for WPCF7 notices.
	 *
	 * @access public
	 * @since 5.5
	 * @param string            $output              The HTML.
	 * @param string            $class               The CSS classes that will be added to the element.
	 * @param string            $content             The notice content.
	 * @param WPCF7_ContactForm $contact_form_object An instance of the WPCF7_ContactForm object.
	 * @return string                                Notice HTML.
	 */
	public function modify_wpcf7_notices( $output, $class, $content, $contact_form_object ) {
		if ( shortcode_exists( 'fusion_alert' ) ) {
			return do_shortcode( '[fusion_alert class="' . $class . '" type="custom"]' . $content . '[/fusion_alert]' );
		}
		return $output;
	}

	/**
	 * Removes the BuddyPress redirection actions.
	 *
	 * @access public
	 */
	public function remove_buddypress_redirection() {
		remove_action( 'bp_init', 'bp_core_wpsignup_redirect' );
	}

	/**
	 * Removes admin notices from Convert Plus plugin.
	 *
	 * @since 5.4.1
	 * @access public
	 * @return void
	 */
	public function remove_convert_plus_notices() {
		if ( ! defined( 'BSF_PRODUCTS_NOTICES' ) ) {
			define( 'BSF_PRODUCTS_NOTICES', false );
		}
	}

	/**
	 * Changes the hook priority of the GF_User_Registration->maybe_activate_user() function.
	 *
	 * @since 5.1
	 * @access public
	 * @return void
	 */
	public function change_gravity_user_registration_priority() {
		remove_action( 'wp', array( gf_user_registration(), 'maybe_activate_user' ) );
		add_action( 'wp', array( gf_user_registration(), 'maybe_activate_user' ), 999 );
	}

	/**
	 * Adds needed argument to FPO loading if clause, so that options will be displayed on Events Espresso pages.
	 *
	 * @since 5.4.2
	 * @access public
	 * @param bool $additional_argument Additional argument.
	 * @return bool
	 */
	public function init_fusion_page_option_for_event_espresso( $additional_argument ) {
		global $pagenow;

		$additional_argument = 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'espresso_events' === $_GET['page'] && isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['post'] );

		return $additional_argument;
	}

	/**
	 * Performs the live search for posts.
	 *
	 * @since 5.9
	 * @access public
	 * @return void.
	 */
	public function live_search_retrieve_posts() {
		$args = apply_filters(
			'fusion_live_search_query_args',
			array(
				's'                   => trim( esc_attr( strip_tags( $_POST['search'] ) ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
				'post_type'           => Avada()->settings->get( 'search_content' ),
				'posts_per_page'      => (int) Avada()->settings->get( 'live_search_results_per_page' ),
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
			)
		);

		if ( Avada()->settings->get( 'search_limit_to_post_titles' ) ) {
			add_filter( 'posts_where', array( $this, 'limit_wp_query_to_title_only' ), 10, 2 );
			$search_results = fusion_cached_query( $args );
			remove_filter( 'posts_where', array( $this, 'limit_wp_query_to_title_only' ) );
		} else {
			$search_results = fusion_cached_query( $args );
		}

		if ( $search_results->have_posts() ) {
			$display_post_type = Avada()->settings->get( 'live_search_display_post_type' );
			$display_featured_image = Avada()->settings->get( 'live_search_display_featured_image' );

			while ( $search_results->have_posts() ) {
				$search_results->the_post();
				global $post;

				$result_suggestions[] = array(
					'id'        => esc_attr( $post->ID ),
					'type'      => $display_post_type ? esc_html( ucwords( get_post_type( $post->ID ) ) ) : '',
					'title'     => get_the_title( $post->ID ),
					'post_url'  => get_the_permalink( $post->ID ),
					'image_url' => $display_featured_image ? get_the_post_thumbnail_url( $post->ID, 'recent-works-thumbnail' ) : '',
				);
			}
		}

		wp_reset_postdata();

		wp_send_json( $result_suggestions );
	}

	/**
	 * Modifies the WP_Query SQL query to limit to post titles.
	 *
	 * @since 5.9
	 * @access public
	 * @param string $where    Search SQL for WHERE clause.
	 * @param object $wp_query The search query.
	 * @return string $where   The modified clause.
	 */
	public function limit_wp_query_to_title_only( $where, $wp_query ) {
		global $wpdb;

		$query_vars = $wp_query->query_vars;
		if ( $title = $wp_query->get( 's' ) ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'' . esc_sql( $wpdb->esc_like( $title ) ) . '%\'';
		}

		return $where;
	}

	/**
	 * Modifies the search SQL query to limit to post titles.
	 *
	 * @since 5.9
	 * @access public
	 * @param string $search   Search SQL for WHERE clause.
	 * @param object $wp_query The search query.
	 * @return string $where The modified clause.
	 */
	public function limit_search_to_title_only( $search, $wp_query ) {
		global $wpdb;

		// If there is no search term, skip process.
		if ( empty( $search ) || ! Avada()->settings->get( 'search_limit_to_post_titles' ) ) {
			return $search;
		}

		$query_vars = $wp_query->query_vars;
		$n          = ! empty( $query_vars['exact'] ) ? '' : '%';
		$search     = '';
		$searchand  = '';

		foreach ( (array) $query_vars['search_terms'] as $term ) {
			$term      = esc_sql( $wpdb->esc_like( $term ) );
			$search   .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
			$searchand = ' AND ';
		}

		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";

			if ( ! is_user_logged_in() ) {
				$search .= " AND ($wpdb->posts.post_password = '') ";
			}
		}

		return $search;
	}

	/**
	 * Modifies the search filter.
	 *
	 * @access public
	 * @param object $query The search query.
	 * @return object $query The modified search query.
	 */
	public function modify_search_filter( $query ) {
		if ( is_search() && $query->is_search ) {
			if ( isset( $_GET ) && ( 2 < count( $_GET ) || ( 2 == count( $_GET ) && ! isset( $_GET['lang'] ) ) ) ) {
				return $query;
			}

			$query->set( 'post_type', Avada()->settings->get( 'search_content' ) );
		}
		return $query;
	}

	/**
	 * Make WordPress respect the search template on an empty search.
	 *
	 * @param  object $query The WP_Query object.
	 * @return  object
	 */
	public function empty_search_filter( $query ) {

		if ( isset( $_GET['s'] ) && empty( $_GET['s'] ) && $query->is_main_query() ) {
			$query->is_search = true;
			$query->is_home   = false;
		}

		return $query;

	}

	/**
	 * Add scripts to the wp_footer action hook.
	 *
	 * @since 5.3.1
	 * @access public
	 * @return void.
	 */
	public function add_wp_footer_scripts() {
		/**
		 * Echo the scripts added to the "before </body>" field in Theme Options.
		 * The 'space_body' setting is not sanitized.
		 * In order to be able to take advantage of this,
		 * a user would have to gain access to the database
		 * in which case this is the least on your worries.
		 */
		echo Avada()->settings->get( 'space_body' ); // WPCS: XSS ok.
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
