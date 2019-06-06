<?php /* Template Name: Hero Page */ ?>
<?php
get_header();

$container   = get_theme_mod( 'understrap_container_type' );
$featuredImage = wp_get_attachment_url(get_post_thumbnail_id($post->ID, 'large'));
?>

<div class="wrapper" id="page-wrapper">	

	
	<div class="hero hero--centered" style="background-image: url('<?php echo $featuredImage;?>'); background-repeat: no-repeat; background-size: cover; background-position: center center;">
		<div class="hero__overlay"></div>
		<div class="container">
            <h1 class="hero__header hero__header"><?php the_title(); ?></h1>
            <p class="hero__lead"><?php echo types_render_field( "tagline", array( ) ); ?></p>
        </div>
    </div>

					<?php while ( have_posts() ) : the_post(); ?>
						<?php the_content(); ?>
					<?php endwhile; // end of the loop. ?>





</div><!-- Wrapper end -->

<?php get_footer(); ?>