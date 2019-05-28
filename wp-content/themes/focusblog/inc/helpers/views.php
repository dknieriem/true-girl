<?php

function _thrive_get_sidebar_element_class( $options = null ) {
	if ( ! $options ) {
		$options = thrive_get_theme_options();
	}

	if ( $options['sidebar_alignement'] != 'color' && $options['sidebar_alignement'] != 'image' && $options['sidebar_alignement'] != 'featured' ) {
		return "sdef";
	}

	if ( $options['sidebar_alignement'] == 'color' ) {
		return "scol";
	}

	if ( $options['sidebar_alignement'] == 'image' ) {
		return "sdib";
	}

	if ( $options['sidebar_alignement'] == 'featured' ) {
		if ( ! is_single() && ! is_page() ) {
			return "sdef";
		}

		return "sdfb";
	}
}

function _thrive_get_sidebar_bg_element( $options = null ) {
	if ( ! $options ) {
		$options = thrive_get_theme_options();
	}

	if ( $options['sidebar_alignement'] != 'color' && $options['sidebar_alignement'] != 'image' && $options['sidebar_alignement'] != 'featured' ) {
		return '<div class="sAsdmy"></div>';
	}

	if ( $options['sidebar_alignement'] == 'color' ) {
		return '<div class="sAsdmy" style="background-color: ' . $options['sidebar_style_value'] . '"></div>';
	}

	if ( $options['sidebar_alignement'] == 'image' ) {
		return "<div class='sAsdmy' style=\"background-image: url('" . $options['sidebar_style_value'] . "')\"></div>";
	}

	if ( $options['sidebar_alignement'] == 'featured' ) {
		if ( ! is_single() && ! is_page() ) {
			return '<div class="sAsdmy"></div>';
		}
		$featured_image = null;
		if ( has_post_thumbnail( get_the_ID() ) ) {
			$featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), "wide" );
		}
		if ( $featured_image && isset( $featured_image[0] ) ) {
			return "<div class='sAsdmy' style=\"background-image: url('" . $featured_image[0] . "')\"></div>";
		} else {
			return '<div class="sAsdmy"></div>';
		}
	}

	return '<div class="sAsdmy"></div>';
}

function _thrive_get_sidebar_author_info( $options = null ) {
	if ( ! $options ) {
		$options = thrive_get_theme_options();
	}
	if ( is_single() || is_page() ) {
		global $post;
		$author_id = $post->post_author;
	} elseif ( is_author() ) {
		$author_id = get_the_author_meta( 'ID' );
	} else {
		$author_id = $options['sidebar_author_value'];
		if ( empty( $author_id ) ) {
			return false;
		}
		if ( ! is_numeric( $author_id ) ) {
			return array( 'static_pic' => $author_id );
		}
	}
	$user_info = get_userdata( $author_id );
	if ( ! $user_info ) {
		return false;
	}
	$social_links = ( array(
		"twitter" => get_the_author_meta( 'twitter', $author_id ),
		"fb"      => get_the_author_meta( 'facebook', $author_id ),
		"g_plus"  => get_the_author_meta( 'gplus', $author_id )
	) );


	$author_website_text = get_the_author_meta( 'thrive_author_website_text', $author_id );
	if ( empty( $author_website_text ) ) {
		$author_website_text = __( "Read the full story here", 'thrive' );
	}

	return array(
		'avatar'              => get_avatar( $user_info->user_email, 125 ),
		'display_name'        => ( ! empty( $user_info->first_name ) || ! empty( $user_info->last_name ) ) ? $user_info->first_name . " " . $user_info->last_name : $user_info->display_name,
		'description'         => $user_info->description,
		'social_links'        => $social_links,
		'posts_url'           => get_author_posts_url( $author_id ),
		'author_website'      => get_the_author_meta( 'thrive_author_website', $author_id ),
		'author_website_text' => $author_website_text
	);
}

function _thrive_is_supported_format( $format ) {
	$supported_formats = array( "audio", "image", "gallery", "quote", "video" );

	return in_array( $format, $supported_formats );
}

