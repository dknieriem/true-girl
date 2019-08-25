<?php

// Register custom theme settings
add_action('customize_register','understrap_custom_customizer');
function understrap_custom_customizer( $wp_customize ) {

//Theme colors

$wp_customize->add_setting( 'main_color', array(
  'default' => '#f72525',
  'sanitize_callback' => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'main_color_control', array(
  'label' => __( 'Main Color', 'understrap-child' ),
  'section' => 'colors',
  'settings' => 'main_color',
) ) );

$wp_customize->add_setting( 'accent_color', array(
  'default' => '#f72525',
  'sanitize_callback' => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'accent_color_control', array(
  'label' => __( 'Accent Color', 'understrap-child' ),
  'section' => 'colors',
  'settings' => 'accent_color',
) ) );

$wp_customize->add_setting( 'text_color', array(
  'default' => '#212529',
  'sanitize_callback' => 'sanitize_hex_color',
) );

$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'text_color_control', array(
  'label' => __( 'Text Color', 'understrap-child' ),
  'section' => 'colors',
  'settings' => 'text_color',
) ) );



//TODO: More. see https://developer.wordpress.org/themes/customize-api/customizer-objects/

//Theme fonts

//Typography Section
$wp_customize->add_section( 'typography', array(
  'title' => __( 'Typography' ),
  'description' => __( 'Set theme fonts here' ),
  'priority' => 50,
  'capability' => 'edit_theme_options',
) );

//$body-font:"Open Sans";
//$header-font:"Prata";
$wp_customize->add_setting( 'body_font', array(
    'default' => 'GoudySans Md BT',
    'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control('body_font_control', array(
    'type' => 'select',
    'choices' => array(
        'GoudySans Md BT' => 'GoudySans Md BT',
        'Open Sans' => 'Open Sans',
        'Prata' => 'Prata',
        'Shark Random Funnyness' => 'Shark Random Funnyness',
    ),
    'label' => __( 'Body Font', 'understrap-child' ),
    'section' => 'typography',
    'settings' => 'body_font',
) );

//$body-font:"Open Sans";
//$header-font:"Prata";
$wp_customize->add_setting( 'header_font', array(
    'default' => 'Prata',
    'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control('header_font_control', array(
    'type' => 'select',
    'choices' => array(
        'GoudySans Md BT' => 'GoudySans Md BT',
        'Open Sans' => 'Open Sans',
        'Prata' => 'Prata',
        'Shark Random Funnyness' => 'Shark Random Funnyness',
    ),
    'label' => __( 'Header Font', 'understrap-child' ),
    'section' => 'typography',
    'settings' => 'header_font',
) );


//Theme logo


}


function understrap_custom_css()
{
    ?>
    <!-- text color -->
     <style type="text/css">
        body, kbd, pre, .form-control-plaintext, .btn-primary, .btn-primary.disabled, .btn-primary:disabled, .btn-warning, .btn-warning.disabled, .btn-warning:disabled, .btn-warning:not(:disabled):not(.disabled):active, .btn-warning:not(:disabled):not(.disabled).active, .show > .btn-warning.dropdown-toggle, .btn-light, .btn-light.disabled, .btn-light:disabled, .btn-light:not(:disabled):not(.disabled):active, .btn-light:not(:disabled):not(.disabled).active, .show > .btn-light.dropdown-toggle .btn-outline-primary:hover, .wpcf7 input:hover[type=submit], .btn-outline-primary:not(:disabled):not(.disabled):active, .wpcf7 input:not(:disabled):not(.disabled):active[type=submit], .btn-outline-primary:not(:disabled):not(.disabled).active, .wpcf7 input:not(:disabled):not(.disabled).active[type=submit], .show > .btn-outline-primary.dropdown-toggle, .wpcf7 .show > input.dropdown-toggle[type=submit], .btn-outline-warning:hover, .btn-outline-warning:not(:disabled):not(.disabled):active, .btn-outline-warning:not(:disabled):not(.disabled).active, .show > .btn-outline-warning.dropdown-toggle, .btn-outline-light:hover, .btn-outline-light:not(:disabled):not(.disabled):active, .btn-outline-light:not(:disabled):not(.disabled).active, .show > .btn-outline-light.dropdown-toggle, .dropdown-menu, .dropdown-item, .dropdown-item-text, .badge-primary,  .badge-primary[href]:hover, .badge-primary[href]:focus , .badge-warning, .badge-warning[href]:hover, .badge-warning[href]:focus, .badge-light, .badge-light[href]:hover, .badge-light[href]:focus, .list-group-item-action:active, .popover-body, .text-body, .navbar-dark .navbar-nav .dropdown-menu .nav-link { color: <?php echo get_theme_mod('text_color', '#212529'); ?>; }
     </style>
     <!-- body font -->
     <style type="text/css">
        body, .dotted-hr__text, .event-philly__title, .footer__heading, .podcast-row__resources-header, .post-thumb__title, .testimonial__source {
            font-family: <?php echo get_theme_mod('body_font', 'Open Sans'); ?>; }
    </style>
    <!-- header font -->
    <style type="text/css">
        .hero__header, .icon-item__title, .image-item__title, .page-heading__tagline , .podcast-row__title, .pull-quote__content, .sidebar-cta-card__heading, .testimonial, .heading, h1, h2, h3, h4, .page-content h1, {
            font-family: <?php echo get_theme_mod('header_font', 'Prata'); ?>; }
    </style>
    <?php
}
add_action( 'wp_head', 'understrap_custom_css');

