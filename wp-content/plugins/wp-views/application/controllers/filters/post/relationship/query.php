<?php

/**
 * Query component of the filter by post relationship.
 *
 * This applies the query filter for Views and WPAs.
 *
 * @since m2m
 */
class WPV_Filter_Post_Relationship_Query {
	
	/**
	 * @var WPV_Filter_Base
	 */
	private $filter = null;
	
	function __construct( WPV_Filter_Base $filter ) {
		$this->filter = $filter;
		
		if ( $this->filter->is_types_installed() ) {
			add_action( 'init', array( $this, 'load_hooks' ) );
		}
	}
	
	/**
	 * Load the hooks to register the filter query.
	 *
	 * @since m2m
	 */
	public function load_hooks() {
		add_filter( 'wpv_filter_query', array( $this, 'filter_query' ), 11, 3 );
		add_action( 'wpv_action_apply_archive_query_settings', array( $this, 'filter_archive_query' ), 40, 3 );
		
		add_filter( 'wpv_filter_requires_current_page', array( $this, 'requires_current_page' ), 10, 2 );
		add_filter( 'wpv_filter_requires_parent_post', array( $this, 'requires_parent_post' ), 20, 2 );
		add_filter( 'wpv_filter_requires_framework_values', array( $this, 'requires_framework_values' ), 20, 2 );
		
		//add_action( 'wpv-before-display-post', array( $this, 'legacy_force_set_related_posts_data' ), 10, 2 );
		
		add_filter( 'wpv_filter_wpv_get_current_post_relationship_frontend_filter_post_owner_data',
			array( $this, 'get_current_filter_post_owner_data' ) );
	}
	
	/**
	 * Add the filter by post relationship to the $query.
	 *
	 * Uses an additional auxiliary query and intersects the post__in query argument if already set.
	 * Usually takes a single related post ID to execute the filter, 
	 * but when filtering by URL parameter we must accept multiple related IDs.
	 *
	 * @param array $query
	 * @param array $view_settings
	 * @param int $view_id
	 *
	 * @return array
	 *
	 * @since unknown
	 */
	public function filter_query( $query, $view_settings, $view_id ) {
		if ( ! isset( $view_settings['post_relationship_mode'][0] ) ) {
			return $query;
		}

		$post_relationship_query = $this->get_settings( $view_settings, $view_id );
		if ( count( $post_relationship_query['post__in'] ) > 0 ) {
			if ( isset( $query['post__in'] ) ) {
				$query['post__in'] = array_intersect( (array) $query['post__in'], $post_relationship_query['post__in'] );
				$query['post__in'] = array_values( $query['post__in'] );
				if ( empty( $query['post__in'] ) ) {
					$query['post__in'] = array( '0' );
				}
			} else {
				$query['post__in'] = $post_relationship_query['post__in'];
			}
		}
		
		if ( count( $post_relationship_query['pr_filter_post__in'] ) > 0 ) {
			$query['pr_filter_post__in'] = $post_relationship_query['pr_filter_post__in'];
		}

		return $query;
	}

	/**
	 * Apply the post relationship filter to WPAs.
	 *
	 * @param WP_Query $query
	 * @param array $archive_settings
	 * @param int $archive_id
	 *
	 * @since 2.1
	 */
	public function filter_archive_query( $query, $archive_settings, $archive_id ) {
		if ( ! isset( $archive_settings['post_relationship_mode'][0] ) ) {
			return;
		}
		
		$post_relationship_query = $this->get_settings( $archive_settings, $archive_id );
		if ( count( $post_relationship_query['post__in'] ) > 0 ) {
			$post__in = $query->get( 'post__in' );
			$post__in = isset( $post__in ) ? $post__in : array();
			if ( count( $post__in ) > 0 ) {
				$post__in = array_intersect( (array) $post__in, $post_relationship_query['post__in'] );
				$post__in = array_values( $post__in );
				if ( empty( $post__in ) ) {
					$post__in = array( '0' );
				}
				$query->set( 'post__in', $post__in );
			} else {
				$query->set( 'post__in', $post_relationship_query['post__in'] );
			}
		}
		
		if ( count( $post_relationship_query['pr_filter_post__in'] ) > 0 ) {
			$query->set( 'pr_filter_post__in', $post_relationship_query['pr_filter_post__in'] );
		}
	}
	
