<?php /* Template Name: Storefront Page */ ?>
<?php
get_header();
$container   = get_theme_mod( 'understrap_container_type' );
$featuredImage = wp_get_attachment_url(get_post_thumbnail_id($post->ID, 'large'));
?>

<div class="wrapper" id="page-wrapper">

    <div id="content" tabindex="-1"><!-- class="container"-->

        <!-- Page header -->
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="heading page-heading__title heading--white-on-pink d-inline-block"><?php the_title(); ?></h1>
                <p class="page-heading__tagline"><?php echo types_render_field( "tagline", array( ) ); ?></p>
            </div>
        </div>
        <!-- End page header -->

        <div class="row content__row">

            <div class="col-12 content__col-12">

            <main class="site-main" id="main">

                <?php while ( have_posts() ) : the_post(); ?>

                    <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

                    <div class="entry-content">

                         <section class="page-section">
                            <div class="page-section__content-wrapper">
                                <div class="row">
                                    <div class="page-section__single-column text-center">
                                    
                                            <?php the_content(); ?>
                                
                                    </div>
                                </div>
                            </div>
                        </section>

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