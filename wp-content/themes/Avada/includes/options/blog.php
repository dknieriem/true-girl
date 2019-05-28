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
 * Blog settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_blog( $sections ) {

	$sections['blog'] = array(
		'label'    => esc_html__( 'Blog', 'Avada' ),
		'id'       => 'blog_section',
		'priority' => 15,
		'icon'     => 'el-icon-file-edit',
		'class'    => 'hidden-section-heading',
		'fields'   => array(
			'blog_general_options'    => array(
				'label'       => esc_html__( 'General Blog', 'Avada' ),
				'description' => '',
				'id'          => 'blog_general_options',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => array(
					'general_blog_important_note_info'     => array(
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options on this tab only control the assigned blog page in "Settings > Reading", blog archives or the blog single post page, not the blog element. The only options on this tab that work with the blog element are the Date Format options and Load More Post Button Color.', 'Avada' ) . '</div>',
						'id'          => 'general_blog_important_note_info',
						'type'        => 'custom',
					),
					'blog_page_title_bar'                  => array(
						'label'           => esc_html__( 'Blog Page Title Bar', 'Avada' ),
						'description'     => esc_html__( 'Controls how the page title bar displays on single blog posts and blog archive pages.', 'Avada' ),
						'id'              => 'blog_page_title_bar',
						'default'         => 'bar_and_content',
						'choices'         => array(
							'bar_and_content' => esc_html__( 'Show Bar and Content', 'Avada' ),
							'content_only'    => esc_html__( 'Show Content Only', 'Avada' ),
							'hide'            => esc_html__( 'Hide', 'Avada' ),
						),
						'type'            => 'select',
						'active_callback' => array( 'Avada_Options_Conditionals', 'is_blog' ),
					),
					'blog_show_page_title_bar'             => array(
						'label'           => esc_html__( 'Blog Assigned Page Title Bar', 'Avada' ),
						'description'     => esc_html__( 'Controls how the page title bar displays on the assigned blog page in "Settings > Reading".', 'Avada' ),
						'id'              => 'blog_show_page_title_bar',
						'default'         => 'bar_and_content',
						'choices'         => array(
							'bar_and_content' => esc_html__( 'Show Bar and Content', 'Avada' ),
							'content_only'    => esc_html__( 'Show Content Only', 'Avada' ),
							'hide'            => esc_html__( 'Hide', 'Avada' ),
						),
						'type'            => 'select',
						'active_callback' => array( 'Avada_Options_Conditionals', 'is_blog' ),
					),
					'blog_title'                           => array(
						'label'           => esc_html__( 'Blog Page Title', 'Avada' ),
						'description'     => esc_html__( 'Controls the title text that displays in the page title bar only if your front page displays your latest post in "Settings > Reading".', 'Avada' ),
						'id'              => 'blog_title',
						'default'         => 'Blog',
						'type'            => 'text',
						'active_callback' => array( 'Avada_Options_Conditionals', 'is_blog' ),
						'required'        => array(
							array(
								'setting'  => 'blog_show_page_title_bar',
								'operator' => '!=',
								'value'    => 'hide',
							),
						),
					),
					'blog_subtitle'                        => array(
						'label'           => esc_html__( 'Blog Page Subtitle', 'Avada' ),
						'description'     => esc_html__( 'Controls the subtitle text that displays in the page title bar only if your front page displays your latest post in "Settings > Reading".', 'Avada' ),
						'id'              => 'blog_subtitle',
						'default'         => '',
						'type'            => 'text',
						'active_callback' => array( 'Avada_Options_Conditionals', 'is_blog' ),
						'required'        => array(
							array(
								'setting'  => 'blog_show_page_title_bar',
								'operator' => '!=',
								'value'    => 'hide',
							),
						),
					),
					'blog_layout'                          => array(
						'label'           => esc_html__( 'Blog Layout', 'Avada' ),
						'description'     => esc_html__( 'Controls the layout for the assigned blog page in "Settings > Reading".', 'Avada' ),
						'id'              => 'blog_layout',
						'default'         => 'Large',
						'type'            => 'select',
						'choices'         => array(
							'Large'            => esc_html__( 'Large', 'Avada' ),
							'Medium'           => esc_html__( 'Medium', 'Avada' ),
							'Large Alternate'  => esc_html__( 'Large Alternate', 'Avada' ),
							'Medium Alternate' => esc_html__( 'Medium Alternate', 'Avada' ),
							'Grid'             => esc_html__( 'Grid', 'Avada' ),
							'Timeline'         => esc_html__( 'Timeline', 'Avada' ),
							'masonry'          => esc_html__( 'Masonry', 'Avada' ),
						),
						'active_callback' => array( 'Avada_Options_Conditionals', 'is_blog' ),
					),
					'blog_archive_layout'                  => array(
						'label'           => esc_html__( 'Blog Archive Layout', 'Avada' ),
						'description'     => esc_html__( 'Controls the layout for the blog archive pages.', 'Avada' ),
						'id'              => 'blog_archive_layout',
						'default'         => 'Large',
						'type'            => 'select',
						'choices'         => array(
							'Large'            => esc_html__( 'Large', 'Avada' ),
							'Medium'           => esc_html__( 'Medium', 'Avada' ),
							'Large Alternate'  => esc_html__( 'Large Alternate', 'Avada' ),
							'Medium Alternate' => esc_html__( 'Medium Alternate', 'Avada' ),
							'Grid'             => esc_html__( 'Grid', 'Avada' ),
							'Timeline'         => esc_html__( 'Timeline', 'Avada' ),
							'masonry'          => esc_html__( 'Masonry', 'Avada' ),
						),
						'active_callback' => array( 'Avada_Options_Conditionals', 'is_blog' ),
					),
					'blog_pagination_type'                 => array(
						'label'           => esc_html__( 'Pagination Type', 'Avada' ),
						'description'     => esc_html__( 'Controls the pagination type for the assigned blog page in "Settings > Reading" or blog archive pages.', 'Avada' ),
						'id'              => 'blog_pagination_type',
						'default'         => 'Pagination',
						'type'            => 'radio-buttonset',
						'choices'         => array(
							'Pagination'       => esc_html__( 'Pagination', 'Avada' ),
							'Infinite Scroll'  => esc_html__( 'Infinite Scroll', 'Avada' ),
							'load_more_button' => esc_html__( 'Load More Button', 'Avada' ),
						),
						'active_callback' => array( 'Avada_Options_Conditionals', 'is_blog' ),
					),
					'blog_load_more_posts_button_bg_color' => array(
						'label'       => esc_html__( 'Load More Posts Button Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the load more button for ajax post loading. Also works with the blog element.', 'Avada' ),
						'id'          => 'blog_load_more_posts_button_bg_color',
						'default'     => '#ebeaea',
						'type'        => 'color-alpha',
					),
					'blog_archive_grid_columns'            => array(
						'label'       => esc_html__( 'Number of Columns', 'Avada' ),
						'description' => __( 'Controls the number of columns for grid and masonry layout when using it for the assigned blog page in "Settings > Reading" or blog archive pages. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'Avada' ),
						'id'          => 'blog_archive_grid_columns',
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
								'setting'  => 'blog_layout',
								'operator' => '=',
								'value'    => 'Grid',
							),
							array(
								'setting'  => 'blog_layout',
								'operator' => '=',
								'value'    => 'masonry',
							),
							array(
								'setting'  => 'blog_archive_layout',
								'operator' => '=',
								'value'    => 'Grid',
							),
							array(
								'setting'  => 'blog_archive_layout',
								'operator' => '=',
								'value'    => 'masonry',
							),
						),
					),
					'blog_archive_grid_column_spacing'     => array(
						'label'       => esc_html__( 'Column Spacing', 'Avada' ),
						'description' => esc_html__( 'Controls the column spacing for blog posts for grid and masonry layout when using it for the assigned blog page in "Settings > Reading" or blog archive pages.', 'Avada' ),
						'id'          => 'blog_archive_grid_column_spacing',
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
								'setting'  => 'blog_layout',
								'operator' => '=',
								'value'    => 'Grid',
							),
							array(
								'setting'  => 'blog_layout',
								'operator' => '=',
								'value'    => 'masonry',
							),
							array(
								'setting'  => 'blog_archive_layout',
								'operator' => '=',
								'value'    => 'Grid',
							),
							array(
								'setting'  => 'blog_archive_layout',
								'operator' => '=',
								'value'    => 'masonry',
							),
						),
					),
					'blog_equal_heights'                   => array(
						'label'       => esc_html__( 'Equal Heights', 'Avada' ),
						'description' => esc_html__( 'Turn on to display grid boxes to equal heights per row.', 'Avada' ),
						'id'          => 'blog_equal_heights',
						'default'     => 0,
						'type'        => 'switch',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'blog_layout',
								'operator' => '=',
								'value'    => 'Grid',
							),
							array(
								'setting'  => 'blog_archive_layout',
								'operator' => '=',
								'value'    => 'Grid',
							),
						),
					),
					'blog_archive_grid_padding'            => array(
						'label'       => esc_html__( 'Blog Archive Grid Text Padding', 'Avada' ),
						'description' => esc_html__( 'Controls the top/right/bottom/left padding of the blog text when using grid / masonry or timeline layout. ', 'Avada' ),
						'id'          => 'blog_archive_grid_padding',
						'class'       => 'fusion-or-gutter',
						'choices'     => array(
							'top'    => true,
							'bottom' => true,
							'left'   => true,
							'right'  => true,
							'units'  => array( 'px', '%' ),
						),
						'default'     => array(
							'top'    => '30px',
							'bottom' => '20px',
							'left'   => '25px',
							'right'  => '25px',
						),
						'type'        => 'spacing',
						'required'    => array(
							array(
								'setting'  => 'blog_layout',
								'operator' => '=',
								'value'    => 'Grid',
							),
							array(
								'setting'  => 'blog_layout',
								'operator' => '=',
								'value'    => 'masonry',
							),
							array(
								'setting'  => 'blog_layout',
								'operator' => '=',
								'value'    => 'Timeline',
							),
							array(
								'setting'  => 'blog_archive_layout',
								'operator' => '=',
								'value'    => 'Grid',
							),
							array(
								'setting'  => 'blog_archive_layout',
								'operator' => '=',
								'value'    => 'masonry',
							),
							array(
								'setting'  => 'blog_archive_layout',
								'operator' => '=',
								'value'    => 'Timeline',
							),
						),
					),
					'blog_layout_alignment'                => array(
						'label'           => esc_html__( 'Blog Archive Grid Content Alignment', 'Avada' ),
						'description'     => esc_html__( 'Controls the content alignment of the blog text when using grid / masonry or timeline layout.', 'Avada' ),
						'id'              => 'blog_layout_alignment',
						'default'         => '',
						'type'            => 'radio-buttonset',
						'choices'         => array(
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						),
						'active_callback' => array( 'Avada_Options_Conditionals', 'is_blog' ),
						'class'           => 'fusion-or-gutter',
						'required'        => array(
							array(
								'setting'  => 'blog_layout',
								'operator' => '=',
								'value'    => 'Grid',
							),
							array(
								'setting'  => 'blog_layout',
								'operator' => '=',
								'value'    => 'masonry',
							),
							array(
								'setting'  => 'blog_layout',
								'operator' => '=',
								'value'    => 'Timeline',
							),
							array(
								'setting'  => 'blog_archive_layout',
								'operator' => '=',
								'value'    => 'Grid',
							),
							array(
								'setting'  => 'blog_archive_layout',
								'operator' => '=',
								'value'    => 'masonry',
							),
							array(
								'setting'  => 'blog_archive_layout',
								'operator' => '=',
								'value'    => 'Timeline',
							),
						),
					),
					'content_length'                       => array(
						'label'       => esc_html__( 'Blog Content Display', 'Avada' ),
						'description' => esc_html__( 'Controls if the blog content displays an excerpt or full content or is completely disabled for the assigned blog page in "Settings > Reading" or blog archive pages.', 'Avada' ),
						'id'          => 'content_length',
						'default'     => 'Excerpt',
						'type'        => 'radio-buttonset',
						'choices'     => array(
							'Excerpt'      => esc_html__( 'Excerpt', 'Avada' ),
							'Full Content' => esc_html__( 'Full Content', 'Avada' ),
							'hide'         => esc_html__( 'No Text', 'Avada' ),
						),
					),
					'excerpt_length_blog'                  => array(
						'label'       => esc_html__( 'Excerpt Length', 'Avada' ),
						'description' => esc_html__( 'Controls the number of words in the post excerpts for the assigned blog page in "Settings > Reading" or blog archive pages.', 'Avada' ),
						'id'          => 'excerpt_length_blog',
						'default'     => '10',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '500',
							'step' => '1',
						),
						'required'    => array(
							array(
								'setting'  => 'content_length',
								'operator' => '==',
								'value'    => 'Excerpt',
							),
						),
					),
					'strip_html_excerpt'                   => array(
						'label'       => esc_html__( 'Strip HTML from Excerpt', 'Avada' ),
						'description' => esc_html__( 'Turn on to strip HTML content from the excerpt for the assigned blog page in "Settings > Reading" or blog archive pages.', 'Avada' ),
						'id'          => 'strip_html_excerpt',
						'default'     => '1',
						'type'        => 'switch',
					),
					'featured_images'                      => array(
						'label'       => esc_html__( 'Featured Image / Video on Blog Archive Page', 'Avada' ),
						'description' => esc_html__( 'Turn on to display featured images and videos on the blog archive pages.', 'Avada' ),
						'id'          => 'featured_images',
						'default'     => '1',
						'type'        => 'switch',
					),
					'alternate_date_format_month_year'     => array(
						'label'       => esc_html__( 'Blog Alternate Layout Month and Year Format', 'Avada' ),
						'description' => __( 'Controls the month and year format for blog alternate layouts. <a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank" rel="noopener noreferrer">Formatting Date and Time</a>', 'Avada' ),
						'id'          => 'alternate_date_format_month_year',
						'default'     => 'm, Y',
						'type'        => 'text',
					),
					'alternate_date_format_day'            => array(
						'label'       => esc_html__( 'Blog Alternate Layout Day Format', 'Avada' ),
						'description' => __( 'Controls the day format for blog alternate layouts. <a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank" rel="noopener noreferrer">Formatting Date and Time</a>', 'Avada' ),
						'id'          => 'alternate_date_format_day',
						'default'     => 'j',
						'type'        => 'text',
					),
					'timeline_date_format'                 => array(
						'label'       => esc_html__( 'Blog Timeline Layout Date Format', 'Avada' ),
						'description' => __( 'Controls the timeline label format for blog timeline layouts. <a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank" rel="noopener noreferrer">Formatting Date</a>', 'Avada' ),
						'id'          => 'timeline_date_format',
						'default'     => 'F Y',
						'type'        => 'text',
					),
				),
			),
			'blog_single_post_info_2' => array(
				'label'       => esc_html__( 'Blog Single Post', 'Avada' ),
				'description' => '',
				'id'          => 'blog_single_post_info_2',
				'default'     => '',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => array(
					'blog_width_100'          => array(
						'label'       => esc_html__( '100% Width Page', 'Avada' ),
						'description' => esc_html__( 'Turn on to display blog posts at 100% browser width according to the window size. Turn off to follow site width.', 'Avada' ),
						'id'          => 'blog_width_100',
						'default'     => 0,
						'type'        => 'switch',
					),
					'featured_images_single'  => array(
						'label'       => esc_html__( 'Featured Image / Video on Single Blog Post', 'Avada' ),
						'description' => esc_html__( 'Turn on to display featured images and videos on single blog posts.', 'Avada' ),
						'id'          => 'featured_images_single',
						'default'     => '1',
						'type'        => 'switch',
					),
					'blog_pn_nav'             => array(
						'label'       => esc_html__( 'Previous/Next Pagination', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the previous/next post pagination for single blog posts.', 'Avada' ),
						'id'          => 'blog_pn_nav',
						'default'     => '1',
						'type'        => 'switch',
					),
					'blog_post_title'         => array(
						'label'       => esc_html__( 'Post Title', 'Avada' ),
						'description' => esc_html__( 'Controls if the post title displays above or below the featured post image or is disabled.', 'Avada' ),
						'id'          => 'blog_post_title',
						'default'     => 'below',
						'type'        => 'radio-buttonset',
						'choices'     => array(
							'below'    => esc_html__( 'Below ', 'Avada' ),
							'above'    => esc_html__( 'Above', 'Avada' ),
							'disabled' => esc_html__( 'Disabled', 'Avada' ),
						),
					),
					'blog_post_meta_position' => array(
						'label'       => esc_html__( 'Meta Data Position', 'Avada' ),
						'description' => esc_html__( 'Choose where the meta data is positioned.', 'Avada' ),
						'id'          => 'blog_post_meta_position',
						'default'     => 'below_article',
						'type'        => 'radio-buttonset',
						'choices'     => array(
							'below_article' => esc_html__( 'Below Article', 'Avada' ),
							'below_title'   => esc_html__( 'Below Title', 'Avada' ),
						),
						'required'    => array(
							array(
								'setting'  => 'blog_post_title',
								'operator' => '!=',
								'value'    => 'disabled',
							),
						),
					),
					'author_info'             => array(
						'label'       => esc_html__( 'Author Info Box', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the author info box below posts.', 'Avada' ),
						'id'          => 'author_info',
						'default'     => '1',
						'type'        => 'switch',
					),
					'social_sharing_box'      => array(
						'label'       => esc_html__( 'Social Sharing Box', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the social sharing box.', 'Avada' ),
						'id'          => 'social_sharing_box',
						'default'     => '1',
						'type'        => 'switch',
					),
					'related_posts'           => array(
						'label'       => esc_html__( 'Related Posts', 'Avada' ),
						'description' => esc_html__( 'Turn on to display related posts.', 'Avada' ),
						'id'          => 'related_posts',
						'default'     => '1',
						'type'        => 'switch',
					),
					'blog_comments'           => array(
						'label'       => esc_html__( 'Comments', 'Avada' ),
						'description' => esc_html__( 'Turn on to display comments.', 'Avada' ),
						'id'          => 'blog_comments',
						'default'     => '1',
						'type'        => 'switch',
					),
				),
			),
			'blog_meta_info'          => array(
				'label'       => esc_html__( 'Blog Meta', 'Avada' ),
				'description' => '',
				'id'          => 'blog_meta',
				'default'     => '',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => array(
					'blog_meta_important_note_info' => array(
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The on/off meta options on this tab only control the assigned blog page in "Settings > Reading" or the blog archives, not the blog element. The only options on this tab that work with the blog element are the Meta Data Font Size and Date Format options.', 'Avada' ) . '</div>',
						'id'          => 'blog_meta_important_note_info',
						'type'        => 'custom',
					),
					'post_meta'                     => array(
						'label'       => esc_html__( 'Post Meta', 'Avada' ),
						'description' => esc_html__( 'Turn on to display post meta on blog posts. If set to "On", you can also control individual meta items below. If set to "Off" all meta items will be disabled.', 'Avada' ),
						'id'          => 'post_meta',
						'default'     => '1',
						'type'        => 'switch',
					),
					'post_meta_author'              => array(
						'label'       => esc_html__( 'Post Meta Author', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the post meta author name.', 'Avada' ),
						'id'          => 'post_meta_author',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'post_meta',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'post_meta_date'                => array(
						'label'       => esc_html__( 'Post Meta Date', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the post meta date.', 'Avada' ),
						'id'          => 'post_meta_date',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'post_meta',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'post_meta_cats'                => array(
						'label'       => esc_html__( 'Post Meta Categories', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the post meta categories.', 'Avada' ),
						'id'          => 'post_meta_cats',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'post_meta',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'post_meta_comments'            => array(
						'label'       => esc_html__( 'Post Meta Comments', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the post meta comments.', 'Avada' ),
						'id'          => 'post_meta_comments',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'post_meta',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'post_meta_read'                => array(
						'label'       => esc_html__( 'Post Meta Read More Link', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the post meta read more link.', 'Avada' ),
						'id'          => 'post_meta_read',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'post_meta',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'post_meta_tags'                => array(
						'label'       => esc_html__( 'Post Meta Tags', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the post meta tags.', 'Avada' ),
						'id'          => 'post_meta_tags',
						'default'     => '0',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'post_meta',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'meta_font_size'                => array(
						'label'       => esc_html__( 'Meta Data Font Size', 'Avada' ),
						'description' => esc_html__( 'Controls the font size for meta data text.', 'Avada' ),
						'id'          => 'meta_font_size',
						'default'     => '12px',
						'type'        => 'dimension',
					),
					'date_format'                   => array(
						'label'       => esc_html__( 'Date Format', 'Avada' ),
						'description' => __( 'Controls the date format for date meta data.  <a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank" rel="noopener noreferrer">Formatting Date and Time</a>', 'Avada' ),
						'id'          => 'date_format',
						'default'     => 'F jS, Y',
						'type'        => 'text',
					),
				),
			),
		),
	);

	return $sections;

}
