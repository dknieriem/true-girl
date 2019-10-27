<?php
/**
 * Sidebar setup for blog sidebar.
 *
 * @package understrap
 */


if ( is_active_sidebar( 'blog-sidebar' ) ) : ?>


<?php dynamic_sidebar( 'blog-sidebar' ); ?>


<?php endif; ?>