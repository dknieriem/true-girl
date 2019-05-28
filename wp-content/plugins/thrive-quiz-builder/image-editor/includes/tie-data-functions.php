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
 * Get all post of type thrive_images and returns them as array
 *
 * @param WP_Post|int $parent
 *
 * @return array
 */
function tie_get_images( $parent ) {

	if ( ! ( $parent instanceof WP_Post ) && ! is_numeric( $parent ) ) {
		return array();
	}

	$args = array(
		'post_type'   => TIE_Post_Types::THRIVE_IMAGE,
		'post_parent' => is_object( $parent ) ? $parent->ID : $parent,
	);

	$query = new WP_Query( $args );

	foreach ( $query->posts as $post ) {
		$post->editor_url = tie_get_editor_url( $post->ID );
		$image            = new TIE_Image( $post );
		$post->settings   = $image->get_settings()->get_data();
		$post->image_url  = $image->get_image_url();
	}

	return $query->posts;
}

/**
 * Save Image
 *
 * @param array $model WP post object.
 *
 * @return false|int id of model or false on error
 */
function tie_save_image( $model ) {

	if ( ! empty( $model['ID'] ) ) {
		$item = get_post( $model['ID'] );
		if ( $item && get_post_type( $item ) === TIE_Post_Types::THRIVE_IMAGE ) {
			$id = wp_update_post( $model );
		}
	} else {
		$default = array(
			'post_type'   => TIE_Post_Types::THRIVE_IMAGE,
			'post_status' => 'publish',
		);

		$id = wp_insert_post( array_merge( $default, $model ) );
	}

	if ( empty( $id ) || is_wp_error( $id ) || 0 === $id ) {
		return false;
	}

	return $id;
}

/**
 * @param int|WP_Post $post
 *
 * @return bool
 */
function tie_delete_image( $post ) {

	if ( $post instanceof WP_Post ) {
		$post = $post->ID;
	}

	// Delete the uploaded image
	$post_obj = get_post( $post );
	tie_delete_image_file( $post_obj->post_parent );

	$deleted = wp_delete_post( $post, true );

	return ( is_wp_error( $deleted ) || $deleted !== false ) && ! is_null( $deleted );
}

/**
 * Deletes the image file stored in WordPress uploads thrive-quiz-builder folder
 *
 * @param int $quiz_id
 */
function tie_delete_image_file( $quiz_id = 0 ) {
	$upload_dir = wp_upload_dir();
	$file_path  = $upload_dir['basedir'] . '/' . Thrive_Quiz_Builder::UPLOAD_DIR_CUSTOM_FOLDER . '/' . $quiz_id . '.png';
	if ( is_file( $file_path ) ) {
		unlink( $file_path );
	}
	array_map( 'unlink', glob( $upload_dir['basedir'] . '/' . Thrive_Quiz_Builder::UPLOAD_DIR_CUSTOM_FOLDER . '/user_badges/*-' . $quiz_id . '.png' ) );
}

function tie_get_image( $id ) {
	$image = get_post( $id );

	if ( empty( $image ) || $image->post_type !== TIE_Post_Types::THRIVE_IMAGE ) {
		return null;
	}

	$image->editor_url = ( tie_get_editor_url( $image->ID ) );

	$img             = new TIE_Image( $image->ID );
	$image->settings = $img->get_settings()->get_data();

	return $image;
}
