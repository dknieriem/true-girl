<?php

/**
* Taxonomy frontend filter
*
* @package Views
*
* @since 2.1
*/

WPV_Taxonomy_Frontend_Filter::on_load();

/**
* WPV_Taxonomy_Frontend_Filter
*
* Views Taxonomy Filter Frontend Class
*
* @since 2.1
*/

class WPV_Taxonomy_Frontend_Filter {
	
	static function on_load() {
		// Apply frontend filter by post taxonomy
        add_filter( 'wpv_filter_query',										array( 'WPV_Taxonomy_Frontend_Filter', 'filter_post_taxonomy' ), 10, 3 );
		add_action( 'wpv_action_apply_archive_query_settings',				array( 'WPV_Taxonomy_Frontend_Filter', 'archive_filter_post_taxonomy' ), 40, 3 );
		// Auxiliar methods for requirements
		add_filter( 'wpv_filter_requires_current_page',						array( 'WPV_Taxonomy_Frontend_Filter', 'requires_current_page' ), 10, 2 );
		add_filter( 'wpv_filter_requires_parent_post',						array( 'WPV_Taxonomy_Frontend_Filter', 'requires_parent_post' ), 20, 2 );
		add_filter( 'wpv_filter_requires_parent_term',						array( 'WPV_Taxonomy_Frontend_Filter', 'requires_parent_term' ), 10, 2 );
		add_filter( 'wpv_filter_requires_current_archive',					array( 'WPV_Taxonomy_Frontend_Filter', 'requires_current_archive' ), 10, 2 );
		add_filter( 'wpv_filter_requires_framework_values',					array( 'WPV_Taxonomy_Frontend_Filter', 'requires_framework_values' ), 10, 2 );
		
		add_shortcode( 'wpv-control-post-taxonomy',							array( 'WPV_Taxonomy_Frontend_Filter', 'wpv_shortcode_wpv_control_post_taxonomy' ) );
		add_filter( 'wpv_filter_wpv_shortcodes_gui_data',					array( 'WPV_Taxonomy_Frontend_Filter', 'wpv_shortcodes_register_wpv_control_post_taxonomy_data' ) );
	}
	
	/**
	* filter_post_taxonomy
	*
	* Apply taxonomy query filters to Views.
	*
	* @since unknown
	* @since 2.1		Renamed from wpv_filter_post_category and moved to a static method
	*/
	
	static function filter_post_taxonomy( $query, $view_settings, $view_id ) {
		$taxonomy_query = WPV_Taxonomy_Frontend_Filter::get_settings( $query, $view_settings, $view_id );
		if ( count( $taxonomy_query ) > 0 ) {
			$taxonomy_query['relation'] = isset( $view_settings['taxonomy_relationship'] ) ? $view_settings['taxonomy_relationship'] : 'AND';
			$query['tax_query'] = $taxonomy_query;
		}
		return $query;
	}
	
	/**
	* archive_filter_post_taxonomy
	*
	* Apply filters by post taxonomy to WPAs.
	*
	* @since 2.1
	*/
	
	static function archive_filter_post_taxonomy( $query, $archive_settings, $archive_id ) {
		$tax_to_exclude = array();
		if ( $query->get( 'wpv_dependency_query' ) ) {
			$wpv_dependency_query = $query->get( 'wpv_dependency_query' );
			if ( isset( $wpv_dependency_query['taxonomy'] ) ) {
				$tax_to_exclude[] = $wpv_dependency_query['taxonomy'];
			}
		}
		if ( 
			$query->is_archive 
			&& (
				$query->is_category  
				|| $query->is_tag  
				|| $query->is_tax 
			)
		) {
			$term = $query->get_queried_object();
			if ( 
				$term 
				&& isset( $term->taxonomy )
			) {
				$tax_to_exclude[] = $term->taxonomy;
			}
		}
		$taxonomy_query = WPV_Taxonomy_Frontend_Filter::get_settings( $query, $archive_settings, $archive_id, $tax_to_exclude );
		
		// Re-apply the taxonomy query caused by a taxonomy archive page
		// Note that on Layout-based archives this duplicates the native archive tax query entry, but we can not avoid it
		if ( 
			isset( $query->tax_query ) 
			&& is_object( $query->tax_query )
		) {
			$tax_query_obj		= clone $query->tax_query;
			$tax_query_queries	= $tax_query_obj->queries;
			if ( 
				count( $tax_query_queries ) > 0 
				&& count( $tax_to_exclude ) > 0
			) {
				foreach ( $tax_query_queries as $tax_query_queries_item ) {
					if ( 
						is_array( $tax_query_queries_item ) 
						&& isset( $tax_query_queries_item['taxonomy'] ) 
						&& in_array( $tax_query_queries_item['taxonomy'], $tax_to_exclude ) 
					) {
						$taxonomy_query[] = $tax_query_queries_item;
					}
				}
			}
		}
		
		if ( count( $taxonomy_query ) > 0 ) {
			$taxonomy_query['relation'] = isset( $archive_settings['taxonomy_relationship'] ) ? $archive_settings['taxonomy_relationship'] : 'AND';
			$query->set( 'tax_query', $taxonomy_query );
			$query->tax_query = new WP_Tax_Query( $taxonomy_query );
		}
	}
	
	/**
	* get_settings
	*
	* Get settings for the query filter by post taxonomy.
	*
	* @note We can pass an array of taxonomies to exclude from the filters.
	*
	* @since 2.1
	*/
	
