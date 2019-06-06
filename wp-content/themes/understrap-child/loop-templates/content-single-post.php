<?php
/**
 * Single post partial template.
 *
 * @package understrap
 */

?>
<article <?php post_class("post-block"); ?> id="post-<?php the_ID(); ?>">

	
		<?php if(has_excerpt()) { ?>
			<div class="post-block__lead-text">
				<?php echo(get_the_excerpt()); ?>
			</div>
		<?php } ?>
	<div class="entry-content">
		
		<?php the_content(); ?>

		<?php
		wp_link_pages( array(
			'before' => '<div class="page-links">' . __( 'Pages:', 'understrap' ),
			'after'  => '</div>',
		) );
		?>

	</div><!-- .entry-content -->

	<footer class="entry-footer">

		<?php understrap_entry_footer(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