function _thrive_get_post_format_fields( $format, $post_id ) {
	$options = array();
	switch ( $format ) {
		case "audio":
			$options['audio_type']                  = get_post_meta( $post_id, '_thrive_meta_postformat_audio_type', true );
			$options['audio_file']                  = get_post_meta( $post_id, '_thrive_meta_postformat_audio_file', true );
			$options['audio_soundcloud_embed_code'] = get_post_meta( $post_id, '_thrive_meta_postformat_audio_soundcloud_embed_code', true );
			break;
		case "gallery":
			$options['gallery_images'] = get_post_meta( $post_id, '_thrive_meta_postformat_gallery_images', true );
			$options['gallery_ids']    = explode( ",", $options['gallery_images'] );
			break;
		case "quote":
			$options['quote_text']   = get_post_meta( $post_id, '_thrive_meta_postformat_quote_text', true );
			$options['quote_author'] = get_post_meta( $post_id, '_thrive_meta_postformat_quote_author', true );
			break;
		case "video":
			$thrive_meta_postformat_video_type        = get_post_meta( $post_id, '_thrive_meta_postformat_video_type', true );
			$thrive_meta_postformat_video_youtube_url = get_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_url', true );
			$thrive_meta_postformat_video_vimeo_url   = get_post_meta( $post_id, '_thrive_meta_postformat_video_vimeo_url', true );
			$thrive_meta_postformat_video_custom_url  = get_post_meta( $post_id, '_thrive_meta_postformat_video_custom_url', true );

			$youtube_attrs = array(
				'hide_logo'       => get_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_logo', true ),
				'hide_controls'   => get_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_controls', true ),
				'hide_related'    => get_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_related', true ),
				'hide_title'      => get_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_title', true ),
				'autoplay'        => get_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_autoplay', true ),
				'hide_fullscreen' => get_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_fullscreen', true ),
				'video_width'     => 1080
			);

			if ( $thrive_meta_postformat_video_type == "youtube" ) {
				$video_code = _thrive_get_youtube_embed_code( $thrive_meta_postformat_video_youtube_url, $youtube_attrs );
			} elseif ( $thrive_meta_postformat_video_type == "vimeo" ) {
				$video_code = _thrive_get_vimeo_embed_code( $thrive_meta_postformat_video_vimeo_url );
			} else {
				if ( strpos( $thrive_meta_postformat_video_custom_url, "<" ) !== false || strpos( $thrive_meta_postformat_video_custom_url, "[" ) !== false ) { //if embeded code or shortcode
					$video_code = do_shortcode( $thrive_meta_postformat_video_custom_url );
				} else {
					$video_code = do_shortcode( "[video src='" . $thrive_meta_postformat_video_custom_url . "']" );
				}
			}
			$options['video_type'] = $thrive_meta_postformat_video_type;
			$options['video_code'] = $video_code;
			break;
	}

	return $options;
}

function _thrive_get_featured_image_style( $post_id = 0 ) {
	if ( $post_id == 0 ) {
		return "thumbnail";
	}
	$feature_img_style = get_post_meta( $post_id, "_thrive_meta_post_featured_image", true );
	if ( empty( $feature_img_style ) || $feature_img_style == "default" ) {
		$feature_img_style = thrive_get_theme_options( "featured_image_style" );
	}

	return $feature_img_style;
}

function _thrive_get_featured_image_size( $post_id = 0 ) {
	if ( $post_id == 0 ) {
		return "thumbnail";
	}
	$feature_img_style = get_post_meta( $post_id, "_thrive_meta_post_featured_image", true );
	if ( empty( $feature_img_style ) || $feature_img_style == "default" ) {
		$feature_img_style = thrive_get_theme_options( "featured_image_style" );
	}
	if ( $feature_img_style == "wide" ) {
		return "large";
	}

	return "thumbnail";
}

function _thrive_get_comments_txt( $no ) {
	if ( $no == 1 ) {
		return __( "comment", 'thrive' );
	} else {
		return __( "comments", 'thrive' );
	}
}

function _thrive_check_display_top_meta_info( $postId, $options = null ) {
	if ( ! $options ) {
		$options = thrive_get_options_for_post( $postId );
	}
	$display_info = false;
	//we display the meta info only when the title is displayed or we're not on a page.
	if ( ( isset( $options['show_post_title'] ) && $options['show_post_title'] == 1 ) && ! is_page() ) {
		$display_info = true;
	}

	return $display_info;
}

function _thrive_get_featured_image_src( $postId = null, $params = array() ) {
	if ( ! $postId ) {
		$postId = get_the_ID();
	}
	if ( ! isset( $params['size'] ) || empty( $params['size'] ) ) {
		$params['size'] = "medium";
	}
	$featuredImage = null;
	if ( has_post_thumbnail( $postId ) ) {
		$featuredImage = wp_get_attachment_image_src( get_post_thumbnail_id( $postId ), $params['size'] );
	}
	if ( $featuredImage && isset( $featuredImage[0] ) ) {
		return $featuredImage[0];
	}
	if ( isset( $params['default'] ) && $params['default'] ) {
		return get_template_directory_uri() . "/images/default_featured.jpg";
	}

	return false;
}