	/**
	 * Get settings for the query filter by post relationship.
	 *
	 * @param array $view_settings
	 * @param int $view_id
	 *
	 * @return array
	 *
	 * @since 2.1
	 */
	private function get_settings( $view_settings, $view_id ) {
		$post_relationship_query = array(
			'post__in' => array(),
			'pr_filter_post__in' => array(),

		);
		$post_owner_data = $this->get_post_owner_data( $view_settings );
		
		if ( null === $post_owner_data ) {
			$post_relationship_query['post__in'] = array( '0' );
			return $post_relationship_query;
		}
		
		if ( ! empty( $post_owner_data ) ) {
			if ( apply_filters( 'toolset_is_m2m_enabled', false ) ) {
				$post_relationship_query = $this->get_settings_post_in_with_m2m( $post_relationship_query, $view_settings, $post_owner_data );
			} else {
				$post_relationship_query = $this->get_settings_post_in_without_m2m( $post_relationship_query, $view_settings, $post_owner_data, $view_id );
			}
			// If there are not posts found, non-existing ID must be added.
			if ( empty( $post_relationship_query['post__in'] ) ) {
				$post_relationship_query['post__in'] = array( 0 );
			}
		}

		return $post_relationship_query;
	}
	
	/**
	 * Generate the needed data from the actual IDs that are applied in the filter.
	 *
	 * @param array $object_settings
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	public function get_post_owner_data( $object_settings = array() ) {
		if ( ! isset( $object_settings['post_relationship_mode'][0] ) ) {
			return array();
		}
		switch ( $object_settings['post_relationship_mode'][0] ) {
			case 'current_page': // @deprecated in 1.12.1
			case 'top_current_post':
				return $this->get_settings_per_top_current_post( $object_settings );
				break;
			case 'parent_view': // @deprecated in 1.12.1
			case 'current_post_or_parent_post_view':
				return $this->get_settings_per_current_post_or_parent_post_view( $object_settings );
				break;
			case 'this_page':
				return $this->get_settings_per_this_page( $object_settings );
				break;
			case 'shortcode_attribute':
				return $this->get_settings_per_shortcode_attribute( $object_settings );
				break;
			case 'url_parameter':
				return $this->get_settings_per_url_parameter( $object_settings );
				break;
			case 'framework':
				return $this->get_settings_per_framework_value( $object_settings );
				break;
		}
		return array();
	}
	
	/**
	 * Generate the needed data from the actual IDs that are applied in the filter when set by the current top post.
	 *
	 * @param array $object_settings
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_settings_per_top_current_post( $object_settings = array() ) {
		$post_owner_data = array();
		$post_owner_id = 0;
		$current_page = apply_filters( 'wpv_filter_wpv_get_top_current_post', null );
		if ( is_archive() ) {
			// For archive pages, the "current page" as "post where this View is inserted" is this
			// @todo check if this is also needed for flters by post author, post parent or post taxonomy
			$current_page = apply_filters( 'wpv_filter_wpv_get_current_post', null );
		}
		if ( $current_page ) {
			$post_owner_id = $current_page->ID;
		}
		if ( $post_owner_id > 0 ) {
			$post_type = get_post_type( $post_owner_id );
			$post_owner_data[ $post_type ][] = $post_owner_id;
		}
		return $post_owner_data;
	}
	
	/**
	 * Generate the needed data from the actual IDs that are applied in the filter when set by the current post.
	 *
	 * @param array $object_settings
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_settings_per_current_post_or_parent_post_view( $object_settings = array() ) {
		$post_owner_data = array();
		$post_owner_id = 0;
		$current_page = apply_filters( 'wpv_filter_wpv_get_current_post', null );
		if ( $current_page ) {
			$post_owner_id = $current_page->ID;
		}
		if ( $post_owner_id > 0 ) {
			$post_type = get_post_type( $post_owner_id );
			$post_owner_data[ $post_type ][] = $post_owner_id;
		}
		return $post_owner_data;
	}
	
	/**
	 * Generate the needed data from the actual IDs that are applied in the filter when set by a specific post.
	 *
	 * @param array $object_settings
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_settings_per_this_page( $object_settings = array() ) {
		$post_owner_data = array();
		if (
			isset( $object_settings['post_relationship_id'] )
			&& intval( $object_settings['post_relationship_id'] ) > 0
		) {
			$post_owner_id = intval( $object_settings['post_relationship_id'] );
			$post_owner_id_type = get_post_type( $post_owner_id );
			// Adjust for WPML support
			$post_owner_id = apply_filters( 'translate_object_id', $post_owner_id, $post_owner_id_type, true, null );
			$post_owner_data[ $post_owner_id_type ][] = $post_owner_id;
		}
		return $post_owner_data;
	}
	
	/**
	 * Generate the needed data from the actual IDs that are applied in the filter when set by a shortcode attribute.
	 *
	 * @param array $object_settings
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_settings_per_shortcode_attribute( $object_settings = array() ) {
		$post_owner_data = array();
		if (
			isset( $object_settings['post_relationship_shortcode_attribute'] )
			&& '' != $object_settings['post_relationship_shortcode_attribute']
		) {
			$post_relationship_shortcode = $object_settings['post_relationship_shortcode_attribute'];
			$view_attrs = apply_filters( 'wpv_filter_wpv_get_view_shortcodes_attributes', false );
			if (
				isset( $view_attrs[ $post_relationship_shortcode ] )
				&& intval( $view_attrs[ $post_relationship_shortcode ] ) > 0
			) {
				$post_owner_id = intval( $view_attrs[ $post_relationship_shortcode ] );
				$post_owner_id_type = get_post_type( $post_owner_id );
				// Adjust for WPML support
				$post_owner_id = apply_filters( 'translate_object_id', $post_owner_id, $post_owner_id_type, true, null );
				$post_owner_data[ $post_owner_id_type ][] = $post_owner_id;
			}
		}
		return $post_owner_data;
	}
	
	/**
	 * Generate the needed data from the actual IDs that are applied in the filter when set by an URL parameter,
	 * when the URL parameter is included in the URL qury string.
	 *
	 * @param string $url_parameter
	 * 
	 * @return array|null
	 *
	 * @since m2m
	 */
	private function get_settings_per_direct_url_parameter( $url_parameter ) {
		// There is a direct ancestor filter applied
		$post_owner_data= array();
		$post_owner_ids_from_url = $_GET[ $url_parameter ];
		$post_owner_ids_sanitized = array();
		if ( is_array( $post_owner_ids_from_url ) ) {
			foreach ( $post_owner_ids_from_url as $id_value ) {
				$id_value = (int) esc_attr( trim( $id_value ) );
				if ( $id_value > 0 ) {
					$post_owner_ids_sanitized[] = $id_value;
				}
			}
		} else {
			$post_owner_ids_from_url = (int) esc_attr( $post_owner_ids_from_url );
			if ( $post_owner_ids_from_url > 0 ) {
				$post_owner_ids_sanitized[] = $post_owner_ids_from_url;
			}
		}
		if ( count( $post_owner_ids_sanitized ) ) {
			global $wpdb;
			// We do not need to prepare this query as $post_owner_ids_sanitized only contains numeric natural IDs
			$post_types_from_url = $wpdb->get_results(
				"SELECT ID, post_type FROM {$wpdb->posts}
				WHERE ID IN ('" . implode("','", $post_owner_ids_sanitized) . "')"
			);
			if ( empty( $post_types_from_url ) ) {
				return null;
			}
			foreach ( $post_types_from_url as $ptfu_key => $ptfu_values ) {
				$post_owner_id_item = $ptfu_values->ID;
				// Adjust for WPML support
				$post_owner_id_item = apply_filters( 'translate_object_id', $post_owner_id_item, $ptfu_values->post_type, true, null );
				$post_owner_data[ $ptfu_values->post_type ][] = $post_owner_id_item;
			}
		}
		return $post_owner_data;
	}
	
