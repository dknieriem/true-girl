<?php

/**
 * Main shortcodes controller for Views.
 * 
 * @since 2.5.0
 */
final class WPV_Shortcodes {

	public function initialize() {
		
		$relationship_service = new Toolset_Relationship_Service();
		$attr_item_chain = new Toolset_Shortcode_Attr_Item_M2M(
			new Toolset_Shortcode_Attr_Item_Legacy(
				new Toolset_Shortcode_Attr_Item_Id(),
				$relationship_service
			),
			$relationship_service
		);
		
		$factory = new WPV_Shortcode_Factory( $attr_item_chain );
		
		$post_shortcodes = array(
			'wpv-post-title', 'wpv-post-link', 'wpv-post-url', 'wpv-post-body', 'wpv-post-excerpt', 'wpv-post-date', 
			'wpv-post-author', 'wpv-post-featured-image', 'wpv-post-id', 'wpv-post-slug', 'wpv-post-type', 'wpv-post-format', 
			'wpv-post-status', 'wpv-post-comments-number', 'wpv-post-class', 'wpv-post-edit-link', 'wpv-post-menu-order', 
			'wpv-post-field', 'wpv-for-each', 'wpv-post-next-link', 'wpv-post-previous-link', 'wpv-post-taxonomy',
			
			WPV_Shortcode_Control_Post_Relationship::SHORTCODE_NAME, 
			WPV_Shortcode_Control_Post_Relationship::SHORTCODE_NAME_ALIAS,
			WPV_Shortcode_Control_Post_Ancestor::SHORTCODE_NAME, 
			WPV_Shortcode_Control_Post_Ancestor::SHORTCODE_NAME_ALIAS,
			
			WPV_Shortcode_WPML_Conditional::SHORTCODE_NAME
		);
		
		foreach ( $post_shortcodes as $shortcode_string ) {
			if ( $shortcode = $factory->get_shortcode( $shortcode_string ) ) {
				add_shortcode( $shortcode_string, array( $shortcode, 'render' ) );
			};
		}
		
		
		
	}

}