<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 9/2/2016
 * Time: 5:04 PM
 *
 * @package Thrive Quiz Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TQB_Post_types Class.
 */
class TQB_Post_types {

	/**
	 * The name of custom post type for quiz
	 */
	const QUIZ_POST_TYPE = 'tqb_quiz';
	const SPLASH_PAGE_POST_TYPE = Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE;
	const QNA_PAGE_POST_TYPE = Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_QNA;
	const OPTIN_PAGE_POST_TYPE = Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN;
	const RESULTS_PAGE_POST_TYPE = Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS;

	/**
	 * Init function
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 10 );
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {

		register_post_type( self::QUIZ_POST_TYPE, array(
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'query_var'           => false,
			'description'         => 'Thrive Quiz Post',
			'rewrite'             => false,
		) );

		register_post_type( self::SPLASH_PAGE_POST_TYPE, array(
			'publicly_queryable' => true,
			'query_var'          => false,
			'description'        => 'Thrive Quiz Builder - Splash Page',
			'rewrite'            => false,
		) );

		register_post_type( self::QNA_PAGE_POST_TYPE, array(
			'publicly_queryable' => true,
			'query_var'          => false,
			'description'        => 'Thrive Quiz Builder - Q&A Page',
			'rewrite'            => false,
		) );

		register_post_type( self::OPTIN_PAGE_POST_TYPE, array(
			'publicly_queryable' => true,
			'query_var'          => false,
			'description'        => 'Thrive Quiz Builder - OPTIN Page',
			'rewrite'            => false,
		) );

		register_post_type( self::RESULTS_PAGE_POST_TYPE, array(
			'publicly_queryable' => true,
			'query_var'          => false,
			'description'        => 'Thrive Quiz Builder - Results Page',
			'rewrite'            => false,
		) );

	}
}

TQB_Post_types::init();
