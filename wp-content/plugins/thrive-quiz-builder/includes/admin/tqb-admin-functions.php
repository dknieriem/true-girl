<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 8/30/2016
 * Time: 4:09 PM
 *
 * @package Thrive Quiz Builder
 */

/**
 * Gets the javascript variables.
 *
 * @return array
 */
function tqb_get_localization() {
	return array(
		't'               => include tqb()->plugin_path( 'includes/admin/i18n.php' ),
		'dash_url'        => admin_url( 'admin.php?page=tve_dash_section' ),
		'quiz_templates'  => tqb()->get_quiz_templates(),
		'quiz_types'      => tqb()->get_quiz_types(),
		'quiz_styles'     => tqb()->get_quiz_styles(),
		'shortcode_name'  => Thrive_Quiz_Builder::SHORTCODE_NAME,
		'chart_colors'    => tqb()->chart_colors(),
		'badge_templates' => tie()->template_manager()->get_templates(),
		'data'            => array(
			'settings'                  => tqb_get_option( Thrive_Quiz_Builder::PLUGIN_SETTINGS, tqb_get_default_values( Thrive_Quiz_Builder::PLUGIN_SETTINGS ) ),
			'quizzes'                   => TQB_Quiz_Manager::get_quizzes(),
			'quiz_types'                => array(
				'number'      => Thrive_Quiz_Builder::QUIZ_TYPE_NUMBER,
				'percentage'  => Thrive_Quiz_Builder::QUIZ_TYPE_PERCENTAGE,
				'personality' => Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY,
			),
			'colors'                    => array(
				'red'   => Thrive_Quiz_Builder::CHART_RED,
				'green' => Thrive_Quiz_Builder::CHART_GREEN,
				'grey'  => Thrive_Quiz_Builder::CHART_GREY,
			),
			'quiz_structure_item_types' => array(
				'splash'  => array(
					'key'       => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE,
					'name'      => tqb()->get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ),
					'mandatory' => false,
					'type'      => 'splash',
				),
				'qna'     => array(
					'key'       => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA,
					'name'      => tqb()->get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA ),
					'mandatory' => true,
					'type'      => 'qna',
				),
				'optin'   => array(
					'key'       => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
					'name'      => tqb()->get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN ),
					'mandatory' => false,
					'type'      => 'optin',
				),
				'results' => array(
					'key'       => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
					'name'      => tqb()->get_style_page_name( Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ),
					'mandatory' => true,
					'type'      => 'results',
				),
			),
			'variation_status'          => array(
				'publish' => Thrive_Quiz_Builder::VARIATION_STATUS_PUBLISH,
				'archive' => Thrive_Quiz_Builder::VARIATION_STATUS_ARCHIVE,
			),
			'max_interval_number'       => Thrive_Quiz_Builder::STATES_MAXIMUM_NUMBER_OF_INTERVALS,
		),
		'event_types'     => array(
			'impression' => Thrive_Quiz_Builder::TQB_IMPRESSION,
			'conversion' => Thrive_Quiz_Builder::TQB_CONVERSION,
			'skip_optin' => Thrive_Quiz_Builder::TQB_SKIP_OPTIN,
		),
		'date_intervals'  => array(
			'days7'      => Thrive_Quiz_Builder::TQB_LAST_7_DAYS,
			'days30'     => Thrive_Quiz_Builder::TQB_LAST_30_DAYS,
			'month_this' => Thrive_Quiz_Builder::TQB_THIS_MONTH,
			'month_last' => Thrive_Quiz_Builder::TQB_LAST_MONTH,
			'year_this'  => Thrive_Quiz_Builder::TQB_THIS_YEAR,
			'year_last'  => Thrive_Quiz_Builder::TQB_LAST_YEAR,
			'months12'   => Thrive_Quiz_Builder::TQB_LAST_12_MONTHS,
			'custom'     => Thrive_Quiz_Builder::TQB_CUSTOM_DATE_RANGE,
		),
		'admin_nonce'     => wp_create_nonce( 'tqb_admin_ajax_request' ),
		'ajax_actions'    => array(
			'admin_controller' => 'tqb_admin_ajax_controller',
		),
	);
}

/**
 * Hook for admin init action
 */
function tqb_admin_init() {
	if ( ! tqb()->check_tcb_version() ) {
		add_action( 'admin_notices', 'tqb_admin_notice_wrong_tcb_version' );
	}
}

/**
 * The TCB version is not compatible with the current TU version
 */
function tqb_admin_notice_wrong_tcb_version() {
	$screen = get_current_screen();

	if ( $screen->base === 'admin_page_tqb_admin_dashboard' ) {
		return;
	}

	$html = '<div class="error"><p>%s</p></div>';
	$text = sprintf( __( 'Current version of Thrive Quiz Builder is not compatible with the current version of Thrive Content Builder. Please update both plugins to the latest versions.', Thrive_Quiz_Builder::T ) );

	if ( $screen && $screen->base != 'plugins' ) {
		$text .= ' <a href="' . admin_url( 'plugins.php' ) . '">' . __( 'Manage plugins', Thrive_Quiz_Builder::T ) . '</a>';
	}

	echo sprintf( $html, $text );
}
