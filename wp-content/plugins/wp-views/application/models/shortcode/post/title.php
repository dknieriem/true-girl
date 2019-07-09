<?php

/**
 * Class WPV_Shortcode_Post_Title
 *
 * @since 2.5.0
 */
class WPV_Shortcode_Post_Title implements WPV_Shortcode_Interface {

	const SHORTCODE_NAME = 'wpv-post-title';

	/**
	 * @var array
	 */
	private $shortcode_atts = array(
		'item'         => null, // post
		'id'           => null, // synonym for 'item'
		'post_id'      => null, // synonym for 'item'
		'output'       => 'raw' // 'raw'|'sanitize'
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
	 * WPV_Shortcode_Post_Title constructor.
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
		
		$out = '';
		
		$item = get_post( $item_id );

		// Adjust for WPML support
		// If WPML is enabled, $item_id should contain the right ID for the current post in the current language
		// However, if using the id attribute, we might need to adjust it to the translated post for the given ID
		$item_id = apply_filters( 'translate_object_id', $item_id, $item->post_type, true, null );
		
		$out .= apply_filters( 'the_title', $item->post_title, $item->ID );
		
		// If output="sanitize" then strip tags, escape attributes and replace brackets
		if ( 'sanitize' === $this->user_atts['output'] ) {
			$out = sanitize_text_field( $out );
			$brackets_before = array( '[', ']', '<', '>' );
			$brackets_after = array( '&#91;', '&#93;', '&lt;', '&gt;' );
			$out = str_replace( $brackets_before, $brackets_after, $out );
		}

		apply_filters( 'wpv_shortcode_debug', 'wpv-post-title', json_encode( $this->user_atts ), '', 'Data received from cache', $out );

		return $out;
	}
	
	
}