	static function get_settings( $query, $view_settings, $view_id, $tax_to_exclude = array() ) {
		$taxonomy_query			= array();
		$taxonomies				= get_taxonomies( '', 'objects' );
		$archive_environment	= apply_filters( 'wpv_filter_wpv_get_current_archive_loop', array() );
		
		foreach ( $taxonomies as $category_slug => $category ) {
			if ( in_array( $category_slug, $tax_to_exclude ) ) {
				continue;
			}
			$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
			if ( isset( $view_settings[ $relationship_name ] ) ) {
				$save_name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input_' . $category->name;
				$attribute_operator = ( isset( $view_settings['taxonomy-' . $category->name . '-attribute-operator'] ) ) ? $view_settings['taxonomy-' . $category->name . '-attribute-operator'] : 'IN';
				
				if ( $attribute_operator == 'IN' ) {
					$include_child = true;	
				} else {
					$include_child = false;	
				}
				
				/*
				 * Filter: wpv_filter_tax_filter_include_children
				 * 
				 * @param: $include_child - current status
				 * @paran: $category->name - Category nicename
				 * @param: $view_id
				 * 
				*/
				//$include_child = apply_filters( 'wpv_filter_tax_filter_include_children', $include_child, $category->name, $view_id );
				
				switch ( $view_settings['tax_' . $category->name . '_relationship'] ) {
					case 'top_current_post':
						$current_page = apply_filters( 'wpv_filter_wpv_get_top_current_post', null );
						if ( $current_page ) {
							$terms = array();
							$term_obj = get_the_terms( $current_page->ID, $category->name );
							if ( 
								$term_obj 
								&& ! is_wp_error( $term_obj ) 
							) {
								$terms = array_values( wp_list_pluck( $term_obj, 'term_id' ) );
							}
							if ( count( $terms ) ) {
								$include_child = apply_filters( 'wpv_filter_tax_filter_include_children', $include_child, $category->name, $view_id );
								$taxonomy_query[] = array(
									'taxonomy'			=> $category->name,
									'field'				=> 'id',
									'terms'				=> WPV_Taxonomy_Frontend_Filter::get_adjusted_terms( $terms, $category->name ),
									'operator'			=> "IN",
									"include_children"	=> $include_child
								);
							} else { // if the current page has no term in the given taxonomy, return nothing
								$taxonomy_query[] = array(
									'taxonomy'	=> $category->name,
									'field'		=> 'id',
									'terms'		=> 0,
									'operator'	=> "IN"
								);
							}
						}
						break;
					case 'FROM PAGE': // @deprecated in 1.12.1
					case 'current_post_or_parent_post_view':
						// @todo this should be FROM PARENT POST VIEW, and create a new mode for get_top_current_page(); might need adjust in labels too
						$current_page = apply_filters( 'wpv_filter_wpv_get_current_post', null );
						if ( $current_page ) {
							$terms = array();
							$term_obj = get_the_terms( $current_page->ID, $category->name );
							if ( 
								$term_obj 
								&& ! is_wp_error( $term_obj ) 
							) {
								$terms = array_values( wp_list_pluck( $term_obj, 'term_id' ) );
							}
							if ( count( $terms ) ) {
								$include_child = apply_filters( 'wpv_filter_tax_filter_include_children', $include_child, $category->name, $view_id );
								$taxonomy_query[] = array(
									'taxonomy'			=> $category->name,
									'field'				=> 'id',
									'terms'				=> WPV_Taxonomy_Frontend_Filter::get_adjusted_terms( $terms, $category->name ),
									'operator'			=> "IN",
									"include_children"	=> $include_child
								);
							} else { // if the current page has no term in the given taxonomy, return nothing
								$taxonomy_query[] = array(
									'taxonomy'	=> $category->name,
									'field'		=> 'id',
									'terms'		=> 0,
									'operator'	=> "IN"
								);
							}
						}
						break;
					case 'FROM ARCHIVE':
						if (
							isset( $archive_environment['type'] ) 
							&& $archive_environment['type'] == 'taxonomy' 
							&& isset( $archive_environment['data']['taxonomy'] ) 
							&& $archive_environment['data']['taxonomy'] == $category->name
							&& isset( $archive_environment['data']['term_id'] ) 
						) {
							$include_child = apply_filters( 'wpv_filter_tax_filter_include_children', $include_child, $category->name, $view_id );
							$taxonomy_query[] = array(
								'taxonomy'			=> $category->name,
								'field'				=> 'id',
								'terms'				=> (int) $archive_environment['data']['term_id'],
								'operator'			=> "IN",
								"include_children"	=> $include_child
							);
						} else if (  
							is_tax() 
							|| is_category() 
							|| is_tag() 
						) {
							global $wp_query;
							$term = $wp_query->get_queried_object();
							if ( 
								$term 
								&& isset( $term->taxonomy )
								&& $term->taxonomy == $category->name
							) {
								$include_child = apply_filters( 'wpv_filter_tax_filter_include_children', $include_child, $category->name, $view_id );
								$taxonomy_query[] = array(
									'taxonomy'			=> $category->name,
									'field'				=> 'id',
									'terms'				=> $term->term_id,
									'operator'			=> "IN",
									"include_children"	=> $include_child
								);
							}
						} else {
							$taxonomy_query[] = array(
								'taxonomy'	=> $category->name,
								'field'		=> 'id',
								'terms'		=> 0,
								'operator'	=> "IN"
							);
						}
						break;
					case 'FROM ATTRIBUTE':
						$attribute = $view_settings['taxonomy-' . $category->name . '-attribute-url'];
						if ( isset( $view_settings['taxonomy-' . $category->name . '-attribute-url-format'] ) ) {
							$attribute_format = $view_settings['taxonomy-' . $category->name . '-attribute-url-format'][0];
						} else {
							$attribute_format = 'name';
						}
						$view_attrs = apply_filters( 'wpv_filter_wpv_get_view_shortcodes_attributes', false );
						if ( 
							isset( $view_attrs[$attribute] ) 
							&& '' != $view_attrs[$attribute]
						) {
							$terms = explode(',', $view_attrs[$attribute]);
							$term_ids = array();
							foreach ( $terms as $t ) {
								// get_term_by does sanitization
								$term = get_term_by( $attribute_format, trim( $t ), $category->name );
								if ( $term ) {
									array_push( $term_ids, $term->term_id );
								}
							}
							if ( count( $term_ids ) > 0 ) {
								$include_child = apply_filters( 'wpv_filter_tax_filter_include_children', $include_child, $category->name, $view_id );
								$taxonomy_query[] = array(
									'taxonomy'			=> $category->name,
									'field'				=> 'id',
									'terms'				=> WPV_Taxonomy_Frontend_Filter::get_adjusted_terms( $term_ids, $category->name ),
									'operator'			=> $attribute_operator,
									"include_children"	=> $include_child
								);
							} else if ( count( $terms ) > 0 ) { // if the shortcode attribute exists and is not empty, and no term matches the value, return nothing
								$taxonomy_query[] = array(
									'taxonomy'	=> $category->name,
									'field'		=> 'id',
									'terms'		=> 0,
									'operator'	=> "IN"
								);
							}
						}
						break;
					case 'FROM URL':
						$url_parameter = $view_settings['taxonomy-' . $category->name . '-attribute-url'];
						if ( isset( $view_settings['taxonomy-' . $category->name . '-attribute-url-format'] ) ) {
							$url_format = $view_settings['taxonomy-' . $category->name . '-attribute-url-format'][0];
						} else {
							$url_format = 'name';
						}
						if ( isset( $_GET[$url_parameter] ) ) {
							if ( is_array( $_GET[$url_parameter] ) ) {
								$terms = $_GET[$url_parameter];
							} else {
								$terms = explode( ',', $_GET[$url_parameter] );
							}
							$term_ids = array();
							foreach ( $terms as $t ) {
								// get_term_by does sanitization
								$term = get_term_by( $url_format, trim( $t ), $category->name );
								if ( $term ) {
									array_push( $term_ids, $term->term_id );
								}
							}
							if ( count( $term_ids ) > 0 ) {
								$include_child = apply_filters( 'wpv_filter_tax_filter_include_children', $include_child, $category->name, $view_id );
								$taxonomy_query[] = array(
									'taxonomy'			=> $category->name,
									'field'				=> 'id',
									'terms'				=> WPV_Taxonomy_Frontend_Filter::get_adjusted_terms( $term_ids, $category->name ),
									'operator'			=> $attribute_operator,
									"include_children"	=> $include_child
								);
							} else if ( ! empty( $_GET[$url_parameter] ) ) {
								$taxonomy_query[] = array(
									'taxonomy'	=> $category->name,
									'field'		=> 'id',
									'terms'		=> 0,
									'operator'	=> "IN"
								);
							}
						}
						break;
					case 'FROM PARENT VIEW': // @deprecated on 1.12.1
					case 'current_taxonomy_view':
						$parent_term_id = apply_filters( 'wpv_filter_wpv_get_parent_view_taxonomy', null );
						if ( $parent_term_id ) {
							$include_child = true;
							$include_child = apply_filters( 'wpv_filter_tax_filter_include_children', $include_child, $category->name, $view_id );
							$taxonomy_query[] = array(
								'taxonomy'			=> $category->name,
								'field'				=> 'id',
								'terms'				=> WPV_Taxonomy_Frontend_Filter::get_adjusted_terms( array( $parent_term_id ), $category->name ),
								'operator'			=> "IN",
								"include_children"	=> $include_child
							);
						} else {
							$taxonomy_query[] = array(
								'taxonomy'	=> $category->name,
								'field'		=> 'id',
								'terms'		=> 0,
								'operator'	=> "IN"
							);
						}
						break;
					case 'IN':
					case 'NOT IN':
					case 'AND':
						if ( $view_settings['tax_' . $category->name . '_relationship'] == 'IN' ) {
							$include_child = true;	
						} else {
							$include_child = false;	
						}
						$include_child = apply_filters( 'wpv_filter_tax_filter_include_children', $include_child, $category->name, $view_id );
						if ( isset( $view_settings[$save_name] ) ) {
							$term_ids = $view_settings[$save_name];
							$taxonomy_query[] = array(
								'taxonomy'			=> $category->name,
								'field'				=> 'id',
								'terms'				=> WPV_Taxonomy_Frontend_Filter::get_adjusted_terms( $term_ids, $category->name ),
								'operator'			=> $view_settings['tax_' . $category->name . '_relationship'],
								"include_children"	=> $include_child
							);
						}
						break;
					case 'framework':
						global $WP_Views_fapi;
						if ( 
							$WP_Views_fapi->framework_valid
							&& isset( $view_settings['taxonomy-' . $category->name . '-framework'] )
							&& '' != $view_settings['taxonomy-' . $category->name . '-framework']
						) {
							$include_child = true;
							$include_child = apply_filters( 'wpv_filter_tax_filter_include_children', $include_child, $category->name, $view_id );
							$framework_key = $view_settings['taxonomy-' . $category->name . '-framework'];
							$taxonomy_terms_candidates = $WP_Views_fapi->get_framework_value( $framework_key, array() );
							if ( ! is_array( $taxonomy_terms_candidates ) ) {
								$taxonomy_terms_candidates = explode( ',', $taxonomy_terms_candidates );
							}
							$taxonomy_terms_candidates = array_map( 'esc_attr', $taxonomy_terms_candidates );
							$taxonomy_terms_candidates = array_map( 'trim', $taxonomy_terms_candidates );
							// is_numeric does sanitization
							$taxonomy_terms_candidates = array_filter( $taxonomy_terms_candidates, 'is_numeric' );
							if ( count( $taxonomy_terms_candidates ) ) {
								$taxonomy_query[] = array(
									'taxonomy'			=> $category->name,
									'field'				=> 'id',
									'terms'				=> WPV_Taxonomy_Frontend_Filter::get_adjusted_terms( $taxonomy_terms_candidates, $category->name ),
									'operator'			=> 'IN',
									"include_children"	=> $include_child
								);
							}
						}
						break;
					
				}
			}
		}
		return $taxonomy_query;
	}
	
	
	/**
	* requires_current_page
	*
	* Whether the current View requires the current page data for any filter by taxonomy
	*
	* @param $state (boolean) the state of this need until this filter is applied
	* @param $view_settings
	*
	* @return $state (boolean)
	*
	* @since unknown
	* @since 2.1		Renamed from wpv_filter_cat_requires_current_page and moved to a static method
	*/
	
