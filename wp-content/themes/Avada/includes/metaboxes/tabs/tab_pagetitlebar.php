<?php
/**
 * Titlebar Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

if ( ! function_exists( 'get_current_screen' ) ) {
	include_once ABSPATH . 'wp-admin/includes/screen.php';
}
$screen = get_current_screen();

// Regular PTB TO.
$page_title_option_name = 'page_title_bar';

if ( get_the_id() === (int) get_option( 'page_for_posts' ) ) {

	// Blog page PTB.
	$page_title_option_name = 'blog_show_page_title_bar';
} elseif ( is_object( $screen ) && 'edit' === $screen->parent_base && 'post' === $screen->post_type ) {

	// Blog archive/post PTB.
	$page_title_option_name = 'blog_page_title_bar';
}

$page_title_default = Avada()->settings->get_default_description( $page_title_option_name, '', 'select' );
$page_title_option  = Avada()->settings->get( $page_title_option_name );

$this->select(
	'page_title',
	esc_attr__( 'Page Title Bar', 'Avada' ),
	array(
		'default'         => esc_attr__( 'Default', 'Avada' ),
		'yes'             => esc_attr__( 'Show Bar and Content', 'Avada' ),
		'yes_without_bar' => esc_attr__( 'Show Content Only', 'Avada' ),
		'no'              => esc_attr__( 'Hide', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Choose to show or hide the page title bar. %s', 'Avada' ), $page_title_default )
);

// Dependency check that page title bar not hidden.
$page_title_dependency = array(
	array(
		'field'      => 'page_title',
		'value'      => 'no',
		'comparison' => '!=',
	),
);
if ( 'hide' === $page_title_option ) {
	$page_title_dependency[] = array(
		'field'      => 'page_title',
		'value'      => 'default',
		'comparison' => '!=',
	);
}

$this->radio_buttonset(
	'page_title_breadcrumbs_search_bar',
	esc_html__( 'Breadcrumbs/Search Bar', 'Avada' ),
	array(
		'default'     => esc_attr__( 'Default', 'Avada' ),
		'breadcrumbs' => esc_attr__( 'Breadcrumbs', 'Avada' ),
		'searchbar'   => esc_attr__( 'Search Bar', 'Avada' ),
		'none'        => esc_attr__( 'None', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Choose to display the breadcrumbs, search bar or none. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bar_bs', '', 'select' ) ),
	'',
	$page_title_dependency
);

$this->radio_buttonset(
	'page_title_text',
	esc_html__( 'Page Title Bar Headings', 'Avada' ),
	array(
		'default' => esc_attr__( 'Default', 'Avada' ),
		'yes'     => esc_attr__( 'Show', 'Avada' ),
		'no'      => esc_attr__( 'Hide', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Choose to show or hide the page title bar headings. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bar_text', '', 'showhide' ) ),
	'',
	$page_title_dependency
);

$page_title_text_dependency   = $page_title_dependency;
$page_title_text_dependency[] = array(
	'field'      => 'page_title_text',
	'value'      => 'no',
	'comparison' => '!=',
);
if ( 0 == Avada()->settings->get( 'page_title_bar_text' ) ) {
	$page_title_text_dependency[] = array(
		'field'      => 'page_title_text',
		'value'      => 'default',
		'comparison' => '!=',
	);
}

$this->radio_buttonset(
	'page_title_text_alignment',
	esc_html__( 'Page Title Bar Text Alignment', 'Avada' ),
	array(
		'default' => esc_attr__( 'Default', 'Avada' ),
		'left'    => esc_attr__( 'Left', 'Avada' ),
		'center'  => esc_attr__( 'Center', 'Avada' ),
		'right'   => esc_attr__( 'Right', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_attr__( 'Choose the title and subhead text alignment. Breadcrumbs / search field will be on opposite side for left / right alignment and below the title for center alignment. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_alignment', '', 'select' ) ),
	'',
	$page_title_dependency
);

$this->textarea(
	'page_title_custom_text',
	esc_attr__( 'Page Title Bar Heading Custom Text', 'Avada' ),
	esc_attr__( 'Insert custom text for the page title bar main heading.', 'Avada' ),
	'',
	$page_title_text_dependency
);

$this->text(
	'page_title_text_size',
	esc_attr__( 'Page Title Bar Heading Font Size', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'In pixels. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_font_size' ) ),
	$page_title_text_dependency
);

$this->text(
	'page_title_line_height',
	esc_attr__( 'Page Title Bar Heading Line Height', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Valid CSS unit. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_line_height' ) ),
	$page_title_text_dependency
);

$this->color(
	'page_title_font_color',
	esc_attr__( 'Page Title Bar Heading Font Color', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Controls the text color of the page title bar main heading. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_color' ) ),
	true,
	$page_title_text_dependency,
	Avada()->settings->get( 'page_title_color' )
);

$this->textarea(
	'page_title_custom_subheader',
	esc_attr__( 'Page Title Bar Subheading Custom Text', 'Avada' ),
	esc_html__( 'Insert custom text for the page title bar subheading.', 'Avada' ),
	'',
	$page_title_text_dependency
);

$this->text(
	'page_title_custom_subheader_text_size',
	esc_html__( 'Page Title Bar Subheading Font Size', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_attr__( 'In pixels. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_subheader_font_size' ) ),
	$page_title_text_dependency
);

$this->color(
	'page_title_subheader_font_color',
	esc_attr__( 'Page Title Bar Subheading Font Color', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Controls the text color of the page title bar subheading. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_subheader_color' ) ),
	true,
	$page_title_text_dependency,
	Avada()->settings->get( 'page_title_subheader_color' )
);

$this->radio_buttonset(
	'page_title_100_width',
	esc_html__( 'Page Title Bar 100% Width', 'Avada' ),
	array(
		'default' => esc_attr__( 'Default', 'Avada' ),
		'yes'     => esc_attr__( 'Yes', 'Avada' ),
		'no'      => esc_attr__( 'No', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Choose "Yes" to have the page title bar area display at 100&#37; width according to the viewport size. Select "No" to follow site width. Only works with wide layout mode. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_100_width', '', 'yesno' ) ),
	'',
	$page_title_dependency
);

$this->text(
	'page_title_height',
	esc_attr__( 'Page Title Bar Height', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Controls the height of the page title bar on desktop. Enter value including any valid CSS unit besides %% which does not work for page title bar, ex: 87px. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_height' ) ),
	$page_title_dependency
);

$this->text(
	'page_title_mobile_height',
	esc_attr__( 'Page Title Bar Mobile Height', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Controls the height of the page title bar on mobile. Enter value including any valid CSS unit besides %% which does not work for page title bar, ex: 70px. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_mobile_height' ) ),
	$page_title_dependency
);

// Dependency check that background is used.
$page_title_bg_dependency   = $page_title_dependency;
$page_title_bg_dependency[] = array(
	'field'      => 'page_title',
	'value'      => 'yes_without_bar',
	'comparison' => '!=',
);
if ( 'content_only' == $page_title_option ) {
	$page_title_bg_dependency[] = array(
		'field'      => 'page_title',
		'value'      => 'default',
		'comparison' => '!=',
	);
}

$ptb_bg_color = Fusion_Color::new_color(
	array(
		'color'    => Avada()->settings->get( 'page_title_bg_color' ),
		'fallback' => '#F6F6F6',
	)
);
$this->color(
	'page_title_bar_bg_color',
	esc_attr__( 'Page Title Bar Background Color', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Controls the background color of the page title bar. Hex code, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg_color' ) ),
	true,
	$page_title_bg_dependency,
	$ptb_bg_color->color
);

$ptb_border_color = Fusion_Color::new_color(
	array(
		'color'    => Avada()->settings->get( 'page_title_border_color' ),
		'fallback' => '#d2d3d4',
	)
);
$this->color(
	'page_title_bar_borders_color',
	esc_attr__( 'Page Title Bar Borders Color', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Controls the border color of the page title bar. Hex code, ex: #000. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_border_color' ) ),
	true,
	$page_title_bg_dependency,
	$ptb_border_color->color
);

$this->upload(
	'page_title_bar_bg',
	esc_attr__( 'Page Title Bar Background', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Select an image to use for the page title bar background. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg', 'url' ) ),
	$page_title_bg_dependency
);

// Add check that regular background image has been added.
$retina_dependency   = $page_title_bg_dependency;
$retina_dependency[] = array(
	'field'      => 'page_title_bar_bg',
	'value'      => '',
	'comparison' => '!=',
);
$this->upload(
	'page_title_bar_bg_retina',
	esc_attr__( 'Page Title Bar Background Retina', 'Avada' ),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Select an image to use for retina devices. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg_retina', 'url' ) ),
	$retina_dependency
);
$this->radio_buttonset(
	'page_title_bar_bg_full',
	esc_html__( '100% Background Image', 'Avada' ),
	array(
		'default' => esc_attr__( 'Default', 'Avada' ),
		'no'      => esc_attr__( 'No', 'Avada' ),
		'yes'     => esc_attr__( 'Yes', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Choose to have the background image display at 100&#37;. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg_full', '', 'yesno' ) ),
	'',
	$retina_dependency
);

$this->radio_buttonset(
	'page_title_bg_parallax',
	esc_html__( 'Parallax Background Image', 'Avada' ),
	array(
		'default' => esc_attr__( 'Default', 'Avada' ),
		'no'      => esc_attr__( 'No', 'Avada' ),
		'yes'     => esc_attr__( 'Yes', 'Avada' ),
	),
	/* translators: Additional description (defaults). */
	sprintf( esc_html__( 'Choose a parallax scrolling effect for the background image. %s', 'Avada' ), Avada()->settings->get_default_description( 'page_title_bg_parallax', '', 'yesno' ) ),
	'',
	$retina_dependency
);

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
