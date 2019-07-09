<?php

/**
 * Class WPV_Shortcode_Post_Edit_Link
 *
 * @since 2.5.0
 */
class WPV_Shortcode_Post_Edit_Link implements WPV_Shortcode_Interface {

	const SHORTCODE_NAME = 'wpv-post-edit-link';

	/**
	 * @var array
	 */
	private $shortcode_atts = array(
		'item'         => null, // post
		'id'           => null, // synonym for 'item'
		'post_id'      => null, // synonym for 'item'
		'text'         => '',
		'label'        => '', // Deprecated, use 'text'  instead
		'style'        => '',
		'class'        => ''
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
	 * WPV_Shortcode_Post_Edit_Link constructor.
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
		
		if ( current_user_can( 'edit_posts' ) ) {
			$style = '';
			if ( ! empty( $this->user_atts['style'] ) ) {
				$style = ' style="'. esc_attr( $this->user_atts['style'] ) .'"';
			}
			$class = '';
			if ( ! empty( $this->user_atts['class'] ) ) {
				$class = ' ' . esc_attr( $this->user_atts['class'] );
			}
			$anchor_text = '';
			if ( 
				isset( $this->user_atts['label'] ) 
				&& ! empty( $this->user_atts['label'] )
			) {
				$anchor_text = sprintf( __( 'Edit %s', 'wpv-views' ), $this->user_atts['label'] );
			} else {
				if ( empty( $this->user_atts['text'] ) ) {
					$anchor_text = __( 'Edit This', 'wpv-views' );
				} else {
					$anchor_text = $this->user_atts['text'];
				}
			}
			$out .= '<a href="' . get_edit_post_link( $item_id ) . '" class="post-edit-link'. $class .'"'. $style .'>';
			$out .= $anchor_text;
			$out .= '</a>';
		}

		apply_filters( 'wpv_shortcode_debug', 'wpv-post-edit-link', json_encode( $this->user_atts ), '', 'Data received from cache', $out );

		return $out;
	}
	
	
}