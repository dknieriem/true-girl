<?php
/**
 * Custom avada functions.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
add_action( 'wp_head', 'avada_set_post_views' );
if ( ! function_exists( 'avada_set_post_views' ) ) {
	/**
	 * Post views inc.
	 */
	function avada_set_post_views() {
		global $post;
		if ( 'post' == get_post_type() && is_single() ) {
			$post_id = $post->ID;
			if ( ! empty( $post_id ) ) {
				$count_key = 'avada_post_views_count';
				$count     = get_post_meta( $post_id, $count_key, true );
				if ( '' == $count ) {
					$count = 0;
					delete_post_meta( $post_id, $count_key );
					add_post_meta( $post_id, $count_key, '0' );
				} else {
					$count++;
					update_post_meta( $post_id, $count_key, $count );
				}
			}
		}
	}
}

if ( ! function_exists( 'avada_get_slider' ) ) {
	/**
	 * Get the slider type.
	 *
	 * @param int    $post_id    The post ID.
	 * @param string $type       The slider type.
	 * @param bool   $is_archive Whether archive page.
	 * @return  string
	 */
	function avada_get_slider( $post_id, $type, $is_archive = false ) {
		$type = Avada_Helper::slider_name( $type );
		if ( $is_archive ) {
			$fusion_taxonomy_options = get_term_meta( $post_id, 'fusion_taxonomy_options', true );
			return ( $type ) ? Avada_Helper::get_fusion_tax_meta( $fusion_taxonomy_options, 'fusion_tax_' . $type ) : false;
		} else {
			return ( $type ) ? get_post_meta( $post_id, 'pyre_' . $type, true ) : false;
		}
	}
}

if ( ! function_exists( 'avada_slider' ) ) {
	/**
	 * Slider.
	 *
	 * @param int  $post_id The post ID.
	 * @param bool $is_archive Whether archive page.
	 */
	function avada_slider( $post_id, $is_archive = false ) {

		$slider_type = Avada_Helper::get_slider_type( $post_id, $is_archive );
		$slider      = avada_get_slider( $post_id, $slider_type, $is_archive );

		if ( $slider ) {
			$slider_name = Avada_Helper::slider_name( $slider_type );
			$slider_name = ( 'slider' == $slider_name ) ? 'layerslider' : $slider_name;

			$function = 'avada_' . $slider_name;

			$function( $slider );
		}
	}
}

if ( ! function_exists( 'avada_revslider' ) ) {
	/**
	 * Revolution Slider.
	 *
	 * @param string $name The revolution slider name.
	 */
	function avada_revslider( $name ) {
		include wp_normalize_path( locate_template( 'templates/revslider.php' ) );
	}
}

if ( ! function_exists( 'avada_layerslider' ) ) {
	/**
	 * Layerslider.
	 *
	 * @param int|string $id The layerslider ID.
	 */
	function avada_layerslider( $id ) {
		include wp_normalize_path( locate_template( 'templates/layerslider.php' ) );
	}
}

if ( ! function_exists( 'avada_elasticslider' ) ) {
	/**
	 * The elastic-slider.
	 *
	 * @param int|string $term The term.
	 */
	function avada_elasticslider( $term ) {
		include wp_normalize_path( locate_template( 'templates/elasticslider.php' ) );
	}
}

if ( ! function_exists( 'avada_wooslider' ) ) {
	/**
	 * Per-term slider.
	 *
	 * @param int|string $term The term.
	 */
	function avada_wooslider( $term ) {
		if ( method_exists( 'Fusion_Slider', 'render_fusion_slider' ) ) {
			Fusion_Slider::render_fusion_slider( $term );
		}
	}
}