function _thrive_render_bottom_related_posts( $postId, $options = null ) {
	if ( ! $postId || ! is_single() ) {
		return false;
	}
	if ( ! $options ) {
		$options = thrive_get_options_for_post( $postId );
	}
	if ( $options['related_posts_box'] != 1 ) {
		return false;
	}
	$postType = get_post_type( $postId );
	if ( $postType != "post" ) {
		return false;
	}

	if ( thrive_get_theme_options( 'related_posts_enabled' ) == 1 ) {
		$relatedPosts = _thrive_get_related_posts( $postId, 'array', $options['related_posts_number'] );
	} else {
		$relatedPosts = get_posts( array(
			'category__in' => wp_get_post_categories( $postId ),
			'numberposts'  => $options['related_posts_number'],
			'post__not_in' => array( $postId )
		) );
	}

	require get_template_directory() . '/partials/bottom-related-posts.php';
}

function thrive_get_post_date( $date_type = 'publish', $relative_time = 1 ) {
	if ( $date_type == 'publish' || get_the_modified_date() == get_the_date() ) {
		if ( $relative_time == 1 ) {
			$post_date = thrive_human_time( get_the_time( 'U' ) );
		} else {
			$post_date = get_the_date();
		}
	} else if ( $date_type == 'modified' ) {
		if ( $relative_time == 1 ) {
			$post_date = 'Updated ' . thrive_human_time( get_the_modified_time( 'U' ) );
		} else {
			$post_date = 'Updated ' . get_the_modified_date();
		}
	}

	return $post_date;
}

add_action( 'tha_head_top', 'thrive_include_meta_post_tags' );

