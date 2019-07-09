<?php

/**
 * Class WPV_Shortcode_Post_Previous_Link
 *
 * @since 2.5.0
 */
class WPV_Shortcode_Post_Previous_Link implements WPV_Shortcode_Interface {

	const SHORTCODE_NAME = 'wpv-post-previous-link';

	/**
	 * @var array
	 */
	private $shortcode_atts = array(
		'item'         => null, // post
		'id'           => null, // synonym for 'item'
		'post_id'      => null, // synonym for 'item'
		'format'       => '%%LINK%% &raquo;',
		'link'         => '%%TITLE%%'
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
	 * WPV_Shortcode_Post_Previous_Link constructor.
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
		
		if ( WPV_WPML_Integration::get_instance()->is_wpml_st_loaded() ) {
			$view_settings = apply_filters( 'wpv_filter_wpv_get_view_settings', array() );
			$context = 'View ' . $view_settings['view_slug'];
			$this->user_atts['format'] = wpv_translate(
				'post_control_for_next_link_format_' . md5( $this->user_atts['format'] ),
				$this->user_atts['format'],
				false,
				$context
			);
			$this->user_atts['link'] = wpv_translate(
				'post_control_for_next_link_text_' . md5( $this->user_atts['link'] ),
				$this->user_atts['link'],
				false,
				$context
			);
		}

		$processed_shortcode_placeholders = process_post_navigation_shortcode_placeholders( $this->user_atts['format'], $this->user_atts['link'] );
		$format = $processed_shortcode_placeholders['format'];
		$link = $processed_shortcode_placeholders['link'];
		
		global $post;
		$original_post = $post;
		$post = $item;

		$out = get_previous_post_link( $format, $link );
		
		$post = $original_post;

		apply_filters( 'wpv_shortcode_debug', 'wpv-post-previous-link', json_encode( $this->user_atts ), '', 'Data received from cache', $out );

		return $out;
	}
	
	
}