	/**
	 * Get the IDs of the ancestors influencing the current query, by their URL parameters,
	 * and also the position of the closest one t the returned post types.
	 *
	 * @param array $relationship_tree_data_reversed
	 * @param string $post_relationship_url_parameter
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_ancestors_influence_and_index( $relationship_tree_data_reversed, $post_relationship_url_parameter ) {
		$ancestor_influence = array();
		$tree_key = 0;
		
		foreach ( $relationship_tree_data_reversed as $tree_ancestor_key => $tree_ancestor_data ) {
			$ancestor_provided_value = toolset_getget( $post_relationship_url_parameter . '-' . $tree_ancestor_key );
			if ( 
				! empty( $ancestor_provided_value ) 
				&& $ancestor_provided_value != array( 0 ) 
			) {
				// This ancestor has a value. Yay!
				$post_owner_ids_from_url = $ancestor_provided_value;
				$post_owner_ids_sanitized = array();
				if ( is_array( $post_owner_ids_from_url ) ) {
					foreach ( $post_owner_ids_from_url as $id_key => $id_value ) {
						$id_value = (int) esc_attr( trim( $id_value ) );
						if ( $id_value > 0 ) {
							$post_owner_ids_sanitized[ $id_key ] = $id_value;
						}
					}
				} else {
					$post_owner_ids_from_url = (int) esc_attr( $post_owner_ids_from_url );
					if ( $post_owner_ids_from_url > 0 ) {
						$post_owner_ids_sanitized[] = $post_owner_ids_from_url;
					}
				}
				$ancestor_influence[ $tree_ancestor_key ] = array(
					'key' => $tree_key,
					'ids' => $post_owner_ids_sanitized
				);
				break;
			}
			$tree_key = $tree_key + 1;
		}
		
		return array(
			'influence' => $ancestor_influence,
			'index' => $tree_key
		);
	}
	
	/**
	 * Generate the needed data from the actual IDs that are applied in the filter when set by an URL parameter.
	 *
	 * @param array $object_settings
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_settings_per_url_parameter( $object_settings = array() ) {
		$post_owner_data = array();
		if (
			! isset( $object_settings['post_relationship_url_parameter'] )
			|| '' === $object_settings['post_relationship_url_parameter']
		) {
			return $post_owner_data;
		}
		
		$post_relationship_url_parameter = $object_settings['post_relationship_url_parameter'];
		
		$direct_ancestor_provided_value = toolset_getget( $post_relationship_url_parameter );
		if ( 
			! empty( $direct_ancestor_provided_value )
			&& $direct_ancestor_provided_value != array( 0 )
			&& $direct_ancestor_provided_value != 0
		) {
			// There is a direct ancestor filter applied
			return $this->get_settings_per_direct_url_parameter( $post_relationship_url_parameter );
		}
		
		if ( ! $this->filter->is_types_installed() ) {
			return $post_owner_data;
		}
		
		$relationship_tree_string = $this->filter->get_search()->get_relationship_tree_string( $object_settings );
		
		if ( empty( $relationship_tree_string ) ) {
			return $post_owner_data;
		}
		
		$relationship_tree_data = $this->filter->get_relationship_tree( $relationship_tree_string );
		
		$relationship_tree_ancestors_keys = array_keys( $relationship_tree_data );
		$relationship_tree_roof = $relationship_tree_ancestors_keys[0];
		$relationship_tree_ground = array_pop( $relationship_tree_ancestors_keys );

		
		$relationship_tree_data_reversed = array_reverse( $relationship_tree_data );
		$relationship_tree_array = array_keys( $relationship_tree_data_reversed );
		
		if ( $relationship_tree_roof === $relationship_tree_ground ) {
			// One-level filter, if there is a filter to apply
			// it should have been detected on the direct URL parameter checking above
			return $post_owner_data;
		}
		
		$tree_roof_provided_value = toolset_getget( $post_relationship_url_parameter . '-' . $relationship_tree_roof );
		
		if ( 
			empty( $tree_roof_provided_value )
			|| $tree_roof_provided_value === array( 0 )
			|| $tree_roof_provided_value === 0
		) {
			// The root of the relationship has no posted value, hence
			// no filter by this relationship has been posted on frontend
			return $post_owner_data;
		}
		
		$ancestors_influence_and_index = $this->get_ancestors_influence_and_index( $relationship_tree_data_reversed, $post_relationship_url_parameter );
		
		$ancestor_influence = $ancestors_influence_and_index['influence'];
		$tree_key = $ancestors_influence_and_index['index'];
		
		if ( empty( $ancestor_influence ) ) {
			return $post_owner_data;
		}
		
		// It should have just one value, but check it anyway
		$ancestor_influence = array_slice( $ancestor_influence, 0, 1 ); 
		$i = 0;
		
		// Build the queries until getting to the direct ncestors of the returned post types
		if ( $this->filter->check_and_init_m2m() ) {
			while ( $i < $tree_key ) {
				$this_key = $tree_key - $i;
				if ( $this_key > 0 ) {
					$current_post_type = $relationship_tree_array[ $this_key - 1 ];
				} else {
					$current_post_type = $relationship_tree_ground;
				}
				$current_influencer = end( $ancestor_influence );
				$current_influencer_type = key( $ancestor_influence );
				
				$current_relationship = $relationship_tree_data_reversed[ $relationship_tree_array[ $this_key ] ];
				if ( empty( $current_relationship['relationship'] ) ) {
					// Legacy relationship, so current is child and direct ancestor is parent
					$relationship_query = new Toolset_Relationship_Query_V2();
					$definitions = $relationship_query
						->add( $relationship_query->do_and( 
							$relationship_query->is_legacy( true ),
							$relationship_query->do_and( 
								$relationship_query->has_domain_and_type( $current_relationship['type'], Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Parent() ),
								$relationship_query->has_domain_and_type( $current_post_type, Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Child() )
							) 
						) )
						->get_results();
					if ( empty( $definitions ) ) {
						return $post_owner_data;
					}
					$definition = reset( $definitions );
				} else {
					$relationship_repository = Toolset_Relationship_Definition_Repository::get_instance();
					$definition = $relationship_repository->get_definition( $current_relationship['relationship'] );
				}
				
				if ( ! $definition instanceof Toolset_Relationship_Definition ) {
					return $post_owner_data;
				}
				
				$association_query = new Toolset_Association_Query_V2();
				$association_query->add( $association_query->relationship_slug( $definition->get_slug() ) );
				$association_query->limit( PHP_INT_MAX );
				$association_query_conditions = array();
				foreach ( $current_influencer['ids'] as $influencer_id ) {
					$association_query_condition_role = Toolset_Relationship_Role::role_from_name( $current_relationship['role'] );
					$association_query_conditions[] = $association_query->element_id_and_domain( $influencer_id, Toolset_Element_Domain::POSTS, $association_query_condition_role );
				}
				$association_query->add( $association_query->do_or( $association_query_conditions ) );
				if ( null === $definition->get_intermediary_post_type() ) {
					$association_query_return_id_role = Toolset_Relationship_Role::role_from_name( Toolset_Relationship_Role::other( $current_relationship['role'] ) ); 
				} else {
					$intermediary_post_type = $definition->get_intermediary_post_type();
					if ( $current_post_type === $intermediary_post_type ) {
						$association_query_return_id_role = new Toolset_Relationship_Role_Intermediary();
					} else {
						$association_query_return_id_role = Toolset_Relationship_Role::role_from_name( Toolset_Relationship_Role::other( $current_relationship['role'] ) ); 
					}
				}
				$associations = $association_query
					->return_element_ids( $association_query_return_id_role )
					->get_results();
				if ( 
					is_array( $associations ) 
					&& count( $associations ) 
				) {
					$ancestor_influence[ $current_post_type ] = array(
						'key' => $this_key - 1,
						'ids' => $associations
					);
				} else {
					return null;
				}
				$i++;
			}
			$post_owner_data[ $relationship_tree_ground ] = $ancestor_influence[ $relationship_tree_ground ]['ids'];
			return $post_owner_data;
		} else {
			while ( $i < $tree_key ) {
				$this_key = $tree_key - $i;
				if ( $this_key > 0 ) {
					$current_post_type = $relationship_tree_array[ $this_key - 1 ];
				} else {
					$current_post_type = $relationship_tree_ground;
				}
				$current_influencer = end( $ancestor_influence );
				$query_here = array();
				$query_here['posts_per_page'] = -1;
				$query_here['paged'] = 1;
				$query_here['offset'] = 0;
				$query_here['fields'] = 'ids';
				$query_here['cache_results'] = false;
				$query_here['update_post_meta_cache'] = false;
				$query_here['update_post_term_cache'] = false;
				$query_here['post_type'] = $current_post_type;
				$query_here['meta_query'][] = array(
					'key' => '_wpcf_belongs_' . $relationship_tree_array[ $this_key ] . '_id',
					'value' => $current_influencer['ids']
				);
				$aux_relationship_query = new WP_Query( $query_here );
				if ( is_array( $aux_relationship_query->posts ) && count( $aux_relationship_query->posts ) ) {
					$ancestor_influence[ $current_post_type ] = array(
						'key' => $this_key - 1,
						'ids' => $aux_relationship_query->posts
					);
				} else {
					return null;
				}
				$i++;
			}
			$post_owner_data[ $relationship_tree_ground ] = $ancestor_influence[ $relationship_tree_ground ]['ids'];
			return $post_owner_data;
		}
	
		return $post_owner_data;
	}
	
	/**
	 * Generate the needed data from the actual IDs that are applied in the filter when set by a framework value.
	 *
	 * @param array $object_settings
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_settings_per_framework_value( $object_settings = array() ) {
		$post_owner_data = array();
		global $WP_Views_fapi;
		if ( $WP_Views_fapi->framework_valid ) {
			if (
				isset( $object_settings['post_relationship_framework'] )
				&& '' != $object_settings['post_relationship_framework']
			) {
				$post_relationship_framework = $object_settings['post_relationship_framework'];
				$post_relationship_candidates = $WP_Views_fapi->get_framework_value( $post_relationship_framework, array() );
				if ( ! is_array( $post_relationship_candidates ) ) {
					$post_relationship_candidates = explode( ',', $post_relationship_candidates );
				}
				$post_relationship_candidates = array_map( 'esc_attr', $post_relationship_candidates );
				$post_relationship_candidates = array_map( 'trim', $post_relationship_candidates );
				// is_numeric does sanitization
				$post_relationship_candidates = array_filter( $post_relationship_candidates, 'is_numeric' );
				$post_relationship_candidates = array_map( 'intval', $post_relationship_candidates );
				if ( count( $post_relationship_candidates ) ) {
					global $wpdb;
					// We do not need to prepare this query as $post_relationship_candidates only contains numeric natural IDs
					$post_types_from_framework = $wpdb->get_results(
						"SELECT ID, post_type FROM {$wpdb->posts}
						WHERE ID IN ('" . implode("','", $post_relationship_candidates) . "')"
					);
					foreach ( $post_types_from_framework as $ptfu_key => $ptfu_values ) {
						$post_owner_id_item = $ptfu_values->ID;
						// Adjust for WPML support
						$post_owner_id_item = apply_filters( 'translate_object_id', $post_owner_id_item, $ptfu_values->post_type, true, null );
						$post_owner_data[ $ptfu_values->post_type ][] = $post_owner_id_item;
					}
				}
			}
		}
		return $post_owner_data;
	}
	
	/**
	 * Get settings with m2m enabled.
	 *
	 * @param array $post_relationship_query Settings of the view retreviewed in get_settings.
	 * @param array $object_settings         Settings of the view retreviewed in get_settings.
	 * @param array $post_owner_data         Owners' IDs grouped by post types.
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_settings_post_in_with_m2m( $post_relationship_query, $object_settings, $post_owner_data ) {
		// and children queries
		$relationship_role = toolset_getarr( $object_settings, 'post_relationship_role', '' );
		$relationship_slug = toolset_getarr( $object_settings, 'post_relationship_slug', '' );
		$returned_post_types = $this->filter->get_returned_post_types( $object_settings );
		do_action( 'toolset_do_m2m_full_init' );
		if ( '-1' === $relationship_slug || '' === $relationship_slug || empty( $returned_post_types ) ) {
			// No relationship set: get all available related posts in any role given the post owner data
			$relationship_slug = false;
			$relationship_role = '';
		}
		foreach ( $post_owner_data as $type => $ides ) {
			$association_query = new Toolset_Association_Query_V2();
			$association_query->limit( PHP_INT_MAX );
			$arguments = array();
			switch( $relationship_role ) {
				case Toolset_Relationship_Role::CHILD:
					$association_query_conditions = array();
					foreach ( $ides as $id ) {
						$association_query_conditions[] = $association_query->element_id_and_domain( $id, Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Parent() );
						$association_query_conditions[] = $association_query->element_id_and_domain( $id, Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Intermediary() );
					}
					$association_query->add( $association_query->do_or( $association_query_conditions ) );
					if ( $relationship_slug ) {
						$association_query->add( $association_query->relationship_slug( $relationship_slug ) );
					}
					break;
				case Toolset_Relationship_Role::PARENT:
					$association_query_conditions = array();
					foreach ( $ides as $id ) {
						$association_query_conditions[] = $association_query->element_id_and_domain( $id, Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Child() );
						$association_query_conditions[] = $association_query->element_id_and_domain( $id, Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Intermediary() );
					}
					$association_query->add( $association_query->do_or( $association_query_conditions ) );
					if ( $relationship_slug ) {
						$association_query->add( $association_query->relationship_slug( $relationship_slug ) );
					}
					break;
				case Toolset_Relationship_Role::INTERMEDIARY:
					$association_query_conditions = array();
					foreach ( $ides as $id ) {
						$association_query_conditions[] = $association_query->element_id_and_domain( $id, Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Parent() );
						$association_query_conditions[] = $association_query->element_id_and_domain( $id, Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Child() );
					}
					$association_query->add( $association_query->do_or( $association_query_conditions ) );
					if ( $relationship_slug ) {
						$association_query->add( $association_query->relationship_slug( $relationship_slug ) );
					}
				default:
					$association_query_conditions = array();
					foreach ( $ides as $id ) {
						$association_query_conditions[] = $association_query->element_id_and_domain( $id, Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Parent() );
						$association_query_conditions[] = $association_query->element_id_and_domain( $id, Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Child() );
						$association_query_conditions[] = $association_query->element_id_and_domain( $id, Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Intermediary() );
					}
					$association_query->add( $association_query->do_or( $association_query_conditions ) );
					if ( $relationship_slug ) {
						$association_query->add( $association_query->relationship_slug( $relationship_slug ) );
					}
			}
			$associations = $association_query->get_results();
			foreach ( $associations as $association ) {
				if ( ! empty( $relationship_role ) ) {
					$element = $association->get_element( $relationship_role );
					$element_type = $element->get_type();
					if ( in_array( $element_type, $returned_post_types ) ) {
						$post_relationship_query['post__in'][] = $element->get_id();
					}
				} else {
					$element_child = $association->get_element( Toolset_Relationship_Role::CHILD );
					$element_child_type = $element_child->get_type();
					if ( in_array( $element_child_type, $returned_post_types ) ) {
						$post_relationship_query['post__in'][] = $element_child->get_id();
					}
					$element_parent = $association->get_element( Toolset_Relationship_Role::PARENT );
					$element_parent_type = $element_parent->get_type();
					if ( in_array( $element_parent_type, $returned_post_types ) ) {
						$post_relationship_query['post__in'][] = $element_parent->get_id();
					}
					$element_intermediary = $association->get_element( Toolset_Relationship_Role::INTERMEDIARY );
					if ( $element_intermediary ) {
						$element_intermediary_type = $element_intermediary->get_type();
						if ( in_array( $element_intermediary_type, $returned_post_types ) ) {
							$post_relationship_query['post__in'][] = $element_intermediary->get_id();
						}
					}
				}
			}
		}

		return $post_relationship_query;
	}
	
	/**
	 * Get settings without m2m enabled.
	 *
	 * @param array $post_relationship_query Settings of the view retreviewed in get_settings
	 * @param array $view_settings           Settings of the view retreviewed in get_settings
	 * @param array $post_owner_data         Owners' IDs grouped by post types.
	 * @param int   $view_id                 ID of the view.
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_settings_post_in_without_m2m( $post_relationship_query, $view_settings, $post_owner_data, $view_id ) {
		$returned_post_types = $this->filter->get_returned_post_types( $view_settings );
		$query_here = array();
		$query_here['posts_per_page'] = -1;
		$query_here['paged'] = 1;
		$query_here['offset'] = 0;
		$query_here['post_type'] = 'any';
		$query_here['fields'] = 'ids';
		$query_here['cache_results'] = false;
		$query_here['update_post_meta_cache'] = false;
		$query_here['update_post_term_cache'] = false;
		$query_here['post_type'] = $returned_post_types;
		$query_here['meta_query']['relation'] = 'AND';
		$query_here = apply_filters( 'wpv_filter_wpv_filter_auxiliar_post_relationship_query', $query_here, $view_settings, $view_id );
		foreach ( $post_owner_data as $type => $ides ) {
			$query_here['meta_query'][] = array(
				'key' => '_wpcf_belongs_' . $type . '_id',
				'value' => $ides,
			);
		}
		
		// Compatibility with WPML "Display as translated" mode
		$query_here['suppress_wpml_where_and_join_filter'] = true;
		
		$aux_relationship_query = new WP_Query( $query_here );

		if ( is_array( $aux_relationship_query->posts ) ) {
			if ( count( $aux_relationship_query->posts ) > 0 ) {
				if ( count( $post_relationship_query['post__in'] ) > 0 ) {
					$post_relationship_query['post__in'] = array_intersect( (array) $post_relationship_query['post__in'], $aux_relationship_query->posts );
					$post_relationship_query['post__in'] = array_values( $post_relationship_query['post__in'] );
					if ( empty( $post_relationship_query['post__in'] ) ) {
						$post_relationship_query['post__in'] = array( '0' );
					}
				} else {
					$post_relationship_query['post__in'] = $aux_relationship_query->posts;
				}
				$post_relationship_query['pr_filter_post__in'] = $aux_relationship_query->posts;
			} else {
				// If post__in is empty all post will be included, in this case no post has to be retrieved.
				$post_relationship_query['post__in'] = array( '0' );
			}
		}

		return $post_relationship_query;
	}
	
	/**
	 * Check if the current filter by post relationship needs info about the top current post.
	 *
	 * @param bool $state
	 * @param array $view_settings
	 *
	 * @return bool
	 *
	 * @since unknown
	 */
	public function requires_current_page( $state, $view_settings ) {
		if ( $state ) {
			return $state;
		}
		if ( isset( $view_settings['post_relationship_mode'][0] ) ) {
			if ( in_array( $view_settings['post_relationship_mode'][0], array( 'current_page', 'top_current_post' ) ) ) {
				$state = true;
			}
		}
		return $state;
	}

