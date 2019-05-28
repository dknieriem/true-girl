<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( class_exists( 'TGE_Editor' ) ) {
	return;
}

class TGE_Editor {

	/**
	 * @var TGE_Editor
	 */
	private static $_instance = null;

	/**
	 * @var WP_Post
	 */
	private $_post = null;

	/**
	 * @var bool
	 */
	private $_can_edit_post = null;

	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	final private function __construct() {
		if ( $this->_can_edit_post() ) {
			$this->_clear_scripts();
			$this->_init();
		}
	}

	private function _can_edit_post() {

		if ( isset( $this->_can_edit_post ) ) {
			return $this->_can_edit_post;
		}

		$this->_can_edit_post = false;
		$this->_can_edit_post = current_user_can( 'manage_options' );
		$post                 = $this->_can_edit_post ? get_post() : null;
		$this->_can_edit_post = $this->_can_edit_post && (bool) $post;

		$this->_can_edit_post ? $this->_post = $post : null;

		return $this->_can_edit_post;
	}

	private function _clear_scripts() {

		//global $wp_filter;
		//print_r( $wp_filter['wp_footer'] );

		remove_all_actions( 'wp_head' );
		remove_all_actions( 'wp_footer' );

		remove_all_actions( 'wp_enqueue_scripts' );
		remove_all_actions( 'wp_print_styles' );
		remove_all_actions( 'wp_print_footer_scripts' );
		remove_all_actions( 'print_footer_scripts' );
		remove_all_actions( 'admin_bar_menu' );

		remove_all_filters( 'single_template' );
		remove_all_filters( 'template_include' );

		add_action( 'wp_head', 'wp_enqueue_scripts' );
		add_action( 'wp_head', 'wp_print_styles' );
		add_action( 'wp_head', 'wp_print_head_scripts' );

		add_action( 'wp_head', '_wp_render_title_tag', 1 );

		add_action( 'wp_footer', '_wp_footer_scripts' );
		add_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
		add_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
		add_action( 'wp_footer', 'print_footer_scripts', 1000 );

		_wp_admin_bar_init();
	}

	private function _init() {

		/**
		 * Scripts
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );

		/**
		 * Styles
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'add_styles' ), PHP_INT_MAX );

		/**
		 * Layout
		 */
		add_filter( 'single_template', array( $this, 'layout' ) );

		add_filter( 'tve_dash_enqueue_frontend', array( $this, 'allow_thrive_dashboard_on_frontend' ) );

		add_action( 'wp_print_footer_scripts', array( $this, 'print_backbone_templates' ) );
		add_action( 'wp_print_footer_scripts', 'tve_dash_backbone_templates' );

		apply_filters( 'tge_filter_edit_post', $this->_post );

		$this->_post->display_weight = (bool) get_post_meta( $this->_post->ID, 'tge_display_weight', true );

		add_filter( 'document_title_parts', array( $this, 'get_title' ) );
	}

	public function get_title( $title ) {
		$title = array(
			'title'  => $this->_post->post_title,
			'editor' => 'Question Editor',
		);

		return $title;
	}

	public function allow_thrive_dashboard_on_frontend() {
		return true;
	}