add_action( 'wp_enqueue_scripts', 'understrap_remove_scripts', 20 );
function understrap_remove_scripts() {
    wp_dequeue_style( 'understrap-styles' );
    wp_deregister_style( 'understrap-styles' );

    wp_dequeue_script( 'understrap-scripts' );
    wp_deregister_script( 'understrap-scripts' );

    // Removes the parent themes stylesheet and scripts from inc/enqueue.php
}

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {

	// Get the theme data
    $the_theme = wp_get_theme();
    $theme_version = $the_theme->get( 'Version' );
    $css_version = $theme_version . '.' . filemtime(get_stylesheet_directory() . '/css/child-theme.min.css');
    wp_enqueue_style( 'child-understrap-styles', get_stylesheet_directory_uri() . '/css/child-theme.min.css', array(), $css_version );
    wp_enqueue_script( 'jquery');
    wp_enqueue_script( 'popper-scripts', get_template_directory_uri() . '/js/popper.min.js', array(), false);
    wp_enqueue_script( 'typed', get_stylesheet_directory_uri() . '/js/typed.js', array(), false);
    wp_enqueue_script( 'child-understrap-scripts', get_stylesheet_directory_uri() . '/js/child-theme.min.js', array(), $the_theme->get( 'Version' ), true );
    
    wp_enqueue_script( 'rellax', get_stylesheet_directory_uri() . '/js/rellax.min.js', array(), false, true);
    wp_enqueue_script( 'custom-scripts', get_stylesheet_directory_uri() . '/js/custom.js', array('rellax'), false, true);
    
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}

