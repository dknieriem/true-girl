<?php /* Template Name: Podcast Page */ ?>
<?php
get_header();
$container   = get_theme_mod( 'understrap_container_type' );
$featuredImage = wp_get_attachment_url(get_post_thumbnail_id($post->ID, 'large'));
$obj = get_post_type_object('mom-moments-episode');
$podcastSlug = types_render_field( "page-podcast-slug", array( "output" => "raw") );
$currentTopic = get_query_var('topic', 0);


// $args = array(
//     'post_type'        => 'podcast',
//    'posts_per_page'   => 25,
//    'orderby' => 'post_date',
//   'order' => 'ASC'
//    );
// $query = new WP_Query( $args );

?>

<div class="wrapper" id="single-wrapper">
    <!-- Page header -->
    <div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">
        <div class="row">
            <div class="col-md-12">
                <div class="podcast-intro">
                    <div class="podcast-intro__post-image-wrapper">
                        <img class="podcast-intro__post-image" src="<?php echo $featuredImage;?>" alt="Featured image" />
                    </div>
                    <div class="podcast-intro__post-meta">
                        <h1 class="podcast-intro__title"><?php the_title(); ?></h1>
                        <div class="podcast-intro__description-wrapper">
                            <p class="podcast-intro__description"><?php echo  types_render_field( "podcast-description", array( "output" => "raw"));?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        
    <!-- End page header -->
    <section class="page-section">
        <div class="page-section__content-wrapper">
            <div class="row">
                <div class="page-section__two-third-column">
                <?php
                
                    if (have_posts()) {
                        while (have_posts()) {
                            
                            the_post();
                            ?>
                             
                        <?php 
                    } // end while
                    }
                    ?>
                    <?php 

                    if ($currentTopic)
                    {
                        $args = array(
                            'post_type'        => $podcastSlug,
                            'posts_per_page'   => 25,
                            'orderby' => 'post_date',
                            'order' => 'DESC',
                            'tax_query' => array(
                                array (
                                    'taxonomy' => 'topics',
                                    'field' => 'slug',
                                    'terms' => $currentTopic,
                                )
                            )
                            );
                            $query = new WP_Query( $args );
                    }
                    else
                    {
                        $args = array(
                            'post_type'        => $podcastSlug,
                            'posts_per_page'   => 25,
                            'orderby' => 'post_date',
                            'order' => 'DESC'
                            );
                            $query = new WP_Query( $args );
                    }
                        
                    ?>
                    <?php 
                    global $wp;
                    $current_url = home_url( add_query_arg( array(), $wp->request ) );
                    $terms = get_terms('topics', array(
                        'hide_empty'       => true
                    ));
                    ?>
                    <?php if ($terms && ($podcastSlug == 'mom-moments-episode')) { ?>
                    <div class="row mt-4 mb-5">
                        
                        <div class="page-section__single-column ">
                        <a class="filter-button " href="<?php echo esc_url( remove_query_arg( 'topic')); ?>">All Topics</a>
                            <?php
                                foreach ( $terms as $term ) { ?>
                                <a class="filter-button " href="<?php echo esc_url( add_query_arg( 'topic', $term->slug )); ?>"><?php echo $term->name; ?></a>
                                <?php }
                            ?>
                        </div>
                    </div>  
                    <?php } ?>  

                    <?php while ( $query->have_posts() ) : the_post(); ?>
                        <?php $query->the_post(); ?>
                        <div class="podcast-row">
                            <div class="podcast-row__summary collapsed" data-toggle="collapse" data-target="#details-<?php echo get_the_id(); ?>">
                                <div class="podcast-row__date">
                                    <?php echo get_the_date('M j, Y'); ?>
                                </div>
                                <h2 class="podcast-row__title"><?php the_title(); ?></h2>
                                <img class="podcast-row__collapse-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/img/collapse-icon.png" alt="">
                            </div>
                            <div class="podcast-row__meta">
                                <?php $postId = get_the_id(); ?>
                                <div id="details-<?php echo $postId ?>" class="podcast-row__details collapse">
                                    <p class="podcast-row__date-mobile">
                                        <?php echo get_the_date('M j, Y'); ?>
                                    </p>
                                    <p class="podcast-row__description"><?php the_content(); ?></p>

                                    <?php $resources = get_post_meta( $postId, 'wpcf-related-resources', true );?>
                                    <?php if ($resources) { ?>
                                        <h3 class="podcast-row__resources-header">More resources</h3>
                                    <?php } ?>
                                    <div class="podcast-row__resources"><?php echo $resources; ?></div>
                                </div>
                            </div>
                        </div>                         
                    <?php endwhile; // end of the loop. ?>
                </div>
                <div class="page-section__one-third-column">
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