<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package understrap
 */

get_header();

$container   = get_theme_mod( 'understrap_container_type' );
$featuredImage = wp_get_attachment_url(get_post_thumbnail_id($post->ID, 'large'));
?>

<div class="wrapper" id="page-wrapper">	

	<div class="container-fluid">
		<div class="row">
			<div class="page-heading page-heading--white">
				<h1 class="page-heading__title"><?php the_title(); ?></h1>
				<p class="page-heading__tagline"><?php echo types_render_field( "tagline", array( ) ); ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="container">
					<div class="row">
						<div class="col-md-12">
							<div class="page-wrapper">
								<?php while ( have_posts() ) : the_post(); ?>
									<?php the_content(); ?>
								<?php endwhile; // end of the loop. ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Page header -->
		
		
		<!-- End page header -->
	</div>
	




</div><!-- Wrapper end -->

<?php get_footer(); ?>