function thrive_include_meta_post_tags() {

	if ( _thrive_check_is_woocommerce_page() ) {
		return false;
	}

	$theme_options = thrive_get_options_for_post();

	if ( ! isset( $theme_options['social_site_meta_enable'] ) || $theme_options['social_site_meta_enable'] === null || $theme_options['social_site_meta_enable'] == "" ) {
		$theme_options['social_site_meta_enable'] = _thrive_get_social_site_meta_enable_default_value();
	}

	if ( $theme_options['social_site_meta_enable'] != 1 ) {
		return false;
	}

	if ( is_single() ) {
		if ( isset( $theme_options['meta_post_date_type'] ) && $theme_options['meta_post_date_type'] == 'modified' ) {
			if ( get_the_modified_date() != get_the_date() ) {
				echo '<meta name="last-modified" content="' . date( "Y-m-d", strtotime( get_the_modified_date() ) ) . '">';
			}
		}
	}

	if ( is_single() || is_page() ) {
		$plugin_file_path = thrive_get_wp_admin_dir() . "/includes/plugin.php";
		include_once( $plugin_file_path );
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			if ( ( ! isset( $theme_options['social_site_title'] ) || $theme_options['social_site_title'] == '' ) &&
			     ( ! isset( $theme_options['social_site_image'] ) || $theme_options['social_site_image'] == '' ) &&
			     ( ! isset( $theme_options['social_site_description'] ) || $theme_options['social_site_description'] == '' ) &&
			     ( ! isset( $theme_options['social_site_twitter_username'] ) || $theme_options['social_site_twitter_username'] == '' )
			) {
				return;
			} else {
				thrive_remove_yoast_meta_description();
			}
		}

		$page_type = 'article';
		if ( ! isset( $theme_options['social_site_title'] ) || $theme_options['social_site_title'] == '' ) {
			$theme_options['social_site_title'] = wp_strip_all_tags( get_the_title() );
		}
		if ( ! isset( $theme_options['social_site_image'] ) || $theme_options['social_site_image'] == '' ) {
			if ( has_post_thumbnail( get_the_ID() ) ) {
				$featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ) );
				if ( $featured_image && isset( $featured_image[0] ) ) {
					$theme_options['social_site_image'] = $featured_image[0];
				}
			}
		}
		if ( ! isset( $theme_options['social_site_description'] ) || $theme_options['social_site_description'] == '' ) {
			$post    = get_post();
			$content = strip_shortcodes( $post->post_content );
			$content = strip_tags( $content );
			$content = preg_replace( "/\s+/", " ", $content );
			$content = str_replace( '&nbsp;', ' ', $content );

			$first_dot         = strpos( $content, '.' ) !== false ? strpos( $content, '.' ) : strlen( $content );
			$first_question    = strpos( $content, '.' ) !== false ? strpos( $content, '.' ) : strlen( $content );
			$first_exclamation = strpos( $content, '.' ) !== false ? strpos( $content, '.' ) : strlen( $content );

			$fist_sentence                            = min( $first_dot, $first_exclamation, $first_question );
			$content                                  = substr( $content, 0, intval( $fist_sentence ) + 1 );
			$theme_options['social_site_description'] = addslashes( $content );
		}
	} else {
		$page_type = 'website';
	}
	$current_url = get_permalink();

	$meta = array(
		//uniqueID => meta
		'og:type'      => array(
			//attribute -> value
			'property' => 'og:type',
			'content'  => $page_type,
		),
		'og:url'       => array(
			'property' => 'og:url',
			'content'  => $current_url,
		),
		'twitter:card' => array(
			'name'    => 'twitter:card',
			'content' => 'summary_large_image'
		),
	);

	if ( isset( $theme_options['social_site_name'] ) && $theme_options['social_site_name'] != '' ) {
		$meta['og:site_name'] = array(
			'property' => 'og:site_name',
			'content'  => str_replace( '"', "'", $theme_options['social_site_name'] )
		);
	}
	if ( isset( $theme_options['social_site_title'] ) && $theme_options['social_site_title'] != '' ) {
		$meta['og:title']      = array(
			'property' => 'og:title',
			'content'  => str_replace( '"', "'", $theme_options['social_site_title'] ),
		);
		$meta['twitter:title'] = array(
			'name'    => 'twitter:title',
			'content' => str_replace( '"', "'", $theme_options['social_site_title'] )
		);
	}
	if ( isset( $theme_options['social_site_image'] ) && $theme_options['social_site_image'] != '' ) {
		$meta['og:image']          = array(
			'property' => 'og:image',
			'content'  => str_replace( '"', "'", $theme_options['social_site_image'] ),
		);
		$meta['twitter:image:src'] = array(
			'name'    => 'twitter:image:src',
			'content' => str_replace( '"', "'", $theme_options['social_site_image'] )
		);

	}
	if ( isset( $theme_options['social_site_description'] ) && $theme_options['social_site_description'] != '' ) {
		$meta['og:description']      = array(
			'property' => 'og:description',
			'content'  => str_replace( '"', "'", $theme_options['social_site_description'] )
		);
		$meta['twitter:description'] = array(
			'name'    => 'twitter:description',
			'content' => str_replace( '"', "'", $theme_options['social_site_description'] )
		);
	}
	if ( isset( $theme_options['social_site_twitter_username'] ) && $theme_options['social_site_twitter_username'] != '' ) {
		$meta['twitter:creator'] = array(
			'name'    => 'twitter:creator',
			'content' => '@' . str_replace( '"', "'", $theme_options['social_site_twitter_username'] )
		);
		$meta['twitter:site']    = array(
			'name'    => 'twitter:site',
			'content' => '@' . str_replace( '"', "'", $theme_options['social_site_twitter_username'] )
		);
	}

	$meta = apply_filters( 'tha_social_meta', $meta );

	if ( empty( $meta ) ) {
		return;
	}
	echo "\n";
	//display all the meta
	foreach ( $meta as $uniquekey => $attributes ) {
		if ( empty( $attributes ) || ! is_array( $attributes ) ) {
			continue;
		}
		echo "<meta ";
		foreach ( $attributes as $attr_name => $attr_value ) {
			echo $attr_name . '="' . $attr_value . '" ';
		}
		echo "/>\n";
	}
	echo "\n";
}

function _thrive_get_main_wrapper_class( $options = null ) {
	if ( ! $options ) {
		$options = thrive_get_theme_options();
	}

	if ( $options['blog_layout'] == "default" || $options['blog_layout'] == "full_width" ) {
		if ( is_archive() || is_author() || is_tag() || is_category() ) {
			return "wrp cnt cidx";
		}

		return "wrp cnt ind";
	}
	if ( $options['blog_layout'] == "grid_full_width" || $options['blog_layout'] == "grid_sidebar" ) {
		return "wrp cnt gin";
	}
	if ( $options['blog_layout'] == "masonry_full_width" || $options['blog_layout'] == "masonry_sidebar" ) {
		return "wrp cnt mryv";
	}

	return "wrp cnt cidx";
}