	static function requires_current_page( $state, $view_settings ) {
		if ( $state ) {
			return $state; // Already set
		}
		$taxonomies = get_taxonomies('', 'objects');
		foreach ( $taxonomies as $category_slug => $category ) {
			$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
			if ( isset( $view_settings[$relationship_name] ) ) {
				if ( $view_settings['tax_' . $category->name . '_relationship'] == "top_current_post" ) {
					$state = true;
					break;
				}
			}
		}
		return $state;
	}
	
	/**
	* requires_parent_post
	*
	* Check if the current filter by post parent needs info about the parent post
	*
	* @since unknown
	* @since 2.1		Renamed from wpv_filter_cat_requires_parent_post and mved to a static method
	*/
	
	static function requires_parent_post( $state, $view_settings ) {
		if ( $state ) {
			return $state; // Already set
		}
		$taxonomies = get_taxonomies('', 'objects');
		foreach ( $taxonomies as $category_slug => $category ) {
			$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
			if ( isset( $view_settings[$relationship_name] ) ) {
				if ( in_array( $view_settings['tax_' . $category->name . '_relationship'], array( "FROM PAGE", 'current_post_or_parent_post_view' ) ) ) {
					$state = true;
					break;
				}
			}
		}
		return $state;
	}
	
	/**
	* requires_parent_term
	*
	* Whether the current View is nested and requires the user set by the parent View for any filter by taxonomy
	*
	* @param $state (boolean) the state of this need until this filter is applied
	* @param $view_settings
	*
	* @return $state (boolean)
	*
	* @since 1.9
	* @since 2.1	Renamed from wpv_filter_cat_requires_parent_term and moved to a static method
	*/
	
	static function requires_parent_term( $state, $view_settings ) {
		if ( $state ) {
			return $state;
		}
		$taxonomies = get_taxonomies('', 'objects');
		foreach ( $taxonomies as $category_slug => $category ) {
			if ( 
				isset( $view_settings['tax_' . $category->name . '_relationship'] ) 
				&& in_array( $view_settings['tax_' . $category->name . '_relationship'], array( 'FROM PARENT VIEW', 'current_taxonomy_view' ) )
			) {
				$state = true;
				break;
			}
		}
		return $state;
	}
	
	/**
	* requires_current_archive
	*
	* Whether the current View requires the current archive loop
	*
	* @param $state (boolean) the state of this need until this filter is applied
	* @param $view_settings
	*
	* @return $state (boolean)
	*
	* @since 1.10
	* @since 2.1	Renamed from wpv_filter_cat_requires_current_archive and moved to a static method
	*/
	
	static function requires_current_archive( $state, $view_settings ) {
		if ( $state ) {
			return $state;
		}
		$taxonomies = get_taxonomies('', 'objects');
		foreach ( $taxonomies as $category_slug => $category ) {
			if ( 
				isset( $view_settings['tax_' . $category->name . '_relationship'] ) 
				&& $view_settings['tax_' . $category->name . '_relationship'] == 'FROM ARCHIVE'
			) {
				$state = true;
				break;
			}
		}
		return $state;
	}
	
