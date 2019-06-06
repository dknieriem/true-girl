<?php /* Template Name: Donation Page */ ?>
<?php
get_header();
$container   = get_theme_mod( 'understrap_container_type' );
$featuredImage = wp_get_attachment_url(get_post_thumbnail_id($post->ID, 'large'));
?>

<div class="wrapper" id="single-wrapper">
    <div class="hero hero--centered" style="background-image: url('<?php echo $featuredImage;?>'); background-repeat: no-repeat; background-size: cover; background-position: center center;">
        <div class="container">
            <h1 class="hero__header hero__header"><?php the_title(); ?></h1>
            <p class="hero__lead"><?php echo types_render_field( "tagline", array( ) ); ?></p>
        </div>
    </div>
    <section class="page-section">
        <div class="page-section__content-wrapper">
            <div class="row">
                <div class="page-section__single-column--narrow-center text-center">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php the_content(); ?>
                    <?php endwhile; // end of the loop. ?>
                </div>
            </div>
        </div>
    </section>
    <section class="page-section">
        <div class="page-section__content-wrapper">
            <div class="row">
                <div class="page-section__single-column">
                    <?php echo types_render_field( "shopify-button-code", array("output" => "raw" ) ) ?>
                </div>
            </div>
        </div>
    </section>

  


</div><!-- Container end -->

</div><!-- Wrapper end -->


<?php get_footer(); ?>