<?php

/**
 * Manage the wpv-control-post-ancestor shortcode and its legacy wpv-control-item alias, when m2m is active.
 *
 * @since m2m
 */
class WPV_Shortcode_Control_Post_Ancestor_From_M2m extends WPV_Shortcode_Control_Post_Ancestor implements WPV_Shortcode_Interface {
	
	/**
	 * Get the shortcode output value.
	 *
	 * @param $atts
	 * @param $content
	 *
	 * @return string
	 *
	 * @since m2m
	 */
	public function get_value( $atts, $content = null ) {
		return parent::get_value( $atts, $content );
	}
	
	/**
	 * Get the posts to include in any ancestor selector but the tree roof.
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	protected function get_relationship_tree_item_options() {
		global $wpdb;
		$ancestor_position_in_tree = array_keys( $this->relationship_pieces, $this->user_atts['ancestor_type'] );
		if ( empty( $ancestor_position_in_tree ) ) {
			array();
		}
		$ancestor_ancestors = array_slice( $this->relationship_pieces, 0, $ancestor_position_in_tree[0] );

		foreach ( $ancestor_ancestors as $ancestor_item )  {
			$ancestor_item_data = explode( '@', $ancestor_item );
			$this->class_array[] = 'js-wpv-' . $ancestor_item_data[0] . '-watch';
		}
		
		$ancestor_direct_ancestor = end( $ancestor_ancestors );
		$ancestor_direct_ancestor_pieces = explode( '@', $ancestor_direct_ancestor );
		$ancestor_direct_ancestor_relationship_pieces = isset( $ancestor_direct_ancestor_pieces[1] ) 
				? explode( '.', $ancestor_direct_ancestor_pieces[1] ) 
				: array( '', 'parent' );
		$ancestor_direct_ancestor_data = $this->relationship_tree[ $ancestor_direct_ancestor_pieces[0] ];
		
		$ancestor_selected = array();
		if (
			isset( $_GET[ $this->user_atts['url_param'] . '-' . $ancestor_direct_ancestor_data['type'] ] )
			&& ! empty( $_GET[ $this->user_atts['url_param'] . '-' . $ancestor_direct_ancestor_data['type'] ] )
			&& $_GET[ $this->user_atts['url_param'] . '-' . $ancestor_direct_ancestor_data['type'] ] != array( 0 )
		) {
			$ancestor_selected = $_GET[ $this->user_atts['url_param'] . '-' . $ancestor_direct_ancestor_data['type'] ];
			$ancestor_selected = is_array( $ancestor_selected ) ? $ancestor_selected : array( $ancestor_selected );
		}
		
		if ( empty( $ancestor_selected ) ) {
			return array();
		}
		
		if ( empty( $ancestor_direct_ancestor_data['relationship'] ) ) {
			// Legacy relationship, so current is child and direct ancestor is parent
			$relationship_query = new Toolset_Relationship_Query_V2();
			$relationship_query->do_not_add_default_conditions();
			$definitions = $relationship_query
				->add( $relationship_query->do_and( 
					$relationship_query->is_legacy( true),
					$relationship_query->do_and( 
						$relationship_query->has_domain_and_type( $ancestor_direct_ancestor_data['type'], Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Parent() ),
						$relationship_query->has_domain_and_type( $this->ancestor_data['type'], Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Child() )
					) 
				) )
				->get_results();
			if ( empty( $definitions ) ) {
				return array();
			}
			$definition = reset( $definitions );
		} else {
			$relationship_repository = Toolset_Relationship_Definition_Repository::get_instance();
			$definition = $relationship_repository->get_definition( $ancestor_direct_ancestor_data['relationship'] );
		}
		
		if ( ! $definition instanceof Toolset_Relationship_Definition ) {
			return array();
		}
		
		$association_query = new Toolset_Association_Query_V2();
		$association_query->add( $association_query->relationship_slug( $definition->get_slug() ) );
		$association_query->limit( PHP_INT_MAX );
		$association_query_conditions = array();
		foreach ( $ancestor_selected as $ancestor_selected_item ) {
			$association_query_condition_role = Toolset_Relationship_Role::role_from_name( $ancestor_direct_ancestor_data['role'] );
			$association_query_conditions[] = $association_query->element_id_and_domain( $ancestor_selected_item, Toolset_Element_Domain::POSTS, $association_query_condition_role );
		}
		$association_query->add( $association_query->do_or( $association_query_conditions ) );
		$associations = $association_query->get_results();
		
		if ( empty( $associations ) ) {
			return array();
		}
		
		$ancestor_ids = array();
		foreach ( $associations as $association ) {
			$association_ancestor_role = Toolset_Relationship_Role::role_from_name( $ancestor_direct_ancestor_data['role_target'] );
			$ancestor_ids[] = $association->get_element_id( $association_ancestor_role );
		}
		if ( empty( $ancestor_ids ) ) {
			return array();
		}

		$ancestor_ids_count = count( $ancestor_ids );
		$ancestor_ids_placeholders = array_fill( 0, $ancestor_ids_count, '%d' );
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title
				FROM {$wpdb->posts}
				WHERE post_status = 'publish' AND ID IN (" . implode( ",", $ancestor_ids_placeholders ) . ")
				ORDER BY {$this->orderby} {$this->order}",
				$ancestor_ids
			)
		);
	}
	
	/**
	 * Get the cache target data for search dependency and counters.
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	protected function set_target_for_cache_extended_for_post_relationship() {
		return array( 'relationship' => $this->relationship_tree );
	}
}