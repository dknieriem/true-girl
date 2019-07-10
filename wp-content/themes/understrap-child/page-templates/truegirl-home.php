<?php
/**
 * /* Template Name: True Girl Page
 * 
 * The home page template file
 *
 * Cloned from the default understrap page template,
 * this template removes the global hero and page header sections.
 * That's it. Really! 
 * All other homepage styling is done via the block editor.
 *
 * @package understrap
 */

get_header();

$container   = get_theme_mod( 'understrap_container_type' );

?>

<div class="wrapper" id="page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row content__row">

			<div class="col-12 content__col-12">

			<main class="site-main" id="main">

				<?php while ( have_posts() ) : the_post(); ?>

					<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

					<div class="entry-content">

						<?php the_content(); ?>

					</div><!-- .entry-content -->

					<!-- <footer class="entry-footer">

						<?php //edit_post_link( __( 'Edit', 'understrap' ), '<span class="edit-link">', '</span>' ); ?>

					</footer><!-- .entry-footer -->

				</article><!-- #post-## -->

				<?php endwhile; // end of the loop. ?>

			</main><!-- #main -->

		</div><!-- .col-12 -->

	</div><!-- .row -->

</div><!-- Container end -->

</div><!-- Wrapper end -->

<?php get_footer(); ?>