function _thrive_get_main_section_class( $options = null ) {
	if ( ! $options ) {
		$options = thrive_get_theme_options();
	}
	if ( is_page() ) {
		$sidebar_is_active = is_active_sidebar( 'sidebar-2' );
	} else {
		$sidebar_is_active = is_active_sidebar( 'sidebar-1' );
	}
	$masonry_class = "";
	if ( $options['blog_layout'] == "masonry_full_width" || $options['blog_layout'] == "masonry_sidebar" ) {
		$masonry_class = " mry";
	}
	if ( $options['blog_layout'] == "full_width" || $options['blog_layout'] == "grid_full_width" || $options['blog_layout'] == "masonry_full_width" || ! $sidebar_is_active ) {
		return "bSe fullWidth" . $masonry_class;
	}

	$sidebar_alignement = ( $options['sidebar_alignement'] == "right" ) ? "left" : "right";

	return "bSe " . $sidebar_alignement . $masonry_class;
}

function _thrive_is_active_sidebar( $options = null ) {
	if ( _thrive_check_is_woocommerce_page() ) {
		return is_active_sidebar( 'sidebar-woo' );
	}
	if ( ! $options ) {
		$options = thrive_get_theme_options();
	}
	if ( is_singular() ) {
		$post_template = _thrive_get_item_template( get_the_ID() );
		if ( $post_template == "Narrow" || $post_template == "Full Width" || $post_template == "Landing Page" ) {
			return false;
		}
	}
	if ( is_page() ) {
		$sidebar_is_active = is_active_sidebar( 'sidebar-2' );
	} else {
		$sidebar_is_active = is_active_sidebar( 'sidebar-1' );
	}
	if ( is_singular() ) {
		return $sidebar_is_active;
	}
	if ( $options['blog_layout'] == "full_width" || $options['blog_layout'] == "grid_full_width" || $options['blog_layout'] == "masonry_full_width" || ! $sidebar_is_active ) {
		return false;
	}

	return true;
}

function _thrive_get_post_content_template( $options = null ) {
	if ( ! $options ) {
		$options = thrive_get_theme_options();
	}

	if ( $options['blog_layout'] == "grid_full_width" || $options['blog_layout'] == "grid_sidebar" ) {
		return "grid";
	}
	if ( $options['blog_layout'] == "masonry_full_width" || $options['blog_layout'] == "masonry_sidebar" ) {
		return "masonry";
	}

	return get_post_format();
}

function _thrive_get_post_text_content_excerpt( $content, $postId = 0, $limit = 120, $allowTags = array() ) {
	$GLOBALS['thrive_post_excerpts']   = isset( $GLOBALS['thrive_post_excerpts'] ) ? $GLOBALS['thrive_post_excerpts'] : array();
	$GLOBALS['thrive_post_excerpts'][] = $postId;

	//get the global post
	global $post;

	//save it temporary
	$current_post = $post;

	//set the global post the post received as parameter
	$post = get_post( $postId );

	if ( ! empty( $GLOBALS['thrive_theme_options']['other_show_excerpt'] ) ) {
		$stripped = strip_shortcodes( $content );
	}

	$content = apply_filters( 'the_content', $content );

	//set the global post back to original
	$post = $current_post;

	//remove the continue reading text
	$read_more_type   = thrive_get_theme_options( 'other_read_more_type' );
	$read_more_option = thrive_get_theme_options( "other_read_more_text" );
	$read_more_text   = ( $read_more_option != "" ) ? $read_more_option : "Read more";
	//$read_more_text = str_replace("&#8230;", "...", $read_more_text);
	if ( $read_more_type === 'button' ) {
		/**
		 * if there is a button we need to remove it entirely
		 * @see thrive_more_link
		 */
		$content = preg_replace( '/<a\sclass=\"(.*?)\"\shref=\"(.*?)\"><span>' . $read_more_text . '<\/span><\/a>/s', "", $content );
	} else if ( $read_more_type === 'text' ) {
		$content = preg_replace( '/<a\sclass=\"(.*?)\"\shref=\"(.*?)\">' . str_replace( array( '[', ']' ), array(
				"\\[",
				"\\]"
			), $read_more_text ) . '<\/a>/s', "", $content );
	} else {
		//default case
		$content = str_replace( $read_more_text, "", $content );
	}
	//remove empty P tags
	$content = preg_replace( '/<p><\/p>/s', "", $content );

	//if post content is check in thrive options for In Blog List Display
	if ( isset( $GLOBALS['thrive_theme_options']['other_show_excerpt'] ) && $GLOBALS['thrive_theme_options']['other_show_excerpt'] == 0 ) {
		return $content;
	}

	$start = '\[';
	$end   = '\]';
	if ( isset( $allowTags['br'] ) ) {
		$content = nl2br( $content );
	}
	$content = wp_kses( $content, $allowTags );
	$content = preg_replace( '#(' . $start . ')(.*)(' . $end . ')#si', '', $content );
	if ( strpos( $content, "[" ) < $limit ) {
		$subcontent = substr( $content, strpos( $content, "]" ), $limit );
		if ( strpos( $subcontent, "[" ) === false ) {
			return _thrive_substring( $content, $limit );
		}
	}

	return _thrive_substring( $content, $limit );
}

