<?php

/**
 * Class WPV_Shortcode_Post_Featured_Image
 *
 * @since 2.5.0
 */
class WPV_Shortcode_Post_Featured_Image implements WPV_Shortcode_Interface {

	const SHORTCODE_NAME = 'wpv-post-featured-image';

	/**
	 * @var array
	 */
	private $shortcode_atts = array(
		'item'         => null, // post
		'id'           => null, // synonym for 'item'
		'post_id'      => null, // synonym for 'item'
		'size'         => 'thumbnail',
		'output'       => '',
		'raw'          => 'false',// DEPRECATED
		'data'         => '',// DEPRECATED
		'attr'         => '',
		'class'        => '',
		'width'        => '',
		'height'       => '',
		'crop'         => false,
		'crop_horizontal' => 'center',
		'crop_vertical'   => 'center'
	);
	
	private $post_attributes_info = array(
		'id'          => 'ID',
		'author'      => 'post_author',
		'date'        => 'post_date',
		'description' => 'post_content',
		'title'       => 'post_title',
		'caption'     => 'post_excerpt',
		'original'    => 'guid'
	);

	/**
	 * @var string|null
	 */
	private $user_content;
	
	/**
	 * @var array
	 */
	private $user_atts;


	/**
	 * @var Toolset_Shortcode_Attr_Interface
	 */
	private $item;

	/**
	 * WPV_Shortcode_Post_Featured_Image constructor.
	 *
	 * @param Toolset_Shortcode_Attr_Interface $item
	 */
	public function __construct(
		Toolset_Shortcode_Attr_Interface $item
	) {
		$this->item  = $item;
	}

	/**
	* Get the shortcode output value.
	*
	* @param $atts
	* @param $content
	*
	* @return string
	*
	* @since 2.5.0
	*/
	public function get_value( $atts, $content = null ) {
		$this->user_atts    = shortcode_atts( $this->shortcode_atts, $atts );
		$this->user_content = $content;

		if ( ! $item_id = $this->item->get( $this->user_atts ) ) {
			// no valid item
			throw new WPV_Exception_Invalid_Shortcode_Attr_Item();
		}
		
		// LEGACY - backwards compatibility
		$this->adjust_legacy_attributes();
		// END LEGACY - backwards compatibility
		
		$out = '';
		
		$item = get_post( $item_id );

		// Adjust for WPML support
		// If WPML is enabled, $item_id should contain the right ID for the current post in the current language
		// However, if using the id attribute, we might need to adjust it to the translated post for the given ID
		$item_id = apply_filters( 'translate_object_id', $item_id, $item->post_type, true, null );
		
		if ( 'img' == $this->user_atts['output'] ) {
			$out = $this->get_featured_image( $item_id );
		} else {
			$out = $this->get_featured_image_data( $item_id );
		}
		$out = apply_filters( 'wpv-post-featured-image', $out );

		apply_filters( 'wpv_shortcode_debug', 'wpv-post-featured-image', json_encode( $this->user_atts ), '', 'Data received from cache', $out );

		return $out;
	}
	
	private function adjust_legacy_attributes() {
		if ( empty( $this->user_atts['output'] ) ) {
			if ( 
				$this->user_atts['raw'] === 'true'  
				|| ! empty( $this->user_atts['data'] ) 
			) {
				if ( empty( $this->user_atts['data'] ) ) {
					$this->user_atts['output'] = 'url';
				} else {
					$this->user_atts['output'] = $this->user_atts['data'];
				}
			} else {
				$this->user_atts['output'] = 'img';
			}
		}
	}
	
	private function get_featured_image( $item_id ) {
		$out = '';
		$crop = false;
		
		$attr_array = $this->get_img_attributes();
		
		// Custom Image Size
		if ( 'custom' == $this->user_atts['size'] ) {
			$post_thumbnail_id = get_post_thumbnail_id( $item_id );
			$out_array = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );

			if ( $out_array ) {
				if ( $this->user_atts['crop'] ) {
					$crop = array ( $this->user_atts['crop_horizontal'], $this->user_atts['crop_vertical'] );
				}

				/**
				 * @see wpv_shortcodes_resize_image()
				 */
				$image = $this->resize_image( $out_array[0], $this->user_atts['width'], $this->user_atts['height'], $crop );

				if ( ! is_wp_error( $image ) ) {
					$attr_array['src'] = $image;
					$out = wpv_get_html_tag( 'img', $attr_array, true, '' );
				}
			}
		} else {
			$out = get_the_post_thumbnail( $item_id, $this->user_atts['size'], $attr_array );
		}