	/**
	* requires_framework_values
	*
	* Whether the current View requires values from a framework
	*
	* @param $state (boolean) the state of this need until this filter is applied
	* @param $view_settings
	*
	* @return $state (boolean)
	*
	* @since 1.10
	* @since 2.1	Renamed from wpv_filter_cat_requires_framework_values and moved to a static method
	*/
	
	static function requires_framework_values( $state, $view_settings ) {
		if ( $state ) {
			return $state;
		}
		$taxonomies = get_taxonomies('', 'objects');
		foreach ( $taxonomies as $category_slug => $category ) {
			if ( 
				isset( $view_settings['tax_' . $category->name . '_relationship'] ) 
				&& $view_settings['tax_' . $category->name . '_relationship'] == 'framework'
			) {
				$state = true;
				break;
			}
		}
		return $state;
	}
	
	/**
	* get_adjusted_terms
	*
	* Adjust terms used on a frontend query filter.
	* Ensures compatibility with WordPress > 4.2 and WPML.
	*
	* @since unknown
	*/
	
	static function get_adjusted_terms( $term_ids, $category_name ) {
		if ( ! empty( $term_ids ) ) {
			$adjusted_term_ids = array();
			foreach ( $term_ids as $candidate_term_id ) {
				// WordPress 4.2 compatibility - split terms
				$candidate_term_id_splitted = wpv_compat_get_split_term( $candidate_term_id, $category_name );
				if ( $candidate_term_id_splitted ) {
					$candidate_term_id = $candidate_term_id_splitted;
				}
				// WPML support
				$candidate_term_id = apply_filters( 'translate_object_id', $candidate_term_id, $category_name, true, null );
				$adjusted_term_ids[] = $candidate_term_id;
			}
			$term_ids = $adjusted_term_ids;
		}
		return $term_ids;	
	}
	
	/**
	 * Callback to display the custom search filter by post taxonomy.
	 *
	 * @param $atts array
	 *		'taxonomy'		string	The taxonomy slug
	 * 		'url_param'		string	URL parameter to listen to
	 *		'type'			'select'|'multi-select'|'radios'|'checbboxes'
	 *		'format'		string.	Placeholders: '%%NAME%%', '%%COUNT%%'
	 *		'default_label'	string	Label for the default empty option in select dropdowns
	 *		'orderby'		string	Order field for the options
	 *		'order'			string	Direction for sorting the options
	 *		'hide_empty'	string	Legacy: hide terms without assigned posts
	 *		'style'			string	Styles to add to the control
	 *		'class'			string	Classnames to add to the control
	 *		'label_style'	string
	 *		'label_class'	string
	 '		'output'		string	The kind of output to produce: 'legacy'|'bootstrap'. Defaults to 'bootstrap'.
	 *
	 * @since 2.4.0
	 */
	
	public static function wpv_shortcode_wpv_control_post_taxonomy( $atts ) {
		$atts = shortcode_atts(
			array(
				'taxonomy'	=> '',
				'url_param'	=> '',
				'type'		=> '',
				'default_label'	=> '',
				'format'	=> '%%NAME%%',
				'orderby'	=> '',
				'order'		=> '',
				'output'	=> 'bootstrap',
				'hide_empty'	=> 'false',
				'style'		=> '',
                'class'		=> '',
                'label_style'	=> '',
                'label_class'	=> ''
			),
			$atts
		);
		
		if (
			empty( $atts['url_param'] ) 
			|| empty( $atts['taxonomy'] ) 
			|| empty( $atts['type'] )
			|| ! taxonomy_exists( $atts['taxonomy'] ) 
		) {
			return;
		}
		
		// Backwards compatibility: before 2.4.0 those were the attribute names for sorting
		$atts['taxonomy_orderby'] = $atts['orderby'];
		$atts['taxonomy_order'] = $atts['order'];
		
		$aux_array = apply_filters( 'wpv_filter_wpv_get_rendered_views_ids', array() );
		$view_name = get_post_field( 'post_name', end( $aux_array ) );
		$view_settings = apply_filters( 'wpv_filter_wpv_get_object_settings', array() );
		
		$walker_args = array(
			'name'				=> $atts['url_param'],
			'selected'			=> array( '0' ),
			'value_type'		=> 'name',
			'format'			=> $atts['format'],
			'style'				=> $atts['style'],
			'class'				=> $atts['class'],
			'label_style'		=> $atts['label_style'],
			'label_class'		=> $atts['label_class'],
			'output'			=> $atts['output'],
			'taxonomy'			=> $atts['taxonomy'],
			'type'				=> $atts['type'],
			'dependency'		=> 'disabled',
			'empty_action'		=> 'hide',
			'operator'			=> 'IN',
			'query_cache'		=> array()
		);
		
		// Translate the default label if any
		if ( ! empty( $atts['default_label'] ) ) {
			$atts['default_label'] = wpv_translate( $atts['url_param'] . '_default_label', $atts['default_label'], false, 'View ' . $view_name );
		}
		
		// Set selected values
		$walker_args = WPV_Taxonomy_Frontend_Filter::set_selected_values( $walker_args, $atts, $view_settings );
		
		// Format and operator
		$walker_args = WPV_Taxonomy_Frontend_Filter::set_format_and_operator( $walker_args, $atts, $view_settings );
		
		// Dependency, counters and empty action
		$walker_args = WPV_Taxonomy_Frontend_Filter::set_dependency_counters_and_empty_action( $walker_args, $atts, $view_settings );
		
		// Query cache
		$walker_args = WPV_Taxonomy_Frontend_Filter::set_query_cache( $walker_args, $atts, $view_settings );
		
		$taxonomy_filter_output = '';
		
		if ( 
			$walker_args['type'] == 'select' 
			|| $walker_args['type'] == 'multi-select' 
		) {
			$taxonomy_filter_output .= WPV_Taxonomy_Frontend_Filter::wpv_control_post_taxonomy_select( $atts, $walker_args );
		} elseif ( 
			$walker_args['type'] == 'radios' 
			|| $walker_args['type'] == 'radio' 
		) {
			$taxonomy_filter_output .= WPV_Taxonomy_Frontend_Filter::wpv_control_post_taxonomy_radios( $atts, $walker_args );
		} else {
			$taxonomy_filter_output .= WPV_Taxonomy_Frontend_Filter::wpv_control_post_taxonomy_checkboxes( $atts, $walker_args );
		}
		
		// This should not be needd anymore...
		if ( $walker_args['taxonomy'] == 'category' ) {
			$taxonomy_filter_output = str_replace(
				'name="post_category', 
				'name="' . $walker_args['name'], 
				$taxonomy_filter_output
			);
		} else {
			$taxonomy_filter_output = str_replace(
				'name="' . $walker_args['taxonomy'], 
				'name="' . $walker_args['name'], 
				$taxonomy_filter_output
			);
		}
		
		return $taxonomy_filter_output;
		
	}
	
