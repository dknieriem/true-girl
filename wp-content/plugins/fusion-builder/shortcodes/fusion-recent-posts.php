<?php

if ( fusion_is_element_enabled( 'fusion_recent_posts' ) ) {

	if ( ! class_exists( 'FusionSC_RecentPosts' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_RecentPosts extends Fusion_Element {

			/**
			 * Recent Posts element counter.
			 *
			 * @access private
			 * @since 1.5.2
			 * @var int
			 */
			private $recent_posts_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * An array of meta settings.
			 *
			 * @access private
			 * @since 1.0
			 * @var array
			 */
			private $meta_info_settings = array();

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_recentposts-shortcode', array( $this, 'attr' ) );
				add_filter( 'fusion_attr_recentposts-shortcode-section', array( $this, 'section_attr' ) );
				add_filter( 'fusion_attr_recentposts-shortcode-column', array( $this, 'column_attr' ) );
				add_filter( 'fusion_attr_recentposts-shortcode-content', array( $this, 'content_attr' ) );
				add_filter( 'fusion_attr_recentposts-shortcode-slideshow', array( $this, 'slideshow_attr' ) );
				add_filter( 'fusion_attr_recentposts-shortcode-img', array( $this, 'img_attr' ) );
				add_filter( 'fusion_attr_recentposts-shortcode-img-link', array( $this, 'link_attr' ) );

				add_shortcode( 'fusion_recent_posts', array( $this, 'render' ) );

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

				global $fusion_settings, $fusion_library;

				$defaults = FusionBuilder::set_shortcode_defaults(
					array(
						'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
						'class'               => '',
						'id'                  => '',
						'pull_by'             => '',
						'cat_id'              => '',
						'cat_slug'            => '',
						'tag_slug'            => '',
						'exclude_tags'        => '',
						'columns'             => 3,
						'content_alignment'   => '',
						'excerpt'             => 'no',
						'exclude_cats'        => '',
						'excerpt_length'      => '',
						'excerpt_words'       => '15', // Deprecated.
						'hover_type'          => 'none',
						'layout'              => 'default',
						'meta'                => 'yes',
						'meta_author'         => 'no',
						'meta_categories'     => 'no',
						'meta_comments'       => 'yes',
						'meta_date'           => 'yes',
						'meta_tags'           => 'no',
						'number_posts'        => '4',
						'offset'              => '',
						'picture_size'        => 'fixed',
						'scrolling'           => 'no',
						'strip_html'          => 'yes',
						'title'               => 'yes',
						'thumbnail'           => 'yes',
						'animation_direction' => 'left',
						'animation_speed'     => '',
						'animation_type'      => '',
						'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
					),
					$args,
					'fusion_recent_posts'
				);

				if ( '0' === $defaults['offset'] ) {
					$defaults['offset'] = '';
				}

				if ( $defaults['columns'] > 6 ) {
					$defaults['columns'] = 6;
				}

				$defaults['strip_html'] = ( 'yes' === $defaults['strip_html'] || 'true' === $defaults['strip_html'] ) ? true : false;

				if ( $defaults['number_posts'] ) {
					$defaults['posts_per_page'] = $defaults['number_posts'];
				}

				if ( '-1' === $defaults['number_posts'] ) {
					$defaults['scrolling'] = 'no';
				}

				if ( $defaults['excerpt_length'] || '0' === $defaults['excerpt_length'] ) {
					$defaults['excerpt_words'] = $defaults['excerpt_length'];
				}
				if ( 'tag' !== $defaults['pull_by'] ) {
					// Check for cats to exclude; needs to be checked via exclude_cats param
					// and '-' prefixed cats on cats param, exclution via exclude_cats param.
					$cats_to_exclude = explode( ',', $defaults['exclude_cats'] );
					if ( $cats_to_exclude ) {
						foreach ( $cats_to_exclude as $cat_to_exclude ) {
							$id_obj = get_category_by_slug( $cat_to_exclude );
							if ( $id_obj ) {
								$cats_id_to_exclude[] = $id_obj->term_id;
							}
						}
						if ( isset( $cats_id_to_exclude ) && $cats_id_to_exclude ) {
							$defaults['category__not_in'] = $cats_id_to_exclude;
						}
					}

					// Setting up cats to be used and exclution using '-' prefix on cats param; transform slugs to ids.
					$cat_ids    = '';
					$categories = explode( ',', $defaults['cat_slug'] );
					if ( isset( $categories ) && $categories ) {
						foreach ( $categories as $category ) {
							if ( $category ) {
								$cat_obj = get_category_by_slug( $category );
								if ( isset( $cat_obj->term_id ) ) {
									if ( 0 === strpos( $category, '-' ) ) {
										// @codingStandardsIgnoreLine
										$cat_ids .= '-' .$cat_obj->cat_ID . ',';
									} else {
										// @codingStandardsIgnoreLine
										$cat_ids .= $cat_obj->cat_ID . ',';
									}
								}
							}
						}
					}
					$defaults['cat'] = substr( $cat_ids, 0, -1 ) . $defaults['cat_id'];
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

				$items = '';

				$args = array(
					'posts_per_page'      => $defaults['number_posts'],
					'ignore_sticky_posts' => 1,
				);

				// Check if there is paged content.
				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				if ( is_front_page() ) {
					$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
				}
				$args['paged'] = $paged;

				if ( $defaults['offset'] ) {
					$args['offset'] = $defaults['offset'] + ( $paged - 1 ) * $defaults['number_posts'];
				}

				if ( isset( $defaults['cat'] ) && $defaults['cat'] ) {
					$args['cat'] = $defaults['cat'];
				}

				if ( isset( $defaults['category__not_in'] ) && is_array( $defaults['category__not_in'] ) ) {
					$args['category__not_in'] = $defaults['category__not_in'];
				}

				if ( isset( $defaults['tag__in'] ) && $defaults['tag__in'] ) {
					$args['tag__in'] = $defaults['tag__in'];
				}

				if ( isset( $defaults['tag__not_in'] ) && is_array( $defaults['tag__not_in'] ) ) {
					$args['tag__not_in'] = $defaults['tag__not_in'];
				}

				extract( $defaults );

				// Deprecated 5.2.1 hide value, mapped to no.
				if ( 'hide' === $excerpt ) {
					$excerpt = 'no';
				}

				$defaults['meta_author']     = ( 'yes' === $defaults['meta_author'] );
				$defaults['meta_categories'] = ( 'yes' === $defaults['meta_categories'] );
				$defaults['meta_comments']   = ( 'yes' === $defaults['meta_comments'] );
				$defaults['meta_date']       = ( 'yes' === $defaults['meta_date'] );
				$defaults['meta_tags']       = ( 'yes' === $defaults['meta_tags'] );

				// Set the meta info settings for later use.
				$this->meta_info_settings['post_meta']          = $defaults['meta'];
				$this->meta_info_settings['post_meta_author']   = $defaults['meta_author'];
				$this->meta_info_settings['post_meta_date']     = $defaults['meta_date'];
				$this->meta_info_settings['post_meta_cats']     = $defaults['meta_categories'];
				$this->meta_info_settings['post_meta_tags']     = $defaults['meta_tags'];
				$this->meta_info_settings['post_meta_comments'] = $defaults['meta_comments'];

				$this->args = $defaults;

				$recent_posts = fusion_cached_query( $args );

				$this->args['max_num_pages'] = $recent_posts->max_num_pages;

				if ( ! $recent_posts->have_posts() ) {
					return fusion_builder_placeholder( 'post', 'blog posts' );
				}

				while ( $recent_posts->have_posts() ) {
					$recent_posts->the_post();

					$attachment = $date_box = $slideshow = $slides = $content = '';

					if ( 'date-on-side' == $layout ) {

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

						$date_box = '<div ' . FusionBuilder::attributes( 'fusion-date-and-formats' ) . '><div ' . FusionBuilder::attributes( 'fusion-date-box updated' ) . '><span ' . FusionBuilder::attributes( 'fusion-date' ) . '>' . get_the_time( $fusion_settings->get( 'alternate_date_format_day' ) ) . '</span><span ' . FusionBuilder::attributes( 'fusion-month-year' ) . '>' . get_the_time( $fusion_settings->get( 'alternate_date_format_month_year' ) ) . '</span></div><div ' . FusionBuilder::attributes( 'fusion-format-box' ) . '><i ' . FusionBuilder::attributes( 'fusion-icon-' . $format_class ) . '></i></div></div>';
					}

					if ( 'yes' === $thumbnail && 'date-on-side' !== $layout && ! post_password_required( get_the_ID() ) ) {

						if ( 'auto' === $picture_size ) {
							$image_size = 'full';
						} elseif ( 'default' == $layout ) {
							$image_size = 'recent-posts';
						} elseif ( 'thumbnails-on-side' == $layout ) {
							$image_size = 'portfolio-five';
						}

						$post_video = apply_filters( 'fusion_builder_post_video', get_the_ID() );

						if ( has_post_thumbnail() || $post_video ) {
							if ( $post_video ) {
								$slides .= '<li><div ' . FusionBuilder::attributes( 'full-video' ) . '>' . $post_video . '</div></li>';
							}

							if ( has_post_thumbnail() ) {

								// Responsive images.
								if ( 'full' === $image_size ) {
									$fusion_library->images->set_grid_image_meta(
										array(
											'layout'       => 'grid',
											'columns'      => $columns,
											'gutter_width' => '30',
										)
									);

									$attachment_image = wp_get_attachment_image( get_post_thumbnail_id(), $image_size );

									$attachment_image = $fusion_library->images->edit_grid_image_src( $attachment_image, null, get_post_thumbnail_id(), 'full' );

									$fusion_library->images->set_grid_image_meta( array() );
								} else {
									$attachment_image = wp_get_attachment_image( get_post_thumbnail_id(), $image_size );
								}

								$slides .= '<li><a href="' . get_permalink( get_the_ID() ) . '" ' . FusionBuilder::attributes( 'recentposts-shortcode-img-link' ) . '>' . $attachment_image . '</a></li>';
							}

							$i                      = 2;
							$posts_slideshow_number = $fusion_settings->get( 'posts_slideshow_number' );
							if ( '' === $posts_slideshow_number ) {
								$posts_slideshow_number = 5;
							}
							while ( $i <= $posts_slideshow_number ) {

								$attachment_new_id = false;

								if ( function_exists( 'fusion_get_featured_image_id' ) && fusion_get_featured_image_id( 'featured-image-' . $i, 'post' ) ) {
									$attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, 'post' );
								}

								if ( $attachment_new_id ) {

									// Responsive images.
									if ( 'full' === $image_size ) {
										$fusion_library->images->set_grid_image_meta(
											array(
												'layout'  => 'grid',
												'columns' => $columns,
												'gutter_width' => '30',
											)
										);

										$attachment_image = wp_get_attachment_image( $attachment_new_id, $image_size );

										$attachment_image = $fusion_library->images->edit_grid_image_src( $attachment_image, null, $attachment_new_id, 'full' );

										$fusion_library->images->set_grid_image_meta( array() );
									} else {
										$attachment_image = wp_get_attachment_image( $attachment_new_id, $image_size );
									}

									$slides .= '<li><a href="' . get_permalink( get_the_ID() ) . '" ' . FusionBuilder::attributes( 'recentposts-shortcode-img-link' ) . '>' . $attachment_image . '</a></li>';
								}
								$i++;
							}

							$slideshow = '<div ' . FusionBuilder::attributes( 'recentposts-shortcode-slideshow' ) . '><ul ' . FusionBuilder::attributes( 'slides' ) . '>' . $slides . '</ul></div>';
						}
					}

					if ( 'yes' == $title ) {
						$content    .= ( function_exists( 'fusion_builder_render_rich_snippets_for_pages' ) ) ? fusion_builder_render_rich_snippets_for_pages( false ) : '';
						$entry_title = '';
						if ( $fusion_settings->get( 'disable_date_rich_snippet_pages' ) && $fusion_settings->get( 'disable_rich_snippet_title' ) ) {
							$entry_title = 'entry-title';
						}
						$content .= '<h4 class="' . $entry_title . '"><a href="' . get_permalink( get_the_ID() ) . '">' . get_the_title() . '</a></h4>';
					} else {
						$content .= fusion_builder_render_rich_snippets_for_pages();
					}

					if ( 'yes' == $meta ) {
						$meta_data = fusion_builder_render_post_metadata( 'recent_posts', $this->meta_info_settings );
						$content  .= '<p ' . FusionBuilder::attributes( 'meta' ) . '>' . $meta_data . '</p>';
					}

					if ( 'yes' === $excerpt ) {
						$content .= fusion_builder_get_post_content( '', 'yes', $excerpt_words, $strip_html );
					} elseif ( 'full' === $excerpt ) {
						$content .= fusion_builder_get_post_content( '', 'no', $excerpt_words, $strip_html );
					}

					$items .= '<article ' . FusionBuilder::attributes( 'recentposts-shortcode-column' ) . '>' . $date_box . $slideshow . '<div ' . FusionBuilder::attributes( 'recentposts-shortcode-content' ) . '>' . $content . '</div></article>';
				}

				// Pagination is used.
				$pagination = '';
				if ( 'no' !== $this->args['scrolling'] ) {
					$infinite_pagination = false;
					if ( 'pagination' !== $this->args['scrolling'] ) {
						$infinite_pagination = true;
					}

					$pagination = fusion_pagination( $recent_posts->max_num_pages, $fusion_settings->get( 'pagination_range' ), $recent_posts, $infinite_pagination, true );

					// If infinite scroll with "load more" button is used.
					if ( 'load_more_button' === $this->args['scrolling'] && 1 < $recent_posts->max_num_pages ) {
						$pagination .= '<div class="fusion-load-more-button fusion-blog-button fusion-clearfix">' . apply_filters( 'avada_load_more_posts_name', esc_attr__( 'Load More Posts', 'fusion-builder' ) ) . '</div>';
					}
				}

				$html = '<div ' . FusionBuilder::attributes( 'recentposts-shortcode' ) . '><section ' . FusionBuilder::attributes( 'recentposts-shortcode-section' ) . '>' . $items . '</section>' . $pagination . '</div>';

				wp_reset_postdata();

				$this->recent_posts_counter++;

				return $html;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					array(
						'class' => 'fusion-recent-posts fusion-recent-posts-' . $this->recent_posts_counter . ' avada-container layout-' . $this->args['layout'] . ' layout-columns-' . $this->args['columns'],
					)
				);

				if ( $this->args['content_alignment'] && 'default' === $this->args['layout'] ) {
					$attr['class'] .= ' fusion-recent-posts-' . $this->args['content_alignment'];
				}

				if ( 'infinite' === $this->args['scrolling'] || 'load_more_button' === $this->args['scrolling'] ) {
					$attr['class'] .= ' fusion-recent-posts-infinite';

					$attr['data-pages'] = $this->args['max_num_pages'];
				}

				if ( 'load_more_button' === $this->args['scrolling'] ) {
					$attr['class'] .= ' fusion-recent-posts-load-more';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the section attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function section_attr() {
				$attr = array(
					'class' => 'fusion-columns columns fusion-columns-' . $this->args['columns'] . ' columns-' . $this->args['columns'],
				);

				return $attr;
			}

			/**
			 * Builds the column attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function column_attr() {

				$columns = 3;
				if ( $this->args['columns'] ) {
					$columns = 12 / $this->args['columns'];
				}

				$attr = array(
					'class' => 'post fusion-column column col col-lg-' . $columns . ' col-md-' . $columns . ' col-sm-' . $columns . '',
					'style' => '',
				);

				if ( '5' == $this->args['columns'] ) {
					$attr['class'] = 'post fusion-column column col-lg-2 col-md-2 col-sm-2';
				}

				if ( $this->args['animation_type'] ) {
					$animations = FusionBuilder::animations(
						array(
							'type'      => $this->args['animation_type'],
							'direction' => $this->args['animation_direction'],
							'speed'     => $this->args['animation_speed'],
							'offset'    => $this->args['animation_offset'],
						)
					);

					$attr = array_merge( $attr, $animations );

					$attr['class'] .= ' ' . $attr['animation_class'];
					unset( $attr['animation_class'] );
				}

				return $attr;

			}

			/**
			 * Builds the slideshow attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function slideshow_attr() {

				$attr = array(
					'class' => 'fusion-flexslider flexslider',
				);

				if ( 'thumbnails-on-side' == $this->args['layout'] ) {
					$attr['class'] .= ' floated-slideshow';
				}

				if ( $this->args['hover_type'] ) {
					$attr['class'] .= ' flexslider-hover-type-' . $this->args['hover_type'];
				}

				return $attr;

			}

			/**
			 * Builds the image attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $args The arguments array.
			 * @return array
			 */
			public function img_attr( $args ) {

				$attr = array(
					'src' => $args['src'],
				);

				if ( $args['alt'] ) {
					$attr['alt'] = $args['alt'];
				}

				return $attr;

			}

			/**
			 * Builds the link attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $args The arguments array.
			 * @return array
			 */
			public function link_attr( $args ) {

				$attr = array();

				if ( $this->args['hover_type'] ) {
					$attr['class'] = 'hover-type-' . $this->args['hover_type'];
				}
				$attr['aria-label'] = the_title_attribute( array( 'echo' => false ) );

				return $attr;

			}

			/**
			 * Builds the content wrapper attributes array.
			 *
			 * @access public
			 * @since 1.5.2
			 * @return array
			 */
			public function content_attr() {
				$attr = array(
					'class' => 'recent-posts-content',
				);

				return $attr;
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.1
			 * @return array
			 */
			public function add_styling() {

				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $fusion_library, $fusion_settings, $dynamic_css_helpers;

				$elements = array(
					'.fusion-flexslider .flex-direction-nav .flex-prev',
					'.fusion-flexslider .flex-direction-nav .flex-next',
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'carousel_nav_color' ) );

				$elements = $dynamic_css_helpers->map_selector( $elements, ':hover' );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'carousel_hover_color' ) );

				return $css;
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 1.5.2
			 * @return void
			 */
			public function add_scripts() {

				global $fusion_settings;

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-recent-posts',
					FusionBuilder::$js_folder_url . '/general/fusion-recent-posts.js',
					FusionBuilder::$js_folder_path . '/general/fusion-recent-posts.js',
					array( 'jquery' ),
					'1',
					true
				);

				Fusion_Dynamic_JS::localize_script(
					'fusion-recent-posts',
					'fusionRecentPostsVars',
					array(
						'infinite_loading_text'  => '<em>' . __( 'Loading the next set of posts...', 'fusion-builder' ) . '</em>',
						'infinite_finished_msg'  => '<em>' . __( 'All items displayed.', 'fusion-builder' ) . '</em>',
						'slideshow_autoplay'     => $fusion_settings->get( 'slideshow_autoplay' ) ? $fusion_settings->get( 'slideshow_autoplay' ) : false,
						'slideshow_speed'        => $fusion_settings->get( 'slideshow_speed' ) ? (int) $fusion_settings->get( 'slideshow_speed' ) : 5000,
						'pagination_video_slide' => $fusion_settings->get( 'pagination_video_slide' ) ? $fusion_settings->get( 'pagination_video_slide' ) : false,
						'status_yt'              => $fusion_settings->get( 'status_yt' ) ? $fusion_settings->get( 'status_yt' ) : false,
					)
				);
			}
		}
	}

	new FusionSC_RecentPosts();

}

