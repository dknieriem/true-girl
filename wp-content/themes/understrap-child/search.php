<?php
/**
 * The template for displaying search results pages.
 *
 * @package understrap
 */

get_header();

$container   = get_theme_mod( 'understrap_container_type' );

?>

<div class="wrapper blog-wrapper" id="index-wrapper">

<div id="content" tabindex="-1"><!--  class="container" -->

	<!-- Page header -->
	<div class="row">
		<div class="col-md-12 text-center">
			<h1 class="heading heading--white-on-pink d-inline-block">Search results for "<?php echo get_search_query(); ?>"</h1>
		</div>
	</div>
	<!-- End page header -->

	<!-- Search Form -->
	<div class="row search-page-form">
		<div class="col-md-6 offset-md-3"><?php get_search_form();?></div>
	</div>
	<!-- End Search Form -->

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

<section class="page-section text-left background-pink responsive-background- " style="background-image: url( '/wp-content/uploads/2019/07/EmailSignupPinkBkgd.png'); background-repeat: no-repeat; background-size:cover; background-position: center center;"><div class="page-section__content-wrapper">
		
<div class="row prefooter full-width--no mc-page-form col-sm-10 offset-sm-1 text-center justify-content-center">
	
<h2 class="text-white">Truth, delivered to your inbox</h2>
<?php echo do_shortcode('[mc4wp_form id="7805"]'); ?>

</div>

	</div>
</section>

</div><!-- Container end -->

</div><!-- Wrapper end -->

<?php get_footer(); ?>
