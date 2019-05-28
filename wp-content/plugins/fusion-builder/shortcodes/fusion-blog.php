<?php

if ( fusion_is_element_enabled( 'fusion_blog' ) ) {

	if ( ! class_exists( 'FusionSC_Blog' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_Blog extends Fusion_Element {

			/**
			 * Blog SC counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $blog_sc_counter = 1;

			/**
			 * Posts counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $post_count = 1;

			/**
			 * The post ID.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $post_id = 0;

			/**
			 * The month of the post.
			 *
			 * @access private
			 * @since 1.0
			 * @var null|int|string
			 */
			private $post_month = null;

			/**
			 * The post's year.
			 *
			 * @access private
			 * @since 1.0
			 * @var null|int|string
			 */
			private $post_year = null;

			/**
			 * An array of meta settings.
			 *
			 * @access private
			 * @since 1.0
			 * @var array
			 */
			private $meta_info_settings = array();

			/**
			 * Header arguments.
			 *
			 * @access private
			 * @since 1.0
			 * @var array
			 */
			private $header = array();

			/**
			 * The Query.
			 *
			 * @access private
			 * @since 1.0
			 * @var string|array|object
			 */
			private $query = '';

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				// Containers.
				add_action( 'fusion_blog_shortcode_before_loop', array( $this, 'before_loop' ) );
				add_action( 'fusion_blog_shortcode_before_loop_timeline', array( $this, 'before_loop_timeline' ) );
				add_action( 'fusion_blog_shortcode_after_loop', array( $this, 'after_loop' ) );

				// Post / loop basic structure.
				add_action( 'fusion_blog_shortcode_loop_header', array( $this, 'loop_header' ) );
				add_action( 'fusion_blog_shortcode_loop_footer', array( $this, 'loop_footer' ) );
				add_action( 'fusion_blog_shortcode_loop_content', array( $this, 'loop_content' ) );
				add_action( 'fusion_blog_shortcode_loop_content', array( $this, 'page_links' ) );
				add_action( 'fusion_blog_shortcode_loop', array( $this, 'loop' ) );

				// Special blog layout structure.
				add_action( 'fusion_blog_shortcode_wrap_loop_open', array( $this, 'wrap_loop_open' ) );
				add_action( 'fusion_blog_shortcode_wrap_loop_close', array( $this, 'wrap_loop_close' ) );
				add_action( 'fusion_blog_shortcode_date_and_format', array( $this, 'add_date_box' ) );
				add_action( 'fusion_blog_shortcode_date_and_format', array( $this, 'add_format_box' ) );
				add_action( 'fusion_blog_shortcode_timeline_date', array( $this, 'timeline_date' ) );

				// Element attributes.
				add_filter( 'fusion_attr_blog-shortcode', array( $this, 'attr' ) );
				add_filter( 'fusion_attr_blog-shortcode-posts-container', array( $this, 'posts_container_attr' ) );
				add_filter( 'fusion_attr_blog-shortcode-loop', array( $this, 'loop_attr' ) );
				add_filter( 'fusion_attr_blog-shortcode-post-title', array( $this, 'post_title_attr' ) );
				add_filter( 'fusion_attr_blog-shortcode-post-content-wrapper', array( $this, 'post_content_wrapper_attr' ) );
				add_filter( 'fusion_attr_blog-fusion-post-wrapper', array( $this, 'post_wrapper_attr' ) );
				add_filter( 'fusion_attr_blog-fusion-content-sep', array( $this, 'content_sep_attr' ) );

				add_shortcode( 'fusion_blog', array( $this, 'render' ) );
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				global $fusion_settings;

				$defaults = FusionBuilder::set_shortcode_defaults(
					array(
						'hide_on_mobile'            => fusion_builder_default_visibility( 'string' ),
						'class'                     => '',
						'id'                        => '',
						'blog_grid_column_spacing'  => $fusion_settings->get( 'blog_grid_column_spacing' ),
						'blog_grid_padding'         => $fusion_settings->get( 'blog_grid_padding' ),
						'content_alignment'         => '',
						'equal_heights'             => 'no',
						'blog_grid_columns'         => $fusion_settings->get( 'blog_grid_columns' ),
						'pull_by'                   => '',
						'cat_slug'                  => '',
						'tag_slug'                  => '',
						'exclude_tags'              => '',
						'excerpt'                   => $fusion_settings->get( 'blog_excerpt' ),
						'excerpt_length'            => $fusion_settings->get( 'blog_excerpt_length' ),
						'exclude_cats'              => '',
						'grid_box_color'            => $fusion_settings->get( 'timeline_bg_color' ),
						'grid_element_color'        => $fusion_settings->get( 'timeline_color' ),
						'grid_separator_color'      => $fusion_settings->get( 'grid_separator_color' ),
						'grid_separator_style_type' => $fusion_settings->get( 'grid_separator_style_type' ),
						'layout'                    => 'large',
						'meta_all'                  => 'yes',
						'meta_author'               => 'yes',
						'meta_categories'           => 'yes',
						'meta_comments'             => 'yes',
						'meta_date'                 => 'yes',
						'meta_link'                 => 'yes',
						'meta_read'                 => 'yes',
						'meta_tags'                 => 'no',
						'number_posts'              => '6',
						'offset'                    => '',
						'order'                     => 'DESC',
						'orderby'                   => 'date',
						'paging'                    => '',
						'posts_per_page'            => '-1',
						'scrolling'                 => 'infinite',
						'show_title'                => 'yes',
						'strip_html'                => 'yes',
						'taxonomy'                  => 'category',
						'thumbnail'                 => 'yes',
						'title_link'                => 'yes',
						'blog_masonry_grid_ratio'   => $fusion_settings->get( 'masonry_grid_ratio' ),
						'blog_masonry_width_double' => $fusion_settings->get( 'masonry_width_double' ),

						'excerpt_words'             => '50', // Deprecated.
						'title'                     => '',   // Deprecated.
					),
					$args,
					'fusion_blog'
				);

				$defaults['blog_grid_column_spacing'] = FusionBuilder::validate_shortcode_attr_value( $defaults['blog_grid_column_spacing'], '' );

				if ( isset( $args['padding_top'] ) && '' !== $args['padding_top'] ) {
					$defaults['blog_grid_padding']['top'] = $args['padding_top'];
				}

				if ( isset( $args['padding_right'] ) && '' !== $args['padding_right'] ) {
					$defaults['blog_grid_padding']['right'] = $args['padding_right'];
				}

				if ( isset( $args['padding_bottom'] ) && '' !== $args['padding_bottom'] ) {
					$defaults['blog_grid_padding']['bottom'] = $args['padding_bottom'];
				}

				if ( isset( $args['padding_left'] ) && '' !== $args['padding_left'] ) {
					$defaults['blog_grid_padding']['left'] = $args['padding_left'];
				}

				// Re-index the array to set the correct values.
				if ( ! isset( $args['blog_grid_padding'] ) ) {
					$defaults['blog_grid_padding'] = array(
						$defaults['blog_grid_padding']['top'],
						$defaults['blog_grid_padding']['right'],
						$defaults['blog_grid_padding']['bottom'],
						$defaults['blog_grid_padding']['left'],
					);
				}

				extract( $defaults );

				// Since WP 4.4 'title' param is reserved.
				if ( $defaults['title'] ) {
					$defaults['show_title'] = $defaults['title'];
				}
				unset( $defaults['title'] );

				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				if ( is_front_page() || is_home() ) {
					$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
				}

				$defaults['paged'] = $paged;

				$defaults['scrolling'] = ( isset( $defaults['paging'] ) && 'no' === $defaults['paging'] && 'pagination' === $defaults['scrolling'] ) ? 'no' : $defaults['scrolling'];

				// Convert all attributes to correct values for WP query.
				$defaults['posts_per_page'] = $defaults['number_posts'];

				if ( -1 == $defaults['posts_per_page'] ) {
					$defaults['scrolling'] = 'no';
				}

				if ( '0' === $defaults['offset'] ) {
					$defaults['offset'] = '';
				}

				// Add hyphens for alternate layout options.
				if ( 'large alternate' === $defaults['layout'] ) {
					$defaults['layout'] = 'large-alternate';
				} elseif ( 'medium alternate' === $defaults['layout'] ) {
					$defaults['layout'] = 'medium-alternate';
				}

				$defaults['load_more'] = false;
				if ( 'no' !== $defaults['scrolling'] ) {
					if ( 'load_more_button' === $defaults['scrolling'] ) {
						$defaults['load_more'] = true;
						$defaults['scrolling'] = 'infinite';
					}
				}

				$defaults['meta_all']        = ( 'yes' === $defaults['meta_all'] );
				$defaults['meta_author']     = ( 'yes' === $defaults['meta_author'] );
				$defaults['meta_categories'] = ( 'yes' === $defaults['meta_categories'] );
				$defaults['meta_comments']   = ( 'yes' === $defaults['meta_comments'] );
				$defaults['meta_date']       = ( 'yes' === $defaults['meta_date'] );
				$defaults['meta_link']       = ( 'yes' === $defaults['meta_link'] );
				$defaults['meta_tags']       = ( 'yes' === $defaults['meta_tags'] );
				$defaults['strip_html']      = ( 'yes' === $defaults['strip_html'] );
				$defaults['thumbnail']       = ( 'yes' === $defaults['thumbnail'] );
				$defaults['show_title']      = ( 'yes' === $defaults['show_title'] );
				$defaults['title_link']      = ( 'yes' === $defaults['title_link'] );

				if ( isset( $args['excerpt_words'] ) && ! isset( $args['excerpt_length'] ) ) {
					$defaults['excerpt_length'] = $args['excerpt_words'];
				}

				// Combine meta info into one variable.
				$defaults['meta_info_combined'] = $defaults['meta_all'] * ( $defaults['meta_author'] + $defaults['meta_date'] + $defaults['meta_categories'] + $defaults['meta_tags'] + $defaults['meta_comments'] + $defaults['meta_link'] );
				// Create boolean that holds info whether content should be excerpted.
				$defaults['is_zero_excerpt'] = ( 'yes' === $defaults['excerpt'] && $defaults['excerpt_length'] < 1 ) ? 1 : 0;

				if ( 'tag' !== $defaults['pull_by'] ) {
					// Check for cats to exclude; needs to be checked via exclude_cats param
					// and '-' prefixed cats on cats param exclusion via exclude_cats param.
					$cats_to_exclude    = explode( ',', $defaults['exclude_cats'] );
					$cats_id_to_exclude = array();
					if ( $cats_to_exclude ) {
						foreach ( $cats_to_exclude as $cat_to_exclude ) {
							$id_obj = get_category_by_slug( $cat_to_exclude );
							if ( $id_obj ) {
								$cats_id_to_exclude[] = $id_obj->term_id;
							}
						}
						if ( $cats_id_to_exclude ) {
							$defaults['category__not_in'] = $cats_id_to_exclude;
						}
					}

					// Setting up cats to be used and exclusion using '-' prefix on cats param; transform slugs to ids.
					$cat_ids = '';
					if ( '' !== $defaults['cat_slug'] ) {
						$categories = explode( ',', $defaults['cat_slug'] );
						if ( isset( $categories ) && $categories ) {
							foreach ( $categories as $category ) {

								$id_obj = get_category_by_slug( $category );

								if ( $id_obj ) {
									// @codingStandardsIgnoreLine
									$cat_ids .= ( 0 === strpos( $category, '-' ) ) ? '-' . $id_obj->cat_ID . ',' : $id_obj->cat_ID . ',';
								}
							}
						}
					}
					$defaults['cat'] = substr( $cat_ids, 0, -1 );
				} else {
					// Check for tags to exclude; needs to be checked via exclude_tags param
					// and '-' prefixed tags on tags param exclusion via exclude_tags param.
					$tags_to_exclude    = explode( ',', $defaults['exclude_tags'] );
					$tags_id_to_exclude = array();
					if ( $tags_to_exclude ) {
						foreach ( $tags_to_exclude as $tag_to_exclude ) {
							$id_obj = get_term_by( 'slug', $tag_to_exclude, 'post_tag' );
							if ( $id_obj ) {
								$tags_id_to_exclude[] = $id_obj->term_id;
							}
						}
						if ( $tags_id_to_exclude ) {
							$defaults['tag__not_in'] = $tags_id_to_exclude;
						}
					}

					// Setting up tags to be used.
					$tag_ids = array();
					if ( '' !== $defaults['tag_slug'] ) {
						$tags = explode( ',', $defaults['tag_slug'] );
						if ( isset( $tags ) && $tags ) {
							foreach ( $tags as $tag ) {
								$id_obj = get_term_by( 'slug', $tag, 'post_tag' );

								if ( $id_obj ) {
									$tag_ids[] = $id_obj->term_id;
								}
							}
						}
					}
					$defaults['tag__in'] = $tag_ids;
				}

				if ( '0' === $defaults['blog_grid_column_spacing'] ) {
					$defaults['blog_grid_column_spacing'] = '0.0';
				}

				$defaults['blog_sc_query'] = true;

				$this->args = $defaults;

				// Set the meta info settings for later use.
				$this->meta_info_settings['post_meta']          = $defaults['meta_all'];
				$this->meta_info_settings['post_meta_author']   = $defaults['meta_author'];
				$this->meta_info_settings['post_meta_date']     = $defaults['meta_date'];
				$this->meta_info_settings['post_meta_cats']     = $defaults['meta_categories'];
				$this->meta_info_settings['post_meta_tags']     = $defaults['meta_tags'];
				$this->meta_info_settings['post_meta_comments'] = $defaults['meta_comments'];

				$fusion_query = fusion_cached_query( $defaults );

				$this->query = $fusion_query;

				$posts = '';

				// Initialize the time stamps for timeline month/year check.
				if ( 'timeline' === $this->args['layout'] ) {
					$this->post_count = 1;

					$prev_post_timestamp = null;
					$prev_post_month     = null;
					$prev_post_year      = null;
					$first_timeline_loop = false;
				}

				// Do the loop.
				if ( $fusion_query->have_posts() ) {

					if ( 'masonry' === $this->args['layout'] ) {
						$posts .= '<article class="fusion-post-grid fusion-post-masonry post fusion-grid-sizer"></article>';
					}

					while ( $fusion_query->have_posts() ) :
						$fusion_query->the_post();

						$this->post_id = get_the_ID();

						if ( 'timeline' === $this->args['layout'] ) {
							// Set the time stamps for timeline month/year check.
							$post_timestamp   = get_the_time( 'U' );
							$this->post_month = date( 'n', $post_timestamp );
							$this->post_year  = get_the_date( 'Y' );
							$current_date     = get_the_date( 'Y-n' );

							$date_params['prev_post_month'] = $prev_post_month;
							$date_params['post_month']      = $this->post_month;
							$date_params['prev_post_year']  = $prev_post_year;
							$date_params['post_year']       = $this->post_year;

							// Set the timeline month label.
							ob_start();
							do_action( 'fusion_blog_shortcode_timeline_date', $date_params );
							$timeline_date = ob_get_contents();
							ob_get_clean();

							$posts .= $timeline_date;
						}

						ob_start();
						do_action( 'fusion_blog_shortcode_before_loop' );
						$before_loop_action = ob_get_contents();
						ob_get_clean();

						$posts .= $before_loop_action;

						if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] || 'timeline' === $this->args['layout'] ) {
							$posts .= '<div ' . FusionBuilder::attributes( 'blog-fusion-post-wrapper' ) . '>';
						}

						$this->header = array(
							'title_link' => true,
						);

						ob_start();
						do_action( 'fusion_blog_shortcode_loop_header' );

						do_action( 'fusion_blog_shortcode_loop_content' );

						do_action( 'fusion_blog_shortcode_loop_footer' );

						do_action( 'fusion_blog_shortcode_after_loop' );
						$loop_actions = ob_get_contents();
						ob_get_clean();

						$posts .= $loop_actions;

						if ( 'timeline' === $this->args['layout'] ) {
							$prev_post_timestamp = $post_timestamp;
							$prev_post_month     = $this->post_month;
							$prev_post_year      = $this->post_year;
							$this->post_count++;
						}

					endwhile;
				} else {

					$this->blog_sc_counter++;
					return fusion_builder_placeholder( 'post', 'blog posts' );

				}

				// Prepare needed wrapping containers.
				$html = '';

				$html .= '<div ' . FusionBuilder::attributes( 'blog-shortcode' ) . '>';

				if ( ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) && $this->args['blog_grid_column_spacing'] ) {
					$html .= '<style type="text/css">.fusion-blog-shortcode-' . $this->blog_sc_counter . ' .fusion-blog-layout-grid .fusion-post-grid{padding:' . ( $defaults['blog_grid_column_spacing'] / 2 ) . 'px;}.fusion-blog-shortcode-' . $this->blog_sc_counter . ' .fusion-posts-container{margin-left: -' . ( $defaults['blog_grid_column_spacing'] / 2 ) . 'px !important; margin-right:-' . $defaults['blog_grid_column_spacing'] / 2 . 'px !important;}</style>';
				}

				$html .= '<div ' . FusionBuilder::attributes( 'blog-shortcode-posts-container' ) . '>';

				ob_start();
				do_action( 'fusion_blog_shortcode_wrap_loop_open' );
				$wrap_loop_open = ob_get_contents();
				ob_get_clean();

				$html .= $wrap_loop_open;

				$html .= $posts;

				ob_start();
				do_action( 'fusion_blog_shortcode_wrap_loop_close' );

				$wrap_loop_close_action = ob_get_contents();
				ob_get_clean();

				$html .= $wrap_loop_close_action;

				$html .= '</div>';

				if ( 'no' !== $this->args['scrolling'] ) {
					$pagination = $this->pagination( $this->query->max_num_pages, $fusion_settings->get( 'pagination_range' ), $this->query );

					$html .= $pagination;
				}

				// If infinite scroll with "load more" button is used.
				if ( $this->args['load_more'] && 1 < $this->query->max_num_pages ) {
					$html .= '<div class="fusion-load-more-button fusion-blog-button fusion-clearfix">' . apply_filters( 'avada_load_more_posts_name', esc_attr__( 'Load More Posts', 'fusion-builder' ) ) . '</div>';
				}

				$html .= '</div>';

				wp_reset_postdata();

				$this->blog_sc_counter++;

				return $html;

			}

			/**
			 * Render the blog pagination.
			 *
			 * @access public
			 * @since 1.0
			 * @param int    $max_pages     Max number of pages.
			 * @param int    $range         How many page numbers to display to either side of the current page.
			 * @param object $current_query The query.
			 */
			public function pagination( $max_pages = '', $range = 1, $current_query = '' ) {
				global $wp_query;

				$range = apply_filters( 'fusion_pagination_size', $range );

				if ( '' === $max_pages ) {
					if ( '' === $current_query ) {
						$max_pages = $wp_query->max_num_pages;
						$max_pages = ( ! $max_pages ) ? 1 : $max_pages;
					} else {
						$max_pages = $current_query->max_num_pages;
					}
				}
				$max_pages = intval( $max_pages );

				$blog_global_pagination = apply_filters( 'fusion_builder_blog_pagination', '' );
				$infinite_pagination    = 'pagination' !== $this->args['scrolling'] && 'Pagination' !== $blog_global_pagination;
				$pagination_html        = fusion_pagination( $max_pages, $range, $current_query, $infinite_pagination, true );

				return apply_filters( 'fusion_builder_blog_pagination_html', $pagination_html, $max_pages, $range, $current_query, $blog_global_pagination );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = array();

				// Set the correct layout class.
				$blog_layout = 'fusion-blog-layout-' . $this->args['layout'];
				if ( 'timeline' === $this->args['layout'] ) {
					$blog_layout = 'fusion-blog-layout-timeline-wrapper';
				} elseif ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) {
					$blog_layout = 'fusion-blog-layout-grid-wrapper';
				}

				$attr['class'] = 'fusion-blog-shortcode fusion-blog-shortcode-' . $this->blog_sc_counter . ' fusion-blog-archive ' . $blog_layout . ' fusion-blog-' . $this->args['scrolling'];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( ! $this->args['thumbnail'] ) {
					$attr['class'] .= ' fusion-blog-no-images';
				}

				if ( $this->args['content_alignment'] && ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] || 'timeline' === $this->args['layout'] ) ) {
					$attr['class'] .= ' fusion-blog-layout-' . $this->args['content_alignment'];
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( '0' == $this->args['blog_grid_column_spacing'] || '0px' === $this->args['blog_grid_column_spacing'] ) {
					$attr['class'] .= ' fusion-no-col-space';
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the posts-container attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function posts_container_attr() {
				global $post, $fusion_settings;

				$attr = array();

				$load_more = '';
				if ( $this->args['load_more'] ) {
					$load_more = ' fusion-posts-container-load-more';
				}

				$attr['class'] = 'fusion-posts-container fusion-posts-container-' . $this->args['scrolling'] . $load_more;

				if ( ! $this->args['meta_info_combined'] ) {
					$attr['class'] .= ' fusion-no-meta-info';
				}

				// Add class if rollover is enabled.
				if ( $fusion_settings->get( 'image_rollover' ) && $this->args['thumbnail'] ) {
					$attr['class'] .= ' fusion-blog-rollover';
				}

				$attr['data-pages'] = $this->query->max_num_pages;

				if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) {
					$attr['class'] .= ' fusion-blog-layout-grid fusion-blog-layout-grid-' . $this->args['blog_grid_columns'] . ' isotope';

					if ( 'masonry' === $this->args['layout'] ) {
						$attr['class'] .= ' fusion-blog-layout-masonry';
					}

					if ( 'grid' === $this->args['layout'] ) {
						if ( 'yes' === $this->args['equal_heights'] ) {
							$attr['class'] .= ' fusion-blog-equal-heights';
						}
					}

					if ( $this->args['blog_grid_column_spacing'] || '0' === $this->args['blog_grid_column_spacing'] ) {
						$attr['data-grid-col-space'] = $this->args['blog_grid_column_spacing'];
					}

					$negative_margin = ( -1 ) * $this->args['blog_grid_column_spacing'] / 2;

					$min_height = 'min-height:500px;';

					if ( '1' === $this->args['posts_per_page'] ) {
						$min_height = '';
					}

					$attr['style'] = 'margin: ' . $negative_margin . 'px ' . $negative_margin . 'px 0;' . $min_height;
				}

				return $attr;

			}

			/**
			 * Opens the wrapper.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function wrap_loop_open() {
				global $post;

				$wrapper = $class_timeline_icon = '';

				if ( 'timeline' === $this->args['layout'] ) {

					$wrapper  = '<div ' . FusionBuilder::attributes( 'fusion-timeline-icon' . $class_timeline_icon ) . '>';
					$wrapper .= '<i ' . FusionBuilder::attributes( 'fusion-icon-bubbles' ) . ' style="color:' . $this->args['grid_element_color'] . ';"></i>';
					$wrapper .= '</div>';
					$wrapper .= '<div ' . FusionBuilder::attributes( 'fusion-blog-layout-timeline fusion-clearfix' ) . '>';
					$wrapper .= '<div class="fusion-timeline-line" style="border-left:1px solid ' . $this->args['grid_element_color'] . ';border-right:1px solid ' . $this->args['grid_element_color'] . ';"></div>';
				}

				echo $wrapper; // WPCS: XSS ok.

			}

			/**
			 * Closes the wrapper.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function wrap_loop_close() {

				$wrapper = '';

				if ( 'timeline' === $this->args['layout'] ) {
					if ( $this->post_count > 1 ) {
						$wrapper = '</div>';
					}
					$wrapper .= '</div>';
				}

				if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) {
					$wrapper .= '<div class="fusion-clearfix"></div>';
				}

				echo $wrapper; // WPCS: XSS ok.

			}

			/**
			 * Add HTML before the loop.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function before_loop() {
				echo '<article ' . FusionBuilder::attributes( 'blog-shortcode-loop' ) . '>' . "\n"; // WPCS: XSS ok.
			}

			/**
			 * Adds markup after the loop.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function after_loop() {
				if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] || 'timeline' === $this->args['layout'] ) {
					echo '</div>' . "\n";
					echo '</article>' . "\n";
				} else {
					echo '</article>' . "\n";
				}
			}

			/**
			 * Builds the loop attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function loop_attr() {
				global $fusion_library;

				$defaults = array(
					'post_id'    => '',
					'post_count' => '',
				);

				$attr['id'] = 'blog-' . $this->blog_sc_counter . '-post-' . $this->post_id;

				$extra_classes = array();

				// Add the correct post class.
				$extra_classes[] = 'fusion-post-' . $this->args['layout'];

				if ( 'masonry' === $this->args['layout'] ) {
					// Additional grid class needed for masonry layout.
					$extra_classes[] = 'fusion-post-grid';

					// Get the element orientation class.
					$element_orientation_class = '';
					if ( has_post_thumbnail() ) {
						$element_orientation_class = $fusion_library->images->get_element_orientation_class( get_post_thumbnail_id(), array(), $this->args['blog_masonry_grid_ratio'], $this->args['blog_masonry_width_double'] );
					}

					$extra_classes[] = $element_orientation_class;
				}

				// Set the correct column class for every post.
				if ( 'timeline' === $this->args['layout'] ) {

					if ( ( $this->post_count % 2 ) > 0 ) {
						$timeline_align = ' fusion-left-column';
					} else {
						$timeline_align = ' fusion-right-column';
					}

					$extra_classes[] = 'fusion-clearfix' . $timeline_align;

					$attr['style'] = 'border-color:' . $this->args['grid_element_color'] . ';';
				}

				// Set the has-post-thumbnail if a video is used. This is needed if no featured image is present.
				$post_video = apply_filters( 'fusion_builder_post_video', $this->post_id );

				if ( $post_video ) {
					$extra_classes[] = 'has-post-thumbnail';
				}

				$post_class = get_post_class( $extra_classes, $this->post_id );

				if ( $post_class && is_array( $post_class ) ) {
					$classes       = implode( ' ', $post_class );
					$attr['class'] = $classes;
				}

				return $attr;

			}

			/**
			 * Adds attributes to the content separator.
			 *
			 * @since 1.3
			 * @access public
			 * @return array
			 */
			public function content_sep_attr() {

				global $fusion_library;

				$attr = array(
					'class' => 'fusion-content-sep',
					'style' => 'border-color:' . $this->args['grid_separator_color'] . ';',
				);

				$separator_styles_array = explode( '|', $this->args['grid_separator_style_type'] );
				$separator_styles       = '';

				foreach ( $separator_styles_array as $separator_style ) {
					$separator_styles .= ' sep-' . $separator_style;
				}

				$attr['class'] .= $separator_styles;

				return $attr;
			}

			/**
			 * Gets the HTML for masonry featured image..
			 *
			 * @access public
			 * @since 1.2
			 * @return string
			 */
			public function get_featured_image_masonry() {

				global $fusion_library, $fusion_settings;

				$lazy_load                 = $fusion_settings->get( 'lazy_load' );
				$responsive_images_columns = $this->args['blog_grid_columns'];
				$masonry_attributes        = array();
				$element_base_padding      = 0.8;

				// Set image or placeholder and correct corresponding styling.
				if ( has_post_thumbnail() ) {
					$post_thumbnail_attachment = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
					$masonry_attribute_style   = $lazy_load ? '' : 'background-image:url(' . $post_thumbnail_attachment[0] . ');';
				} else {
					$post_thumbnail_attachment = array();
					$masonry_attribute_style   = 'background-color:#f6f6f6;';
				}

				// Get the correct image orientation class.
				$element_orientation_class = $fusion_library->images->get_element_orientation_class( get_post_thumbnail_id(), $post_thumbnail_attachment, $this->args['blog_masonry_grid_ratio'], $this->args['blog_masonry_width_double'] );
				$element_base_padding      = $fusion_library->images->get_element_base_padding( $element_orientation_class );

				$masonry_column_offset = ' - ' . ( (int) $this->args['blog_grid_column_spacing'] / 2 ) . 'px';
				if ( false !== strpos( $element_orientation_class, 'fusion-element-portrait' ) ) {
					$masonry_column_offset = '';
				}

				$masonry_column_spacing = ( (int) $this->args['blog_grid_column_spacing'] ) . 'px';

				// Calculate the correct size of the image wrapper container, based on orientation and column spacing.
				if ( class_exists( 'Fusion_Sanitize' ) && class_exists( 'Fusion_Color' ) &&
					'transparent' !== Fusion_Sanitize::color( $this->args['grid_element_color'] ) &&
					'0' != Fusion_Color::new_color( $this->args['grid_element_color'] )->alpha ) {

					$masonry_column_offset = ' - ' . ( (int) $this->args['blog_grid_column_spacing'] / 2 ) . 'px';
					if ( false !== strpos( $element_orientation_class, 'fusion-element-portrait' ) ) {
						$masonry_column_offset = ' + 4px';
					}

					$masonry_column_spacing = ( (int) $this->args['blog_grid_column_spacing'] - 2 ) . 'px';
					if ( false !== strpos( $element_orientation_class, 'fusion-element-landscape' ) ) {
						$masonry_column_spacing = ( (int) $this->args['blog_grid_column_spacing'] - 6 ) . 'px';
					}
				}

				// Check if a featured image is set and also that not a video with no featured image.
				$post_video = apply_filters( 'fusion_builder_post_video', $this->post_id );
				if ( ! empty( $post_thumbnail_attachment ) || ! $post_video ) {

					// Calculate the correct size of the image wrapper container, based on orientation and column spacing.
					$masonry_attribute_style .= 'padding-top:calc((100% + ' . $masonry_column_spacing . ') * ' . $element_base_padding . $masonry_column_offset . ');';
				}

				// Check if we have a landscape image, then it has to stretch over 2 cols.
				if ( '1' !== $this->args['blog_grid_columns'] && 1 !== $this->args['blog_grid_columns'] && false !== strpos( $element_orientation_class, 'fusion-element-landscape' ) ) {
					$responsive_images_columns = (int) $this->args['blog_grid_columns'] / 2;
				}

				// Set the masonry attributes to use them in the first featured image function.
				$masonry_attributes = array(
					'class' => 'fusion-masonry-element-container',
					'style' => $masonry_attribute_style,
				);

				if ( $lazy_load && isset( $post_thumbnail_attachment[0] ) ) {
					$masonry_attributes['data-bg'] = $post_thumbnail_attachment[0];
					$masonry_attributes['class'] .= ' lazyload';
				}

				// Get the post image.
				$fusion_library->images->set_grid_image_meta(
					array(
						'layout'       => 'portfolio_full',
						'columns'      => $responsive_images_columns,
						'gutter_width' => $this->args['blog_grid_column_spacing'],
					)
				);

				$post_id   = get_the_ID();
				$permalink = get_permalink( $post_id );

				$image = fusion_render_first_featured_image_markup( $post_id, 'full', $permalink, false, false, false, 'default', 'default', '', '', 'yes', false, $masonry_attributes );

				$fusion_library->images->set_grid_image_meta( array() );

				return $image;
			}

			/**
			 * Gets the HTML for slideshows.
			 *
			 * @access public
			 * @since 1.0
			 * @return string
			 */
			public function get_slideshow() {

				global $fusion_settings;

				$html = '';

				if ( ! post_password_required( $this->post_id ) ) {

					$slideshow = array(
						'images' => $this->get_post_thumbnails( $this->post_id, $fusion_settings->get( 'posts_slideshow_number' ) ),
					);

					$post_video = apply_filters( 'fusion_builder_post_video', '', $this->post_id );

					if ( $post_video ) {
						$slideshow['video'] = $post_video;
					}

					if ( 'medium' === $this->args['layout'] || 'medium alternate' === $this->args['layout'] ) {
						$slideshow['size'] = 'blog-medium';
					}

					ob_start();
					$atts = $this->args;

					$atts['loop-id'] = '#blog-' . $this->blog_sc_counter . '-post-';

					include FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/shortcodes/new-slideshow-blog-shortcode.php';

					$post_slideshow_action = ob_get_contents();
					ob_get_clean();

					$html .= $post_slideshow_action;
				}

				return $html;
			}

			/**
			 * Gets the post thumbnails.
			 *
			 * @access public
			 * @since 1.0
			 * @param int $post_id The post-ID.
			 * @param int $count   How many thumbnails.
			 * @return array
			 */
			public function get_post_thumbnails( $post_id, $count = '' ) {

				global $fusion_settings;

				$attachment_ids = array();

				if ( get_post_thumbnail_id( $post_id ) ) {
					$attachment_ids[] = get_post_thumbnail_id( $post_id );
				}

				$i                      = 2;
				$posts_slideshow_number = $fusion_settings->get( 'posts_slideshow_number' );
				if ( '' === $posts_slideshow_number ) {
					$posts_slideshow_number = 5;
				}
				while ( $i <= $posts_slideshow_number ) {

					if ( function_exists( 'fusion_get_featured_image_id' ) && fusion_get_featured_image_id( 'featured-image-' . $i, 'post' ) ) {
						$attachment_ids[] = fusion_get_featured_image_id( 'featured-image-' . $i, 'post' );
					}

					$i++;
				}

				if ( isset( $count ) && $count >= 1 ) {
					$attachment_ids = array_slice( $attachment_ids, 0, $count );
				}

				return $attachment_ids;

			} // End get_post_thumbnails().

			/**
			 * Adds the loop-header HTML.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function loop_header() {
				global $fusion_settings;

				$defaults = array(
					'title_link' => false,
				);

				$args = wp_parse_args( $this->header, $defaults );

				$pre_title_content = $meta_data = $content_sep = $link = '';

				if ( $this->args['thumbnail'] && 'medium-alternate' !== $this->args['layout'] ) {

					// Masonry layout.
					if ( 'masonry' === $this->args['layout'] ) {
						$pre_title_content = $this->get_featured_image_masonry();
					} else {
						$pre_title_content = $this->get_slideshow();
					}
				}

				if ( 'medium-alternate' === $this->args['layout'] || 'large-alternate' === $this->args['layout'] ) {
					$pre_title_content .= '<div ' . FusionBuilder::attributes( 'fusion-date-and-formats' ) . '>';
					ob_start();
					do_action( 'fusion_blog_shortcode_date_and_format' );
					$pre_title_content .= ob_get_contents();
					ob_get_clean();
					$pre_title_content .= '</div>';

					if ( $this->args['thumbnail'] && 'medium-alternate' === $this->args['layout'] ) {
						$pre_title_content .= $this->get_slideshow();
					}

					if ( $this->args['meta_all'] ) {
						$meta_data .= fusion_builder_render_post_metadata( 'alternate', $this->meta_info_settings );
					}
				}

				if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] || 'timeline' === $this->args['layout'] ) {
					$content_wrapper_styles = '';
					$is_there_meta_above    = 0 < $this->args['meta_all'] * ( $this->args['meta_author'] + $this->args['meta_date'] + $this->args['meta_categories'] + $this->args['meta_tags'] );
					$is_there_meta_below    = $this->args['meta_all'] * $this->args['meta_comments'] || $this->args['meta_all'] * $this->args['meta_link'];
					$is_there_content       = 'no' === $this->args['excerpt'] || ( 'yes' === $this->args['excerpt'] && ! $this->args['is_zero_excerpt'] );

					// See 7199.
					if ( 'masonry' !== $this->args['layout'] && ( ( $this->args['show_title'] && $is_there_meta_above && ( $is_there_content || $is_there_meta_below ) ) || ( $this->args['show_title'] && ! $is_there_meta_above && $is_there_meta_below ) ) ) {
						$content_sep = '<div ' . FusionBuilder::attributes( 'blog-fusion-content-sep' ) . '></div>';
					}

					if ( $this->args['meta_all'] ) {
						$meta_data .= fusion_builder_render_post_metadata( 'grid_timeline', $this->meta_info_settings );
					}
					$pre_title_content .= '<div ' . FusionBuilder::attributes( 'blog-shortcode-post-content-wrapper' ) . '>';
				}

				$pre_title_content .= '<div ' . FusionBuilder::attributes( 'fusion-post-content post-content' ) . '>';

				if ( $this->args['show_title'] ) {
					if ( $this->args['title_link'] ) {
						$link_target       = '';
						$link_icon_target  = apply_filters( 'fusion_builder_link_icon_target', '', $this->post_id );
						$post_links_target = apply_filters( 'fusion_builder_post_links_target', '', $this->post_id );

						if ( 'yes' === $link_icon_target || 'yes' === $post_links_target ) {
							$link_target = ' target="_blank" rel="noopener noreferrer"';
						}

						$link = '<a href="' . get_permalink() . '"' . $link_target . '>' . get_the_title() . '</a>';
					} else {
						$link = get_the_title();
					}
				}

				if ( 'timeline' === $this->args['layout'] ) {
					$pre_title_content .= '<div ' . FusionBuilder::attributes( 'fusion-timeline-circle' ) . ' style="background-color:' . $this->args['grid_element_color'] . ';"></div>';
					$pre_title_content .= '<div ' . FusionBuilder::attributes( 'fusion-timeline-arrow' ) . ' style="color:' . $this->args['grid_element_color'] . ';"></div>';
				}
				if ( '' !== $link ) {
					$link = '<h2 ' . FusionBuilder::attributes( 'blog-shortcode-post-title' ) . '>' . $link . '</h2>';
				}
				$html = $pre_title_content . $link . $meta_data . $content_sep;

				echo $html; // WPCS: XSS ok.

			} // End loop_header().

			/**
			 * Builds the post-title attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function post_title_attr() {

				global $fusion_settings;

				$attr = array();

				$attr['class'] = 'blog-shortcode-post-title';

				if ( $fusion_settings->get( 'disable_date_rich_snippet_pages' ) && $fusion_settings->get( 'disable_rich_snippet_title' ) ) {
					$attr['class'] .= ' entry-title';
				}

				return $attr;

			}

			/**
			 * Builds the fusion-post-title-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.2
			 * @return array
			 */
			public function post_content_wrapper_attr() {
				global $fusion_settings, $fusion_library;

				$attr = array(
					'class' => 'fusion-post-content-wrapper',
				);

				if ( 'grid' === $this->args['layout'] || 'timeline' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) {
					$padding       = ( is_array( $this->args['blog_grid_padding'] ) ) ? implode( ' ', $this->args['blog_grid_padding'] ) : $this->args['blog_grid_padding'];
					$attr['style'] = 'padding:' . $padding . ';';

					if ( 'masonry' === $this->args['layout'] ) {
						$color     = Fusion_Color::new_color( $this->args['grid_box_color'] );
						$color_css = $color->to_css( 'rgba' );
						if ( 0 === $color->alpha ) {
							$color_css = $color->to_css( 'rgb' );
						}
						$attr['style'] .= 'background-color:' . $color_css . ';';
					}

					if ( ! $this->args['meta_info_combined'] && ( $this->args['is_zero_excerpt'] || 'hide' === $this->args['excerpt'] ) && ! $this->args['show_title'] ) {
						$attr['style'] .= ' display:none;';
					}
				}

				return $attr;
			}

			/**
			 * Builds the fusion-post-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.3
			 * @return array
			 */
			public function post_wrapper_attr() {
				$attr = array(
					'class' => 'fusion-post-wrapper',
				);

				if ( 'masonry' === $this->args['layout'] ) {
					$color     = Fusion_Color::new_color( $this->args['grid_box_color'] );
					$color_css = $color->to_css( 'rgba' );
					if ( 0 === $color->alpha ) {
						$color_css = $color->to_css( 'rgb' );
					}
					$attr['style'] = 'background-color:' . $color_css . ';';

					$element_color = Fusion_Color::new_color( $this->args['grid_element_color'] );
					if ( 0 === $element_color->alpha || 'transparent' === $this->args['grid_element_color'] ) {
						$attr['class'] .= ' fusion-masonary-is-transparent ';
						$attr['style'] .= 'border:none;';
					} else {
						$attr['style'] .= 'border:1px solid ' . $this->args['grid_element_color'] . ';border-bottom-width:3px;';
					}
				} elseif ( 'grid' === $this->args['layout'] ) {
					$color         = Fusion_Color::new_color( $this->args['grid_box_color'] );
					$color_css     = $color->to_css( 'rgba' );
					$attr['style'] = 'background-color:' . $color_css . ';';

					$element_color = Fusion_Color::new_color( $this->args['grid_element_color'] );
					if ( 0 === $element_color->alpha || 'transparent' === $this->args['grid_element_color'] ) {
						$attr['style'] .= 'border:none;';
					} else {
						$attr['style'] .= 'border:1px solid ' . $this->args['grid_element_color'] . ';border-bottom-width:3px;';
					}
				} elseif ( 'timeline' === $this->args['layout'] ) {
					$color         = Fusion_Color::new_color( $this->args['grid_box_color'] );
					$color_css     = $color->to_css( 'rgba' );
					$attr['style'] = 'background-color:' . $color_css . ';';
				}
				return $attr;
			}

			/**
			 * Adds the loop-footer HTML.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function loop_footer() {
				if ( in_array( $this->args['layout'], array( 'grid', 'masonry', 'timeline' ), true ) ) {
					echo '</div>';

					if ( 0 < $this->args['meta_info_combined'] && ( $this->args['meta_comments'] || $this->args['meta_link'] ) ) {
						$inner_content  = $this->read_more();
						$inner_content .= $this->grid_timeline_comments();

						echo '<div class="fusion-meta-info">' . $inner_content . '</div>'; // WPCS: XSS ok.
					}
				}

				echo '</div>';
				echo '<div class="fusion-clearfix"></div>';

				if ( 0 < $this->args['meta_info_combined'] && in_array( $this->args['layout'], array( 'large', 'medium' ), true ) ) {
					echo '<div class="fusion-meta-info">' . fusion_builder_render_post_metadata( 'standard', $this->meta_info_settings ) . $this->read_more() . '</div>'; // WPCS: XSS ok.
				}

				if ( $this->args['meta_all'] && in_array( $this->args['layout'], array( 'large-alternate', 'medium-alternate' ), true ) ) {
					echo $this->read_more(); // WPCS: XSS ok.
				}

			}

			/**
			 * Adds the date box.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function add_date_box() {

				global $fusion_settings;

				$inner_content  = '<div ' . FusionBuilder::attributes( 'fusion-date-box updated' ) . '>';
				$inner_content .= '<span ' . FusionBuilder::attributes( 'fusion-date' ) . '>' . get_the_time( $fusion_settings->get( 'alternate_date_format_day' ) ) . '</span>';
				$inner_content .= '<span ' . FusionBuilder::attributes( 'fusion-month-year' ) . '>' . get_the_time( $fusion_settings->get( 'alternate_date_format_month_year' ) ) . '</span>';
				$inner_content .= '</div>';

				echo $inner_content; // WPCS: XSS ok.

			}

			/**
			 * Adds the format box.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function add_format_box() {

				switch ( get_post_format() ) {
					case 'gallery':
						$format_class = 'images';
						break;
					case 'link':
						$format_class = 'link';
						break;
					case 'image':
						$format_class = 'image';
						break;
					case 'quote':
						$format_class = 'quotes-left';
						break;
					case 'video':
						$format_class = 'film';
						break;
					case 'audio':
						$format_class = 'headphones';
						break;
					case 'chat':
						$format_class = 'bubbles';
						break;
					default:
						$format_class = 'pen';
						break;
				}

				$inner_content  = '<div ' . FusionBuilder::attributes( 'fusion-format-box' ) . '>';
				$inner_content .= '<i ' . FusionBuilder::attributes( 'fusion-icon-' . $format_class ) . '></i>';
				$inner_content .= '</div>';

				echo $inner_content; // WPCS: XSS ok.

			}

			/**
			 * Adds the timeline date.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $date_params The date parameters.
			 */
			public function timeline_date( $date_params ) {

				global $fusion_settings;

				$defaults = array(
					'prev_post_month' => null,
					'post_month'      => null,
					'prev_post_year'  => null,
					'post_year'       => null,
				);

				$args          = wp_parse_args( $date_params, $defaults );
				$inner_content = '';

				if ( $args['prev_post_month'] !== $args['post_month'] || $args['prev_post_year'] !== $args['post_year'] ) {

					if ( $this->post_count > 1 ) {
						$inner_content = '</div>';
					}

					$inner_content .= '<h3 ' . FusionBuilder::attributes( 'fusion-timeline-date' ) . ' style="background-color:' . $this->args['grid_element_color'] . ';">' . get_the_date( $fusion_settings->get( 'timeline_date_format' ) ) . '</h3>';
					$inner_content .= '<div class="fusion-collapse-month">';
				}

				echo $inner_content; // WPCS: XSS ok.

			}

			/**
			 * The timeline comments for grids.
			 *
			 * @access public
			 * @since 1.0
			 * @return string
			 */
			public function grid_timeline_comments() {

				if ( $this->args['meta_comments'] ) {

					$comments_icon = '<i ' . FusionBuilder::attributes( 'fusion-icon-bubbles' ) . '></i>&nbsp;';

					$comments = '<i class="fusion-icon-bubbles"></i>&nbsp;' . esc_attr__( 'Protected', 'fusion-builder' );

					if ( ! post_password_required( $this->post_id ) ) {
						ob_start();
						comments_popup_link( $comments_icon . '0', $comments_icon . '1', $comments_icon . '%' );
						$comments = ob_get_contents();
						ob_get_clean();
					}

					$comment_align_class = 'fusion-alignright';
					if ( ( ! $this->args['meta_link'] || ! $this->args['meta_read'] ) && $this->args['content_alignment'] ) {
						$comment_align_class = 'fusion-align' . $this->args['content_alignment'];
					}

					return '<div ' . FusionBuilder::attributes( $comment_align_class ) . '>' . $comments . '</div>';

				}

			}

			/**
			 * The read-more element.
			 *
			 * @access public
			 * @since 1.0
			 * @return string
			 */
			public function read_more() {

				if ( $this->args['meta_link'] ) {
					$inner_content = '';

					if ( $this->args['meta_read'] ) {

						$read_more_wrapper_class = 'fusion-alignright';
						if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] || 'timeline' === $this->args['layout'] ) {
							$read_more_wrapper_class = 'fusion-alignleft';

							if ( $this->args['content_alignment'] && ! $this->args['meta_comments'] ) {
								$read_more_wrapper_class = 'fusion-align' . $this->args['content_alignment'];
							}
						}

						$link_target       = '';
						$link_icon_target  = apply_filters( 'fusion_builder_link_icon_target', '', $this->post_id );
						$post_links_target = apply_filters( 'fusion_builder_post_links_target', '', $this->post_id );

						if ( 'yes' === $link_icon_target || 'yes' === $post_links_target ) {
							$link_target = ' target="_blank" rel="noopener noreferrer"';
						}

						$inner_content .= '<div ' . FusionBuilder::attributes( $read_more_wrapper_class ) . '>';
						$inner_content .= '<a class="fusion-read-more" href="' . get_permalink() . '"' . $link_target . '>';
						$inner_content .= apply_filters( 'avada_read_more_name', esc_attr__( 'Read More', 'fusion-builder' ) );
						$inner_content .= '</a>';
						$inner_content .= '</div>';

						if ( 'large-alternate' === $this->args['layout'] || 'medium-alternate' === $this->args['layout'] ) {
							$inner_content = '<div class="fusion-meta-info">' . $inner_content . '</div>';
						}
					}

					return $inner_content;
				}

			}

			/**
			 * The loop content.
			 *
			 * @access public
			 * @since 1.0
			 * @return void
			 */
			public function loop_content() {

				if ( 'hide' !== $this->args['excerpt'] ) {
					$content = fusion_builder_get_post_content( '', $this->args['excerpt'], $this->args['excerpt_length'], $this->args['strip_html'] );

					echo '<div class="fusion-post-content-container">' . $content . '</div>'; // WPCS: XSS ok.
				}

			}

			/**
			 * The page links.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function page_links() {
				fusion_link_pages();
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.1
			 * @return array
			 */
			public function add_styling() {

				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $content_min_media_query, $small_media_query, $medium_media_query, $large_media_query, $six_columns_media_query, $five_columns_media_query, $four_columns_media_query, $three_columns_media_query, $two_columns_media_query, $one_column_media_query, $fusion_library, $fusion_settings, $dynamic_css_helpers;

				$css['global']['.fusion-load-more-button.fusion-blog-button']['background-color']       = $fusion_library->sanitize->color( $fusion_settings->get( 'blog_load_more_posts_button_bg_color' ) );
				$css['global']['.fusion-load-more-button.fusion-blog-button:hover']['background-color'] = Fusion_Color::new_color( $fusion_settings->get( 'blog_load_more_posts_button_bg_color' ) )->get_new( 'alpha', '0.8' )->to_css( 'rgba' );

				$button_brightness = fusion_calc_color_brightness( $fusion_library->sanitize->color( $fusion_settings->get( 'blog_load_more_posts_button_bg_color' ) ) );
				$text_color        = ( 140 < $button_brightness ) ? '#333' : '#fff';
				$elements          = array(
					'.fusion-load-more-button.fusion-blog-button',
					'.fusion-load-more-button.fusion-blog-button:hover',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $text_color;

				$elements = array(
					'.fusion-blog-layout-grid .post .fusion-post-wrapper',
					'.fusion-blog-layout-timeline .post',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'timeline_bg_color' ) );

				$elements = array(
					'.fusion-blog-layout-grid .post .flexslider',
					'.fusion-blog-layout-grid .post .fusion-post-wrapper',
					'.products li',
					'.product-buttons',
					'.product-buttons-container',
					'.fusion-blog-layout-timeline .fusion-timeline-line',
					'.fusion-blog-timeline-layout .post',
					'.fusion-blog-timeline-layout .post .fusion-content-sep',
					'.fusion-blog-timeline-layout .post .flexslider',
					'.fusion-blog-layout-timeline .post',
					'.fusion-blog-layout-timeline .post .flexslider',
					'.fusion-blog-layout-timeline .fusion-timeline-date',
					'.fusion-blog-layout-timeline .fusion-timeline-arrow',
					'.fusion-events-shortcode .fusion-layout-column',
					'.fusion-events-shortcode .fusion-events-thumbnail',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'timeline_color' ) );

				if ( 'transparent' == $fusion_library->sanitize->color( $fusion_settings->get( 'timeline_color' ) ) || '0' == Fusion_Color::new_color( $fusion_settings->get( 'timeline_color' ) )->alpha ) {
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border'] = 'none';
				}

				$css['global']['.fusion-body .product .fusion-content-sep']['border-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'grid_separator_color' ) );

				if ( 'transparent' == $fusion_library->sanitize->color( $fusion_settings->get( 'grid_separator_color' ) ) || '0' == Fusion_Color::new_color( $fusion_settings->get( 'grid_separator_color' ) )->alpha ) {
					$css['global']['.fusion-body .product .fusion-content-sep']['border'] = 'none';
				}

				$elements = array(
					'.fusion-blog-layout-timeline .fusion-timeline-circle',
					'.fusion-blog-layout-timeline .fusion-timeline-date',
					'.fusion-blog-timeline-layout .fusion-timeline-circle',
					'.fusion-blog-timeline-layout .fusion-timeline-date',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'timeline_color' ) );

				$elements = array(
					'.fusion-timeline-icon',
					'.fusion-timeline-arrow',
					'.fusion-blog-timeline-layout .fusion-timeline-icon',
					'.fusion-blog-timeline-layout .fusion-timeline-arrow',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'timeline_color' ) );

				if ( $fusion_settings->get( 'blog_grid_column_spacing' ) || '0' === $fusion_settings->get( 'blog_grid_column_spacing' ) ) {

					$css['global']['#posts-container.fusion-blog-layout-grid']['margin'] = '-' . intval( $fusion_settings->get( 'blog_grid_column_spacing' ) / 2 ) . 'px -' . intval( $fusion_settings->get( 'blog_grid_column_spacing' ) / 2 ) . 'px 0 -' . intval( $fusion_settings->get( 'blog_grid_column_spacing' ) / 2 ) . 'px';

					$css['global']['#posts-container.fusion-blog-layout-grid .fusion-post-grid']['padding'] = intval( $fusion_settings->get( 'blog_grid_column_spacing' ) / 2 ) . 'px';

				}

				// Six Column Breakpoint.
				$elements = array(
					'.fusion-blog-layout-grid-6 .fusion-post-grid',
				);
				$css[ $six_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']           = '20% !important';
				$css[ $six_columns_media_query ]['.fusion-blog-layout-grid-6 .fusion-element-landscape']['width'] = '40% !important';

				$elements = array(
					'.fusion-blog-layout-grid-5 .fusion-post-grid',
				);
				$css[ $six_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']           = '25% !important';
				$css[ $six_columns_media_query ]['.fusion-blog-layout-grid-5 .fusion-element-landscape']['width'] = '50% !important';

				// Five Column Breakpoint.
				$elements = array(
					'.fusion-blog-layout-grid-6 .fusion-post-grid',
				);
				$css[ $five_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']           = '20% !important';
				$css[ $five_columns_media_query ]['.fusion-blog-layout-grid-6 .fusion-element-landscape']['width'] = '40% !important';

				$elements = array(
					'.fusion-blog-layout-grid-5 .fusion-post-grid',
				);
				$css[ $five_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']           = '33.3333333333% !important';
				$css[ $five_columns_media_query ]['.fusion-blog-layout-grid-5 .fusion-element-landscape']['width'] = '66.6666666666% !important';

				$elements = array(
					'.fusion-blog-layout-grid-4 .fusion-post-grid',
				);
				$css[ $five_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']           = '33.3333333333% !important';
				$css[ $five_columns_media_query ]['.fusion-blog-layout-grid-4 .fusion-element-landscape']['width'] = '66.6666666666% !important';

				// Four Column Breakpoint.
				$elements = array(
					'.fusion-blog-layout-grid-6 .fusion-post-grid',
				);
				$css[ $four_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']           = '25% !important';
				$css[ $four_columns_media_query ]['.fusion-blog-layout-grid-6 .fusion-element-landscape']['width'] = '50% !important';

				$elements = array(
					'.fusion-blog-layout-grid-5 .fusion-post-grid',
					'.fusion-blog-layout-grid-4 .fusion-post-grid',
					'.fusion-blog-layout-grid-3 .fusion-post-grid',
				);
				$css[ $four_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width'] = '50% !important';

				$elements = $dynamic_css_helpers->map_selector( $elements, '.fusion-element-landscape' );
				$css[ $four_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width'] = '100% !important';

				// Three Column Breakpoint.
				$elements = array(
					'.fusion-blog-layout-grid-6 .fusion-post-grid',
				);
				$css[ $three_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']           = '33.3333333333% !important';
				$css[ $three_columns_media_query ]['.fusion-blog-layout-grid-6 .fusion-element-landscape']['width'] = '66.6666666666% !important';

				$elements = array(
					'.fusion-blog-layout-grid-5 .fusion-post-grid',
					'.fusion-blog-layout-grid-4 .fusion-post-grid',
					'.fusion-blog-layout-grid-3 .fusion-post-grid',
				);
				$css[ $three_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width'] = '50% !important';

				$elements = $dynamic_css_helpers->map_selector( $elements, '.fusion-element-landscape' );
				$css[ $three_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width'] = '100% !important';

				// Two Column Breakpoint.
				$elements = array(
					'.fusion-blog-layout-grid .fusion-post-grid',
				);
				$css[ $two_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width'] = '100% !important';

				$elements = array(
					'.fusion-blog-layout-grid-6 .fusion-post-grid',
				);
				$css[ $two_columns_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']           = '50% !important';
				$css[ $two_columns_media_query ]['.fusion-blog-layout-grid-6 .fusion-element-landscape']['width'] = '100% !important';

				// One Column Breakpoint.
				$elements = array(
					'.fusion-blog-layout-grid-6 .fusion-post-grid',
				);
				$css[ $one_column_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width'] = '100% !important';

				// Portrait Column Breakpoint for iPad.
				$elements = array(
					'.fusion-blog-layout-grid-6 .fusion-post-grid',
				);
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']           = '33.3333333333% !important';
				$css[ $ipad_portrait_media_query ]['.fusion-blog-layout-grid-6 .fusion-element-landscape']['width'] = '66.6666666666% !important';

				$elements = array(
					'.fusion-blog-layout-grid-5 .fusion-post-grid',
					'.fusion-blog-layout-grid-4 .fusion-post-grid',
					'.fusion-blog-layout-grid-3 .fusion-post-grid',
				);
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width'] = '50% !important';

				$elements = $dynamic_css_helpers->map_selector( $elements, '.fusion-element-landscape' );
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width'] = '100% !important';

				$elements = array(
					'.fusion-blog-layout-medium-alternate .fusion-post-content',
					'.fusion-blog-layout-medium-alternate .has-post-thumbnail .fusion-post-content',
				);
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['margin']      = '0';
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['padding-top'] = '20px';
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['flex']        = '1 0 100%';

				$elements = array(
					'.fusion-blog-layout-large .fusion-meta-info .fusion-alignleft',
					'.fusion-blog-layout-medium .fusion-meta-info .fusion-alignleft',
					'.fusion-blog-layout-large .fusion-meta-info .fusion-alignright',
					'.fusion-blog-layout-medium .fusion-meta-info .fusion-alignright',
				);
				$css[ $six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'block';
				$css[ $six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['float']   = 'none';
				$css[ $six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['margin']  = '0';
				$css[ $six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']   = '100%';

				// Blog medium layout.
				$css[ $six_fourty_media_query ]['.fusion-blog-layout-medium .fusion-post-slideshow']['margin'] = '0 0 20px 0';
				$css[ $six_fourty_media_query ]['.fusion-blog-layout-medium .fusion-post-slideshow']['height'] = 'auto';
				$css[ $six_fourty_media_query ]['.fusion-blog-layout-medium .fusion-post-slideshow']['width']  = 'auto';
				$css[ $six_fourty_media_query ]['.fusion-blog-layout-medium .fusion-post-slideshow']['flex']   = '1 0 100%';

				$css[ $six_fourty_media_query ]['.fusion-blog-layout-medium .fusion-post-content']['flex'] = '0 1 100%';

				// Blog large alternate layout.
				$css[ $six_fourty_media_query ]['.fusion-blog-layout-large-alternate .fusion-date-and-formats']['margin-bottom'] = '35px';

				$css[ $six_fourty_media_query ]['.fusion-blog-layout-large-alternate .fusion-post-content']['margin'] = '0';

				// Blog medium alternate layout.
				$css[ $six_fourty_media_query ]['.fusion-blog-layout-medium-alternate .has-post-thumbnail .fusion-post-slideshow']['display']      = 'inline-block';
				$css[ $six_fourty_media_query ]['.fusion-blog-layout-medium-alternate .has-post-thumbnail .fusion-post-slideshow']['margin-right'] = '0';
				$css[ $six_fourty_media_query ]['.fusion-blog-layout-medium-alternate .has-post-thumbnail .fusion-post-slideshow']['max-width']    = '197px';

				$css[ $six_fourty_media_query ]['.fusion-blog-layout-medium .fusion-post-content']['flex'] = '0 1 100%';

				// Blog grid layout.
				$css[ $six_fourty_media_query ]['.fusion-blog-layout-grid .fusion-post-grid']['position'] = 'static';
				$css[ $six_fourty_media_query ]['.fusion-blog-layout-grid .fusion-post-grid']['width']    = '100%';

				$css[ $six_fourty_media_query ]['.fusion-blog-layout-timeline']['padding-top'] = '0';

				$css[ $six_fourty_media_query ]['.fusion-blog-layout-timeline .fusion-post-timeline']['float'] = 'none';
				$css[ $six_fourty_media_query ]['.fusion-blog-layout-timeline .fusion-post-timeline']['width'] = '100%';

				$css[ $six_fourty_media_query ]['.fusion-blog-layout-timeline .fusion-timeline-date']['margin-bottom'] = '0';
				$css[ $six_fourty_media_query ]['.fusion-blog-layout-timeline .fusion-timeline-date']['margin-top']    = '2px';

				$elements = array(
					'.fusion-timeline-icon',
					'.fusion-timeline-line',
					'.fusion-timeline-circle',
					'.fusion-timeline-arrow',
				);
				$css[ $six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'none';

				$ipad_portrait[ $ipad_portrait_media_query ]['.fusion-blog-layout-medium-alternate .fusion-post-content']['flex']       = '1 0 100%';
				$ipad_portrait[ $ipad_portrait_media_query ]['.fusion-blog-layout-medium-alternate .fusion-post-content']['width']      = '100%';
				$ipad_portrait[ $ipad_portrait_media_query ]['.fusion-blog-layout-medium-alternate .fusion-post-content']['margin-top'] = '20px';

				if ( $fusion_settings->get( 'slideshow_smooth_height' ) ) {
					$css['global']['.fusion-flexslider.fusion-post-slideshow']['overflow'] = 'hidden';
				}

				if ( ! fusion_library()->get_option( 'image_rollover' ) ) {
					$css['global']['.fusion-rollover']['display'] = 'none';
				}

				if ( 'left' != fusion_library()->get_option( 'image_rollover_direction' ) ) {

					switch ( fusion_library()->get_option( 'image_rollover_direction' ) ) {

						case 'fade':
							$image_rollover_direction_value       = 'translateY(0%)';
							$image_rollover_direction_hover_value = '';

							$css['global']['.fusion-image-wrapper .fusion-rollover']['transition'] = 'opacity 0.5s ease-in-out';
							break;
						case 'right':
							$image_rollover_direction_value       = 'translateX(100%)';
							$image_rollover_direction_hover_value = '';
							break;
						case 'bottom':
							$image_rollover_direction_value       = 'translateY(100%)';
							$image_rollover_direction_hover_value = 'translateY(0%)';
							break;
						case 'top':
							$image_rollover_direction_value       = 'translateY(-100%)';
							$image_rollover_direction_hover_value = 'translateY(0%)';
							break;
						case 'center_horiz':
							$image_rollover_direction_value       = 'scaleX(0)';
							$image_rollover_direction_hover_value = 'scaleX(1)';
							break;
						case 'center_vertical':
							$image_rollover_direction_value       = 'scaleY(0)';
							$image_rollover_direction_hover_value = 'scaleY(1)';
							break;
						default:
							$image_rollover_direction_value       = 'scaleY(0)';
							$image_rollover_direction_hover_value = 'scaleY(1)';
							break;
					}

					$css['global']['.fusion-image-wrapper .fusion-rollover']['transform'] = $image_rollover_direction_value;

					if ( '' != $image_rollover_direction_hover_value ) {
						$css['global']['.fusion-image-wrapper:hover .fusion-rollover']['transform'] = $image_rollover_direction_hover_value;
					}
				}

				$elements = array(
					'.fusion-image-wrapper .fusion-rollover .fusion-rollover-link',
					'.fusion-image-wrapper .fusion-rollover .fusion-rollover-gallery',
				);
				if ( ! fusion_library()->get_option( 'icon_circle_image_rollover' ) ) {
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background'] = 'none';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']      = 'calc(' . $fusion_library->sanitize->size( fusion_library()->get_option( 'image_rollover_icon_size' ) ) . ' * 1.5)';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height']     = 'calc(' . $fusion_library->sanitize->size( fusion_library()->get_option( 'image_rollover_icon_size' ) ) . ' * 1.5)';
				} else {
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background'] = $fusion_library->sanitize->color( fusion_library()->get_option( 'image_rollover_text_color' ) );
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']      = 'calc(' . $fusion_library->sanitize->size( fusion_library()->get_option( 'image_rollover_icon_size' ) ) . ' * 2.41)';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height']     = 'calc(' . $fusion_library->sanitize->size( fusion_library()->get_option( 'image_rollover_icon_size' ) ) . ' * 2.41)';
				}

				$elements = array(
					'.fusion-image-wrapper .fusion-rollover .fusion-rollover-link:before',
					'.fusion-image-wrapper .fusion-rollover .fusion-rollover-gallery:before',
				);
				if ( fusion_library()->get_option( 'image_rollover_icon_size' ) ) {
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = $fusion_library->sanitize->size( fusion_library()->get_option( 'image_rollover_icon_size' ) );
					if ( ! fusion_library()->get_option( 'icon_circle_image_rollover' ) ) {
						$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['line-height'] = '1.5';
					} else {
						$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['line-height'] = '2.41';
					}
				}

				$css['global']['.fusion-image-wrapper .fusion-rollover']['background-image'][] = 'linear-gradient(top, ' . $fusion_library->sanitize->color( fusion_library()->get_option( 'image_gradient_top_color' ) ) . ' 0%, ' . $fusion_library->sanitize->color( fusion_library()->get_option( 'image_gradient_bottom_color' ) ) . ' 100%)';
				$css['global']['.fusion-image-wrapper .fusion-rollover']['background-image'][] = '-webkit-gradient(linear, left top, left bottom, color-stop(0, ' . $fusion_library->sanitize->color( fusion_library()->get_option( 'image_gradient_top_color' ) ) . '), color-stop(1, ' . $fusion_library->sanitize->color( fusion_library()->get_option( 'image_gradient_bottom_color' ) ) . '))';
				$css['global']['.fusion-image-wrapper .fusion-rollover']['background-image'][] = 'filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=' . Fusion_Color::new_color( fusion_library()->get_option( 'image_gradient_top_color' ) )->to_css( 'hex' ) . ', endColorstr=' . Fusion_Color::new_color( fusion_library()->get_option( 'image_gradient_bottom_color' ) )->to_css( 'hex' ) . '), progid: DXImageTransform.Microsoft.Alpha(Opacity=0)';

				$css['global']['.no-cssgradients .fusion-image-wrapper .fusion-rollover']['background'] = Fusion_Color::new_color( fusion_library()->get_option( 'image_gradient_top_color' ) )->to_css( 'hex' );

				$css['global']['.fusion-image-wrapper:hover .fusion-rollover']['filter'] = 'progid:DXImageTransform.Microsoft.gradient(startColorstr=' . Fusion_Color::new_color( fusion_library()->get_option( 'image_gradient_top_color' ) )->to_css( 'hex' ) . ', endColorstr=' . Fusion_Color::new_color( fusion_library()->get_option( 'image_gradient_bottom_color' ) )->to_css( 'hex' ) . '), progid: DXImageTransform.Microsoft.Alpha(Opacity=100)';

				$elements = array(
					'.fusion-rollover .fusion-rollover-content .fusion-rollover-title',
					'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-rollover-title a',
					'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-rollover-categories',
					'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-rollover-categories a',
					'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content a',
					'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .price *',
					'.fusion-image-wrapper .fusion-rollover .fusion-rollover-content .fusion-product-buttons a:before',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $fusion_library->sanitize->color( fusion_library()->get_option( 'image_rollover_text_color' ) );

				$elements = array(
					'.fusion-image-wrapper .fusion-rollover .fusion-rollover-link:before',
					'.fusion-image-wrapper .fusion-rollover .fusion-rollover-gallery:before',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $fusion_library->sanitize->color( fusion_library()->get_option( 'image_rollover_icon_color' ) );

				$elements = array(
					'.fusion-blog-pagination .pagination .current',
					'.fusion-blog-pagination .fusion-hide-pagination-text .pagination-prev:hover',
					'.fusion-blog-pagination .fusion-hide-pagination-text .pagination-next:hover',
					'.fusion-date-and-formats .fusion-date-box',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $fusion_library->sanitize->color( fusion_library()->get_option( 'primary_color' ) );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color']     = $fusion_library->sanitize->color( fusion_library()->get_option( 'primary_color' ) );

				$elements = array(
					'.fusion-blog-pagination .pagination .pagination-prev:before',
					'.fusion-blog-pagination .pagination .pagination-next:after',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $fusion_library->sanitize->color( fusion_library()->get_option( 'link_color' ) );

				$css['global']['.fusion-blog-pagination .pagination a.inactive:hover, .fusion-hide-pagination-text .fusion-blog-pagination .pagination .pagination-next:hover, .fusion-hide-pagination-text .fusion-blog-pagination .pagination .pagination-prev:hover']['border-color'] = $fusion_library->sanitize->color( fusion_library()->get_option( 'primary_color' ) );
				$css['global']['.fusion-blog-pagination .pagination a.inactive, .fusion-hide-pagination-text .fusion-blog-pagination .pagination .pagination-next, .fusion-hide-pagination-text .fusion-blog-pagination .pagination .pagination-prev']['border-color']                   = $fusion_library->sanitize->color( $fusion_settings->get( 'sep_color' ) );
				$elements = array(
					'.fusion-date-and-formats .fusion-format-box',
					'.fusion-blog-pagination .pagination .pagination-prev:hover:before',
					'.fusion-blog-pagination .pagination .pagination-next:hover:after',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $fusion_library->sanitize->color( fusion_library()->get_option( 'primary_color' ) );
				$elements = array(
					'.fusion-blog-pagination .pagination',
					'.fusion-blog-pagination .pagination .current',
					'.fusion-blog-pagination .pagination .pagination-next',
					'.fusion-blog-pagination .pagination .pagination-prev',
					'.fusion-blog-pagination .pagination a.inactive',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['font-size'] = $fusion_library->sanitize->size( fusion_library()->get_option( 'pagination_font_size' ) );

				$elements = array(
					'.fusion-blog-pagination .pagination .current',
					'.fusion-hide-pagination-text .fusion-blog-pagination .pagination .pagination-next',
					'.fusion-hide-pagination-text .fusion-blog-pagination .pagination .pagination-prev',
					'.fusion-blog-pagination .pagination a.inactive',
				);

				if ( 'padding' === fusion_library()->get_option( 'pagination_sizing' ) ) {
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['padding'] = $fusion_library->sanitize->size( $fusion_settings->get( 'pagination_box_padding', 'height' ) ) . ' ' . $fusion_library->sanitize->size( $fusion_settings->get( 'pagination_box_padding', 'width' ) );
				} else {
					$pagination_size = $fusion_library->sanitize->size( fusion_library()->get_option( 'pagination_width_height' ), 'px' );
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width'] = $pagination_size;
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height'] = $pagination_size;
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['line-height'] = $pagination_size;
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-left'] = ( (int) $pagination_size / 10 ) . 'px';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-right'] = ( (int) $pagination_size / 10 ) . 'px';
				}
				return $css;
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Blog settings.
			 */
			public function add_options() {
				return array(
					'blog_shortcode_section' => array(
						'label'       => esc_attr__( 'Blog Element', 'fusion-builder' ),
						'description' => '',
						'id'          => 'blog_shortcode_section',
						'default'     => '',
						'type'        => 'accordion',
						'fields'      => array(
							'blog_grid_columns'        => array(
								'label'       => esc_attr__( 'Number of Columns', 'fusion-builder' ),
								'description' => __( 'Set the number of columns per row for grid and masonry layout. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'fusion-builder' ),
								'id'          => 'blog_grid_columns',
								'default'     => 3,
								'type'        => 'slider',
								'choices'     => array(
									'min'  => 1,
									'max'  => 6,
									'step' => 1,
								),
							),
							'blog_grid_column_spacing' => array(
								'label'       => esc_attr__( 'Column Spacing', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the column spacing for blog posts for grid and masonry layout.', 'fusion-builder' ),
								'id'          => 'blog_grid_column_spacing',
								'default'     => '40',
								'type'        => 'slider',
								'choices'     => array(
									'min'  => '0',
									'step' => '1',
									'max'  => '300',
									'edit' => 'yes',
								),
							),
							'blog_grid_padding'        => array(
								'label'       => esc_attr__( 'Blog Grid Text Padding', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the top/right/bottom/left padding of the blog text when using grid / masonry or timeline layout.', 'fusion-builder' ),
								'id'          => 'blog_grid_padding',
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
							),
							'blog_excerpt'             => array(
								'label'       => esc_attr__( 'Content Display', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls if the post content displays an excerpt, full content or is completely disabled for blog elements.', 'fusion-builder' ),
								'id'          => 'blog_excerpt',
								'default'     => 'yes',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'hide' => esc_attr__( 'No Text', 'fusion-builder' ),
									'yes'  => esc_attr__( 'Excerpt', 'fusion-builder' ),
									'no'   => esc_attr__( 'Full Content', 'fusion-builder' ),
								),
							),
							'blog_excerpt_length'      => array(
								'label'       => esc_attr__( 'Excerpt Length', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the number of words in the excerpts for blog elements.', 'fusion-builder' ),
								'id'          => 'blog_excerpt_length',
								'default'     => '10',
								'type'        => 'slider',
								'choices'     => array(
									'min'  => '0',
									'max'  => '500',
									'step' => '1',
								),
								'required'    => array(
									array(
										'setting'  => 'blog_excerpt',
										'operator' => '==',
										'value'    => 'yes',
									),
								),
							),
							'dates_box_color'          => array(
								'label'       => esc_attr__( 'Blog Date Box Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the color of the date box in blog alternate and recent posts layouts.', 'fusion-builder' ),
								'id'          => 'dates_box_color',
								'default'     => '#eef0f2',
								'type'        => 'color-alpha',
							),
						),
					),
				);
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 1.1
			 * @return void
			 */
			public function add_scripts() {

				Fusion_Dynamic_JS::enqueue_script( 'fusion-blog' );
			}
		}
	}

	new FusionSC_Blog();

}

// Add needed action and filter to make sure queries with offset have correct pagination.
add_action( 'pre_get_posts', 'fusion_query_offset', 1 );
/**
 * Adds offset to the query.
 *
 * @since 1.0
 * @param object $query The query.
 */
function fusion_query_offset( $query ) {
	// Check if we are in a blog shortcode query and if offset is set.
	if ( is_admin() || is_object( $query ) && ( $query->is_main_query() || is_array( $query->query ) && ! array_key_exists( 'blog_sc_query', $query->query ) ) || ! $query->query['offset'] ) {
		return;
	}

	// The query is paged.
	if ( $query->is_paged ) {
		// Manually determine page query offset (offset + ( current page - 1 ) x posts per page ).
		$page_offset = $query->query['offset'] + ( ( $query->query_vars['paged'] - 1 ) * $query->query['posts_per_page'] );

		// Apply adjusted page offset.
		$query->set( 'offset', $page_offset );

		// This is the first page, so we can just use the offset.
	} else {
		$query->set( 'offset', $query->query['offset'] );
	}
}

add_filter( 'found_posts', 'fusion_adjust_offset_pagination', 1, 2 );
/**
 * Adds an offset to the pagination.
 *
 * @since 1.0
 * @param int    $found_posts How many posts we found.
 * @param object $query       The query.
 * @return int
 */
function fusion_adjust_offset_pagination( $found_posts, $query ) {
	// Modification only in a blog shortcode query with set offset.
	if ( array_key_exists( 'blog_sc_query', $query->query ) && $query->query['offset'] ) {
		// Reduce found_posts count by the offset.
		return $found_posts - $query->query['offset'];
	}
	return $found_posts;
}

add_filter( 'redirect_canonical', 'fusion_blog_redirect_canonical' );
/**
 * Make sure that the blog pagination also works on front page.
 *
 * @since 1.0
 * @param string $redirect_url The URL we want to redirect to.
 * @return string
 */
function fusion_blog_redirect_canonical( $redirect_url ) {
	global $wp_rewrite, $wp_query;

	if ( $wp_rewrite->using_permalinks() ) {

		$paged = 1;
		// Check the query var.
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
			// Check query paged.
		} elseif ( ! empty( $wp_query->query['paged'] ) ) {
			$paged = $wp_query->query['paged'];
		}

		if ( 1 < $paged ) {
			return false;
		}
	}

	return $redirect_url;
}

/**
 * Map shortcode to Fusion Builder.
 *
 * @since 1.0
 */
function fusion_element_blog() {
	global $fusion_settings, $pagenow;

	fusion_builder_map(
		array(
			'name'       => esc_attr__( 'Blog', 'fusion-builder' ),
			'shortcode'  => 'fusion_blog',
			'icon'       => 'fusiona-blog',
			'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-blog-preview.php',
			'preview_id' => 'fusion-builder-block-module-blog-preview-template',
			'params'     => array(
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Blog Layout', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the layout for the element', 'fusion-builder' ),
					'param_name'  => 'layout',
					'default'     => 'large',
					'value'       => array(
						'large'            => esc_attr__( 'Large', 'fusion-builder' ),
						'medium'           => esc_attr__( 'Medium', 'fusion-builder' ),
						'large alternate'  => esc_attr__( 'Large Alternate', 'fusion-builder' ),
						'medium alternate' => esc_attr__( 'Medium Alternate', 'fusion-builder' ),
						'grid'             => esc_attr__( 'Grid', 'fusion-builder' ),
						'timeline'         => esc_attr__( 'Timeline', 'fusion-builder' ),
						'masonry'          => esc_attr__( 'Masonry', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
					'description' => __( 'Set the number of columns per row. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'fusion-builder' ),
					'param_name'  => 'blog_grid_columns',
					'value'       => '',
					'default'     => $fusion_settings->get( 'blog_grid_columns' ),
					'min'         => '1',
					'max'         => '6',
					'step'        => '1',
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'medium',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'medium alternate',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large alternate',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'timeline',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the column spacing for blog posts.', 'fusion-builder' ),
					'param_name'  => 'blog_grid_column_spacing',
					'value'       => '',
					'default'     => $fusion_settings->get( 'blog_grid_column_spacing' ),
					'min'         => '0',
					'step'        => '1',
					'max'         => '300',
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'medium',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'medium alternate',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large alternate',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'timeline',
							'operator' => '!=',
						),
						array(
							'element'  => 'blog_grid_columns',
							'value'    => 1,
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Masonry Image Aspect Ratio', 'fusion-builder' ),
					'description' => __( 'Set the ratio to decide when an image should become landscape (ratio being width : height) and portrait (ratio being height : width). <strong>IMPORTANT:</strong> The value of "1.0" represents a special case, which will use the auto calculated ratios like in versions prior to Avada 5.5.', 'fusion-builder' ),
					'param_name'  => 'blog_masonry_grid_ratio',
					'value'       => '',
					'min'         => '1',
					'max'         => '4',
					'step'        => '0.1',
					'default'     => $fusion_settings->get( 'masonry_grid_ratio' ),
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'masonry',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Masonry 2x2 Width', 'fusion-builder' ),
					'description' => __( 'This option decides when a square 1x1 image should become 2x2. This will not apply to images that highly favor landscape or portrait layouts. <strong>IMPORTANT:</strong> There is a “Masonry Image Layout” setting for every image in the WP media library that allows you to manually set how an image will appear (1x1, landscape, portrait or 2x2), regardless of the original ratio. In pixels.', 'fusion-builder' ),
					'param_name'  => 'blog_masonry_width_double',
					'value'       => '',
					'min'         => '200',
					'max'         => '5120',
					'step'        => '1',
					'default'     => $fusion_settings->get( 'masonry_width_double' ),
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'masonry',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Equal Heights', 'fusion-builder' ),
					'description' => esc_attr__( 'Set to yes to display grid boxes with equal heights per row.', 'fusion-builder' ),
					'param_name'  => 'equal_heights',
					'default'     => 'no',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'grid',
							'operator' => '==',
						),
						array(
							'element'  => 'blog_grid_columns',
							'value'    => 1,
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Posts Per Page', 'fusion-builder' ),
					'description' => esc_attr__( 'Select number of posts per page.  Set to -1 to display all. Set to 0 to use number of posts from Settings > Reading.', 'fusion-builder' ),
					'param_name'  => 'number_posts',
					'value'       => '6',
					'min'         => '-1',
					'max'         => '25',
					'step'        => '1',
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Post Offset', 'fusion-builder' ),
					'description' => esc_attr__( 'The number of posts to skip. ex: 1.', 'fusion-builder' ),
					'param_name'  => 'offset',
					'value'       => '0',
					'min'         => '0',
					'max'         => '25',
					'step'        => '1',
					'dependency'  => array(
						array(
							'element'  => 'number_posts',
							'value'    => '-1',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Pull Posts By', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to show posts by category or tag.', 'fusion-builder' ),
					'param_name'  => 'pull_by',
					'default'     => 'category',
					'value'       => array(
						'category' => esc_attr__( 'Category', 'fusion-builder' ),
						'tag'      => esc_attr__( 'Tag', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'multiple_select',
					'heading'     => esc_attr__( 'Categories', 'fusion-builder' ),
					'description' => esc_attr__( 'Select categories or leave blank for all.', 'fusion-builder' ),
					'param_name'  => 'cat_slug',
					'value'       => ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ? fusion_builder_shortcodes_categories( 'category' ) : array(),
					'default'     => '',
					'dependency'  => array(
						array(
							'element'  => 'pull_by',
							'value'    => 'tag',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'multiple_select',
					'heading'     => esc_attr__( 'Exclude Categories', 'fusion-builder' ),
					'description' => esc_attr__( 'Select categories to exclude.', 'fusion-builder' ),
					'param_name'  => 'exclude_cats',
					'value'       => ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ? fusion_builder_shortcodes_categories( 'category' ) : array(),
					'default'     => '',
					'dependency'  => array(
						array(
							'element'  => 'pull_by',
							'value'    => 'tag',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'multiple_select',
					'heading'     => esc_attr__( 'Tags', 'fusion-builder' ),
					'description' => esc_attr__( 'Select a tag or leave blank for all.', 'fusion-builder' ),
					'param_name'  => 'tag_slug',
					'value'       => ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ? fusion_builder_shortcodes_tags( 'post_tag' ) : array(),
					'default'     => '',
					'dependency'  => array(
						array(
							'element'  => 'pull_by',
							'value'    => 'category',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'multiple_select',
					'heading'     => esc_attr__( 'Exclude Tags', 'fusion-builder' ),
					'description' => esc_attr__( 'Select a tag to exclude.', 'fusion-builder' ),
					'param_name'  => 'exclude_tags',
					'value'       => ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ? fusion_builder_shortcodes_tags( 'post_tag' ) : array(),
					'default'     => '',
					'dependency'  => array(
						array(
							'element'  => 'pull_by',
							'value'    => 'category',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Order By', 'fusion-builder' ),
					'description' => esc_attr__( 'Defines how posts should be ordered.', 'fusion-builder' ),
					'param_name'  => 'orderby',
					'default'     => 'date',
					'value'       => array(
						'date'          => esc_attr__( 'Date', 'fusion-builder' ),
						'title'         => esc_attr__( 'Post Title', 'fusion-builder' ),
						'name'          => esc_attr__( 'Post Slug', 'fusion-builder' ),
						'author'        => esc_attr__( 'Author', 'fusion-builder' ),
						'comment_count' => esc_attr__( 'Number of Comments', 'fusion-builder' ),
						'modified'      => esc_attr__( 'Last Modified', 'fusion-builder' ),
						'rand'          => esc_attr__( 'Random', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Order', 'fusion-builder' ),
					'description' => esc_attr__( 'Defines the sorting order of posts.', 'fusion-builder' ),
					'param_name'  => 'order',
					'default'     => 'DESC',
					'value'       => array(
						'DESC' => esc_attr__( 'Descending', 'fusion-builder' ),
						'ASC'  => esc_attr__( 'Ascending', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'orderby',
							'value'    => 'rand',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Thumbnail', 'fusion-builder' ),
					'description' => esc_attr__( 'Display the post featured image.', 'fusion-builder' ),
					'param_name'  => 'thumbnail',
					'default'     => 'yes',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Title', 'fusion-builder' ),
					'description' => esc_attr__( 'Display the post title below the featured image.', 'fusion-builder' ),
					'param_name'  => 'title',
					'default'     => 'yes',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Link Title To Post', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose if the title should be a link to the single post page.', 'fusion-builder' ),
					'default'     => 'yes',
					'param_name'  => 'title_link',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'title',
							'value'    => 'yes',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Content Alignment', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the alignment of contents.', 'fusion-builder' ),
					'param_name'  => 'content_alignment',
					'default'     => '',
					'value'       => array(
						''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
						'left'   => esc_attr__( 'Left', 'fusion-builder' ),
						'center' => esc_attr__( 'Center', 'fusion-builder' ),
						'right'  => esc_attr__( 'Right', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'medium',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'medium alternate',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large alternate',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Text display', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls if the blog post content is displayed as excerpt, full content or is completely disabled.', 'fusion-builder' ),
					'param_name'  => 'excerpt',
					'value'       => array(
						''     => esc_attr__( 'Default', 'fusion-builder' ),
						'yes'  => esc_attr__( 'Excerpt', 'fusion-builder' ),
						'no'   => esc_attr__( 'Full Content', 'fusion-builder' ),
						'hide' => esc_attr__( 'No Text', 'fusion-builder' ),
					),
					'default'     => '',
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Excerpt Length', 'fusion-builder' ),
					'description' => esc_attr__( 'Insert the number of words/characters you want to show in the excerpt.', 'fusion-builder' ),
					'param_name'  => 'excerpt_length',
					'value'       => '10',
					'min'         => '0',
					'max'         => '500',
					'step'        => '1',
					'dependency'  => array(
						array(
							'element'  => 'excerpt',
							'value'    => 'no',
							'operator' => '!=',
						),
						array(
							'element'  => 'excerpt',
							'value'    => 'hide',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Strip HTML from Posts Content', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to strip HTML from the post content.', 'fusion-builder' ),
					'param_name'  => 'strip_html',
					'default'     => 'yes',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'excerpt',
							'value'    => 'no',
							'operator' => '!=',
						),
						array(
							'element'  => 'excerpt',
							'value'    => 'hide',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Meta Info', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to show all meta data.', 'fusion-builder' ),
					'param_name'  => 'meta_all',
					'default'     => 'yes',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Author Name', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to show the author.', 'fusion-builder' ),
					'param_name'  => 'meta_author',
					'default'     => 'yes',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'meta_all',
							'value'    => 'yes',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Categories', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to show the categories.', 'fusion-builder' ),
					'param_name'  => 'meta_categories',
					'default'     => 'yes',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'meta_all',
							'value'    => 'yes',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Comment Count', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to show the comments.', 'fusion-builder' ),
					'param_name'  => 'meta_comments',
					'default'     => 'yes',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'meta_all',
							'value'    => 'yes',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Date', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to show the date.', 'fusion-builder' ),
					'param_name'  => 'meta_date',
					'default'     => 'yes',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'meta_all',
							'value'    => 'yes',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Read More Link', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to show the Read More link.', 'fusion-builder' ),
					'param_name'  => 'meta_link',
					'default'     => 'yes',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'meta_all',
							'value'    => 'yes',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Tags', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to show the tags.', 'fusion-builder' ),
					'param_name'  => 'meta_tags',
					'default'     => 'yes',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'meta_all',
							'value'    => 'yes',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Pagination Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose the type of pagination.', 'fusion-builder' ),
					'param_name'  => 'scrolling',
					'default'     => 'pagination',
					'value'       => array(
						'no'               => esc_attr__( 'No Pagination', 'fusion-builder' ),
						'pagination'       => esc_attr__( 'Pagination', 'fusion-builder' ),
						'infinite'         => esc_attr__( 'Infinite Scrolling', 'fusion-builder' ),
						'load_more_button' => esc_attr__( 'Load More Button', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Grid Box Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the background color for the grid boxes.', 'fusion-builder' ),
					'param_name'  => 'grid_box_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'timeline_bg_color' ),
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'medium',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'medium alternate',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large alternate',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Grid Element Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the color of borders/date box/timeline dots and arrows for the grid boxes.', 'fusion-builder' ),
					'param_name'  => 'grid_element_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'timeline_color' ),
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'medium',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'medium alternate',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large alternate',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Grid Separator Style', 'fusion-builder' ),
					'description' => __( 'Controls the line style of grid separators. <strong>Note:</strong> Separators will display, when excerpt/content or meta data below the separators is displayed.', 'fusion-builder' ),
					'param_name'  => 'grid_separator_style_type',
					'value'       => array(
						''              => esc_attr__( 'Default', 'fusion-builder' ),
						'none'          => esc_attr__( 'No Style', 'fusion-builder' ),
						'single|solid'  => esc_attr__( 'Single Border Solid', 'fusion-builder' ),
						'double|solid'  => esc_attr__( 'Double Border Solid', 'fusion-builder' ),
						'single|dashed' => esc_attr__( 'Single Border Dashed', 'fusion-builder' ),
						'double|dashed' => esc_attr__( 'Double Border Dashed', 'fusion-builder' ),
						'single|dotted' => esc_attr__( 'Single Border Dotted', 'fusion-builder' ),
						'double|dotted' => esc_attr__( 'Double Border Dotted', 'fusion-builder' ),
						'shadow'        => esc_attr__( 'Shadow', 'fusion-builder' ),
					),
					'default'     => '',
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'medium',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'medium alternate',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large alternate',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'masonry',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Grid Separator Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the line style color of grid separators.', 'fusion-builder' ),
					'param_name'  => 'grid_separator_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'grid_separator_color' ),
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'medium',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'medium alternate',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large alternate',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'masonry',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'             => 'dimension',
					'remove_from_atts' => true,
					'heading'          => esc_attr__( 'Blog Grid Text Padding ', 'fusion-builder' ),
					'description'      => esc_attr__( 'Controls the padding for the blog text when using grid / masonry or timeline layout. Enter values including any valid CSS unit, ex: 30px, 25px, 0px, 25px.', 'fusion-builder' ),
					'param_name'       => 'blog_grid_padding',
					'value'            => array(
						'padding_top'    => '',
						'padding_right'  => '',
						'padding_bottom' => '',
						'padding_left'   => '',
					),
					'dependency'       => array(
						array(
							'element'  => 'layout',
							'value'    => 'medium',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'medium alternate',
							'operator' => '!=',
						),
						array(
							'element'  => 'layout',
							'value'    => 'large alternate',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'checkbox_button_set',
					'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
					'param_name'  => 'hide_on_mobile',
					'value'       => fusion_builder_visibility_options( 'full' ),
					'default'     => fusion_builder_default_visibility( 'array' ),
					'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
					'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'class',
					'value'       => '',
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'id',
					'value'       => '',
				),
			),
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_blog' );