if ( ! function_exists( 'avada_get_page_title_bar_contents' ) ) {
	/**
	 * Get the contents of the title bar.
	 *
	 * @param  int  $post_id               The post ID.
	 * @param  bool $get_secondary_content Determine if we want secondary content.
	 * @return array
	 */
	function avada_get_page_title_bar_contents( $post_id, $get_secondary_content = true ) {

		if ( $get_secondary_content ) {
			ob_start();
			$title_breadcrumbs_search_bar = get_post_meta( $post_id, 'pyre_page_title_breadcrumbs_search_bar', true );
			if ( fusion_get_option( 'page_title_bar_bs', 'page_title_breadcrumbs_search_bar', $post_id ) != 'none' ) {
				if ( ( 'Breadcrumbs' == Avada()->settings->get( 'page_title_bar_bs' ) && in_array( $title_breadcrumbs_search_bar, array( 'breadcrumbs', 'default', '' ) ) ) || 'breadcrumbs' === $title_breadcrumbs_search_bar ) {
					fusion_breadcrumbs();
				} elseif ( ( 'Search Box' == Avada()->settings->get( 'page_title_bar_bs' ) && in_array( $title_breadcrumbs_search_bar, array( 'searchbar', 'default', '' ) ) ) || 'searchbar' === $title_breadcrumbs_search_bar ) {
					get_search_form();
				}
			}
			$secondary_content = ob_get_contents();
			ob_get_clean();
		} else {
			$secondary_content = '';
		}

		$title                       = '';
		$subtitle                    = '';
		$page_title_custom_text      = get_post_meta( $post_id, 'pyre_page_title_custom_text', true );
		$page_title_custom_subheader = get_post_meta( $post_id, 'pyre_page_title_custom_subheader', true );
		$page_title_text             = get_post_meta( $post_id, 'pyre_page_title_text', true );

		if ( '' != $page_title_custom_text ) {
			$title = $page_title_custom_text;
		}

		if ( '' != $page_title_custom_subheader ) {
			$subtitle = $page_title_custom_subheader;
		}

		if ( '' == $page_title_text || 'default' === $page_title_text ) {
			if ( Avada()->settings->get( 'page_title_bar_text' ) ) {
				$page_title_text = 'yes';
			} else {
				$page_title_text = 'no';
			}
		} else {
			$page_title_text = $page_title_text;
		}

		if ( is_search() ) {
			/* translators: The search query. */
			$title    = sprintf( esc_html__( 'Search results for: %s', 'Avada' ), get_search_query() );
			$subtitle = '';
		}

		if ( ! $title ) {
			$title = get_the_title( $post_id );

			// Only assign blog title theme option to default blog page and not posts page.
			if ( is_home() && get_option( 'show_on_front' ) != 'page' ) {
				$title = Avada()->settings->get( 'blog_title' );
			}

			if ( is_404() ) {
				$title = esc_html__( 'Error 404 Page', 'Avada' );
			}

			if ( class_exists( 'Tribe__Events__Main' ) && ( ( Avada_Helper::tribe_is_event( $post_id ) && ! is_single() && ! is_home() && ! is_tag() ) || Avada_Helper::is_events_archive( $post_id ) && ! is_tag() || ( Avada_Helper::is_events_archive( $post_id ) && is_404() ) ) ) {
				$title = tribe_get_events_title();
			} elseif ( is_archive() && ! Avada_Helper::is_bbpress() && ! is_search() ) {
				if ( is_day() ) {
					/* translators: Date. */
					$title = sprintf( esc_html__( 'Daily Archives: %s', 'Avada' ), '<span>' . get_the_date() . '</span>' );
				} elseif ( is_month() ) {
					/* translators: Date. */
					$title = sprintf( esc_html__( 'Monthly Archives: %s', 'Avada' ), '<span>' . get_the_date( 'F Y' ) . '</span>' );
				} elseif ( is_year() ) {
					/* translators: Date. */
					$title = sprintf( esc_html__( 'Yearly Archives: %s', 'Avada' ), '<span> ' . get_the_date( 'Y' ) . '</span>' );
				} elseif ( is_author() ) {
					$curauth = get_user_by( 'id', get_query_var( 'author' ) );
					$title   = $curauth->nickname;
				} elseif ( is_post_type_archive() ) {
					$title = post_type_archive_title( '', false );

					$sermon_settings = get_option( 'wpfc_options' );
					if ( is_array( $sermon_settings ) ) {
						$title = $sermon_settings['archive_title'];
					}
				} else {
					$title = single_cat_title( '', false );
				}
			} elseif ( class_exists( 'bbPress' ) && Avada_Helper::is_bbpress() && Avada_Helper::bbp_is_forum_archive() ) {
				$title = post_type_archive_title( '', false );
			}

			if ( class_exists( 'WooCommerce' ) && Avada_Helper::is_woocommerce() && ( is_product() || is_shop() ) && ! is_search() ) {
				if ( ! is_product() ) {
					$title = woocommerce_page_title( false );
				}
			}
		} // End if().

		// Only assign blog subtitle theme option to default blog page and not posts page.
		if ( ! $subtitle && is_home() && get_option( 'show_on_front' ) != 'page' ) {
			$subtitle = Avada()->settings->get( 'blog_subtitle' );
		}

		// Which TO to check for.
		$page_title_option = Avada()->settings->get( 'page_title_bar' );
		if ( is_home() ) {
			// Blog designated page.
			$page_title_option = Avada()->settings->get( 'blog_show_page_title_bar' );
		} elseif ( is_tag() || is_category() || is_author() || is_date() || is_singular( 'post' ) ) {
			// Blog archive or post.
			$page_title_option = Avada()->settings->get( 'blog_page_title_bar' );
		}

		if ( ! is_archive() && ! is_search() && ! ( is_home() && ! is_front_page() ) ) {
			$page_title = get_post_meta( $post_id, 'pyre_page_title', true );
			if ( 'no' == $page_title_text && ( 'yes' === $page_title || 'yes_without_bar' === $page_title || ( 'hide' !== $page_title_option && 'no' !== $page_title ) ) ) {
				$title    = '';
				$subtitle = '';
			}
		} else {
			if ( 'hide' != $page_title_option && 'no' == $page_title_text ) {
				$title    = '';
				$subtitle = '';
			}
		}

		return array( $title, $subtitle, $secondary_content );
	}
} // End if().

