<?php
/**
 * Declaring widgets
 *
 * @package understrap
 */

add_action( 'widgets_init', 'understrap_child_widgets_init' );

if ( ! function_exists( 'understrap_child_widgets_init' ) ) {
	/**
	 * Initializes themes widgets.
	 */
	function understrap_child_widgets_init() {
		register_sidebar( array(
			'name'          => __( 'Blog Sidebar', 'understrap' ),
			'id'            => 'blog-sidebar',
			'description'   => 'Blog sidebar widget area',
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );

	}
} // endif function_exists( 'understrap_child_widgets_init' ).