<?php /* Template Name: Event Schedule Page */ ?>
<?php
get_header();
$container   = get_theme_mod( 'understrap_container_type' );
$featuredImage = wp_get_attachment_url(get_post_thumbnail_id($post->ID, 'large'));
$args = array(
    'post_type'        => 'event',
   'posts_per_page'   => 25,
   'orderby' => 'meta_value',
  'order' => 'ASC',
  'meta_key' => 'wpcf-event-start-date',
  'meta_query' => array( // WordPress has all the results, now, return only the events after today's date
    array(
        'key' => 'wpcf-event-start-date', // Check the start date field
        'value' => strtotime("today"), // Set today's date
        'compare' => '>=', // Return the ones greater than today's date
        'type' => 'UNSIGNED' // strtotime returns an int
        )
    )
   );
$query = new WP_Query( $args );

?>

<div class="wrapper" id="single-wrapper">
    <!-- Page header -->
    <div class="row">
        <div class="page-heading">
            <h1 class="page-heading__title"><?php the_title(); ?></h1>
            <p class="page-heading__tagline"><?php echo types_render_field( "tagline", array( ) ); ?></p>
        </div>
    </div>
    <!-- End page header -->
    <section class="page-section">
        <div class="page-section__content-wrapper">
            <div class="row">
                <div class="page-section__two-third-column text-center">
                    <?php while ( $query->have_posts() ) : the_post(); ?>
                        <?php $query->the_post(); ?>
                        <div class="event-summary">
                            <div class="event-summary__image-wrapper">
                                <img src="<?php echo types_render_field("event-image", array( "output" => "raw"));?>" alt="" class="event-summary__event-image" />
                            </div> 
                            <div class="event-summary__meta">
                                <div class="event-summary__date">
                                    <?php echo types_render_field( "event-start-date", array("format" => "D, M j, Y g:i a" ) ); ?> - <?php echo types_render_field( "event-end-date", array( "format" => "D, M j, Y g:i a" ) ); ?>
                                </div>
                            <h2 class="event-summary__title"><?php the_title(); ?></h2>
                            <p class="event-summary__location"><?php echo types_render_field( "event-location", array( ) ); ?></p>
                            <p class="event-summary__description"><?php the_content(); ?></p>
                            <a href="<?php echo types_render_field( "event-website", array("output" => "raw") ); ?>" class="button button--white" target="_blank">Event details & tickets</a>
                        </div>  
                        </div>                      
                        
                    <?php endwhile; // end of the loop. ?>
                </div>
                <div class="page-section__one-third-column" style="margin-top:6rem;">
                    <div class="sidebar-cta-card">
                        <h2 class="sidebar-cta-card__heading">Want Dannah to speak at your event?</h2>
                        <p class="sidebar-cta-card__text">Dannah delivers meaty biblical truth in a transparent, conversational style that brings her audience into the presentation. Your audience will laugh. They may cry. They will love the research-based, user-friendly information on popular topics such as biblical womanhood, modesty, purity, raising children to reflect biblical values, the power of prayer, intimacy with God, emotional and sexual healing, and parent-child communication.</p>
                        <a href="<?php echo get_site_url();?>/speaking-requests/" class="button">Book Dannah</a>
                    </div>
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