function add_child_theme_textdomain() {
    load_child_theme_textdomain( 'understrap-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'add_child_theme_textdomain' );

// Exclude images from search results - WordPress
add_action( 'init', 'exclude_images_from_search_results' );
function exclude_images_from_search_results() {
	global $wp_post_types;
 
	$wp_post_types['attachment']->exclude_from_search = true;
}

/* CUSTOMIZE EXCERPT READ MORE CONTENT
================================================== */

function understrap_all_excerpts_get_more_link( $post_excerpt ) {

	return $post_excerpt;
}

add_filter( 'wp_trim_excerpt', 'understrap_all_excerpts_get_more_link' );

// Filter to add teal class to body element on specific page templates
add_filter( 'body_class', 'custom_class' );
function custom_class( $classes ) {
    if ( is_home() || is_search() || is_page_template('page-templates/search-page.php') ) {
        $classes[] = 'teal';
    }
    elseif (is_archive() || is_page('calendar') || is_singular('event') || is_page_template('page-templates/no-hero.php') || is_page_template('page-templates/no-hero-advanced.php')){
        $classes[] = 'peach';
    }
    elseif (is_page_template('page-templates/storefront.php') || is_page_template('page-templates/podcast.php') || is_singular('post') || is_page_template('page-templates/no-hero-advanced-white.php') ) {
        $classes[] = 'white-flourish';
    }
    return $classes;
}

add_filter( 'su/data/shortcodes', 'remove_su_shortcodes' );

/**
 * Filter to modify original shortcodes data
 *
 * @param array   $shortcodes Default shortcodes
 * @return array Modified array
 */
function remove_su_shortcodes( $shortcodes ) {

	// Remove button shortcode
    unset( $shortcodes['tabs'] );
    unset( $shortcodes['tab'] );
    unset( $shortcodes['frame'] );
    unset( $shortcodes['column'] );
    //unset( $shortcodes['lightbox_content'] );
    unset( $shortcodes['screenr'] );
    unset( $shortcodes['spoiler'] );
    unset( $shortcodes['accordion'] );
    unset( $shortcodes['divider'] );
    unset( $shortcodes['spacer'] );
    unset( $shortcodes['highlight'] );
    unset( $shortcodes['label'] );
    unset( $shortcodes['quote'] );
    unset( $shortcodes['pullquote'] );
    unset( $shortcodes['dropquote'] );
    unset( $shortcodes['row'] );
    unset( $shortcodes['list'] );
    unset( $shortcodes['dropcap'] );
    unset( $shortcodes['service'] );
    unset( $shortcodes['expand'] );
    //unset( $shortcodes['lightbox'] );
    unset( $shortcodes['private'] );
    unset( $shortcodes['youtube'] );
    unset( $shortcodes['vimeo'] );
    unset( $shortcodes['dailymotion'] );
    unset( $shortcodes['audio'] );
    //unset( $shortcodes['video'] );
    unset( $shortcodes['table'] );
    unset( $shortcodes['permalink'] );
    unset( $shortcodes['members'] );
    unset( $shortcodes['guests'] );
    unset( $shortcodes['menu'] );
    unset( $shortcodes['siblings'] );
    unset( $shortcodes['document'] );
    unset( $shortcodes['slider'] );
    unset( $shortcodes['carousel'] );
    unset( $shortcodes['custom_gallery'] );
    unset( $shortcodes['youtube_advanced'] );
    unset( $shortcodes['feed'] );
    unset( $shortcodes['subpages'] );
    unset( $shortcodes['animate'] );
    //unset( $shortcodes['gmap'] );
    unset( $shortcodes['posts'] );
    unset( $shortcodes['dummy_text'] );
    unset( $shortcodes['dummy_image'] );
    unset( $shortcodes['meta'] );
    unset( $shortcodes['user'] );
    unset( $shortcodes['footnote'] );
    unset( $shortcodes['scheduler'] );
    unset( $shortcodes['post'] );
    unset( $shortcodes['template'] );
    unset( $shortcodes['qrcode'] );
    unset( $shortcodes['tooltip'] );
    unset( $shortcodes['note'] );
    unset( $shortcodes['box'] );
    

	// Return modified data
	return $shortcodes;

}

// https://gist.github.com/jlengstorf/ce2470df87fd9a892f68

function setup_theme(  ) {
    // Theme setup code...
    
    // Filters the oEmbed process to run the responsive_embed() function
    add_filter('embed_oembed_html', 'responsive_embed', 10, 3);

    // Sets the 'mute' attribute on autoplaying videos for modern browsers
    add_filter('wp_video_shortcode', 'mute_autoplay_video', 10, 5);

}
add_action('after_setup_theme', 'setup_theme');
/**
 * Adds a responsive embed wrapper around oEmbed content
 * @param  string $html The oEmbed markup
 * @param  string $url  The URL being embedded
 * @param  array  $attr An array of attributes
 * @return string       Updated embed markup
 */
function responsive_embed($html, $url, $attr) {
    return $html!=='' ? '<div class="embed-container">'.$html.'</div>' : '';
}

function mute_autoplay_video($output, $atts, $video, $post_id, $library){
    if ( false !== strpos( $output, 'autoplay="1"' ) ) {
        $output = str_replace( '<video', '<video muted', $output );
    }
    return $output;
}

function custom_query_vars_filter($vars) {
    $vars[] .= 'topic';
    return $vars;
  }

add_filter( 'query_vars', 'custom_query_vars_filter' );

function understrap_child_mime_types($mimes) {
 $mimes['svg'] = 'image/svg+xml';
 $mimes['js'] = 'application/javascript';
 return $mimes;
}

add_filter('upload_mimes', 'understrap_child_mime_types');

function get_excerpt( $count ) {
    $permalink = get_permalink($post->ID);
    $excerpt = get_the_content();
    $excerpt = strip_tags($excerpt);
    $excerpt = substr($excerpt, 0, $count);
    $excerpt = substr($excerpt, 0, strripos($excerpt, " "));
    $excerpt = $excerpt.'...';
return $excerpt;
}

//set up new widgets
require get_stylesheet_directory() . '/inc/widgets.php';
