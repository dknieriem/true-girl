<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-image-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Wrapper over the wp_enqueue_script function.
 * It will add the plugin version to the script source if no version is specified.
 *
 * @param string $handle Name of the script. Should be unique.
 * @param string $src Full URL of the script, or path of the script relative to the WordPress root directory.
 * @param array $deps Optional. An array of registered script handles this script depends on. Default empty array.
 * @param bool $ver Optional. String specifying script version number, if it has one, which is added to the URL.
 * @param bool $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
 */
function tie_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {

	if ( false === $ver ) {
		$ver = Thrive_Image_Editor::VERSION;
	}

	if ( defined( 'TVE_DEBUG' ) && TVE_DEBUG ) {
		$src = preg_replace( '#\.min\.js$#', '.js', $src );
	}

	wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

/**
 * Wrapper over the wp_enqueue_style function.
 * it will add the plugin version to the style link if no version is specified
 *
 * @param string $handle Name of the stylesheet. Should be unique.
 * @param string|bool $src Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
 * @param array $deps Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
 * @param bool|string $ver Optional. String specifying stylesheet version number.
 * @param string $media Optional. The media for which this stylesheet has been defined.
 */
function tie_enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
	if ( false === $ver ) {
		$ver = Thrive_Image_Editor::VERSION;
	}
	wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}

/**
 * Returns the url for editing screen.
 *
 * If no post id is set then will use native WP functions to get the editing URL for the piece of content that's currently being edited
 *
 * @param bool $post_id
 *
 * @return string
 */
function tie_get_editor_url( $post_id = false ) {
	/**
	 * we need to make sure that if the admin is https, then the editor link is also https, otherwise any ajax requests through wp ajax api will not work
	 */
	$admin_ssl = strpos( admin_url(), 'https' ) === 0;
	$post_id   = $post_id ? $post_id : get_the_ID();
	/*
     * We need the post to complete the full arguments for the preview_post_link filter
     */
	$post        = get_post( $post_id );
	$editor_link = set_url_scheme( get_permalink( $post_id ) );
	$editor_link = ( apply_filters( 'preview_post_link', add_query_arg( apply_filters( 'tie_edit_link_query_args', array( Thrive_Image_Editor::EDITOR_FLAG => 'true' ), $post_id ), $editor_link ), $post ) );

	return $admin_ssl ? str_replace( 'http://', 'https://', $editor_link ) : $editor_link;
}