	/**
	 * Calculate the selected values for a taxonomy frontend filter.
	 *
	 * @param array $walker_args   The walker arguments being built
	 * @param array $atts          The shortcode attributes
	 * @param array $view_settings The current View settings
	 *
	 * @return array The walker arguments after being filled
	 *
	 * @since 2.4.0
	 */
	public static function set_selected_values( $walker_args, $atts, $view_settings ) {
		if ( isset( $_GET[ $atts['url_param'] ] ) ) {
			if ( is_array( $_GET[ $atts['url_param'] ] ) ) {
				$walker_args['selected'] = $_GET[ $atts['url_param'] ];
			} else {
				// support csv terms
				$walker_args['selected'] = explode( ',', $_GET[ $atts['url_param'] ] );
			}
		}
		return $walker_args;
	}
	
	/**
	 * Calculate the type of values and the operator for a taxonomy frontend filter.
	 *
	 * @param array $walker_args   The walker arguments being built
	 * @param array $atts          The shortcode attributes
	 * @param array $view_settings The current View settings
	 *
	 * @return array The walker arguments after being filled
	 *
	 * @since 2.4.0
	 */
	public static function set_format_and_operator( $walker_args, $atts, $view_settings ) {
		if (
			isset( $view_settings['taxonomy-' . $walker_args['taxonomy'] . '-attribute-url-format'] ) 
			&& 'slug' == $view_settings['taxonomy-' . $walker_args['taxonomy'] . '-attribute-url-format'][0] 
		) {
			$walker_args['value_type'] = 'slug';
		}
		if ( isset( $view_settings['taxonomy-' . $walker_args['taxonomy'] . '-attribute-operator'] ) ) {
			$walker_args['operator'] = $view_settings['taxonomy-' . $walker_args['taxonomy'] . '-attribute-operator'];
		}
		return $walker_args;
	}
	
	/**
	 * Calculate whether to use dependency and counters, and the default empty action, for a taxonomy frontend filter.
	 *
	 * @param array $walker_args   The walker arguments being built
	 * @param array $atts          The shortcode attributes
	 * @param array $view_settings The current View settings
	 *
	 * @return array The walker arguments after being filled
	 *
	 * @since 2.4.0
	 */
	public static function set_dependency_counters_and_empty_action( $walker_args, $atts, $view_settings ) {
		if ( 
			isset( $view_settings['dps'] ) 
			&& is_array( $view_settings['dps'] ) 
			&& isset( $view_settings['dps']['enable_dependency'] ) 
			&& $view_settings['dps']['enable_dependency'] == 'enable' 
			&& ! apply_filters( 'wpv_filter_wpv_get_force_disable_dps', false )
		) {
			$walker_args['dependency'] = 'enabled';
			$walker_args['type'] = isset( $walker_args['type'] ) ? $walker_args['type'] : '';
			switch ( $walker_args['type'] ) {
				case 'select':
					if (
						isset( $view_settings['dps']['empty_select'] ) 
						&& $view_settings['dps']['empty_select'] == 'disable'
					) {
						$walker_args['empty_action'] = 'disable';
					}
					break;
				case 'multi-select':
					if (
						isset( $view_settings['dps']['empty_multi_select'] ) 
						&& $view_settings['dps']['empty_multi_select'] == 'disable'
					) {
						$walker_args['empty_action'] = 'disable';
					}
					break;
				case 'radios':
				case 'radio':
					if (
						isset( $view_settings['dps']['empty_radios'] ) 
						&& $view_settings['dps']['empty_radios'] == 'disable'
					) {
						$walker_args['empty_action'] = 'disable';
					}
					break;
				case 'checkboxes':
					if (
						isset( $view_settings['dps']['empty_checkboxes'] ) 
						&& $view_settings['dps']['empty_checkboxes'] == 'disable'
					) {
						$walker_args['empty_action'] = 'disable';
					}
					break;
			}
		}
		$counters = ( strpos( $walker_args['format'], '%%COUNT%%' ) !== false ) ? true : false;
		$walker_args['counters'] = $counters ? 'enabled' : 'disabled';
		return $walker_args;
	}
	
	/**
	 * Calculate the query cache for a taxonomy frontend filter when using dependency or counters.
	 *
	 * @param array $walker_args   The walker arguments being built
	 * @param array $atts          The shortcode attributes
	 * @param array $view_settings The current View settings
	 *
	 * @return array The walker arguments after being filled
	 *
	 * @since 2.4.0
	 */
	public static function set_query_cache( $walker_args, $atts, $view_settings ) {
		if ( 
			$walker_args['dependency'] == 'enabled' 
			|| $walker_args['counters'] == 'enabled' 
		) {
			if ( 
				empty( $walker_args['selected'] ) 
				|| (
					is_array( $walker_args['selected'] ) 
					&& in_array( (string) 0, $walker_args['selected'] ) 
				) || (
					(
						$walker_args['type'] == 'multi-select'
						|| $walker_args['type'] == 'checkboxes'
					)
					&& $walker_args['operator'] == 'AND' 
				) 
			) {
				// This is when there is no non-default selected
				$wpv_data_cache = WPV_Cache::$stored_cache;
				if ( 
					isset( $wpv_data_cache[ $walker_args['taxonomy'] . '_relationships' ] ) 
					&& is_array( $wpv_data_cache[ $walker_args['taxonomy'] . '_relationships' ] ) 
				) {
					foreach ( $wpv_data_cache[ $walker_args['taxonomy'] . '_relationships' ] as $pid => $tax_array ) {
						if ( 
							is_array( $tax_array ) 
							&& count( $tax_array ) > 0 
						) {
							$this_post_taxes = wp_list_pluck( $tax_array, 'term_id', 'term_id' );
							$walker_args['query_cache'][ $pid ] = $this_post_taxes;
						}
					}
				}
			} else {
				// When there is a selected value, create a pseudo-cache based on all the other filters
				$query = apply_filters( 'wpv_filter_wpv_get_dependant_extended_query_args', array(), $view_settings, array( 'taxonomy' => $walker_args['taxonomy'] ) );
				$aux_cache_query = null;
				if ( 
					isset( $query['tax_query'] ) 
					&& is_array( $query['tax_query'] ) 
				) {
					foreach ( $query['tax_query'] as $qt_index => $qt_val ) {
						if ( 
							is_array( $qt_val ) 
							&& isset( $qt_val['taxonomy'] ) 
							&& $qt_val['taxonomy'] == $walker_args['taxonomy'] 
						) {
							unset( $query['tax_query'][ $qt_index ] );
						}
					}
				}
				$aux_cache_query = new WP_Query( $query );
				if ( 
					is_array( $aux_cache_query->posts ) 
					&& ! empty( $aux_cache_query->posts ) 
				) {
					$f_taxes = array( $walker_args['taxonomy'] );
					$wpv_data_cache = WPV_Cache::generate_cache( $aux_cache_query->posts, array( 'tax' => $f_taxes ) );
					if ( 
						isset( $wpv_data_cache[ $walker_args['taxonomy'] . '_relationships' ] ) 
						&& is_array( $wpv_data_cache[ $walker_args['taxonomy'] . '_relationships' ] ) 
					) {
						foreach ( $wpv_data_cache[ $walker_args['taxonomy'] . '_relationships' ] as $pid => $tax_array ) {
							if ( 
								is_array( $tax_array ) 
								&& count( $tax_array ) > 0 
							) {
								//$this_post_taxes = array_combine( array_values( array_keys( $tax_array ) ) , array_keys( $tax_array ) );
								$this_post_taxes = wp_list_pluck( $tax_array, 'term_id', 'term_id' );
								$walker_args['query_cache'][ $pid ] = $this_post_taxes;
							}
						}
					}
				}
			}
		}
		return $walker_args;
	}
	
