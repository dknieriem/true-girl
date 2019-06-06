<?php 

get_header();

$container   = get_theme_mod( 'understrap_container_type' );
?>

<div class="wrapper" id="page-wrapper">	
	
	<!-- Page header -->
    <div class="hero hero--centered" style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/img/sunset.jpg'); background-repeat: no-repeat; background-size: cover; background-position: center bottom;">
        <div class="hero__overlay"></div>
        <div class="container">
            <h1 class="hero__header hero__header">Uh oh!</h1>
            <p class="hero__lead">Looks like that's a bad link or the page has been removed.</p>
        </div>
    </div>
    <!-- End page header -->
    <section class="page-section">
        <div class="page-section__content-wrapper">
            <div class="row mt-3 mb-5">
                <div class="page-section__single-column--narrow-center text-center">
                    <h4>While you're here, why don't you learn a bit more about Dannah and Pure Freedom?</h4>
                </div>  
            </div>
            <div class="row">
                <a href="<?php echo get_site_url();?>/calendar/" class="icon-item">
                    <img src="<?php echo get_site_url();?>/wp-content/uploads/2018/08/icon-speaking.png" class="icon-item__icon" />
                    <h2 class="icon-item__title">Speaking</h2>
                    <p class="icon-item__content">Be inspired to change and grow.</p>
                </a>
                <a href="<?php echo get_site_url();?>/store/" class="icon-item">
                    <img src="<?php echo get_site_url();?>/wp-content/uploads/2018/08/icon-resources.png" class="icon-item__icon" />
                    <h2 class="icon-item__title">Resources</h2>
                    <p class="icon-item__content">Read your way to freedom and purpose.</p>
                </a>
                <a href="<?php echo get_site_url();?>/masterclass/" class="icon-item">
                    <img src="<?php echo get_site_url();?>/wp-content/uploads/2018/08/icon-workshop.png" class="icon-item__icon" />
                    <h2 class="icon-item__title">Workshops</h2>
                    <p class="icon-item__content">Gain confidence to talk about sex and gender.</p>
                </a>
                <a href="<?php echo get_site_url();?>/podcasts/" class="icon-item">
                    <img src="<?php echo get_site_url();?>/wp-content/uploads/2018/08/icon-podcasts.png" class="icon-item__icon" />
                    <h2 class="icon-item__title">Podcasts</h2>
                    <p class="icon-item__content">Download game-changing parenting advice</p>
                </a>
            </div>
        </div>
    </section>
    <section class="page-section page-section--teal" style="background-image: url('<?php echo get_site_url();?>/wp-content/uploads/2018/08/watercolor-teal-flourish.png'); background-repeat: no-repeat; background-size:70%; background-position: top center;">
        <div class="page-section__content-wrapper">
            <div class="row">
                <div class="page-section__single-column text-center">
                    <h1 class="heading heading--white">Trying to find something in particular?</h1>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12"><?php get_search_form();?></div>
            </div>
        </div>
    </section>





</div><!-- Wrapper end -->

<?php get_footer(); ?>