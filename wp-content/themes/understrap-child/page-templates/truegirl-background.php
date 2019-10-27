<?php /* Template Name: True Girl Page w/ Background
/**
 * 
 *
 * @package understrap
 */

get_header();

$container   = get_theme_mod( 'understrap_container_type' );
?>

<?php if ( is_front_page() && is_home() ) : ?>
	<?php get_template_part( 'global-templates/hero' ); ?>
<?php endif; ?>

<div class="wrapper blog-wrapper" id="index-wrapper">

	<div id="content" tabindex="-1"><!-- class="container"-->

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
