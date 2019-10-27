<?php

/**
 * Get slides data.
 *
 * @since  5.0.5
 * @param array   $args Query args.
 * @return array        Slides collection.
 */
function su_get_slides( $args ) {

	$args = wp_parse_args(
		$args,
		array(
			'source'  => 'none',
			'limit'   => 20,
			'gallery' => null,
			'type'    => '',
			'link'    => 'none',
		)
	);

	if (
		null !== $args['gallery'] ||
		( 'none' === $args['source'] && get_option( 'su_option_galleries-432' ) )
	) {
		return su_get_slides_432( $args );
	}

	$slides = array();

	foreach ( array( 'media', 'posts', 'category', 'taxonomy' ) as $type ) {

		if ( strpos( trim( $args['source'] ), $type . ':' ) === 0 ) {
			$args['source'] = array(
				'type' => $type,
				'val'  => (string) trim( str_replace( array( $type . ':', ' ' ), '', $args['source'] ), ',' ),
			);
			break;
		}

	}

	if ( ! is_array( $args['source'] ) ) {
		return $slides;
	}

	$query = array( 'posts_per_page' => $args['limit'] );

	if ( 'media' === $args['source']['type'] ) {

		$query['post_type']   = 'attachment';
		$query['post_status'] = 'any';
		$query['post__in']    = (array) explode( ',', $args['source']['val'] );
		$query['orderby']     = 'post__in';

	}

	// Source: posts
	if ( 'posts' === $args['source']['type'] ) {

		if ( 'recent' !== $args['source']['val'] ) {

			$query['post__in']  = (array) explode( ',', $args['source']['val'] );
			$query['orderby']   = 'post__in';
			$query['post_type'] = 'any';

		}

	} elseif ( 'category' === $args['source']['type'] ) {
		$query['category__in'] = (array) explode( ',', $args['source']['val'] );
	} elseif ( 'taxonomy' === $args['source']['type'] ) {

		$args['source']['val'] = explode( '/', $args['source']['val'] );

		if (
			! is_array( $args['source']['val'] ) ||
			count( $args['source']['val'] ) !== 2
		) {
			return $slides;
		}

		$query['tax_query'] = array(
			array(
				'taxonomy' => $args['source']['val'][0],
				'field'    => 'id',
				'terms'    => (array) explode( ',', $args['source']['val'][1] ),
			),
		);
		$query['post_type'] = 'any';

	}

	$query = apply_filters( 'su/slides_query', $query, $args );
	$query = new WP_Query( $query );

	if ( is_array( $query->posts ) ) {

		foreach ( $query->posts as $post ) {

			$thumb = 'media' === $args['source']['type'] || 'attachment' === $post->post_type
				? $post->ID
				: get_post_thumbnail_id( $post->ID );

			if ( ! is_numeric( $thumb ) ) {
				continue;
			}

			$slide = array(
				'image' => wp_get_attachment_url( $thumb ),
				'link'  => '',
				'title' => get_the_title( $post->ID ),
			);

			if ( 'image' === $args['link'] || 'lightbox' === $args['link'] ) {
				$slide['link'] = $slide['image'];
			} elseif ( 'custom' === $args['link'] ) {
				$slide['link'] = get_post_meta( $post->ID, 'su_slide_link', true );
			} elseif ( 'post' === $args['link'] ) {
				$slide['link'] = get_permalink( $post->ID );
			} elseif ( 'attachment' === $args['link'] ) {
				$slide['link'] = get_attachment_link( $thumb );
			}

			$slides[] = $slide;

		}

	}

	return $slides;

}

/**
 * Get slides data.
 *
 * Deprecated since 4.3.2.
 *
 * @since  5.0.5
 * @param array   $args Query args.
 * @return array       Slides collection.
 */
function su_get_slides_432( $args ) {

	$args = wp_parse_args(
		$args,
		array(
			'gallery' => 1,
		)
	);

	$slides = array();

	$args['gallery'] = null === $args['gallery']
		? 0
		: $args['gallery'] - 1;

	$galleries = get_option( 'su_option_galleries-432' );

	if ( ! is_array( $galleries ) ) {
		return $slides;
	}

	if ( isset( $galleries[ $args['gallery'] ] ) ) {
		$slides = $galleries[ $args['gallery'] ]['items'];
	}

	return $slides;

}

/**
 * Helper function to get gallery slides.
 *
 * Example input:
 *
 * media: 1, 2, 3
 * media: recent
 * posts: 1, 2, 3
 * posts: recent
 * taxonomy: book/3, 5
 *
 * Example output:
 *
 * [
 *   [
 *     'attachment_id' => 1,
 *     'link'          => 'https://...',
 *     'caption'       => '...'
 *   ],
 *   ...
 * ]
 *
 * @since  5.4.0
 * @param  string $source Images source string.
 * @param  array  $args   Additional parameters.
 * @return array          Array with parsed data on success, False otherwise.
 */