	/**
	 * Check if the current filter by post relationship needs info about the parent post.
	 *
	 * @param bool $state
	 * @param array $view_settings
	 *
	 * @return bool
	 *
	 * @since unknown
	 */
	public function requires_parent_post( $state, $view_settings ) {
		if ( $state ) {
			return $state;
		}
		if ( isset( $view_settings['post_relationship_mode'][0] ) ) {
			if ( in_array( $view_settings['post_relationship_mode'][0], array( 'parent_view', 'current_post_or_parent_post_view' ) ) ) {
				$state = true;
			}
		}
		return $state;
	}

	/**
	 * Check if the current filter by post relationship needs info about the framework values.
	 *
	 * @param bool $state
	 * @param array $view_settings
	 *
	 * @return bool
	 *
	 * @since 1.10
	 */
	public function requires_framework_values( $state, $view_settings ) {
		if ( $state ) {
			return $state;
		}
		if ( isset( $view_settings['post_relationship_mode'][0] ) ) {
			if ( $view_settings['post_relationship_mode'][0] == 'framework' ) {
				$state = true;
			}
		}
		return $state;
	}
	
	// This is not used anymore, keep for checking this set_variable thing
	public function legacy_force_set_related_posts_data( $post, $view_id ) {
		if ( ! $this->filter->is_types_installed() ) {
			return;
		}
		if ( $this->filter->check_and_init_m2m() ) {
			return;
		}
		
		static $related = array();
		
		if ( function_exists( 'wpcf_pr_get_belongs' ) ) {
			global $WP_Views;
			if ( ! isset( $related[ $post->post_type ] ) ) {
				$related[ $post->post_type ] = wpcf_pr_get_belongs( $post->post_type );
			}
			if ( is_array( $related[ $post->post_type ] ) ) {
				foreach( $related[ $post->post_type ] as $post_type => $data ) {
					$related_id = wpcf_pr_post_get_belongs( $post->ID, $post_type );
					if ( $related_id ) {
						$WP_Views->set_variable( $post_type . '_id', $related_id );
					}
				}
			}
		}

	}
	
	/**
	 * API filter to get the current loop post owner data, which is a pair of post_type->items 
	 * where the post type is an ancestor of the currently returned posts in the loop, and the data 
	 * contains information about the currently applied filter.
	 *
	 * @param array $post_owner_data
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	public function get_current_filter_post_owner_data( $post_owner_data ) {
		$view_settings = apply_filters( 'wpv_filter_wpv_get_object_settings', array() );
		if ( ! isset( $view_settings['post_relationship_mode'][0] ) ) {
			return $post_owner_data;
		}
		$post_owner_data_candidate = $this->get_post_owner_data( $view_settings );
		if ( empty( $post_owner_data_candidate ) ) {
			return $post_owner_data;
		}
		return $post_owner_data_candidate;
	}
	
}