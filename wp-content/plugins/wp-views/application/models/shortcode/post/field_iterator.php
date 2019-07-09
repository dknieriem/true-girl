<?php

/**
 * Class WPV_Shortcode_Post_Field_Iterator
 *
 * @since 2.5.0
 */
class WPV_Shortcode_Post_Field_Iterator implements WPV_Shortcode_Interface {

	const SHORTCODE_NAME = 'wpv-post-field-iterator';
	const SHORTCODE_NAME_ALIAS = 'wpv-for-each';

	/**
	 * @var array
	 */
	private $shortcode_atts = array(
		'item'         => null, // post
		'id'           => null, // synonym for 'item'
		'post_id'      => null, // synonym for 'item'
		'field'        => '',
		'start'        => 1,
		'end'          => null
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
	 * WPV_Shortcode_Post_Field_Iterator constructor.
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
		
		if ( strpos( $this->user_content, 'wpv-b64-' ) === 0) {
			$this->user_content = substr( $this->user_content, 7 );
			$this->user_content = base64_decode( $this->user_content );
		}

		if ( empty( $this->user_atts['field'] ) ) {
			return wpv_do_shortcode( $this->user_content );
		}

		if ( ! $item_id = $this->item->get( $this->user_atts ) ) {
			// no valid item
			throw new WPV_Exception_Invalid_Shortcode_Attr_Item();
		}
		
		$out = '';
		
		$item = get_post( $item_id );

		// Adjust for WPML support
		// If WPML is enabled, $item_id should contain the right ID for the current post in the current language
		// However, if using the id attribute, we might need to adjust it to the translated post for the given ID
		$item_id = apply_filters( 'translate_object_id', $item_id, $item->post_type, true, null );

		$meta = get_post_meta( $item_id, $this->user_atts['field'] );

		if ( ! $meta ) {
			// This happens when there is no meta with that key asociated with that post, so return nothing
			// From 1.4
			return '';
		}

		// When the metavalue for this key is empty, $meta is an array with just an empty first element
		// In that case, return nothing either
		// Since 1.4.0
		if ( 
			is_array( $meta ) 
			&& ( count( $meta ) == 1 ) 
			&& empty( $meta[0] ) 
		) {
			return '';
		}

		$start = (int) $this->user_atts['start'];
		$start = $start - 1;
		if ( $start < 0 ) {
			$start = 0;
		}
		
		if ( is_null( $this->user_atts['end'] ) ) {
			$this->user_atts['end'] = count( $meta );
		}
		$end = (int) $this->user_atts['end'];
		if ( $end > count( $meta ) ) {
			$end = count( $meta );
		}
		
		$inner_loopers = "/\\[(wpv-post-field|types).*?\\]/i";
		$counts = preg_match_all( $inner_loopers, $this->user_content, $matches );
		$value_arr = array();
		for ( $i = $start; $i < $end; $i++ ) {
			// Set indexes in the wpv-post-field shortcode
			if ( $counts > 0 ) {
				$new_value = $this->user_content;
				foreach( $matches[0] as $index => $match ) {
					// execute shortcode content and replace
					$shortcode = $matches[ 1 ][ $index ];
					$apply_index = $this->should_apply_loop_idex( $shortcode, $match, $this->user_atts['field'] );
					
					if ( $apply_index ) {
						$resolved_match = str_replace( '[' . $shortcode . ' ', '[' . $shortcode . ' index="' . $i . '" ', $match );
						$new_value = str_replace( $match, $resolved_match, $new_value );
					}
				}
				$value_arr[] = $new_value;

			} else {
				$value_arr[] = $this->user_content;
			}
		}
		$out .= implode( '', $value_arr );

		apply_filters( 'wpv_shortcode_debug', 'wpv-for-each', json_encode( $this->user_atts ), '', 'Data received from cache', $out );

		return $out;
	}
	
	/**
	 * @todo add suppor for only adding the index to the right wpv-post-field or types shortcode.
	 */
	private function should_apply_loop_idex( $shortcode_type, $shortcode, $field ) {
		$apply_index = false;
		
		if ( strpos( $shortcode, " index=" ) === false ) {
			$apply_index = true;
		}
		
		return $apply_index;
		
	}
	
	
}