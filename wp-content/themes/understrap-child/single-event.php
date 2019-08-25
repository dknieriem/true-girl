<?php 

get_header();

$container   = get_theme_mod( 'understrap_container_type' );
$featuredImage = types_render_field( "event-image", array( "raw" => "true"));
?>

<div class="wrapper" id="page-wrapper">

	<div id="content" tabindex="-1"><!--  class="<?php echo esc_attr( $container ); ?>" -->

		<div class="row content__row">

			<div class="col-12 content__col-12">

                <main class="site-main" id="main">

                    <?php while ( have_posts() ) : the_post(); ?>

                        <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

                        <div class="entry-content">
                            <!-- Hero Section -->
                            <section class="page-section page-section--full-width-none text-center background- video-mobile-bg page-section__full-video events-hero pt-0 pb-0" style="background-image: url(''); background-repeat: no-repeat; background-size:cover; background-position: center center;">
                                <div class="page-section__background-video">
                                    <div style="padding:56.25% 0 0 0;">
                                        <iframe src="https://player.vimeo.com/video/347813476?autoplay=1&loop=1&title=0&byline=0&portrait=0&muted=1&background=1" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="autoplay"></iframe>
                                    </div>
                                </div>
                                <script src="https://player.vimeo.com/api/player.js"></script>
                                <div class="page-section__overlay-image">
                                    <img src="https://mytruegirl.com/wp-content/uploads/2019/07/EventPageVideoOverlay-2.png" />
                                </div>
                                
                                <div class="page-section__content-wrapper">
                                    <div class="row full-width--no ">
                                        <div class="page-section__single-column full-width--no text-center events-hero-banner">
                                            <img class="post-image post-image--full-width aligncenter" src="https://mytruegirl.com/wp-content/uploads/2019/07/01_TicketIcon.png" alt="" />
                                            <h2 class="heading  heading--sm heading--white ">true girl tour in <?php echo types_render_field( "display-city-state", array( ) ); ?></h2> 
                                            <div class="hr">
                                                <div class="hr__inner hr__inner--solid hr__inner--solid--white hr__inner--white " style="width:12.5%;"></div>
                                            </div> 
                                        </div> 
                                    </div> 
                                    <div class="row full-width--no ">
                                        <div class="page-section__single-column full-width--no text-center events-hero-header">
                                            <span class="heading heading__on-pink">The most fun you&#8217;ll ever have</span><br />
                                            <span class="heading heading__on-pink">digging into god&#8217;s word together</span><br />
                                            <a href="https://purefreedom.brushfire.com/true-girl-pajama-party-tour/454375?_ga=2.256938443.1171851674.1565645108-597427503.1564516123" class="button button--yellow button--large mb-lg mt-md" >get tickets!</a> 
                                        </div> 
                                    </div> 
                                </div>
                            </section>

                            <!-- Touring Now: -->
                            <section class="page-section page-section--full-width-none text-center background- pt-md pb-md events-header" style="background-image: url('https://mytruegirl.com/wp-content/uploads/2019/07/EventPageTouringNowBkgd.png'); background-repeat: no-repeat; background-size:cover; background-position: center center;">
                                <div class="page-section__content-wrapper">
                                    <div class="row full-width--no ">
                                        <div class="section-header section-header__10">
                                            <h1 class="section-header__text section-header__text--large">Touring Now:</h1>
                                        </div> 
                                        <div class="page-section__single-column full-width--no text-center">
                                            <h3 class="text-white text-center">The All-New True Girl Pajama Party Tour In <?php echo types_render_field( "display-city-state", array( ) ); ?></h3>
                                        </div> 
                                    </div> 
                                </div>
                            </section>

                            <!-- Tour Video -->
                            <section class="page-section page-section--full-width-none text-center events-video-feature background- " style="background-image: url(''); background-repeat: no-repeat; background-size:; background-position: center center;">
                                <div class="page-section__content-wrapper">
                                    <div class="row full-width--no ">
                                        <div class="page-section__two-third-column ">
                                            <div class="video_feature mt-n6 ">
                                                <div class="video_feature__video" style="background-image: url();">
                                                    <div style="width: 640px;" class="wp-video"><!--[if lt IE 9]><script>document.createElement('video');</script><![endif]-->
                                                        <video class="wp-video-shortcode" id="video-14087-1" width="640" height="360" preload="metadata" controls="controls"><source type="video/youtube" src="https://www.youtube.com/watch?v=tfDk1pXX2M0&#038;controls=0&#038;_=1" />
                                                            <a href="https://www.youtube.com/watch?v=tfDk1pXX2M0&#038;controls=0">https://www.youtube.com/watch?v=tfDk1pXX2M0&#038;controls=0</a>
                                                        </video>
                                                    </div>
                                                </div>
                                            </div> 
                                        </div> 
                                        
                                        <div class="page-section__one-third-column ">
                                            <p class="text-teal mt-md"><strong>Grab your popcorn and a cozy onesie—</strong> we&#8217;re throwing some HUGE pajama parties across the nation <strong>this fall! And we are coming to <?php echo types_render_field( "display-city-state", array( ) ); ?> soon!!!</strong></p>
                                            <a href="https://purefreedom.brushfire.com/true-girl-pajama-party-tour/454375?_ga=2.256938443.1171851674.1565645108-597427503.1564516123" class="button button--yellow " >get your tickets!</a> 
                                        </div> 
                                    </div> 
                                </div>
                            </section>

                            <!-- Testimonial -->
                            <section class="page-section page-section--full-width-none text-center background-teal page-section__testimonial" style="background-image: url(''); background-repeat: no-repeat; background-size:cover; background-position: center center;">
                                <div class="page-section__content-wrapper">
                                    <div class="row full-width--no ">   
                                        <div class="col-md-8 offset-md-2">
                                            <?php echo do_shortcode( '[wpv-view name="slider-view"]' ); ?>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Event Description -->
                            <section class="page-section page-section--full-width-none text-center background-white " style="background-image: url('https://mytruegirl.com/wp-content/uploads/2019/07/EventPage_JoinMomsBkgd.png'); background-repeat: no-repeat; background-size:cover; background-position: center bottom;">
                                <div class="page-section__content-wrapper">
                                    <div class="row full-width--no ">
                                        <div class="text-feature col col-md-8 offset-md-4 text-center text-teal" >
                                            <div class="hr">
                                                <div class="hr__inner hr__inner--solid hr__inner--solid--teal hr__inner--teal divider-top" style="width:50%;"></div>
                                            </div>
                                            <strong>Join over 400,000 moms in experiencing America’s largest mother-daughter connecting event.</strong><br />For 2 ½ hours, you and your 7-12-year-old daughter will have uninterrupted bonding time growing closer to each other and closer to Jesus. You&#8217;ll experience deep connection with your daughter through practical bible teaching, live worship, hilarious games, fashion shows, and more. The event is built with both you and your girl in mind, so we promise you’ll both leave feeling like it was just for you!
                                            <div class="hr">
                                                <div class="hr__inner hr__inner--solid hr__inner--solid--teal hr__inner--teal divider-bottom" style="width:50%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Event 1, 2, 3 -->
                            <section class="page-section page-section--full-width-none text-center background-white events-3steps" style="background-image: url('https://mytruegirl.com/wp-content/uploads/2019/07/EventPage_3StepsBkgd.png'); background-repeat: no-repeat; background-size:cover; background-position: center center;">
                                <div class="page-section__content-wrapper">
                                    <div class="row full-width--no ">
                                        <div class="page-section__single-column full-width--no mt-sm events-header-sm">
                                            <span class="heading  heading--sm heading--white-on-pink d-inline-block">Are you ready for a total heart</span><br />
                                            <span class="heading  heading--sm heading--white-on-pink d-inline-block">encounter with your daughter?</span><br />
                                            <span class="heading  heading--sm heading--white-on-pink d-inline-block">Then you&#8217;re ready to experience the True Girl tour.</span> 
                                        </div> 
                                    </div> 
                                    <img class="post-image post-image--full-width aligncenter" src="https://mytruegirl.com/wp-content/uploads/2019/07/arrow-down-right.png" alt="" />
                                    <h2 class="heading  heading--sm heading--white events-3steps__title">3 Steps to deep connection with your daughter</h2> 
                                    <div class="hr">
                                        <div class="hr__inner hr__inner--solid hr__inner--solid--white hr__inner--white " style="width:70%;"></div>
                                    </div> 
                                    <div class="row full-width--no col-md-10 offset-md-1 events-3steps__steps">
                                        <div class="page-section__one-third-column ">
                                            <h2 class="heading h1 heading--normal heading--teal heading__on-white background-circle">1</h2>
                                            <h4 class="text-shark text-white text-uppercase">COME TO <br />THE EVENT IN <?php echo types_render_field( "display-city-state", array( ) ); ?></h4>
                                        </div> 
                                        <div class="page-section__one-third-column ">
                                            <h2 class="heading h1 heading--normal heading--teal heading__on-white background-circle">2</h2>
                                            <h4 class="text-shark text-white text-uppercase">LAUGH, DANCE, SING, AND DIG INTO DEEP BIBLICAL TRUTH WITH YOUR GIRL.</h4>
                                        </div> 
                                        <div class="page-section__one-third-column ">
                                            <h2 class="heading h1 heading--normal heading--teal heading__on-white background-circle">3</h2>
                                            <h4 class="text-shark text-white text-uppercase">FEEL YOUR FACE HURT FROM SMILING SO MUCH.</h4>
                                        </div> 
                                        <div class="page-section__single-column full-width--no text-center">
                                            <a href="https://purefreedom.brushfire.com/true-girl-pajama-party-tour/454375?_ga=2.256938443.1171851674.1565645108-597427503.1564516123" class="button button--yellow " >Get your tickets now!</a>
                                        </div> 
                                    </div>
                                </div>
                            </section>

                            <!-- Questions / FAQ -->
                            <section class="page-section page-section--full-width-none text-center background-pink events-faq" style="background-image: url('https://mytruegirl.com/wp-content/uploads/2019/07/EventPageTouringNowBkgd.png'); background-repeat: no-repeat; background-size:cover; background-position: center center;">
                                <div class="page-section__content-wrapper">
                                    <div class="row full-width--no p-0">
                                        <div class="page-section__single-column full-width--no text-center">
                                            <h2 class="heading  heading--lg heading--white ">Questions?</h2> 
                                        </div> 
                                    </div> 
                                    <div class="row full-width--no no-inner-margin">
                                        <div class="page-section__single-column full-width--no text-center events-faq__button">
                                            <img class="events-faq__arrow-left" src="https://mytruegirl.com/wp-content/uploads/2019/07/07_ArrowLeft.png">
                                            <a href="/faq/" class="button button--yellow " >Check out our FAQ!</a>
                                            <img class="events-faq__arrow-right" src="https://mytruegirl.com/wp-content/uploads/2019/07/07_ArrowRight.png"> 
                                        </div> 
                                    </div> 
                                </div>
                            </section>
                        </div><!-- .entry-content -->

                    </article><!-- #post-## -->

                    <?php endwhile; // end of the loop. ?>

                </main><!-- #main -->

		    </div><!-- .col-12 -->

	    </div><!-- .row -->

    </div><!-- Container end -->

</div><!-- Wrapper end -->

<?php get_footer(); ?>