if ( ! function_exists( 'avada_current_page_title_bar' ) ) {
	/**
	 * Get the current page title.
	 *
	 * @param int $post_id The post ID.
	 */
	function avada_current_page_title_bar( $post_id ) {
		$page_title_bar_contents = avada_get_page_title_bar_contents( $post_id );
		$page_title              = get_post_meta( $post_id, 'pyre_page_title', true );

		// Which TO to check for.
		$page_title_option = Avada()->settings->get( 'page_title_bar' );

		if ( is_home() ) {

			// Blog designated page.
			$page_title_option = Avada()->settings->get( 'blog_show_page_title_bar' );
		} elseif ( is_tag() || is_category() || is_author() || is_date() || is_singular( 'post' ) ) {

			// Blog archive or post.
			$page_title_option = Avada()->settings->get( 'blog_page_title_bar' );
		}

		// Check if archive or WooCommerce shop page.
		if ( ( ! is_archive() || class_exists( 'WooCommerce' ) && is_shop() ) && ! is_search() ) {

			// Check that combination of page option and TO means page title bar should show.
			if ( 'yes' === $page_title || 'yes_without_bar' === $page_title || ( 'hide' !== $page_title_option && 'no' !== $page_title ) ) {
				avada_page_title_bar( $page_title_bar_contents[0], $page_title_bar_contents[1], $page_title_bar_contents[2] );
			}

			// No page option to check for so just check that TO is not set to hide.
		} elseif ( 'hide' !== $page_title_option ) {
			avada_page_title_bar( $page_title_bar_contents[0], $page_title_bar_contents[1], $page_title_bar_contents[2] );
		}
	}
} // End if().