	/**
	 * Return the frontend filter by a taxonomy as a select dropdown.
	 *
	 * @param array $atts        The shortcode attributes
	 * @param array $walker_args The Walker class arguments
	 *
	 * @return string
	 *
	 * @since 2.4.0
	 */
	public static function wpv_control_post_taxonomy_select( $atts = array(), $walker_args = array() ) {
		$taxonomy_filter_output = '';
		$get_value = ( $atts['hide_empty'] == 'true' ) ? '' : 'all';
		$default_selected = '';
		
		$select_args = array(
			'name'	=> $walker_args['name'],
			'class'	=> ( empty( $walker_args['class'] ) ) ? array() : explode( ' ', $walker_args['class'] )
		);
		
		$select_args['class'][] = 'js-wpv-filter-trigger';
		if ( 'bootstrap' == $walker_args['output'] ) {
			$select_args['class'][] = 'form-control';
		}
		if ( ! empty( $walker_args['style'] ) ) {
			$select_args['style'] = $walker_args['style'];
		}
		
		if ( 'multi-select' == $walker_args['type'] ) {
			$select_args['name'] = $walker_args['name'] . '[]';
			$select_args['multiple'] = 'multiple';
			$select_args['size'] = '10';
		}
		
		$taxonomy_filter_output .= '<select';
		foreach ( $select_args as $att_key => $att_value ) {
			if ( 
				in_array( $att_key, array( 'style', 'class' ) ) 
				&& empty( $att_value )
			) {
				continue;
			}
			$taxonomy_filter_output .= ' ' . $att_key . '="';
			if ( is_array( $att_value ) ) {
				$att_value = array_unique( $att_value );
				$att_real_value = implode( ' ', $att_value );
				$taxonomy_filter_output .= $att_real_value;
			} else {
				$taxonomy_filter_output .= $att_value;
			}
			$taxonomy_filter_output .= '"';
		}
		$taxonomy_filter_output .= '>';
		
		if ( $walker_args['type'] == 'select' ) {
			if ( 
				empty( $walker_args['selected'] ) 
				|| in_array( (string) 0, $walker_args['selected'] ) 
			) {
				$default_selected = " selected='selected'";
			}

			// The select control shouldn't include an option with value="0" 
			// when we are on a taxonomy archive page and the page includes a filter by that taxonomy.
			$create_empty_value = true;
			if (
				is_tax()
				|| is_category()
				|| is_tag()
			) {
				global $wp_query;
				$term = $wp_query->get_queried_object();

				if ( 
					$term
					&& isset( $term->taxonomy )
					&& $term->taxonomy == $walker_args['taxonomy']
				) {
					$create_empty_value = false;
				}
			}

			if ( true == $create_empty_value ) {
				// TODO we do not add counters nor any format here, as we do for custom fields. 
				// WE might need to review this.
				$taxonomy_filter_output .= '<option' 
					. $default_selected 
					. ' value="0">' 
					. $atts['default_label'] 
					. '</option>';
			}
		}
		$taxonomy_filter_walker = new WPV_Walker_Taxonomy_Select( $walker_args );
		$taxonomy_filter_output .= WPV_Taxonomy_Frontend_Filter::walker_walk(
			array(
				'taxonomy'			=> $walker_args['taxonomy'], 
				'selected_cats'		=> $walker_args['selected'], 
				'walker'			=> $taxonomy_filter_walker, 
				'taxonomy_orderby'	=> $atts['taxonomy_orderby'], 
				'taxonomy_order'	=> $atts['taxonomy_order'], 
				'get_value'			=> $get_value,
				'output'			=> $walker_args['output']
			)
		);
		$taxonomy_filter_output .= '</select>';
		
		return $taxonomy_filter_output;
	}
	
	/**
	 * Return the frontend filter by a taxonomy as a set of radio inputs.
	 *
	 * @param array $atts        The shortcode attributes
	 * @param array $walker_args The Walker class arguments
	 *
	 * @return string
	 *
	 * @since 2.4.0
	 */
	public static function wpv_control_post_taxonomy_radios( $atts = array(), $walker_args = array() ) {
		$taxonomy_filter_output = '';
		$get_value = ( $atts['hide_empty'] == 'true' ) ? '' : 'all';
		$default_selected = '';
		$name = $walker_args['taxonomy'];
		if ( $name == 'category' ) {
			$name = 'post_category';
		}
		
		if ( 
			isset( $atts['default_label'] ) 
			&& ! empty( $atts['default_label'] )
		) {
			if ( 
				empty( $walker_args['selected'] ) 
				|| in_array( (string) 0, $walker_args['selected'] )
			) {
				$default_selected = " checked='checked'";
			}

			// The radio control shouldn't include an option with value="0" when we are on a taxonomy archive page and the page includes a filter by that taxonomy.
			$create_empty_value = true;
			if (
				is_tax()
				|| is_category()
				|| is_tag()
			) {
				global $wp_query;
				$term = $wp_query->get_queried_object();

				if ( $term
					&& isset( $term->taxonomy )
					&& $term->taxonomy == $walker_args['taxonomy']
				) {
					$create_empty_value = false;
				}
			}

			if ( true == $create_empty_value ) {
				
				switch( $walker_args['output'] ) {
					case 'bootstrap':
						$taxonomy_filter_output .= '<div class="radio">';
						$taxonomy_filter_output .= '<label for="' . $name . '-"' 
							. ( ! empty( $walker_args['label_style'] ) ? ( ' style="' . $walker_args['label_style'] . '"' ) : '' ) 
							. ( ! empty( $walker_args['label_class'] ) ? ( ' class="'. $walker_args['label_class'] . '"' ) : '' )
							. '>';
						$taxonomy_filter_output .= '<input id="' . $name . '-"'
							. ( ! empty( $walker_args['style'] ) ? ' style="' . $walker_args['style'] . '"' : '' ) 
							. ' class="js-wpv-filter-trigger'. ( ! empty( $walker_args['class'] ) ? ' '. $walker_args['class'] : '' ) .'"'
							. ' name="' . $walker_args['name'] . '"'
							. ' type="radio"'
							. ' value="0"' 
							. $default_selected 
							. '/>';
						$taxonomy_filter_output .= $atts['default_label'];
						$taxonomy_filter_output .= '</label>';
						$taxonomy_filter_output .= '</div>';
						break;
					case 'legacy':
					default:
						$taxonomy_filter_output .= '<input id="' . $name . '-"'
							. ( ! empty( $walker_args['style'] ) ? ' style="' . $walker_args['style'] . '"' : '' ) 
							. ' class="js-wpv-filter-trigger'. ( ! empty( $walker_args['class'] ) ? ' '. $walker_args['class'] : '' ) .'"'
							. ' name="' . $walker_args['name'] . '"'
							. ' type="radio"'
							. ' value="0"' 
							. $default_selected 
							. '/>'
							. ' '
							. '<label for="' . $name . '-"' 
							. ( ! empty( $walker_args['label_style'] ) ? ' style="' . $walker_args['label_style'] . '"' : '' ) 
							. ' class="radios-taxonomies-title'. ( ! empty( $walker_args['label_class'] ) ? ' '. $walker_args['label_class'] : '' ) .'"'
							. '>' 
							. $atts['default_label'] 
							. '</label>';
						break;
				}
				
			}
		}
		$taxonomy_filter_walker = new WPV_Walker_Taxonomy_Radios( $walker_args );
		$taxonomy_filter_output .= WPV_Taxonomy_Frontend_Filter::walker_walk(
			array(
				'taxonomy'			=> $walker_args['taxonomy'], 
				'selected_cats'		=> $walker_args['selected'], 
				'walker'			=> $taxonomy_filter_walker, 
				'taxonomy_orderby'	=> $atts['taxonomy_orderby'], 
				'taxonomy_order'	=> $atts['taxonomy_order'], 
				'get_value'			=> $get_value,
				'output'			=> $walker_args['output']
			)
		);
		
		return $taxonomy_filter_output;
	}
	
