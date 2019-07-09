<?php

/**
 * Main AJAX call controller for Views.
 *
 * This class can be used in any way only after the Common Library is loaded.
 *
 * Please read the important usage instructions for the superclass:
 *
 * @inheritdoc
 *
 * @since m2m
 */
class WPV_Ajax extends Toolset_Ajax {
	const HANDLER_CLASS_PREFIX = 'WPV_Ajax_Handler_';

	/**
	 * Action names.
	 *
	 * @var string
	 * @since m2m
	 */
	// Filters update and delete
	const CALLBACK_FILTER_POST_RELATIONSHIP_UPDATE = 'filter_post_relationship_update';
	const CALLBACK_FILTER_POST_RELATIONSHIP_DELETE = 'filter_post_relationship_delete';
	const CALLBACK_FILTER_RELATIONSHIP = 'filter_relationship_action';
	const CALLBACK_UPDATE_CONTENT_SELECTION = 'update_content_selection';
	
	const CALLBACK_GET_RELATIONSHIPS_DATA = 'get_relationships_data';


	/**
	 * Legacy nonce for query type view page
	 *
	 * @var string
	 * @since m2m
	 */
	const LEGACY_VIEW_QUERY_TYPE_NONCE = 'view_query_type_nonce';
	
	
	/**
	 * List of callbacks.
	 *
	 * @var array
	 * @since m2m
	 */
	private static $callbacks = array(
		self::CALLBACK_FILTER_POST_RELATIONSHIP_UPDATE,
		self::CALLBACK_FILTER_POST_RELATIONSHIP_DELETE,
		self::CALLBACK_FILTER_RELATIONSHIP,
		self::CALLBACK_UPDATE_CONTENT_SELECTION,
		
		self::CALLBACK_GET_RELATIONSHIPS_DATA
	);
	
	
	/**
	 * @var WPV_Ajax
	 * @since m2m
	 */
	private static $views_instance;


	public static function get_instance() {
		if( null === self::$views_instance ) {
			self::$views_instance = new self();
		}
		return self::$views_instance;
	}


	/**
	 * @inheritdoc
	 *
	 * @param bool $capitalized Capitalized text?.
	 * @return string
	 * @since m2m
	 */
	protected function get_plugin_slug( $capitalized = false ) {
		return ( $capitalized ? 'WPV' : 'wpv' );
	}


	/**
	 * @inheritdoc
	 * @return array
	 * @since m2m
	 */
	protected function get_callback_names() {
		return self::$callbacks;
	}


	/**
	 * Handles all initialization of everything except AJAX callbacks itself that is needed when
	 * we're DOING_AJAX.
	 *
	 * Since this is executed on every AJAX call, make sure it's as lightweight as possible.
	 *
	 * @since 2.1
	 */
	protected function additional_ajax_init() {
		// TODO Nothing yet.
	}
}
