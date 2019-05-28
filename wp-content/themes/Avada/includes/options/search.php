<?php
/**
 * Avada Options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      4.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Search.
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_search( $sections ) {

	$sections['search'] = array(
		'label'    => esc_html__( 'Search', 'Avada' ),
		'id'       => 'heading_search',
		'priority' => 23,
		'icon'     => 'el-icon-search',
		'fields'   => array(
			'search_form_options_section'   => array(
				'label'       => esc_html__( 'Search Form', 'Avada' ),
				'description' => '',
				'id'          => 'search_form_options_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => array(
					'search_content'             => array(
						'label'       => esc_html__( 'Search Results Content', 'Avada' ),
						'description' => esc_html__( 'Controls the type of content that displays in search results.', 'Avada' ),
						'id'          => 'search_content',
						'default'     => array( 'post', 'page', 'avada_portfolio', 'avada_faq', 'product', 'tribe_events' ),
						'type'        => 'select',
						'multi'       => true,
						'choices'     => array(
							'post'            => esc_html__( 'Posts', 'Avada' ),
							'page'            => esc_html__( 'Pages', 'Avada' ),
							'avada_portfolio' => esc_html__( 'Portfolio Items', 'Avada' ),
							'avada_faq'       => esc_html__( 'FAQ Items', 'Avada' ),
							'product'         => esc_html__( 'WooCommerce Products', 'Avada' ),
							'tribe_events'    => esc_html__( 'Events Calendar Posts', 'Avada' ),
						),
					),
					'search_limit_to_post_titles' => array(
						'label'       => esc_html__( 'Limit Search to Post Titles', 'Avada' ),
						'description' => esc_html__( 'Turn on to limit the search to post titles only.', 'Avada' ),
						'id'          => 'search_limit_to_post_titles',
						'default'     => '0',
						'type'        => 'switch',
					),
					'search_form_design'                => array(
						'label'       => esc_html__( 'Search Form Design', 'Avada' ),
						'description' => esc_html__( 'Controls the design of the search forms.', 'Avada' ),
						'id'          => 'search_form_design',
						'default'     => 'classic',
						'type'        => 'radio-buttonset',
						'choices'     => array(
							'classic' => esc_html__( 'Classic', 'Avada' ),
							'clean'   => esc_html__( 'Clean', 'Avada' ),
						),
					),
					'live_search' => array(
						'label'       => esc_html__( 'Enable Live Search', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable live search results on menu search field and other fitting search forms.', 'Avada' ),
						'id'          => 'live_search',
						'default'     => '0',
						'type'        => 'switch',
					),
					'live_search_min_char_count' => array(
						'label'       => esc_html__( 'Live Search Minimal Character Count', 'Avada' ),
						'description' => esc_html__( 'Set the minimal character count to trigger the live search.', 'Avada' ),
						'id'          => 'live_search_min_char_count',
						'default'     => '4',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '1',
							'max'  => '20',
							'step' => '1',
						),
						'required'    => array(
							array(
								'setting'  => 'live_search',
								'operator' => '=',
								'value'    => '1',
							),
						),
					),
					'live_search_results_per_page'    => array(
						'label'       => esc_html__( 'Live Search Number of Posts', 'Avada' ),
						'description' => esc_html__( 'Controls the number of posts that should be displayed as search result suggestions.', 'Avada' ),
						'id'          => 'live_search_results_per_page',
						'default'     => '100',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '10',
							'max'  => '500',
							'step' => '10',
						),
						'required'    => array(
							array(
								'setting'  => 'live_search',
								'operator' => '=',
								'value'    => '1',
							),
						),
					),
					'live_search_results_height'    => array(
						'label'       => esc_html__( 'Live Search Results Container Height', 'Avada' ),
						'description' => esc_html__( 'Controls the height of the container in which the search results will be listed.', 'Avada' ),
						'id'          => 'live_search_results_height',
						'default'     => '250',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '100',
							'max'  => '800',
							'step' => '5',
						),
						'required'    => array(
							array(
								'setting'  => 'live_search',
								'operator' => '=',
								'value'    => '1',
							),
						),
					),
					'live_search_display_featured_image' => array(
						'label'       => esc_html__( 'Live Search Display Featured Image', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the featured image of each live search result.', 'Avada' ),
						'id'          => 'live_search_display_featured_image',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'live_search',
								'operator' => '=',
								'value'    => '1',
							),
						),
					),
					'live_search_display_post_type' => array(
						'label'       => esc_html__( 'Live Search Display Post Type', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the post type of each live search result.', 'Avada' ),
						'id'          => 'live_search_display_post_type',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'live_search',
								'operator' => '=',
								'value'    => '1',
							),
						),
					),
				),
			),
			'search_page_options_section'   => array(
				'label'       => esc_html__( 'Search Page', 'Avada' ),
				'description' => '',
				'id'          => 'search_page_options_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => array(
					'search_layout'              => array(
						'label'       => esc_html__( 'Search Results Layout', 'Avada' ),
						'description' => esc_html__( 'Controls the layout for the search results page.', 'Avada' ),
						'id'          => 'search_layout',
						'default'     => 'Grid',
						'type'        => 'select',
						'choices'     => array(
							'Large'            => esc_html__( 'Large', 'Avada' ),
							'Medium'           => esc_html__( 'Medium', 'Avada' ),
							'Large Alternate'  => esc_html__( 'Large Alternate', 'Avada' ),
							'Medium Alternate' => esc_html__( 'Medium Alternate', 'Avada' ),
							'Grid'             => esc_html__( 'Grid', 'Avada' ),
							'Timeline'         => esc_html__( 'Timeline', 'Avada' ),
						),
					),
					'search_results_per_page'    => array(
						'label'       => esc_html__( 'Number of Search Results Per Page', 'Avada' ),
						'description' => esc_html__( 'Controls the number of search results per page.', 'Avada' ),
						'id'          => 'search_results_per_page',
						'default'     => '10',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
					),
					'search_pagination_type'                 => array(
						'label'           => esc_html__( 'Search Pagination Type', 'Avada' ),
						'description'     => esc_html__( 'Controls the pagination type for the search results page.', 'Avada' ),
						'id'              => 'search_pagination_type',
						'default'         => 'Pagination',
						'type'            => 'radio-buttonset',
						'choices'         => array(
							'Pagination'       => esc_html__( 'Pagination', 'Avada' ),
							'Infinite Scroll'  => esc_html__( 'Infinite Scroll', 'Avada' ),
							'load_more_button' => esc_html__( 'Load More Button', 'Avada' ),
						),
						'active_callback' => array( 'Avada_Options_Conditionals', 'is_blog' ),
					),
					'search_grid_columns'            => array(
						'label'       => esc_html__( 'Number of Columns', 'Avada' ),
						'description' => __( 'Controls the number of columns for grid and masonry layouts. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'Avada' ),
						'id'          => 'search_grid_columns',
						'default'     => 3,
						'type'        => 'slider',
						'class'       => 'fusion-or-gutter',
						'choices'     => array(
							'min'  => 1,
							'max'  => 6,
							'step' => 1,
						),
						'required'    => array(
							array(
								'setting'  => 'search_layout',
								'operator' => '=',
								'value'    => 'Grid',
							),
							array(
								'setting'  => 'search_layout',
								'operator' => '=',
								'value'    => 'masonry',
							),
						),
					),
					'search_grid_column_spacing'     => array(
						'label'       => esc_html__( 'Column Spacing', 'Avada' ),
						'description' => esc_html__( 'Controls the column spacing for search results.', 'Avada' ),
						'id'          => 'search_grid_column_spacing',
						'default'     => '40',
						'type'        => 'slider',
						'class'       => 'fusion-or-gutter',
						'choices'     => array(
							'min'  => '0',
							'step' => '1',
							'max'  => '300',
							'edit' => 'yes',
						),
						'required'    => array(
							array(
								'setting'  => 'search_layout',
								'operator' => '=',
								'value'    => 'Grid',
							),
							array(
								'setting'  => 'search_layout',
								'operator' => '=',
								'value'    => 'masonry',
							),
						),
					),
					'search_content_length'                       => array(
						'label'       => esc_html__( 'Search Content Display', 'Avada' ),
						'description' => esc_html__( 'Controls if the search results content displays as an excerpt or full content or is completely disabled.', 'Avada' ),
						'id'          => 'search_content_length',
						'default'     => 'Excerpt',
						'type'        => 'radio-buttonset',
						'choices'     => array(
							'excerpt'      => esc_html__( 'Excerpt', 'Avada' ),
							'full_content' => esc_html__( 'Full Content', 'Avada' ),
							'no_text'      => esc_html__( 'No Text', 'Avada' ),
						),
					),
					'search_excerpt_length'                  => array(
						'label'       => esc_html__( 'Search Excerpt Length', 'Avada' ),
						'description' => esc_html__( 'Controls the number of words (or characters) in the search results excerpts.', 'Avada' ),
						'id'          => 'search_excerpt_length',
						'default'     => '10',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '500',
							'step' => '1',
						),
						'required'    => array(
							array(
								'setting'  => 'search_content_length',
								'operator' => '==',
								'value'    => 'Excerpt',
							),
						),
					),
					'search_strip_html_excerpt'                   => array(
						'label'       => esc_html__( 'Search Strip HTML from Excerpt', 'Avada' ),
						'description' => esc_html__( 'Turn on to strip HTML content from the excerpt for the search results page.', 'Avada' ),
						'id'          => 'search_strip_html_excerpt',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'search_content_length',
								'operator' => '==',
								'value'    => 'Excerpt',
							),
						),
					),
					'search_featured_images'     => array(
						'label'       => esc_html__( 'Featured Images for Search Results', 'Avada' ),
						'description' => esc_html__( 'Turn on to display featured images for search results.', 'Avada' ),
						'id'          => 'search_featured_images',
						'default'     => '1',
						'type'        => 'switch',
					),
					'search_meta'              => array(
						'label'       => esc_html__( 'Search Results Meta', 'Avada' ),
						'description' => esc_html__( 'Select the post meta data you want to be displayed in the individual search results.', 'Avada' ),
						'id'          => 'search_meta',
						'default'     => array( 'author', 'date', 'categories', 'comments', 'read_more' ),
						'type'        => 'select',
						'multi'       => true,
						'choices'     => array(
							'author'     => esc_html__( 'Author', 'Avada' ),
							'date'       => esc_html__( 'Date', 'Avada' ),
							'categories' => esc_html__( 'Categories', 'Avada' ),
							'tags'       => esc_html__( 'Tags', 'Avada' ),
							'comments'   => esc_html__( 'Comments', 'Avada' ),
							'read_more'  => esc_html__( 'Read More Link', 'Avada' ),
							'post_type'  => esc_html__( 'Post Type', 'Avada' ),
						),
					),
					'search_new_search_position' => array(
						'label'       => esc_html__( 'Search Field Position', 'Avada' ),
						'description' => esc_html__( 'Controls the position of the search bar on the search results page.', 'Avada' ),
						'id'          => 'search_new_search_position',
						'default'     => 'top',
						'type'        => 'radio-buttonset',
						'choices'     => array(
							'top'    => esc_html__( 'Above Results', 'Avada' ),
							'bottom' => esc_html__( 'Below Results', 'Avada' ),
							'hidden' => esc_html__( 'Hide', 'Avada' ),
						),
					),
				),
			),
		),
	);

	return $sections;

}
