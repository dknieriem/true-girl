<?php 

get_header();

$container   = get_theme_mod( 'understrap_container_type' );
$featuredImage = types_render_field( "event-image", array( "raw" => "true"));
?>

<div class="wrapper" id="page-wrapper">	
	
	<!-- Page header -->
    <div class="hero hero--centered" style="background-image: url('<?php echo $featuredImage;?>'); background-repeat: no-repeat; background-size: cover; background-position: center center;">
        <div class="hero__overlay"></div>
        <div class="container">
            <h1 class="hero__header hero__header"><?php the_title(); ?></h1>
            <p class="hero__lead"><?php echo types_render_field( "event-start-date", array("format" => "D, M j, Y g:i a" ) ); ?> - <?php echo types_render_field( "event-end-date", array( "format" => "D, M j, Y g:i a" ) ); ?></p>
        </div>
    </div>
    <!-- End page header -->
    <section class="page-section mb-0 pb-0">
        <div class="page-section__content-wrapper">
            <div class="row">
                <?php while ( have_posts() ) : the_post(); ?>   
                <div class="page-section__one-half text-left">

                    <p class="event-summary__location"><?php echo types_render_field( "event-location", array( ) ); ?></p>
                    <p class="event-sumary__description"><?php the_content(); ?></p>
                    <p>Contact: <?php echo types_render_field( "event-contact-name", array( ) ); ?></p>
                    <p>Phone: <?php echo types_render_field( "event-contact-phone", array( ) ); ?></p>
                    <p>Email: <?php echo types_render_field( "event-contact-email", array( ) ); ?></p>
                    <p>Website: <?php echo types_render_field( "event-website", array( ) ); ?></p>
                    <p>Purchase: <?php echo types_render_field( "event-ticket-purchase-link", array( ) ); ?></p>
                    <p>Address: <?php echo types_render_field( "event-address", array( ) ); ?></p>
   
                </div>
                <?php endwhile; // end of the loop. ?>
            </div>
        </div>
    </section>





</div><!-- Wrapper end -->

<?php get_footer(); ?>