if ( ! function_exists( 'avada_is_page_title_bar_active' ) ) {
	/**
	 * Check if page title bar is active.
	 * Note: This checks if the PTB is displayed at all.
	 *
	 * @since 5.8.1
	 * @param int $post_id The post ID.
	 * @return bool
	 */
	function avada_is_page_title_bar_active( $post_id ) {

		// Which TO to check for.
		$page_title_bar_theme_option = Avada()->settings->get( 'page_title_bar' );
		if ( is_home() ) {

			// Blog designated page.
			$page_title_bar_theme_option = Avada()->settings->get( 'blog_show_page_title_bar' );
		} elseif ( is_tag() || is_category() || is_author() || is_date() || is_singular( 'post' ) ) {

			// Blog archive or post.
			$page_title_bar_theme_option = Avada()->settings->get( 'blog_page_title_bar' );
		}

		$page_title_page_option = get_post_meta( $post_id, 'pyre_page_title', true );
		if ( $page_title_page_option ) {
			if ( 'hide' === $page_title_page_option ) {
				$page_title_bar_theme_option = 'hide';
			} else if ( 'yes' === $page_title_page_option || 'yes_without_bar' === $page_title_page_option ) {
				$page_title_bar_theme_option = 'show';
			}
		}

		if ( 'hide' === $page_title_bar_theme_option ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'avada_is_page_title_bar_enabled' ) ) {
	/**
	 * Check if page title bar is enabled.
	 *
	 * @param int $post_id The post ID.
	 * @return bool
	 */
	function avada_is_page_title_bar_enabled( $post_id ) {
		$page_title           = get_post_meta( $post_id, 'pyre_page_title', true );
		$page_title_text      = get_post_meta( $post_id, 'pyre_page_title_text', true );
		$is_title_bar_enabled = false;

		// Which TO to check for.
		$page_title_option      = Avada()->settings->get( 'page_title_bar' );
		$page_title_text_option = Avada()->settings->get( 'page_title_bar_text' );

		if ( is_home() ) {

			// Blog designated page.
			$page_title_option = Avada()->settings->get( 'blog_show_page_title_bar' );
		} elseif ( is_tag() || is_category() || is_author() || is_date() || is_singular( 'post' ) ) {

			// Blog archive or post.
			$page_title_option = Avada()->settings->get( 'blog_page_title_bar' );
		}

		// Check if archive or WooCommerce shop page.
		if ( ( ! is_archive() || class_exists( 'WooCommerce' ) && is_shop() ) && ! is_search() ) {

			// Check that combination of page option and TO means page title bar and text should show.
			if ( ( 'yes' === $page_title || 'yes_without_bar' === $page_title || ( 'hide' !== $page_title_option && 'no' !== $page_title ) ) && ( 'yes' === $page_title_text || ( 'no' !== $page_title_text && '0' !== $page_title_text_option ) ) ) {
					$is_title_bar_enabled = true;

			}

			// No page option to check for so just check that TO is not set to hide.
		} elseif ( 'hide' !== $page_title_option && '0' !== $page_title_text_option ) {
			$is_title_bar_enabled = true;
		}

		return $is_title_bar_enabled;
	}
} // End if().

if ( ! function_exists( 'avada_backend_check_new_bbpress_post' ) ) {
	/**
	 * Check if we're creating a new bbPress post.
	 *
	 * @return bool
	 */
	function avada_backend_check_new_bbpress_post() {
		global $pagenow, $post_type;
		return ( 'post-new.php' == $pagenow && in_array( $post_type, array( 'forum', 'topic', 'reply' ) ) ) ? true : false;
	}
}

if ( ! function_exists( 'avada_featured_images_for_pages' ) ) {
	/**
	 * Featured images for pages.
	 */
	function avada_featured_images_for_pages() {

		$video           = '';
		$featured_images = '';
		$post_id         = get_the_ID();

		if ( ! post_password_required( $post_id ) ) {
			if ( Avada()->settings->get( 'featured_images_pages' ) && ! is_archive() ) {
				$pyre_video = apply_filters( 'privacy_iframe_embed', get_post_meta( $post_id, 'pyre_video', true ) );
				if ( 0 < avada_number_of_featured_images() || $pyre_video ) {
					if ( $pyre_video ) {
						$video = '<li><div class="full-video">' . $pyre_video . '</div></li>';
					}

					if ( has_post_thumbnail() && 'yes' !== get_post_meta( $post_id, 'pyre_show_first_featured_image', true ) ) {
						$attachment_data = Avada()->images->get_attachment_data( get_post_thumbnail_id() );
						if ( is_array( $attachment_data ) ) {
							$featured_images .= '<li><a href="' . esc_url( $attachment_data['url'] ) . '" data-rel="iLightbox[gallery' . $post_id . ']" title="' . esc_attr( $attachment_data['caption_attribute'] ) . '" data-title="' . esc_attr( $attachment_data['title_attribute'] ) . '" data-caption="' . esc_attr( $attachment_data['caption_attribute'] ) . '">';
							$featured_images .= '<img src="' . esc_url( $attachment_data['url'] ) . '" alt="' . esc_attr( $attachment_data['alt'] ) . '" />';
							$featured_images .= '</a></li>';
						}
					}

					$i = 2;
					while ( $i <= Avada()->settings->get( 'posts_slideshow_number' ) ) :

						$attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, 'page' );

						if ( $attachment_new_id ) {
							$attachment_data = Avada()->images->get_attachment_data( $attachment_new_id );

							$featured_images .= '<li><a href="' . esc_url( $attachment_data['url'] ) . '" data-rel="iLightbox[gallery' . $post_id . ']" title="' . esc_attr( $attachment_data['caption_attribute'] ) . '" data-title="' . esc_attr( $attachment_data['title_attribute'] ) . '" data-caption="' . esc_attr( $attachment_data['caption_attribute'] ) . '">';
							$featured_images .= '<img src="' . esc_url( $attachment_data['url'] ) . '" alt="' . esc_attr( $attachment_data['alt'] ) . '" />';
							$featured_images .= '</a></li>';
						}
						$i++;
					endwhile;
					?>
					<div class="fusion-flexslider flexslider post-slideshow">
						<ul class="slides">
							<?php echo $video . $featured_images; // WPCS: XSS ok. ?>
						</ul>
					</div>
					<?php
				} // End if().
			} // End if().
		} // End if().
	}
} // End if().

