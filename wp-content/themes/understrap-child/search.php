<?php
/**
 * The template for displaying search results pages.
 *
 * @package understrap
 */

get_header();

$container   = get_theme_mod( 'understrap_container_type' );

?>

<div class="wrapper" id="index-wrapper">

<div id="content" tabindex="-1"><!--  class="container" -->

	<!-- Page header -->
	<div class="row">
		<div class="col-md-12">
			<h1 class="heading heading--lg heading--white text-center">Search results for "<?php echo get_search_query(); ?>"</h1>
		</div>
	</div>
	<!-- End page header -->
	<div class="row">
		<div class="col-md-12"><?php get_search_form();?></div>
	</div>

		<?php if ( have_posts() ) : ?>

			<?php /* Start the Loop */ ?>
			<div class="post-card-row">
			<?php while ( have_posts() ) : the_post(); ?>

				<?php

				/*
					* Include the Post-Format-specific template for the content.
					* If you want to override this in a child theme, then include a file
					* called content-___.php (where ___ is the Post Format name) and that will be used instead.
					*/
				get_template_part( 'loop-templates/post-card', get_post_format() );
				?>

			<?php endwhile; ?>
			</div>
		<?php else : ?>

			<?php get_template_part( 'loop-templates/content', 'none' ); ?>

		<?php endif; ?>


	<!-- The pagination component -->
	<?php understrap_pagination(); ?>

<!-- Do the right sidebar check -->
	

</div><!-- .row -->

</div><!-- Container end -->

</div><!-- Wrapper end -->

<?php get_footer(); ?>
