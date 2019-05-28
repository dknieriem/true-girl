<?php
/**
 * Created by PhpStorm.
 * User: Danut
 * Date: 6/23/2015
 * Time: 3:28 PM
 */

if ( ! defined( 'THRIVE_SETUP_FLAG' ) ) {
	return;
}

$thrive_theme_options = get_option( 'thrive_theme_options' );
if ( empty( $thrive_theme_options ) ) {
	return;
}

$thrive_theme_blog_layout = isset( $thrive_theme_options['blog_layout'] ) ? $thrive_theme_options['blog_layout'] : null;
if ( ! $thrive_theme_blog_layout ) {
	return;
}

$thrive_theme_post_list_display = isset( $thrive_theme_options['other_show_excerpt'] ) ? $thrive_theme_options['other_show_excerpt'] : null;

/*
 * If the blog layout is grid or masonry
 * and in blog list posts excerpts is set to post content: Thrive Options -> Blog Settings -> In Blog List display: Post Content
 */
if ( ( strstr( $thrive_theme_blog_layout, "grid" ) !== false || strstr( $thrive_theme_blog_layout, "masonry" ) !== false ) && $thrive_theme_post_list_display == 0 ) {
	$thrive_theme_options['other_show_excerpt'] = '1';
	update_option( 'thrive_theme_options', $thrive_theme_options );
}
