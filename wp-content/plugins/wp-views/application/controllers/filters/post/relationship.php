<?php

/**
 * Filter by post relationship. Used in Views and WPAs.
 *
 * @since m2m
 */
class WPV_Filter_Post_Relationship extends WPV_Filter_Base {
	
	const SLUG = 'post-relationship';
	
	/**
	 * @var array
	 */
	private $conditions = array();
	
	/**
	 * @var bool
	 */
	private $is_m2m_available = null;
	
	/**
	 * @var Toolset_Relationship_Definition[]|null
	 */
	private $legacy_relationships = null;
	
	/**
	 * @var null|string[]
	 */
	private $intermediary_post_types = null;
	
	function __construct() {
		if ( $this->is_types_installed() ) {
			$this->gui = new WPV_Filter_Post_Relationship_Gui( $this );
			$this->query = new WPV_Filter_Post_Relationship_Query( $this );
			$this->search = new WPV_Filter_Post_Relationship_Search( $this );
		}
	}
	
	/**
	 * Check if Toolset Types is installed.
	 *
	 * @return bool
	 *
	 * @since m2m
	 */
	public function is_types_installed() {
		if ( ! isset( $this->conditions['types'] ) ) {
			$this->conditions['types'] = new Toolset_Condition_Plugin_Types_Active();
		}
		
		return $this->conditions['types']->is_met();
	}
	
	/**
	 * Check if WPML is installed.
	 *
	 * @return bool
	 *
	 * @since m2m
	 */
	public function is_wpml_installed_and_ready() {
		if ( ! isset( $this->conditions['wpml'] ) ) {
			$this->conditions['wpml'] = new Toolset_Condition_Plugin_Wpml_Is_Active_And_Configured();
		}
		
		return $this->conditions['wpml']->is_met();
	}
	
	/**
	 * Check if m2m is activated. If so, maybe full initialize it.
	 *
	 * @return bool
	 *
	 * @since m2m
	 */
	public function check_and_init_m2m() {
		if ( ! is_null( $this->is_m2m_available ) ) {
			return $this->is_m2m_available;
		}
		
		$this->is_m2m_available = apply_filters( 'toolset_is_m2m_enabled', false );
		if ( $this->is_m2m_available ) {
			do_action( 'toolset_do_m2m_full_init' );
		}
		
		return $this->is_m2m_available;
	}
	
	/**
	 * Get post types that are directly related to the ones returned by the current loop.
	 *
	 * @return string[]
	 *
	 * @since m2m
	 * @todo review the m2m query
	 */
	public function get_closest_related_to_returned_post_types() {
		$returned_post_types = $this->get_returned_post_types();
		$closest_related = array();
		
		if ( $this->check_and_init_m2m() ) {
			$relationship_query = new Toolset_Relationship_Query_V2();
			$conditions = array();
			foreach ( $returned_post_types as $returned_post_type_slug ) {
				$conditions[] = $relationship_query->has_domain_and_type( $returned_post_type_slug, Toolset_Element_Domain::POSTS );
				$conditions[] = $relationship_query->intermediary_type( $returned_post_type_slug );
			}
			$definitions = $relationship_query
				->add( $relationship_query->do_or( $conditions ) )
				->get_results();
			foreach ( $definitions as $definition ) {
				$parent_type = $definition->get_parent_type()->get_types();
				$child_type = $definition->get_child_type()->get_types();
				$closest_related = array_merge( $closest_related, $parent_type );
				$closest_related = array_merge( $closest_related, $child_type );
				if ( null != $definition->get_intermediary_post_type() ) {
					$closest_related[] = $definition->get_intermediary_post_type();
				}
			}
			return $closest_related;
		} else if ( $this->is_types_installed() ) {
			foreach ( $returned_post_types as $returned_post_type_slug ) {
				$parent_parents_array = wpcf_pr_get_belongs( $returned_post_type_slug );
				if ( $parent_parents_array != false && is_array( $parent_parents_array ) ) {
					$closest_related = array_merge( $closest_related, array_values( array_keys( $parent_parents_array ) ) );
				}
			}
			return $closest_related;
		}
		
		return array();
	}
	
	/**
	 * Get relationships defined before activating m2m.
	 *
	 * @return Toolset_Relationship_Definition[]
	 *
	 * @since m2m
	 */
	public function get_legacy_relationships() {
		
		if ( null !== $this->legacy_relationships ) {
			return $this->legacy_relationships;
		}
		
		if ( ! $this->check_and_init_m2m() ) {
			$this->legacy_relationships = array();
			return $this->legacy_relationships;
		}
		
		$relationship_query = new Toolset_Relationship_Query_V2();
		$this->legacy_relationships = $relationship_query
			->add( $relationship_query->is_legacy( true ) )
			->get_results();
		
		return $this->legacy_relationships;
	}
	
	/**
	 * Create an array of ancestors in legacy Types post relationships.
	 *
	 * @param $post_types (array) array of post type slugs to get the relationships from
	 * @param $level (int) depth of recursion, we are hardcoding limiting it to 5
	 *
	 * @return string[]
	 *
	 * @note this function is recursive
	 *
	 * @since 1.6.0
	 */
	public function get_legacy_post_type_ancestors( $post_types = array(), $level = 0 ) {
		$parents_array = array();
		if ( ! is_array( $post_types ) ) {
			// Sometimes, when saving the Content Selection section with no post type selected, this is not an array
			// That can happen when switching to list taxonomy terms or users without selecting a post type first
			return $parents_array;
		}
		if ( 
			function_exists( 'wpcf_pr_get_belongs' ) 
			&& $level < 5 
		) {
			foreach ( $post_types as $post_type_slug ) {
				$this_parents = wpcf_pr_get_belongs( $post_type_slug );
				if ( 
					$this_parents != false 
					&& is_array( $this_parents ) 
				) {
					$new_parents = array_values( array_keys( $this_parents ) );
					$parents_array = array_merge( $parents_array, $new_parents );
					$grandparents_array = $this->get_legacy_post_type_ancestors( $new_parents, $level + 1 );
					$parents_array = array_merge( $parents_array, $grandparents_array );
				}
			}
		}
		return $parents_array;
	}
	