/**
 * Map shortcode to Fusion Builder.
 *
 * @since 1.0
 */
function fusion_element_recent_posts() {
	global $pagenow;

	fusion_builder_map(
		array(
			'name'       => esc_attr__( 'Recent Posts', 'fusion-builder' ),
			'shortcode'  => 'fusion_recent_posts',
			'icon'       => 'fusiona-feather',
			'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-recent-posts-preview.php',
			'preview_id' => 'fusion-builder-block-module-recent-posts-preview-template',
			'params'     => array(
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the layout for the element.', 'fusion-builder' ),
					'param_name'  => 'layout',
					'value'       => array(
						'default'            => esc_attr__( 'Standard', 'fusion-builder' ),
						'thumbnails-on-side' => esc_attr__( 'Thumbnails on Side', 'fusion-builder' ),
						'date-on-side'       => esc_attr__( 'Date on Side', 'fusion-builder' ),
					),
					'default'     => 'default',
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Picture Size', 'fusion-builder' ),
					'description' => __( 'Fixed = width and height will be fixed.<br/>Auto = width and height will adjust to the image.<br/>', 'fusion-builder' ),
					'param_name'  => 'picture_size',
					'default'     => 'fixed',
					'value'       => array(
						'fixed' => esc_attr__( 'Fixed', 'fusion-builder' ),
						'auto'  => esc_attr__( 'Auto', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'date-on-side',
							'operator' => '!=',
						),
						array(
							'element'  => 'thumbnail',
							'value'    => 'yes',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Hover Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the hover effect type.', 'fusion-builder' ),
					'param_name'  => 'hover_type',
					'value'       => array(
						'none'    => esc_attr__( 'None', 'fusion-builder' ),
						'zoomin'  => esc_attr__( 'Zoom In', 'fusion-builder' ),
						'zoomout' => esc_attr__( 'Zoom Out', 'fusion-builder' ),
						'liftup'  => esc_attr__( 'Lift Up', 'fusion-builder' ),
					),
					'default'     => 'none',
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'date-on-side',
							'operator' => '!=',
						),
						array(
							'element'  => 'thumbnail',
							'value'    => 'yes',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the number of columns to display.', 'fusion-builder' ),
					'param_name'  => 'columns',
					'value'       => '3',
					'min'         => '1',
					'max'         => '6',
					'step'        => '1',
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Posts Per Page', 'fusion-builder' ),
					'description' => esc_attr__( 'Select number of posts per page.  Set to -1 to display all.', 'fusion-builder' ),
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
					'description' => esc_attr__( 'Select a category or leave blank for all.', 'fusion-builder' ),
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
					'description' => esc_attr__( 'Select a category to exclude.', 'fusion-builder' ),
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
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Thumbnail', 'fusion-builder' ),
					'description' => esc_attr__( 'Display the post featured image.', 'fusion-builder' ),
					'param_name'  => 'thumbnail',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'default'     => 'yes',
					'dependency'  => array(
						array(
							'element'  => 'layout',
							'value'    => 'date-on-side',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Title', 'fusion-builder' ),
					'description' => esc_attr__( 'Display the post title below the featured image.', 'fusion-builder' ),
					'param_name'  => 'title',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'default'     => 'yes',
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Meta', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to show all meta data.', 'fusion-builder' ),
					'param_name'  => 'meta',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'default'     => 'yes',
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Show Author Name', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to show the author.', 'fusion-builder' ),
					'param_name'  => 'meta_author',
					'default'     => 'no',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'meta',
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
					'default'     => 'no',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'meta',
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
							'element'  => 'meta',
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
							'element'  => 'meta',
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
					'default'     => 'no',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'dependency'  => array(
						array(
							'element'  => 'meta',
							'value'    => 'yes',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Content Alignment', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the alignment of contents.', 'fusion-builder' ),
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
							'value'    => 'default',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Text display', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to display the post excerpt.', 'fusion-builder' ),
					'param_name'  => 'excerpt',
					'value'       => array(
						'yes'  => esc_attr__( 'Excerpt', 'fusion-builder' ),
						'full' => esc_attr__( 'Full Content', 'fusion-builder' ),
						'no'   => esc_attr__( 'None', 'fusion-builder' ),
					),
					'default'     => 'yes',
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Excerpt Length', 'fusion-builder' ),
					'description' => esc_attr__( 'Insert the number of words/characters you want to show in the excerpt.', 'fusion-builder' ),
					'param_name'  => 'excerpt_length',
					'value'       => '35',
					'min'         => '0',
					'max'         => '500',
					'step'        => '1',
					'dependency'  => array(
						array(
							'element'  => 'excerpt',
							'value'    => 'yes',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Strip HTML', 'fusion-builder' ),
					'description' => esc_attr__( 'Strip HTML from the post excerpt.', 'fusion-builder' ),
					'param_name'  => 'strip_html',
					'value'       => array(
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'default'     => 'yes',
					'dependency'  => array(
						array(
							'element'  => 'excerpt',
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
					'default'     => 'no',
					'value'       => array(
						'no'               => esc_attr__( 'No Pagination', 'fusion-builder' ),
						'pagination'       => esc_attr__( 'Pagination', 'fusion-builder' ),
						'infinite'         => esc_attr__( 'Infinite Scrolling', 'fusion-builder' ),
						'load_more_button' => esc_attr__( 'Load More Button', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Animation Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the type of animation to use on the element.', 'fusion-builder' ),
					'param_name'  => 'animation_type',
					'value'       => fusion_builder_available_animations(),
					'default'     => '',
					'group'       => esc_attr__( 'Animation', 'fusion-builder' ),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Direction of Animation', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the incoming direction for the animation.', 'fusion-builder' ),
					'param_name'  => 'animation_direction',
					'value'       => array(
						'down'   => esc_attr__( 'Top', 'fusion-builder' ),
						'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						'up'     => esc_attr__( 'Bottom', 'fusion-builder' ),
						'left'   => esc_attr__( 'Left', 'fusion-builder' ),
						'static' => esc_attr__( 'Static', 'fusion-builder' ),
					),
					'default'     => 'left',
					'group'       => esc_attr__( 'Animation', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'animation_type',
							'value'    => '',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Speed of Animation', 'fusion-builder' ),
					'description' => esc_attr__( 'Type in speed of animation in seconds (0.1 - 1).', 'fusion-builder' ),
					'param_name'  => 'animation_speed',
					'min'         => '0.1',
					'max'         => '1',
					'step'        => '0.1',
					'value'       => '0.3',
					'group'       => esc_attr__( 'Animation', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'animation_type',
							'value'    => '',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Offset of Animation', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls when the animation should start.', 'fusion-builder' ),
					'param_name'  => 'animation_offset',
					'value'       => array(
						''                => esc_attr__( 'Default', 'fusion-builder' ),
						'top-into-view'   => esc_attr__( 'Top of element hits bottom of viewport', 'fusion-builder' ),
						'top-mid-of-view' => esc_attr__( 'Top of element hits middle of viewport', 'fusion-builder' ),
						'bottom-in-view'  => esc_attr__( 'Bottom of element enters viewport', 'fusion-builder' ),
					),
					'default'     => '',
					'group'       => esc_attr__( 'Animation', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'animation_type',
							'value'    => '',
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
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'id',
					'value'       => '',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
				),
			),
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_recent_posts' );
