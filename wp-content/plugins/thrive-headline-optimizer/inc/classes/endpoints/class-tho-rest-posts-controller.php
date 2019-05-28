<?php

/**
 * Created by PhpStorm.
 * User: Laura
 * Date: 05-Feb-16
 * Time: 16:36
 */
class THO_REST_Posts_Controller extends THO_REST_Controller {

	public $base = 'posts';

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$id = $request->get_param( 'id' );

		if ( empty( $id ) ) {
			return new WP_Error( 'code', __( 'Invalid arguments!', THO_TRANSLATE_DOMAIN ) );
		}

		$title = get_the_title( $id );

		$data = array(
			'title' => empty( $title ) ? __( 'Post', THO_TRANSLATE_DOMAIN ) : $title
		);

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$per_page      = $request->get_param( 'itemsPerPage' );
		$page          = $request->get_param( 'page' );
		$exclude_posts = $request->get_param( 'exclude' );
		$test_ids      = tho_get_running_test_ids();

		$filters = array(
			'exclude'     => array_merge( ( empty( $exclude_posts ) ? array() : $exclude_posts ), $test_ids ),
			'per_page'    => empty( $per_page ) ? 10 : $per_page,
			'page'        => empty( $page ) ? 1 : $page,
			'post_types'  => $request->get_param( 'post_types' ),
			'search_by'   => trim( wp_unslash( $request->get_param( 'search_by' ) ) ),
			'select'      => array( 'ID' ),
			'post_status' => 'publish'
		);

		/* @var Tho_Db */
		global $thodb;

		$items       = $thodb->get_posts( $filters );
		$total_items = $thodb->get_posts( $filters, true );

		$data = array();
		foreach ( $items as $item ) {
			$data[] = $this->prepare_response_for_collection( $item );
		}
		$res = array(
			'items'       => $data,
			'total_count' => $total_items
		);

		return new WP_REST_Response( $res, 200 );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	public function get_item_permissions_check( $request ) {
		return true;//current_user_can( 'manage_options' );
	}

	/**
	 * Prepare a response for inserting into a collection.
	 *
	 * @param $item Object Response object.
	 *
	 * @return array Response data, ready for insertion into collection data.
	 */
	public function prepare_response_for_collection( $item ) {
		$post = array(
			'id'        => $item->ID,
			'title'     => get_the_title( $item->ID ),
			'post_type' => get_post_type( $item->ID )
		);

		return $post;
	}
}