function su_get_gallery_slides( $source, $args = array() ) {

	$slides = array();
	$query  = array();
	$source = su_parse_images_source( $source );

	if ( ! $source ) {
		return array();
	}

	if ( 'media' === $source['type'] ) {

		$query['post_mime_type'] = 'image/jpeg,image/gif,image/jpg,image/png';
		$query['post_type']      = 'attachment';
		$query['post_status']    = 'inherit';

		if ( 'recent' === $source['ids'] ) {
			$query['posts_per_page'] = intval( $args['limit'] );
		}

		if ( 'recent' !== $source['ids'] ) {

			$query['posts_per_page'] = -1;
			$query['post__in']       = $source['ids'];
			$query['orderby']        = 'post__in';

		}

	}

	if ( 'posts' === $source['type'] ) {

		$query['post_type'] = 'any';

		if ( 'recent' === $source['ids'] ) {
			$query['posts_per_page'] = intval( $args['limit'] );
		}

		if ( 'recent' !== $source['ids'] ) {

			$query['posts_per_page'] = -1;
			$query['post__in']       = $source['ids'];
			$query['orderby']        = 'post__in';

		}

	}

	if ( 'taxonomy' === $source['type'] ) {

		if ( ! $source['tax'] ) {
			return array();
		}

		$query['tax_query']      = array(
			array(
				'taxonomy' => $source['tax'],
				'terms'    => $source['ids'],
				'field'    => 'id',
			),
		);
		$query['post_type']      = 'any';
		$query['posts_per_page'] = intval( $args['limit'] );

	}

	$query = apply_filters( 'su/get_gallery_slides_query', $query, $source, $args );
	$query = new WP_Query( $query );

	if ( ! is_array( $query->posts ) ) {
		return array();
	}

	foreach ( $query->posts as $post ) {

		$attachment_id = 'media' === $source['type'] || 'attachment' === $post->post_type
			? $post->ID
			: get_post_thumbnail_id( $post->ID );

		if ( ! is_numeric( $attachment_id ) ) {
			continue;
		}

		$slide = array(
			'attachment_id' => $attachment_id,
			'caption'       => trim( wp_get_attachment_caption( $attachment_id ) ),
		);

		switch ( $args['link'] ) {

			case 'image':
			case 'lightbox':
				$slide['link'] = wp_get_attachment_image_src( $attachment_id, 'full', false );
				$slide['link'] = $slide['link'][0];
				break;

			case 'custom':
				$slide['link'] = get_post_meta( $attachment_id, 'su_slide_link', true );
				break;

			case 'post':
				$slide['link'] = get_permalink( $post->ID );
				break;

			case 'attachment':
				$slide['link'] = get_attachment_link( $attachment_id );
				break;

			default:
				$slide['link'] = '';
				break;

		}

		$slides[] = $slide;

	}

	return $slides;

}

/**
 * Helper function to parse image source strings.
 *
 * Input:
 *
 * media: 1, 2, 3
 * media: recent
 * posts: 1, 2, 3
 * posts: recent
 * taxonomy: book/3, 5
 *
 * Output:
 *
 * [
 *   'type' => 'media',
 *   'tax'  => 'book',
 *   'ids'  => [ 1, 2, 3 ]
 * ]
 *
 * @since  5.4.0
 * @param  string     $source Images source string.
 * @return array|bool         Array with parsed data on success, False otherwise.
 */
function su_parse_images_source( $source ) {

	$source = str_replace( ' ', '', $source );
	$source = strtolower( $source );

	/**
	 * $match - result of preg_match
	 *  - 1: pattern match the subject
	 *  - 0: pattern does not match the subject
	 *  - False: error occurred
	 *
	 * $source[1] - image source (e.g. media, posts)
	 * $source[2] - taxonomy name (e.g. book)
	 * $source[3] - post/term IDs (e.g. 1,2,3, recent)
	 */
	$match = preg_match(
		'/^(media|posts|taxonomy):(?:([a-zA-Z0-9-_]*)\/)*((?:\d+,)*\d+|recent)/',
		$source,
		$source
	);

	if ( 1 !== $match ) {
		return false;
	}

	if ( 'recent' !== $source[3] ) {
		$source[3] = explode( ',', $source[3] );
	}

	return array(
		'type' => $source[1],
		'tax'  => $source[2],
		'ids'  => $source[3],
	);

}

/**
 * Helper function to get array with available intermediate image sizes.
 *
 * @since 5.4.0
 * @return array Array with available image sizes.
 */
function su_get_image_sizes() {

	$sizes = array(
		'full' => __( 'Original image size', 'shortcodes-ultimate' ),
	);

	foreach ( get_intermediate_image_sizes() as $size ) {
		$sizes[ $size ] = ucfirst( $size );
	}

	return $sizes;

}