	/**
	 * Get a list of intermediary post types.
	 *
	 * @return string[]
	 *
	 * @since m2m
	 */
	public function get_intermediary_post_types() {
		if ( null != $this->intermediary_post_types ) {
			return $this->intermediary_post_types;
		}
		
		$post_type_query_factory = new Toolset_Post_Type_Query_Factory();
		$intermediary_post_type_query = $post_type_query_factory->create(
			array(
				Toolset_Post_Type_Query::IS_INTERMEDIARY => true,
				Toolset_Post_Type_Query::RETURN_TYPE => 'slug'
			)
		);
		
		$this->intermediary_post_types = $intermediary_post_type_query->get_results();
		
		return $this->intermediary_post_types;
	}
	
	/**
	 * Generate a relationships tree structure given a relationships string
	 * as passed to the shortcode ancestors attribute.
	 *
	 * Each node of the returned array refers to a relationship node in the filter, and contains:
	 * - the key as the involved post type slug, as it can only appear once.
	 * - type: the involved post type slug.
	 * - relationship: the involved relationship slug.
	 * - role: the role of the involved post type in the involved relationship.
	 * - role_target: the role that plays, in the involved relationship, 
	 *   the post type in the next element of the relationships chain.
	 *
	 * @para string $relationships_chain_string
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	public function get_relationship_tree( $relationships_chain_string ) {
		$relationship_tree = array();
		
		$relationships_chain_pieces = explode( '>', $relationships_chain_string );
		foreach ( $relationships_chain_pieces as $piece_index => $relationships_chain_step ) {
			// $piece should be a string with the format 'postType@relationship.role' 
			// but on legacy relationships, in which case it will be just 'postType'.
			$relationships_chain_step_data = explode( '@', $relationships_chain_step );
			
			$ancestor_type = $relationships_chain_step_data[0];
			
			$relationships_chain_step_role_data = isset( $relationships_chain_step_data[1] ) 
				? explode( '.', $relationships_chain_step_data[1] ) 
				: array( '', 'parent' );
			$relationship_tree[ $ancestor_type ] = array(
				'type' => $ancestor_type,
				'relationship' => $relationships_chain_step_role_data[0],
				'role' => isset( $relationships_chain_step_role_data[1] ) 
					? $relationships_chain_step_role_data[1] 
					: 'parent'
			);
			
			if ( empty( $relationship_tree[ $ancestor_type ]['relationship'] ) ) {
				$relationship_tree[ $ancestor_type ]['role_target'] = 'child';
				continue;
			}
			
			if ( ! $this->check_and_init_m2m() ) {
				continue;
			}
			
			$relationship_repository = Toolset_Relationship_Definition_Repository::get_instance();
			$relationship_definition = $relationship_repository->get_definition( $relationship_tree[ $ancestor_type ]['relationship'] );
			if ( null === $relationship_definition ) {
				$relationship_tree[ $ancestor_type ]['role_target'] = Toolset_Relationship_Role::CHILD;
				continue;
			}
			
			if ( isset( $relationships_chain_pieces[ $piece_index + 1 ] ) )  {
				$next_pieces = explode( '@', $relationships_chain_pieces[ $piece_index + 1 ] );
				$next_post_types = array( $next_pieces[0] );
			} else {
				$next_post_types = $this->get_returned_post_types();
			}
			
			$relationship_tree[ $ancestor_type ]['role_target'] = $this->get_ancestor_step_role_target( 
				$relationship_definition, $relationship_tree[ $ancestor_type ]['role'], $next_post_types
			);
			
		}
		
		return $relationship_tree;
	}
	
	
	/**
	 * Get the role of the next step in the relationships tree chain in the relationship of the current step.
	 *
	 * @param Toolset_Relationship_Definition $relationship_definition
	 * @param string $role
	 * @param string[] $next_post_types
	 *
	 * @return string
	 *
	 * @since m2m
	 */
	private function get_ancestor_step_role_target( Toolset_Relationship_Definition $relationship_definition, $role, $next_post_types ) {
		$parent_types = $relationship_definition->get_parent_type()->get_types();
		$intermediary_type = $relationship_definition->get_intermediary_post_type();
		
		switch ( $role ) {
			case Toolset_Relationship_Role::PARENT:
				if ( 
					null != $intermediary_type
					&& in_array( $intermediary_type, $next_post_types ) 
				) {
					return Toolset_Relationship_Role::INTERMEDIARY;
					break;
				}
				return Toolset_Relationship_Role::CHILD;
				break;
			case Toolset_Relationship_Role::CHILD:
				if ( 
					null != $intermediary_type
					&& in_array( $intermediary_type, $next_post_types ) 
				) {
					return Toolset_Relationship_Role::INTERMEDIARY;
					break;
				}
				return Toolset_Relationship_Role::PARENT;
				break;
			case Toolset_Relationship_Role::INTERMEDIARY:
				$parent_types_intersect = array_intersect( $parent_types, $next_post_types );
				if ( count( $parent_types_intersect ) > 0 ) {
					return Toolset_Relationship_Role::PARENT;
				} else {
					return Toolset_Relationship_Role::CHILD;
				}
				break;
			default:
				return Toolset_Relationship_Role::CHILD;
				break;
		}
		
		return Toolset_Relationship_Role::CHILD;
	}
	
}