<?php 

// Custom Menu Walker (to add dropdown indicator)
include( get_stylesheet_directory() . '/inc/custom-menu-walker-skg.php' );

/**
 * Register our sidebars.
 */
  
	function skg_widgets_init() {

		register_sidebar( array(
			'name'          => 'Avada Blog Sidebar',
			'id'            => 'avada-blog-sidebar',
			'description'   => __( 'Default Sidebar of Avada', 'Avada' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<div class="heading"><h4 class="widget-title">',
			'after_title'   => '</h4></div>',
		) );

		register_sidebar( array(
			'name'          => 'Avada Footer Widget 1',
			'id'            => 'avada-footer-widget-1',
			'before_widget' => '<div id="%1$s" class="fusion-footer-widget-column widget %2$s">',
			'after_widget'  => '<div style="clear:both;"></div></div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		) );
	
		register_sidebar( array(
			'name'          => 'Avada Footer Widget 2',
			'id'            => 'avada-footer-widget-2',
			'before_widget' => '<div id="%1$s" class="fusion-footer-widget-column widget %2$s">',
			'after_widget'  => '<div style="clear:both;"></div></div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		) );

		register_sidebar( array(
			'name'          => 'Avada Footer Widget 3',
			'id'            => 'avada-footer-widget-3',
			'before_widget' => '<div id="%1$s" class="fusion-footer-widget-column widget %2$s">',
			'after_widget'  => '<div style="clear:both;"></div></div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		) );

		register_sidebar( array(
			'name'          => 'Avada Footer Widget 4',
			'id'            => 'avada-footer-widget-4',
			'before_widget' => '<div id="%1$s" class="fusion-footer-widget-column widget %2$s">',
			'after_widget'  => '<div style="clear:both;"></div></div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		) );
	}
	
	add_action( 'widgets_init', 'skg_widgets_init' );
	
	function exclude_category($query) {
    if ( $query->is_feed ) {
        $query->set('cat', '-14');
    }
return $query;
}
add_filter('pre_get_posts', 'exclude_category');


// Add External Link to Featured Image with Custom Field
 
add_filter('post_thumbnail_html','add_external_link_on_page_post_thumbnail',10);
    function add_external_link_on_page_post_thumbnail( $html ) {
    if( is_singular() ) {
            global $post;
            $name = get_post_meta($post->ID, 'ExternalUrl', true);
            if( $name ) {
                    $html = '<a href="' . ( $name ) . '" target="_blank" >' . $html . '</a>';
            }
    }
    return $html;
    }

?>