/**
 * Cut the content to the limit without cutting the last word
 *
 * @param $content
 * @param $limit
 *
 * @return string
 */
function _thrive_substring( $content, $limit ) {
	if ( strlen( $content ) <= $limit ) {
		return $content;
	}
	$length = strpos( $content, " ", $limit );

	/**
	 * this means there's a really long word there, which has no space after it
	 */
	if ( false === $length ) {
		$content = substr( $content, 0, $limit ) . '...';
	} else {
		$content = substr( $content, 0, $length );
	}

	return $content;
}

function thrive_remove_yoast_meta_description() {
	if ( has_action( 'wpseo_head' ) ) {
		if ( isset( $GLOBALS['wpseo_og'] ) ) {
			remove_action( 'wpseo_head', array( $GLOBALS['wpseo_og'], 'opengraph' ), 30 );
		}
		remove_action( 'wpseo_head', array( 'WPSEO_Twitter', 'get_instance' ), 40 );
		remove_action( 'wpseo_head', array( 'WPSEO_GooglePlus', 'get_instance' ), 35 );
	}
}

function thrive_trim_title_words( $title, $characters = 35 ) {

	if ( strlen( $title ) < $characters ) {
		return $title;
	}

	$final_title = '';
	$space_pos   = 0;
	while ( strlen( $final_title ) < $characters ) {
		if ( strpos( $title, ' ', $space_pos ) === false ) {
			break;
		}
		$space_pos   = strpos( $title, ' ', $space_pos ) + 1;
		$final_title = substr( $title, 0, $space_pos ) . ' ...';
	}
	if ( $final_title == '' ) {
		$final_title = substr( $title, 0, $characters ) . '...';
	}

	return $final_title;
}

function thrive_get_wp_admin_dir() {
	$wp_include_dir = preg_replace( '/wp-content$/', 'wp-admin', WP_CONTENT_DIR );

	return $wp_include_dir;
}

function _thrive_check_focus_area_for_pages( $page, $position = "top" ) {
	if ( ! $page ) {
		return false;
	}
	if ( $page == "blog" && ! is_home() ) {
		return false;
	}

	if ( $page == "blog" ) {
		$query = new WP_Query( "post_type=focus_area&meta_key=_thrive_meta_focus_page_blog&meta_value=blog&order=ASC" );
	} elseif ( $page == "archive" ) {
		$query = new WP_Query( "post_type=focus_area&meta_key=_thrive_meta_focus_page_archive&meta_value=archive&order=ASC" );
	}

	$focus_areas = $query->get_posts();

	foreach ( $focus_areas as $focus_area ) {
		$post_custom_atr = get_post_custom( $focus_area->ID );
		if ( isset( $post_custom_atr['_thrive_meta_focus_display_location'] )
		     && isset( $post_custom_atr['_thrive_meta_focus_display_location'][0] )
		     && $post_custom_atr['_thrive_meta_focus_display_location'][0] == $position
		) {
			return true;
		}
	}

	return false;
}

function _thrive_get_main_content_class( $options = null ) {
	if ( ! $options ) {
		$options = thrive_get_theme_options();
	}
	$main_content_class = "fullWidth";
	if ( $options['sidebar_alignement'] == "right" ) {
		$main_content_class = "left";
	} elseif ( $options['sidebar_alignement'] == "left" ) {
		$main_content_class = "right";
	}
	if ( is_page() ) {
		$sidebar_is_active = is_active_sidebar( 'sidebar-2' );
	} else {
		$sidebar_is_active = is_active_sidebar( 'sidebar-1' );
	}
	if ( ! $sidebar_is_active ) {
		$main_content_class = "fullWidth";
	}

	return $main_content_class;
}