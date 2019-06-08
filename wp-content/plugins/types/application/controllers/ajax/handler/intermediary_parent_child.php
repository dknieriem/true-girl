<?php

/**
 * Class Types_Ajax_Handler_Intermediary_Parent_Child
 *
 * @since 3.0
 */
class Types_Ajax_Handler_Intermediary_Parent_Child extends Types_Ajax_Handler_Post_Reference_Field {
	/**
	 * @param array $arguments Original action arguments.
	 *
	 * @return mixed
	 */
	function process_call( $arguments ) {
		$this->get_am()
		     ->ajax_begin(
			     array(
				     'nonce' => $this->get_am()->get_action_js_name( Types_Ajax::CALLBACK_INTERMEDIARY_PARENT_CHILD ),
				     'capability_needed' => 'edit_posts'
			     )
		     );

		// Read and validate input
		$action = sanitize_text_field( toolset_getpost( 'intermediary_action' ) );

		// route action
		return $this->route( $action );
	}

	/**
	 * Route ajax calls
	 *
	 * @param $action
	 *
	 * @return array
	 */
	protected function route( $action ) {
		switch ( $action ) {
			case 'json_intermediary_parent_child_posts':
				return $this->json_posts();
			case 'json_save_association':
				return $this->json_save_association();
		}
	}

	/**
	 * Types_Ajax_Handler_Post_Reference_Field::json_posts()
	 * Just added here for better class overview.
	 */
	protected function json_posts() {
		$post_type      = sanitize_text_field( toolset_getpost( 'post_type' ) );
		$search         = sanitize_text_field( toolset_getpost( 'search' ) );
		$page           = sanitize_text_field( toolset_getpost( 'page' ) );
		$post_id        = sanitize_text_field( toolset_getpost( 'post_id' ) );
		$posts_per_page = Types_Field_Type_Post_View_Backend_Display::SELECT2_POSTS_PER_LOAD;

		global $wpdb;

		$prepare_values = array( $post_type );

		// SEARCH
		$search_where = " AND p.post_status IN ('publish', 'draft') ";

		// user access
		$user = wp_get_current_user();
		$user_access = new \OTGS\Toolset\Types\User\Access( $user );
		$user_can_edit_any = $user_access->canEditAny( $post_type );
		$user_can_edit_own = $user_access->canEditOwn( $post_type );

		if( ! $user_can_edit_any && ! $user_can_edit_own ) {
			wp_send_json(
				array(
					'items'              => array(),
					'total_count'        => 0,
					'incomplete_results' => false,
					'posts_per_page'     => $posts_per_page,
				)
			);
		}

		if( ! $user_can_edit_any && $user_can_edit_own ) {
			$search_where .= " AND p.post_author = " . $user->ID . " ";
		}

		if( $post_id ) {
			// don't display current post in list of assignable posts
			$search_where .= " AND p.ID != %d ";
			$prepare_values[] = $post_id;
		}

		if ( $search != '' ) {
			if ( method_exists( $wpdb, 'esc_like' ) ) {
				$search_term = '%' . $wpdb->esc_like( $search ) . '%';
			} else {
				$search_term = '%' . like_escape( esc_sql( $search ) ) . '%';
			}
			$search_where     .= " AND p.post_title LIKE %s ";
			$prepare_values[] = $search_term;
			$orderby = ' ORDER BY p.post_title ';
		} else {
			$orderby = ' ORDER BY p.post_date DESC ';
		}

		// PAGE
		if ( preg_match( '/^\d+$/', $page ) ) {
			$prepare_values[] = ( (int) $page - 1 ) * $posts_per_page;
		} else {
			$prepare_values[] = 0;
		}

		$prepare_values[] = $posts_per_page;

		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS
					p.ID as id, 
					p.post_title as text, 
					p.post_type as type, 
					p.post_status as status 
				FROM {$wpdb->posts} p
				WHERE p.post_type IN ('%s')
				{$search_where}
				{$orderby}
				LIMIT %d, %d",
				$prepare_values
			)
		);

		$posts_count = $wpdb->get_var( 'SELECT FOUND_ROWS()' );

		foreach( $posts as $key => $post ) {
			$posts[$key]->url = get_permalink( $post->id );
		}

		wp_send_json(
			array(
				'items'              => $posts,
				'total_count'        => $posts_count,
				'incomplete_results' => $posts_count > $posts_per_page,
				'posts_per_page'     => $posts_per_page,
			)
		);
	}

	/**
	 *
	 */
	private function json_save_association() {
		try {
			// get user data
			$intermediary_id   = sanitize_text_field( toolset_getpost( 'intermediary_id' ) );
			$parent_id         = sanitize_text_field( toolset_getpost( 'parent_id' ) );
			$child_id          = sanitize_text_field( toolset_getpost( 'child_id' ) );

			// response handler
			$response_handler = new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\ResponseHandler();
			$response_handler->addResponse(
				new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\ResponseAssociationDelete(
					new Toolset_Association_Persistence()
				)
			);
			$response_handler->addResponse(
				new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\ResponseAssociationMissingData()
			);
			$response_handler->addResponse(
				new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\ResponseAssociationConflict()
			);
			$response_handler->addResponse(
				new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\ResponseAssociationSave(
					new Toolset_Association_Persistence(),
					new Toolset_Association_Factory()
				)
			);

			/** @var \OTGS\Toolset\Types\Model\Post\Intermediary\Request $request */
			$request = new \OTGS\Toolset\Types\Model\Post\Intermediary\Request(
				new Toolset_Element_Factory(),
				Toolset_Post_Type_Repository::get_instance(),
				new Toolset_Association_Query_V2(),
				new Toolset_Relationship_Query_V2(),
				new Toolset_Relationship_Role_Parent(),
				new Toolset_Relationship_Role_Child(),
				new Toolset_Relationship_Role_Intermediary()
			);

			$request->setIntermediaryId( $intermediary_id );
			$request->setParentId( $parent_id );
			$request->setChildId( $child_id );

			$result  = new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\Result();

			wp_send_json( $response_handler->response( $request, $result ) );

		} catch ( Toolset_Element_Exception_Element_Doesnt_Exist $e ) {
			// some element could not be loaded, probably DOM invalid
			wp_send_json( new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\Result(
				'', \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\Result::RESULT_DOM_ERROR
			) );

		} catch ( Exception $e ) {
			if( defined( 'WP_DEBUG') && WP_DEBUG ) {
				error_log( $e->getMessage() );
			}

			wp_send_json( new \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\Result(
				'', \OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary\Result::RESULT_SYSTEM_ERROR
			) );
		}
	}
}