	/**
	 * Return the frontend filter by a taxonomy as a set of checkboxes.
	 *
	 * @param array $atts        The shortcode attributes
	 * @param array $walker_args The Walker class arguments
	 *
	 * @return string
	 *
	 * @since 2.4.0
	 */
	public static function wpv_control_post_taxonomy_checkboxes( $atts = array(), $walker_args = array() ) {
		$taxonomy_filter_output = '';
		$get_value = ( $atts['hide_empty'] == 'true' ) ? '' : 'all';
		$default_selected = '';
		$name = $walker_args['taxonomy'];
		if ( $name == 'category' ) {
			$name = 'post_category';
		}
		
		$taxonomy_filter_walker = new WPV_Walker_Taxonomy_Checkboxes( $walker_args );
		if ( $walker_args['output'] == 'legacy' ) {
			$taxonomy_filter_output .= '<ul class="categorychecklist form-no-clear">';
		}
		$taxonomy_filter_output .= WPV_Taxonomy_Frontend_Filter::walker_walk(
			array(
				'taxonomy'			=> $walker_args['taxonomy'], 
				'selected_cats'		=> $walker_args['selected'], 
				'walker'			=> $taxonomy_filter_walker, 
				'taxonomy_orderby'	=> $atts['taxonomy_orderby'], 
				'taxonomy_order'	=> $atts['taxonomy_order'], 
				'get_value'			=> $get_value, 
				'output'			=> $walker_args['output']
			)
		);
		if ( $walker_args['output'] == 'legacy' ) {
			$taxonomy_filter_output .= '</ul>';
		}
		
		return $taxonomy_filter_output;
	}
	
	/**
	 * Auxiliar method to walk the walker for displaying the output of the wpv-control-post-taxonomy shortcode.
	 *
	 * @param $args array
	 *		'taxonomy'		string	The taxonomy slug
	 * 		'walker'		string	The Walker class name to use
	 *		'taxonomy_orderby'	string	Order field for the options
	 *		'taxonomy_order'	string	Direction for sorting the options
	 *		'get_value'		string	Legacy: flag to maybe omit the terms without assigned posts.
	 *		'output'		string	The kind of output to produce: 'legacy'|'bootstrap'
	 *
	 * @since 2.4.0
	 */
	public static function walker_walk( $args = array() ) {
		$defaults = array(
			'popular_cats'		=> array(),
			'walker'			=> null,
			'taxonomy'			=> 'category',
			'taxonomy_orderby'	=> 'name',
			'taxonomy_order'	=> 'ASC',
			'get_value'			=> 'all',
			'output'			=> 'legacy'
		);
		$args = wp_parse_args( $args, $defaults );
		
		if ( 
			empty( $args['taxonomy'] ) 
			|| empty( $args['walker'] ) 
			|| ! is_a( $args['walker'], 'Walker' ) 
		) {
			return;
		}
		
		$args['taxonomy_orderby'] = toolset_getarr( $args, 'taxonomy_orderby', 'name', array( 'id', 'count', 'name', 'slug', 'term_group', 'none' ) );
		$args['taxonomy_order'] = toolset_getarr( $args, 'taxonomy_order', 'ASC', array( 'ASC', 'DESC' ) );
		
		$walker_walk_args = array( 
			'taxonomy'		=> $args['taxonomy'], 
			'popular_cats'	=> array()
		);
		
		if ( 'legacy' == $args['output'] ) {
			$walker_walk_args['popular_cats'] = get_terms( 
				$args['taxonomy'], 
				array( 
					'fields'		=> 'ids', 
					'orderby'		=> 'count', 
					'order'			=> 'DESC', 
					'number'		=> 10, 
					'hierarchical'	=> false 
				) 
			);
		}
		
		$taxonomy_terms = (array) get_terms( 
			$args['taxonomy'], 
			array(
				'get'		=> $args['get_value'], 
				'orderby'	=> $args['taxonomy_orderby'], 
				'order'		=> $args['taxonomy_order'] 
			) 
		);
		
		return call_user_func_array( array( &$args['walker'], 'walk' ), array( $taxonomy_terms, 0, $walker_walk_args ) );
	}
	
	/**
	 * Register the wpv-control-post-taxonomy shortcode attributes in the shortcodes GUI API.
	 *
	 * @since 2.4.0
	 */
	
	public static function wpv_shortcodes_register_wpv_control_post_taxonomy_data( $views_shortcodes ) {
		$views_shortcodes['wpv-control-post-taxonomy'] = array(
			'callback' => array( 'WPV_Taxonomy_Frontend_Filter', 'wpv_shortcodes_get_wpv_control_post_taxonomy_data' )
		);
		return $views_shortcodes;
	}
	
