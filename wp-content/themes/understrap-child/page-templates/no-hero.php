<?php /* Template Name: No Hero Page */ ?>
<?php
get_header();
$container   = get_theme_mod( 'understrap_container_type' );
$featuredImage = wp_get_attachment_url(get_post_thumbnail_id($post->ID, 'large'));
?>

<div class="wrapper" id="single-wrapper">
    <!-- Page header -->
    <div class="container">
        <div class="row">
            <div class="page-heading">
                <h1 class="page-heading__title"><?php the_title(); ?></h1>
                <p class="page-heading__tagline"><?php echo types_render_field( "tagline", array( ) ); ?></p>
            </div>
        </div>
    </div>
    <!-- End page header -->
    <section class="page-section">
        <div class="page-section__content-wrapper">
            <div class="row">
                <div class="page-section__single-column--narrow-center text-center page-content">
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