		return $out;
	}
	
	private function get_featured_image_data( $item_id ) {
		$out = '';
		$crop = false;
		
		$post_thumbnail_id = get_post_thumbnail_id( $item_id );
		if ( ! empty( $post_thumbnail_id ) ) {
			switch ( $this->user_atts['output'] ) {
				case 'id':
				case 'author':
				case 'date':
				case 'description':
				case 'title':
				case 'caption':
				case 'original':
					$new_info = get_post( $post_thumbnail_id );
					$new_value = $this->post_attributes_info[ $this->user_atts['output'] ];
					$out = isset( $new_info->$new_value ) ? $new_info->$new_value : '';
					break;
				case 'alt':
					$out = get_post_meta( $post_thumbnail_id , '_wp_attachment_image_alt', true );
					break;
				case 'url':
				default:
					if ( $this->user_atts['size'] == 'full' ) {
						$new_info = get_post( $post_thumbnail_id );
						$out = isset( $new_info->guid ) ? $new_info->guid : '';
					} else {
						$out_array = wp_get_attachment_image_src( $post_thumbnail_id, $this->user_atts['size'] );
						$out = $out_array[0];

						if ( 'custom' == $this->user_atts['size'] ) {
							if ( $this->user_atts['crop'] ) {
								$crop = array ( $this->user_atts['crop_horizontal'], $this->user_atts['crop_vertical'] );
							}

							$image = $this->resize_image( $out_array[0], $this->user_atts['width'], $this->user_atts['height'], $crop );

							if ( ! is_wp_error( $image ) ) {
								$out = $image;
							}
						}
					}

					$out = set_url_scheme( $out );
				break;
			}
		}
		return $out;
	}
	
	private function get_img_attributes() {
		$attr_array = array();
		
		if ( ! empty( $this->user_atts['attr'] ) ) {
			$ampersand_valid = '&';
			$character_valid = array( '&' );
			$query_var_valid = array(
				'&title', 
				'&alt', 
				'&class'
			);
			$brackets_valid = array( '[', ']' );
			$ampersand_alias = array( '&amp;', '&#038;' );
			$query_var_html_entity = array( 
				'&#038;title', 
				'&#038;alt', 
				'&#038;class' 
			);
			$query_var_escaped = array( 
				'&amp;title', 
				'&amp;alt',  
				'&amp;class' 
			);
			$brackets_escaped = array( '&#91;', '&#93;' );
			$character_hack_replace = array( '#wpv-amperhack#' );
			$query_var_hack_replace = array( 
				'#wpv-title-hack#',  
				'#wpv-alt-hack#', 
				'#wpv-class-hack#' 
			);
			
			// first, escape and strip tags
			$attr = esc_attr( strip_tags( $this->user_atts['attr'] ) );
			// now, hack the ampersands on legitimate query-like attributes
			$attr = str_replace( $query_var_html_entity, $query_var_hack_replace, $attr );
			$attr = str_replace( $query_var_escaped, $query_var_hack_replace, $attr );
			// adjust the brackets
			$attr = str_replace( $brackets_valid, $brackets_escaped, $attr );
			// hack the remaining ampersands, even the ones coming from HTML characters
			$attr = str_replace( $ampersand_alias, $ampersand_valid, $attr );
			$attr = str_replace( $character_valid, $character_hack_replace, $attr );
			// add nack the legitimate ampersands
			$attr = str_replace( $query_var_hack_replace, $query_var_valid, $attr );
			// parse the attributes
			wp_parse_str( $attr, $attr_array );
			// restore the other ampersands
			$attr_array = str_replace( $character_hack_replace, $character_valid, $attr_array );
		}
		
		if ( ! empty( $this->user_atts['class'] ) ) {
			$attr_array['class'] = 'attachment-' . esc_attr( $this->user_atts['size'] ) . '  ' . esc_attr( $this->user_atts['class'] );
		}
		
		return $attr_array;
	}
	
	 /**
	 * Apply image resizing, based on width/height and proportional/cropping.
	 * Supports wp_upload_dir() based image locations only.
	 *
	 * @param $image_url URL of the image
	 * @param $width Intended maximum width of the image
	 * @param $height Intended maximum height of the image
	 * @param $crop bool|array Array of crop positions or 'false'
	 *
	 * @return mixed Resized image URL or WP_Error
	 *
	 * @since 2.2.0
	 *
	 * @uses wpv_image_resize()
	 */
	private function resize_image( $image_url, $width, $height, $crop ) {
		$uploads = wp_upload_dir();
		// Get image absolute path
		$file = str_replace( $uploads['baseurl'], $uploads['basedir'], $image_url );
		$suffix = "{$width}x{$height}";

		if ( false !== $crop ) {
			$suffix .= '_' . implode( '_', $crop );
		}

		$image = wpv_image_resize( $file, $width, $height, $crop, $suffix );

		if ( ! is_wp_error( $image ) ) {
			// Get image URL
			$image = str_replace( $uploads['basedir'], $uploads['baseurl'], $image );
		}

		return $image;
	}
	
	
}