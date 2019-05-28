<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-image-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TIE_Post_Types {

	const THRIVE_IMAGE = 'thrive_image';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_images' ) );
	}

	public static function register_post_images() {
		if ( post_type_exists( 'thrive_custom_image' ) ) {
			return;
		}

		register_post_type( 'thrive_image', array(
			'publicly_queryable'  => true,
			'query_var'           => false,
			'exclude_from_search' => true,
			'rewrite'             => false,
			'hierarchical'        => true,
		) );
	}
}

TIE_Post_Types::init();
