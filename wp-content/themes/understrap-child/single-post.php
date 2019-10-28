<?php
/**
 * The template for displaying all single posts.
 *
 * @package understrap
 */

get_header();
$container   = get_theme_mod( 'understrap_container_type' );
$featuredImage = wp_get_attachment_url(get_post_thumbnail_id($post->ID, 'large'));
?>

<div class="wrapper" id="single-wrapper">

	<div  id="content" tabindex="-1"><!-- class="<?php echo esc_attr( $container ); ?>" -->

		<div class="row post-intro">
			<div class="post-intro__breadcrumb">
				You are here: <a class="breadcrumb-link" href="/">True Girl</a> / <a class="breadcrumb-link" href="/blog/">Blog</a> / <?php the_title(); ?>
			</div>
			<div class="post-intro__post-meta">
				<h1 class="post-intro__title"><?php the_title(); ?></h1>
			</div>
		</div>
		<div class="row post-content">
			<!-- Do the left sidebar check -->
			<div class="col-md-8 post-content__body">
				<div class="post-content__featured-image">
					<img class="post-intro__post-image" src="<?php echo $featuredImage;?>" alt="Featured image" />
				</div>

				<?php while ( have_posts() ) : 
					the_post();

					get_template_part( 'loop-templates/content', 'single-post' );

					endwhile; // end of the loop. 
				?>
			</div>
			<div class="col-md-4 post-sidebar">
				<div class="blog__sidebar">
				<?php get_template_part( 'sidebar-templates/sidebar', 'blog-sidebar' ); ?>
				</div>
			</div>
		<!-- Do the right sidebar check -->
	</div><!-- .row -->
</div><!-- Container end -->

	<section class="page-section text-left background-pink responsive-background- " style="background-image: url( '/wp-content/uploads/2019/07/EmailSignupPinkBkgd.png'); background-repeat: no-repeat; background-size:cover; background-position: center center;">
		<div class="page-section__content-wrapper">
			<div class="col-sm-10 col-xl-8 full-width--no justify-content-center mc-page-form offset-sm-1 offset-xl-2 pb-md pt-md text-center">
				<h2 class="text-white mb-0">Start connecting now.</h2>
				<h3 class="text-white text-bold mb-4">Get a free mother-daughter devo in your inbox every week.</h3>
				<?php echo do_shortcode('[mc4wp_form id="7805"]'); ?>
			</div>
		</div>
	</section>

</div><!-- Wrapper end -->

<?php get_footer(); ?>