	public function add_scripts() {

		/** some themes have hooks defined here, which rely on functions defined only in the admin part - these will not be defined on frontend */
		remove_all_filters( 'media_view_settings' );

		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui-draggable' );

		$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

		wp_enqueue_script( 'tge-jquery', tge()->url( 'assets/vendors/jquery.min.js' ) );
		wp_enqueue_script( 'tge-lodash', tge()->url( 'assets/vendors/lodash.min.js' ) );
		wp_enqueue_script( 'tge-backbone', tge()->url( 'assets/vendors/backbone-min.js' ), array(
			'tge-jquery',
			'tge-lodash',
		) );
		wp_enqueue_script( 'tge-jointjs', tge()->url( 'assets/vendors/jointjs/joint' . $js_suffix ), array(
			'tge-jquery',
			'tge-lodash',
			'tge-backbone',
		) );
		tve_dash_enqueue_script( 'tve-dash-main-js', TVE_DASH_URL . '/js/dist/tve-dash' . $js_suffix, array(
			'tge-jointjs',
		) );
		wp_enqueue_script( 'tge-editor', tge()->url( 'assets/js/dist/tge-editor' . $js_suffix ), array(
			'tge-jointjs',
			'tve-dash-main-js',
		), false, true );

		$question_manager = new TGE_Question_Manager( $this->_post->ID );
		$questions        = $question_manager->get_quiz_questions( array( 'with_answers' => true ) );

		$data = array(
			'debug_mode'             => defined( 'TVE_DEBUG' ) && TVE_DEBUG,
			'quiz_dash_url'          => $this->get_quiz_dash_url(),
			'ajaxurl'                => admin_url( 'admin-ajax.php' ),
			'ajax_controller_action' => 'tge_admin_ajax_controller',
			'nonce'                  => wp_create_nonce( TGE_Ajax::AJAX_NONCE_NAME ),
			'assets_url'             => tge()->url( 'assets' ),
			'post_id'                => $this->_post->ID,
			'quiz'                   => $this->_post,
			'question_types'         => TGE_Question_Manager::get_question_types(),
			'icons'                  => array(
				'delete' => tge()->url( 'assets/img/delete-qe.png' ),
				'edit'   => tge()->url( 'assets/img/edit-qe.png' ),
			),
			'questions'              => $question_manager->prepare_questions( $questions ),
			't'                      => array(
				'multiple_invalid_answers'    => __( 'You are not allowed to add a new answer as long as you already have invalid answers added', Thrive_Graph_Editor::T ),
				'select_question_type'        => __( 'Please select question type', Thrive_Graph_Editor::T ),
				'quiz_start'                  => __( 'Quiz Start', Thrive_Graph_Editor::T ),
				'question_text_required'      => __( 'Question text is required', Thrive_Graph_Editor::T ),
				'answer_text_required'        => __( 'Answer text required', Thrive_Graph_Editor::T ),
				'answer_points_required'      => __( 'Answer points required', Thrive_Graph_Editor::T ),
				'answer_weight_required'      => __( 'Answer weight required', Thrive_Graph_Editor::T ),
				'answer_points_number'        => __( 'Answer points must be a number', Thrive_Graph_Editor::T ),
				'answer_weight_number'        => __( 'Answer weight must be a number', Thrive_Graph_Editor::T ),
				'points_input_number'         => __( 'The input must be an integer number with no more than 6 digits.', Thrive_Graph_Editor::T ),
				'invalid_answer'              => __( 'There are some invalid answers', Thrive_Graph_Editor::T ),
				'insufficient_answers'        => __( 'A question needs at least 1 answer', Thrive_Graph_Editor::T ),
				'answer_image_required'       => __( 'Answer image is mandatory', Thrive_Graph_Editor::T ),
				'question_success_deleted'    => __( 'Question has been deleted', Thrive_Graph_Editor::T ),
				'question_error_deleted'      => __( 'Question could not be deleted', Thrive_Graph_Editor::T ),
				'select_result'               => __( 'Please select category', Thrive_Graph_Editor::T ),
				'saving'                      => __( 'Saving...', Thrive_Graph_Editor::T ),
				'changes_saved'               => __( 'Changes saved', Thrive_Graph_Editor::T ),
				'changes_automatically_saved' => __( 'All your changes are auto saved', Thrive_Graph_Editor::T ),
				'change_question_type'        => __( 'Change Question Type', Thrive_Graph_Editor::T ),
				'edit_question'               => __( 'Edit question', Thrive_Graph_Editor::T ),
				'minimize'                    => __( 'Minimize', Thrive_Graph_Editor::T ),
				'maximize'                    => __( 'Maximize', Thrive_Graph_Editor::T ),
				'media'                       => array(
					'question_title' => __( 'Select image for your question', Thrive_Graph_Editor::T ),
				),
			),
		);
		wp_localize_script( 'tge-editor', 'TGE_Editor', $data );
		tve_dash_enqueue_script( 'tge-api-wistia-popover', '//fast.wistia.com/assets/external/E-v1.js', array(), '', true );
	}

	public function add_styles() {
		wp_enqueue_style( 'tge-jointjs', tge()->url( 'assets/vendors/jointjs/joint.min.css' ) );
		wp_enqueue_style( 'tge-editor', tge()->url( 'assets/css/tge-editor.css', array(
			'tge-jointjs'
		) ) );

		tve_dash_enqueue_style( 'tve-dash-styles-css', TVE_DASH_URL . '/css/styles.css' );
	}

	public function layout() {
		$layout = dirname( dirname( __FILE__ ) ) . '/layouts/editor.php';

		return $layout;
	}

	public function print_backbone_templates() {
		$templates = tve_dash_get_backbone_templates( tge()->path( 'includes/templates/backbone' ), 'backbone' );
		tve_dash_output_backbone_templates( $templates );
	}

	public function get_quiz_dash_url() {
		return admin_url( 'admin.php?page=tqb_admin_dashboard#dashboard/quiz/' . $this->_post->ID );
	}
}