	public static function wpv_shortcodes_get_wpv_control_post_taxonomy_data( $parameters = array(), $overrides = array() ) {
		$data = array(
			'name' => __( 'Filter by post taxonomy', 'wpv-views' ),
			'label' => __( 'Filter by post taxonomy', 'wpv-views' ),
			'additional_data' => array(),
			'attributes' => array(
				'display-options' => array(
					'label' => __( 'Display options', 'wpv-views' ),
					'header' => __( 'Display options', 'wpv-views' ),
					'fields' => array(
						'type' => array(
							'label'			=> __( 'Type of control', 'wpv-views'),
							'type'			=> 'select',
							'options'		=> array(
												'select'		=> __( 'Select dropdown', 'wpv-views' ),
												'multi-select'	=> __( 'Select multiple', 'wpv-views' ),
												'radios'		=> __( 'Set of radio buttons', 'wpv-views' ),
												'checkboxes'	=> __( 'Set of checkboxes', 'wpv-views' ),
											),
							'default_force' => 'select'
						),
						'default_label' => array(
							'label'			=> __( 'Label for the first \'default\' option', 'wpv-views'),
							'type'			=> 'text',
							'default'		=> '',
						),
						'format' => array(
							'label'			=> __( 'Format', 'wpv-views'),
							'type'			=> 'text',
							'placeholder'	=> '%%NAME%%',
							'description'	=> __( 'You can use %%NAME%% or %%COUNT%% as placeholders.', 'wpv-views' ),
						),
						'taxonomy_order_combo' => array(
							'label'			=> __( 'Options sorting', 'wpv-views' ),
							'type'			=> 'grouped',
							'fields'		=> array(
								'orderby' => array(
									'pseudolabel'	=> __( 'Order by', 'wpv-views'),
									'type'			=> 'select',
									'default'		=> 'name',
									'options'		=> array(
														'name'	=> __( 'Name', 'wpv-views' ),
														'id'	=> __( 'ID', 'wpv-views' ),
														'count'	=> __( 'Count', 'wpv-views' ),
														'slug'	=> __( 'Slug', 'wpv-views' ),
														'term_group'	=> __( 'Group', 'wpv-views' ),
														'none'	=> __( 'None', 'wpv-views' ),
													),
									'description'	=> __( 'Order options by this parameter.', 'wpv-views' ),
								),
								'order' => array(
									'pseudolabel'	=> __( 'Order', 'wpv-views'),
									'type'			=> 'select',
									'default'		=> 'ASC',
									'options'		=> array(
														'ASC'	=> __( 'Ascending', 'wpv-views' ),
														'DESC'	=> __( 'Descending', 'wpv-views' ),
													),
									'description'	=> __( 'Order options in this direction.', 'wpv-views' ),
								)
							)
						),
						'value_compare' => array(
							'label'			=> __( 'Comparison function', 'wpv-views' ),
							'type'			=> 'select',
							'default'		=> 'IN',
							'options'		=> array(
												'IN'		=> __( 'Posts matching any of the selected terms', 'wpv-views' ),
												'AND'		=> __( 'Post matching all the selected terms', 'wpv-views' ),
												'NOT IN'	=> __( 'Post matching none of the selected terms', 'wpv-views' ),
											),
						),
						'url_param' => array(
							'label'			=> __( 'URL parameter to use', 'wpv-views'),
							'type'			=> 'text',
							'default_force'	=> isset( $parameters['attributes']['taxonomy'] ) ? 'wpv-' . $parameters['attributes']['taxonomy'] : 'wpv-taxonomy-filter',
							'required'		=> true,
							'description'	=> __( 'The filter will apply the values passed to this URL parameter.', 'wpv-views' )
						),
						/*
						'hide_empty' => array(
							// @deprecated in 2.4.0
							'label'			=> __( 'Hide empty', 'wpv-views'),
							'type'			=> 'select',
							'default'		=> 'false',
							'options'		=> array(
												'false'	=> __( 'Show', 'wpv-views' ),
												'true'	=> __( 'Hide', 'wpv-views' ),
											),
							'description'	=> __( 'Hide empty terms.', 'wpv-views' ),
						),
						*/
					),
				),
				'style-options' => array(
					'label' => __( 'Style options', 'wpv-views' ),
					'header' => __( 'Style options', 'wpv-views' ),
					'fields' => array(
						'output' => array(
							'label'		=> __( 'Output style', 'wpv-views' ),
							'type'		=> 'radio',
							'options'		=> array(
								'bootstrap'	=> __( 'Fully styled output', 'wpv-views' ),
								'legacy'	=> __( 'Raw output', 'wpv-views' ),
							),
							'default'		=> 'bootstrap',
						),
						'input_frontend_combo' => array(
							'label'			=> __( 'Element styling', 'wpv-views' ),
							'type'			=> 'grouped',
							'fields'		=> array(
								'class' => array(
									'pseudolabel'	=> __( 'Element classnames', 'wpv-views'),
									'type'			=> 'text',
									'description'	=> __( 'Space-separated list of classnames to apply. For example: classone classtwo', 'wpv-views' )
								),
								'style' => array(
									'pseudolabel'	=> __( 'Element inline style', 'wpv-views'),
									'type'			=> 'text',
									'description'	=> __( 'Raw inline styles to apply. For example: color:red;background:none;', 'wpv-views' )
								),
							),
						),
						'label_frontend_combo' => array(
							'label'			=> __( 'Label styling', 'wpv-views' ),
							'type'			=> 'grouped',
							'fields'		=> array(
								'label_class' => array(
									'pseudolabel'	=> __( 'Label classnames', 'wpv-views'),
									'type'			=> 'text',
									'description'	=> __( 'Space-separated list of classnames to apply to the labels. For example: classone classtwo', 'wpv-views' )
								),
								'label_style' => array(
									'pseudolabel'	=> __( 'Label inline style', 'wpv-views'),
									'type'			=> 'text',
									'description'	=> __( 'Raw inline styles to apply to the labels. For example: color:red;background:none;', 'wpv-views' )
								),
							),
						),
					)
				),
			),
		);
		
		$dialog_label = __( 'Filter by post taxonomy', 'wpv-views' );
		$dialog_target = false;
		
		if ( isset( $parameters['attributes']['taxonomy'] ) ) {
			$dialog_target = $parameters['attributes']['taxonomy'];
		}
		if ( isset( $overrides['attributes']['taxonomy'] ) ) {
			$dialog_target = $overrides['attributes']['taxonomy'];
		}
		
		if ( $dialog_target ) {
			$taxonomy_object = get_taxonomy( $dialog_target );
			if ( $taxonomy_object ) {
				$title = $taxonomy_object->label;
				$dialog_label = sprintf( __( 'Filter by %s', 'wpv-views' ), $taxonomy_object->label );
				$data['additional_data']['shortcode_label'] = $taxonomy_object->label;
			}
		}
		
		$data['name']	= $dialog_label;
		$data['label']	= $dialog_label;
		
		return $data;
	}
	
}

/**
* This might be deprecated, but does not hurt
* Maybe add a _doing_it_wrong call_user_func
*/
function wpv_get_taxonomy_view_params($view_settings) {
	$results = array();
	
	$taxonomies = get_taxonomies('', 'objects');
	foreach ($taxonomies as $category_slug => $category) {
		$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
		
		if (isset($view_settings[$relationship_name])) {
			
			$save_name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input_' . $category->name;			
			
			if ($view_settings['tax_' . $category->name . '_relationship'] == "FROM ATTRIBUTE") {
				$attribute = $view_settings['taxonomy-' . $category->name . '-attribute-url'];
				$results[] = $attribute;
			}
		}
    }
    
	return $results;
}

