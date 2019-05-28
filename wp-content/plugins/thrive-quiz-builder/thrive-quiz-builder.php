<?php
/**
 * Plugin Name: Thrive Quiz Builder
 * Plugin URI: https://thrivethemes.com
 * Version: 1.0.3
 * Author: <a href="https://thrivethemes.com">Thrive Themes</a>
 * Description: The plugin is built to deliver the following benefits to users: engage visitors with fun and interesting quizzes, lower bounce rate, generate more leads and gain visitor insights to find out about their interests.
 * Text Domain: thrive-quiz-builder
 *
 * @package Thrive Quiz Builder
 */

if ( ! class_exists( 'Thrive_Quiz_Builder' ) ) :

	/**
	 * Main TQB Class
	 */
	final class Thrive_Quiz_Builder {

		/**
		 * Plugin text domain
		 */
		const T = 'thrive-quiz-builder';

		/**
		 * Plugin version
		 */
		const V = '1.0.3';

		/**
		 * Required TCB version
		 */
		const REQUIRED_TCB_VERSION = '1.500.10';

		/**
		 * Quiz Builder Database Version
		 */
		const DB = '1.0.1';

		/**
		 * Quiz Builder database prefix
		 */
		const DB_PREFIX = 'tqb_';

		/**
		 * The single instance of the class.
		 *
		 * @var Thrive_Quiz_Builder singleton instance.
		 */
		protected static $_instance = null;

		/**
		 * Quiz shortcode name
		 */
		const SHORTCODE_NAME = 'tqb_quiz';

		/**
		 * Results page test conversion goals
		 */
		const CONVERSION_GOAL_OPTIN = '1';
		const CONVERSION_GOAL_SOCIAL = '2';

		/**
		 * Quiz types
		 */
		const QUIZ_TYPE_NUMBER = 'number';
		const QUIZ_TYPE_PERCENTAGE = 'percentage';
		const QUIZ_TYPE_PERSONALITY = 'personality';


		const TQB_LAST_7_DAYS = 1;
		const TQB_LAST_30_DAYS = 2;
		const TQB_THIS_MONTH = 3;
		const TQB_LAST_MONTH = 4;
		const TQB_THIS_YEAR = 5;
		const TQB_LAST_YEAR = 6;
		const TQB_LAST_12_MONTHS = 7;
		const TQB_CUSTOM_DATE_RANGE = 8;

		/**
		 * Chart Colors
		 */
		const CHART_RED = '#F60000';
		const CHART_GREEN = '#006600';
		const CHART_GREY = '#C0C0C0';

		/**
		 * Quiz structure item types
		 */
		const QUIZ_STRUCTURE_ITEM_SPLASH_PAGE = 'tqb_splash';
		const QUIZ_STRUCTURE_ITEM_QNA = 'tqb_qna';
		const QUIZ_STRUCTURE_ITEM_OPTIN = 'tqb_optin';
		const QUIZ_STRUCTURE_ITEM_RESULTS = 'tqb_results';

		/**
		 * Impression and conversion event type representation
		 */
		const TQB_SKIP_OPTIN = 3;
		const TQB_CONVERSION = 2;
		const TQB_IMPRESSION = 1;

		/**
		 * Variation status
		 */
		const VARIATION_STATUS_PUBLISH = 'publish';
		const VARIATION_STATUS_ARCHIVE = 'archive';

		/**
		 * Other Constants
		 */
		const VARIATION_QUERY_KEY_NAME = 'tqb_key';

		/**
		 * Fields
		 */
		const FIELD_TEMPLATE = 'tpl';
		const FIELD_CONTENT = 'content';
		const FIELD_INLINE_CSS = 'inline_css';
		const FIELD_USER_CSS = 'user_css';
		const FIELD_CUSTOM_FONTS = 'fonts';
		const FIELD_ICON_PACK = 'icons';
		const FIELD_MASONRY = 'masonry';
		const FIELD_TYPEFOCUS = 'typefocus';
		const FIELD_SOCIAL_SHARE_BADGE = 'social_share_badge';

		/**
		 * States
		 */
		const STATES_MAXIMUM_NUMBER_OF_INTERVALS = 12;
		const STATES_DYNAMIC_CONTENT_DEFAULT = '<p class="tve_draggable">Your dynamic content here...</p>';
		const STATES_DYNAMIC_CONTENT_PATTERN = '<div style="display:none">__TQB__dynamic_DELIMITER</div>';
		const STATES_MINIMUM_WIDTH_SIZE = 20; // The minimum width size (in pixel) that a state could have.

		/**
		 * Wistia videos
		 */
		const VIDEO_QUIZ_TYPE_NUMBER = '//fast.wistia.net/embed/iframe/pestdpyl7m?popover=true';
		const VIDEO_QUIZ_TYPE_PERCENTAGE = '//fast.wistia.net/embed/iframe/qowc9skloc?popover=true';
		const VIDEO_QUIZ_TYPE_PERSONALITY = '//fast.wistia.net/embed/iframe/88xn85tngf?popover=true';
		const VIDEO_PAGE_SPLASH = '//fast.wistia.net/embed/iframe/0kq7h49dpj?popover=true';
		const VIDEO_PAGE_OPTIN = '//fast.wistia.net/embed/iframe/31f01ikryl?popover=true';
		const VIDEO_PAGE_RESULTS = '//fast.wistia.net/embed/iframe/twees9ywki?popover=true';
		const VIDEO_PLUGIN_DASHBOARD = '//fast.wistia.net/embed/iframe/ys2zbz9a62?popover=true';
		const VIDEO_QUIZ_TEMPLATE_SCRATCH = '//fast.wistia.net/embed/iframe/u3bvd48eng?popover=true';
		const VIDEO_QUIZ_TEMPLATE_LIST = '//fast.wistia.net/embed/iframe/rdi3amtxhg?popover=true';
		const VIDEO_QUIZ_TEMPLATE_SOCIAL = '//fast.wistia.net/embed/iframe/6p08yptf80?popover=true';
		const VIDEO_QUIZ_STYLES = '//fast.wistia.net/embed/iframe/5qfoowdp4t?popover=true';
		const VIDEO_A_B_TEST = '//fast.wistia.net/embed/iframe/g06fx55i67?popover=true';

		/**
		 * Knowledge base article about linking variations to next step in quiz
		 */
		const KB_NEXT_STEP_ARTICLE = 'https://thrivethemes.com/tkb_item/how-to-link-to-the-next-stage-in-the-quiz-manually-using-the-live-editor/';

		/**
		 * Quiz final result shortcode
		 */
		const QUIZ_RESULT_SHORTCODE = '%result%';
		const QUIZ_RESULT_SOCIAL_MEDIA_MSG = 'I got: %result%';

		/**
		 * Upload directory custom folder for thrive quiz builder
		 */
		const UPLOAD_DIR_CUSTOM_FOLDER = 'thrive-quiz-builder';

		/**
		 * Quiz Builder Plugin Settings
		 */
		const PLUGIN_SETTINGS = 'tqb_settings';

		/**
		 * Plugin Sales Page URL
		 */
		const PLUGIN_SALES_PAGE = 'https://thrivethemes.com/quizbuilder';

		/**
		 * A list with all fields that TCB uses to store various pieces of content / flags
		 *
		 * @return array
		 */
		public static function editor_fields() {
			return array(
				self::FIELD_CUSTOM_FONTS,
				self::FIELD_INLINE_CSS,
				self::FIELD_USER_CSS,
				self::FIELD_TEMPLATE,
				self::FIELD_TYPEFOCUS,
				self::FIELD_MASONRY,
				self::FIELD_ICON_PACK,
				self::FIELD_SOCIAL_SHARE_BADGE,
			);
		}

		/**
		 * Thrive Quiz Builder Constructor.
		 */
		private function __construct() {
			$this->includes();
			$this->init_hooks();
		}

		/**
		 * Hook into actions and filters
		 */
		private function init_hooks() {
			add_action( 'plugins_loaded', array( $this, 'load_dashboard_module' ) );
			add_action( 'init', array( $this, 'init' ) );

			/**
			 * Register impression and conversion hooks
			 */
			add_action( 'tqb_register_impression', array( 'TQB_Quiz_Manager', 'tqb_register_impression' ), 10, 2 );
			add_action( 'tqb_register_conversion', array( 'TQB_Quiz_Manager', 'tqb_register_conversion' ), 10, 2 );
			add_action( 'tqb_register_skip_optin', array( 'TQB_Quiz_Manager', 'tqb_register_skip_optin_event' ), 10, 2 );
			add_action( 'tcb_api_form_submit', array( 'TQB_Quiz_Manager', 'tqb_register_optin_conversion' ) );
			add_action( 'tqb_register_social_media_conversion', array( 'TQB_Quiz_Manager', 'tqb_register_social_media_conversion' ) );

			/**
			 * Load Thrive Dashboard Ajax Load
			 */
			add_filter( 'tve_dash_main_ajax_tqb_lazy_load', array( $this, 'tqb_frontend_ajax_load' ) );
			add_filter( 'tve_dash_enqueue_frontend', '__return_true' );

			/**
			 * Modify default no-cache headers
			 */
			add_filter( 'nocache_headers', array( $this, 'filter_nocache_headers' ), 10, 1 );
		}

		/**
		 * Handle lazy loading
		 */
		public function tqb_frontend_ajax_load() {
			$quiz_ids = $_POST['quiz_ids'];
			$data     = array();
			foreach ( $quiz_ids as $key => $id ) {
				$data[ $key ]            = TQB_Quiz_Manager::get_shortcode_content( $id );
				$data[ $key ]['quiz_id'] = $id;
			}

			return $data;
		}

		/**
		 * Load Thrive Dashboard
		 */
		public function load_dashboard_module() {
			$tve_dash_path      = dirname( __FILE__ ) . '/thrive-dashboard';
			$tve_dash_file_path = $tve_dash_path . '/version.php';

			if ( is_file( $tve_dash_file_path ) ) {
				$version                                  = require_once( $tve_dash_file_path );
				$GLOBALS['tve_dash_versions'][ $version ] = array(
					'path'   => $tve_dash_path . '/thrive-dashboard.php',
					'folder' => '/thrive-quiz-builder',
					'from'   => 'plugins',
				);
			}
		}

		/**
		 * Init Thrive Quiz Builder when Wordpress initializes
		 */
		public function init() {
			$this->load_plugin_textdomain();
			$this->update_checker();

			add_filter( 'tge_filter_edit_post', array( $this, 'set_responses' ) );
			add_filter( 'tge_filter_edit_post', array( $this, 'set_quiz_type' ) );
		}


		/**
		 * Checks for updates
		 */
		public function update_checker() {
			/** plugin updates script **/

			new TVE_PluginUpdateChecker(
				'http://service-api.thrivethemes.com/plugin/update',
				__FILE__,
				'thrive-quiz-builder',
				12,
				'',
				'thrive_quiz_builder'
			);
		}

		/**
		 * Main Quiz Builder Instance.
		 * Ensures only one instance of Quiz Builder is loaded or can be loaded.
		 *
		 * @return Thrive_Quiz_Builder
		 */
		public static function instance() {
			if ( empty( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {

			/**
			 * Include TCB
			 */
			$this->include_tcb();

			require_once( 'image-editor/thrive-image-editor.php' );
			require_once( 'graph-editor/thrive-graph-editor.php' );

			/**
			 * Here we include all core function needed in admin and frontend as well.
			 */
			require_once( 'includes/tqb-global-functions.php' );
			require_once( 'includes/class-tqb-post-types.php' );
			require_once( 'includes/class-tqb-post-meta.php' );
			require_once( 'includes/tqb-data-functions.php' );
			require_once( 'includes/class-tqb-request-handler.php' );
			require_once( 'includes/class-tqb-template-manager.php' );
			require_once( 'includes/class-tqb-state-manager.php' );
			require_once( 'includes/database/class-tqb-database-manager.php' );
			require_once( 'includes/class-tqb-db.php' );
			require_once( 'includes/managers/class-tqb-structure-manager.php' );
			require_once( 'includes/managers/class-tqb-variation-manager.php' );
			require_once( 'includes/managers/class-tqb-page-manager.php' );
			require_once( 'includes/managers/class-tqb-quiz-manager.php' );
			require_once( 'includes/managers/class-tqb-test-manager.php' );
			require_once( 'includes/managers/class-tqb-reporting-manager.php' );

			/**
			 *  Include ajax controllers
			 */
			require_once( 'includes/class-tqb-ajax.php' );

			if ( $this->is_request( 'admin' ) ) {
				require_once( 'includes/admin/class-tqb-admin.php' );
			}

			/* Include the hooks file only if the variation query name exists in request */
			require_once( 'tcb-bridge/tqb-class-hooks.php' );

			/**
			 *  Include frontend files
			 */
			require_once( 'includes/class-tqb-shortcodes.php' );

			$this->resolve_tqb_conflicts();
		}

		private function include_tcb() {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			$tcb_exists = file_exists( dirname( $this->plugin_path() ) . '/thrive-visual-editor/thrive-visual-editor.php' );
			$tcb_active = is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' );

			if ( ! $tcb_exists || ! $tcb_active ) {
				require_once( 'tcb-bridge/tqb-class-tcb.php' );
				defined( 'EXTERNAL_TCB' ) || define( 'EXTERNAL_TCB', 0 );
			} else {
				defined( 'EXTERNAL_TCB' ) || define( 'EXTERNAL_TCB', 1 );
			}
		}

		/**
		 * Add extra headers for it to actually work on most browsers
		 *
		 * @param $headers
		 *
		 * @return array
		 */
		public function filter_nocache_headers( $headers ) {
			$headers['Cache-Control'] = $headers['Cache-Control'] . ', no-store';
			$headers['pragma']        = 'no-cache';

			return $headers;
		}

		/**
		 * What type of request is this?
		 *
		 * @param  string $type admin, ajax, cron or frontend.
		 *
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
				default:
					return false;
			}
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/thrive/thrive-quiz-builder-LOCALE.mo
		 */
		public function load_plugin_textdomain() {

			$locale = apply_filters( 'plugin_locale', get_locale(), self::T );

			load_textdomain( self::T, WP_LANG_DIR . '/thrive/' . self::T . '-' . $locale . '.mo' );
			load_plugin_textdomain( self::T, false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Full plugin url to file if specified
		 *
		 * @param string $file to be appended to the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url( $file = '' ) {
			return plugin_dir_url( __FILE__ ) . ltrim( $file, '\\/' );
		}

		/**
		 * Chart colors
		 *
		 * @return string
		 */
		public function chart_colors() {
			return array( '#75b343', '#925699', '#ffa143', '#0679b6', '#16db94', '#c4ad88', '#844a17', '#e5e339', '#ef5780', '#8cd6dd', '#2f672c', '#faa8ff' );
		}

		/**
		 * Full plugin path to file if specified
		 *
		 * @param string $file to be appended to the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path( $file = '' ) {
			return plugin_dir_path( __FILE__ ) . ltrim( $file, '\\/' );
		}

		/**
		 * Quiz templates
		 *
		 * @return array
		 */
		public function get_quiz_templates() {

			$templates = array();

			$templates[] = array(
				'id'                     => '1',
				'is_empty'               => true, //build from scratch
				'name'                   => __( 'Build from scratch', self::T ),
				'description'            => __( 'Build a quiz from scratch with no predefined settings', self::T ),
				'learn_more'             => '<a href=\'' . Thrive_Quiz_Builder::VIDEO_QUIZ_TEMPLATE_SCRATCH . '\' class=\'wistia-popover[height=450,playerColor=2bb914,width=800]\'><span class=\'tvd-icon-play tqb-purple-icon\'></span></a>',
				'image'                  => self::plugin_url( 'assets/images/tqb-quiz-template1.png' ),
				'splash'                 => false,
				'qna'                    => true,
				'optin'                  => false,
				'results'                => true,
				'default_page_templates' => array(
					'tqb_splash'  => 'template_1',
					'tqb_optin'   => 'template_1',
					'tqb_results' => null,
				),
			);

			$templates[] = array(
				'id'                     => '2',
				'is_empty'               => false,
				'name'                   => __( 'List building', self::T ),
				'description'            => __( 'Quiz optimized for building an email list. The Results Page is visible only if the user signs up.', self::T ),
				'learn_more'             => '<a href=\'' . Thrive_Quiz_Builder::VIDEO_QUIZ_TEMPLATE_LIST . '\' class=\'wistia-popover[height=450,playerColor=2bb914,width=800]\'><span class=\'tvd-icon-play tqb-purple-icon\'></span></a>',
				'image'                  => self::plugin_url( 'assets/images/tqb-quiz-template2.png' ),
				'splash'                 => true,
				'qna'                    => true,
				'optin'                  => true,
				'results'                => true,
				'default_page_templates' => array(
					'tqb_splash'  => 'template_1',
					'tqb_optin'   => 'template_1',
					'tqb_results' => 'template_2',
				),
			);

			$templates[] = array(
				'id'                     => '3',
				'is_empty'               => false,
				'name'                   => __( 'Social shares', self::T ),
				'description'            => __( 'Quiz optimized for social sharing. The Results Page contains a Social Share Badge that your visitors can share with their friends to increase the popularity of the quiz.', self::T ),
				'learn_more'             => '<a href=\'' . Thrive_Quiz_Builder::VIDEO_QUIZ_TEMPLATE_SOCIAL . '\' class=\'wistia-popover[height=450,playerColor=2bb914,width=800]\'><span class=\'tvd-icon-play tqb-purple-icon\'></span></a>',
				'image'                  => self::plugin_url( 'assets/images/tqb-quiz-template3.png' ),
				'splash'                 => true,
				'qna'                    => true,
				'optin'                  => false,
				'results'                => true,
				'default_page_templates' => array(
					'tqb_splash'  => 'template_1',
					'tqb_optin'   => 'template_1',
					'tqb_results' => 'template_3',
				),
			);

			return $templates;
		}

		/**
		 * Social share badge share templates
		 *
		 * @return array
		 */
		public function get_tcb_social_share_badge_templates() {
			$templates = array();

			$templates[] = array(
				'name'  => 'Style 1',
				'file'  => 'set_02',
				'image' => self::plugin_url( 'tcb-bridge/assets/images/social-template-top.png' ),
			);

			$templates[] = array(
				'name'  => 'Style 2',
				'file'  => 'set_01',
				'image' => self::plugin_url( 'tcb-bridge/assets/images/social-template-bottom.png' ),
			);

			$templates[] = array(
				'name'  => 'Style 3',
				'file'  => 'set_04',
				'image' => self::plugin_url( 'tcb-bridge/assets/images/social-template-left.png' ),
			);

			$templates[] = array(
				'name'  => 'Style 4',
				'file'  => 'set_03',
				'image' => self::plugin_url( 'tcb-bridge/assets/images/social-template-right.png' ),
			);

			return $templates;
		}

		public function get_style_thumbnail( $style_image ) {
			return self::plugin_url( 'assets/images/' . $style_image . '.png' );
		}

		/**
		 * Quiz styles
		 *
		 * @return array
		 */
		public function get_quiz_styles( $style_id = null ) {
			$styles = array();

			$styles[] = array(
				'id'                                                 => '0',
				'name'                                               => __( 'Light Blue Quiz', self::T ),
				'cover'                                              => self::plugin_url( 'assets/images/style-0-cover.jpg' ),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ),
					'image'  => self::get_style_thumbnail( 'splash-0' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA         => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA ),
					'image'  => self::get_style_thumbnail( 'qa-0' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN       => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN ),
					'image'  => self::get_style_thumbnail( 'optin-0' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS     => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ),
					'image'  => self::get_style_thumbnail( 'results-0' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
			);

			$styles[] = array(
				'id'                                                 => '1',
				'name'                                               => __( 'Dark Quiz', self::T ),
				'cover'                                              => self::plugin_url( 'assets/images/style-1-cover.png' ),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ),
					'image'  => self::get_style_thumbnail( 'splash-1' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA         => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA ),
					'image'  => self::get_style_thumbnail( 'qa-1' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN       => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN ),
					'image'  => self::get_style_thumbnail( 'optin-1' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS     => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ),
					'image'  => self::get_style_thumbnail( 'results-1' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
			);

			$styles[] = array(
				'id'                                                 => '2',
				'name'                                               => __( 'Style 3', self::T ),
				'cover'                                              => self::plugin_url( 'assets/images/style-2-cover.png' ),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ),
					'image'  => self::get_style_thumbnail( 'splash-2' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA         => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA ),
					'image'  => self::get_style_thumbnail( 'qa-2' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN       => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN ),
					'image'  => self::get_style_thumbnail( 'optin-2' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS     => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ),
					'image'  => self::get_style_thumbnail( 'results-2' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
			);

			$styles[] = array(
				'id'                                                 => '3',
				'name'                                               => __( 'Style 4', self::T ),
				'cover'                                              => self::plugin_url( 'assets/images/style-3-cover.png' ),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ),
					'image'  => self::get_style_thumbnail( 'splash-3' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA         => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA ),
					'image'  => self::get_style_thumbnail( 'qa-3' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN       => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN ),
					'image'  => self::get_style_thumbnail( 'optin-3' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
				Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS     => array(
					'name'   => self::get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ),
					'image'  => self::get_style_thumbnail( 'results-3' ),
					'config' => array(
						'main-content-style' => '',
					),
				),
			);

			return $styles;
		}

		/**
		 * Gets the style config
		 *
		 * @param int $id
		 *
		 * @return mixed
		 */
		public function get_style_config( $id = 0 ) {
			$styles = $this->get_quiz_styles();
			foreach ( $styles as $style ) {
				if ( $style['id'] == $id ) {
					return $style;
				}
			}
		}

		/**
		 * The function return the style css file name or null if quiz style meta is empty
		 *
		 * @param null $quiz_style_meta
		 *
		 * @return null|string
		 */
		public function get_style_css( $quiz_style_meta = null ) {
			if ( is_numeric( $quiz_style_meta ) ) {
				return 'style-' . $quiz_style_meta . '.css';
			}

			return null;
		}

		/**
		 * Gets the page name based on page type.
		 *
		 * @param string $type
		 *
		 * @return string|void
		 */
		public function get_style_page_name( $type = '' ) {
			$label = '';
			switch ( $type ) {
				case self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE:
					$label = __( 'Splash Page', self::T );
					break;
				case self::QUIZ_STRUCTURE_ITEM_OPTIN:
					$label = __( 'Opt-in Gate', self::T );
					break;
				case self::QUIZ_STRUCTURE_ITEM_QNA:
					$label = __( 'Q&A', self::T );
					break;
				case self::QUIZ_STRUCTURE_ITEM_RESULTS:
					$label = __( 'Results Page', self::T );
					break;
				default:
					break;
			}

			return $label;
		}

		/**
		 * Gets the post_type using internal identifier.
		 *
		 * @param string $type
		 *
		 * @return string|void
		 */
		public function get_structure_post_type_name( $type = '' ) {
			$post_type = '';
			switch ( $type ) {
				case 'splash':
					$post_type = self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE;
					break;
				case 'qna':
					$post_type = self::QUIZ_STRUCTURE_ITEM_QNA;
					break;
				case 'optin':
					$post_type = self::QUIZ_STRUCTURE_ITEM_OPTIN;
					break;
				case 'results':
					$post_type = self::QUIZ_STRUCTURE_ITEM_RESULTS;
					break;
				default:
					break;
			}

			return $post_type;
		}

		/**
		 * Gets the internal identifier using post_type .
		 *
		 * @param string $post_type
		 *
		 * @return string|void
		 */
		public function get_structure_type_name( $post_type = '' ) {
			$type = '';
			switch ( $post_type ) {
				case self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE:
					$type = 'splash';
					break;
				case self::QUIZ_STRUCTURE_ITEM_QNA:
					$type = 'qna';
					break;
				case self::QUIZ_STRUCTURE_ITEM_OPTIN:
					$type = 'optin';
					break;
				case self::QUIZ_STRUCTURE_ITEM_RESULTS:
					$type = 'results';
					break;
				default:
					break;
			}

			return $type;
		}

		/**
		 * Get array of internal identifiers.
		 *
		 * @return array
		 */
		public function get_structure_internal_identifiers() {
			return array(
				'splash',
				'qna',
				'optin',
				'results',
			);
		}

		/**
		 * Gets the page description based on page type.
		 *
		 * @param string $type
		 *
		 * @return string|void
		 */
		public function get_style_page_description( $type = '' ) {
			$description = '';
			switch ( $type ) {
				case self::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE:
					$description = __( 'The variation marked with (control) will be displayed as your splash page. If you want to improve your conversion rate, add more variations and start an A/B Test.', self::T );
					break;
				case self::QUIZ_STRUCTURE_ITEM_OPTIN:
					$description = __( 'The variation marked with (control) will be displayed as your opt-in gate. If you want to improve your conversion rate, add more variations and start an A/B Test.', self::T );
					break;
				case self::QUIZ_STRUCTURE_ITEM_QNA:
					$description = '';
					break;
				case self::QUIZ_STRUCTURE_ITEM_RESULTS:
					$description = __( 'The variation marked with (control) will be displayed as your results page. If you want to improve your conversion rate, add more variations and start an A/B Test.', self::T );
					break;
				default:
					break;
			}

			return $description;
		}

		/**
		 * Quiz Types
		 *
		 * @return array
		 */
		public function get_quiz_types() {

			$types = array();

			$types[] = array(
				'key'           => self::QUIZ_TYPE_NUMBER,
				'label'         => __( 'Number', self::T ),
				'image'         => self::plugin_url( 'assets/images/logo-score-lg.png' ),
				'tooltip'       => __( 'Use this quiz type if you want to display the final result of the quiz as a number.', self::T ),
				'learn_more'    => '<a href=\'' . Thrive_Quiz_Builder::VIDEO_QUIZ_TYPE_NUMBER . '\' class=\'wistia-popover[height=450,playerColor=2bb914,width=800]\'><span class=\'tvd-icon-play tqb-purple-icon\'></span></a>',
				'has_next_step' => false,
			);
			$types[] = array(
				'key'           => self::QUIZ_TYPE_PERCENTAGE,
				'label'         => __( 'Percentage', self::T ),
				'image'         => self::plugin_url( 'assets/images/logo-percentage.png' ),
				'tooltip'       => __( 'Use this quiz type if you want to display the final result of the quiz as a percentage.', self::T ),
				'learn_more'    => '<a href=\'' . Thrive_Quiz_Builder::VIDEO_QUIZ_TYPE_PERCENTAGE . '\' class=\'wistia-popover[height=450,playerColor=2bb914,width=800]\'><span class=\'tvd-icon-play tqb-purple-icon\'></span></a>',
				'has_next_step' => false,
			);
			$types[] = array(
				'key'           => self::QUIZ_TYPE_PERSONALITY,
				'label'         => __( 'Category', self::T ),
				'image'         => self::plugin_url( 'assets/images/logo-personality.png' ),
				'tooltip'       => __( 'In this quiz you set up a number of possible result categories. An example of this would be a personality type quiz.', self::T ),
				'learn_more'    => '<a href=\'' . Thrive_Quiz_Builder::VIDEO_QUIZ_TYPE_PERSONALITY . '\' class=\'wistia-popover[height=450,playerColor=2bb914,width=800]\'><span class=\'tvd-icon-play tqb-purple-icon\'></span></a>',
				'has_next_step' => true,
			);

			return $types;
		}

		/**
		 * Sets on the post the results saved in db based on post->ID
		 *
		 * @param WP_Post $post
		 *
		 * @return WP_Post
		 */
		public function set_responses( $post ) {
			$quiz_manager  = new TQB_Quiz_Manager( $post );
			$post->results = $quiz_manager->get_results();

			return $post;
		}

		public function set_quiz_type( $post ) {
			$type            = TQB_Post_meta::get_quiz_type_meta( $post->ID );
			$post->quiz_type = $type['type'];

			return $post;
		}

		/**
		 * Check if there is a valid activated license for the TQB plugin.
		 *
		 * @return bool
		 */
		public function license_activated() {
			return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( TVE_Dash_Product_LicenseManager::TQB_TAG );
		}

		/**
		 * check if the current TCB version is the one required by Thrive Quiz Builder
		 */
		public function check_tcb_version() {
			if ( ! EXTERNAL_TCB ) { // the internal TCB code will always be up to date
				return true;
			}

			if ( ! defined( 'TVE_VERSION' ) || TVE_VERSION != self::REQUIRED_TCB_VERSION ) {
				return false;
			}

			return true;
		}

		/**
		 * Resolves the conflicts with other plugins
		 */
		private function resolve_tqb_conflicts() {

			/**
			 * Resolves the conflict with Better WordPress Minify
			 * https://ro.wordpress.org/plugins/bwp-minify/
			 *
			 * TQB registers the scripts at a low priority then BWM plugin.
			 * BWM plugin registers the scripts with priority the same as  `wp_print_head_scripts`
			 * TQB plugin registers the scripts with priority the same as `wp_footer`
			 */
			add_filter( 'bwp_minify_is_loadable', '__return_false', 10 );
		}
	}

endif;

/**
 *  Main instance of Thrive Quiz Builder.
 *
 * @return Thrive_Quiz_Builder
 */
function tqb() {
	return Thrive_Quiz_Builder::instance();
}

/**
 * This helps to display the errors on ajax requests too
 */
if ( defined( 'TVE_DEBUG' ) && TVE_DEBUG === true ) {
	ini_set( 'display_errors', 1 );
	error_reporting( E_ALL );
}
tqb();