if ( ! function_exists( 'avada_display_sidenav' ) ) {
	/**
	 * Displays side navigation.
	 *
	 * @param  int $post_id The post ID.
	 * @return string
	 */
	function avada_display_sidenav( $post_id ) {

		if ( is_page_template( 'side-navigation.php' ) && 0 !== get_queried_object_id() ) {
			$html = '<ul class="side-nav">';

			$post_ancestors = get_ancestors( $post_id, 'page' );
			$post_parent    = end( $post_ancestors );

			$html .= ( is_page( $post_parent ) ) ? '<li class="current_page_item">' : '<li>';

			if ( $post_parent ) {
				$html    .= '<a href="' . get_permalink( $post_parent ) . '" title="' . esc_html__( 'Back to Parent Page', 'Avada' ) . '">' . get_the_title( $post_parent ) . '</a></li>';
				$children = wp_list_pages( 'title_li=&child_of=' . $post_parent . '&echo=0' );
			} else {
				$html    .= '<a href="' . get_permalink( $post_id ) . '" title="' . esc_html__( 'Back to Parent Page', 'Avada' ) . '">' . get_the_title( $post_id ) . '</a></li>';
				$children = wp_list_pages( 'title_li=&child_of=' . $post_id . '&echo=0' );
			}

			if ( $children ) {
				$html .= $children;
			}

			$html .= '</ul>';

			return $html;
		}
	}
}

if ( ! function_exists( 'avada_number_of_featured_images' ) ) {
	/**
	 * Get the number of featured images.
	 *
	 * @return int
	 */
	function avada_number_of_featured_images() {
		global $post;
		$number_of_images = 0;

		if ( has_post_thumbnail() && 'yes' != get_post_meta( $post->ID, 'pyre_show_first_featured_image', true ) ) {
			$number_of_images++;
		}

		$posts_slideshow_number = Avada()->settings->get( 'posts_slideshow_number' );
		for ( $i = 2; $i <= $posts_slideshow_number; $i++ ) {
			$attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, $post->post_type );

			if ( $attachment_new_id ) {
				$number_of_images++;
			}
		}

		return $number_of_images;
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
