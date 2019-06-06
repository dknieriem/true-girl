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

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="post-intro">
			<div class="post-intro__post-image-wrapper">
				<img class="post-intro__post-image" src="<?php echo $featuredImage;?>" alt="Featured image" />
			</div>
			<div class="post-intro__post-meta">
				<p class="post-intro__publish-date"><?php echo get_the_date('F j, Y'); ?></p>
				<h1 class="post-intro__title"><?php the_title(); ?></h1>
				<div class="post-intro__author-wrapper">
					<?php 
						$authorId = get_post_field( 'post_author', $post_id ); 
						$authorPhotoUrl = do_shortcode('[types usermeta="author-photo" size="thumbnail" output="raw"][/types]');
					?>
					<div style="background-image:url('<?php echo $authorPhotoUrl; ?>');" class="post-intro__author-photo"></div>
					<p class="post-intro__byline">Written by <strong class="post-intro__author-name"><?php the_author_meta('nickname', $post->post_author); ?><strong></p>
				</div>
				
			</div>
		</div>
		<div class="row">
			<!-- Do the left sidebar check -->
			<div class="col-md-8 offset-md-2">
				<main class="site-main" id="main">

					<?php while ( have_posts() ) : the_post(); ?>

						<?php get_template_part( 'loop-templates/content', 'single-post' ); ?>

							<?php understrap_post_nav(); ?>

					<?php endwhile; // end of the loop. ?>

				</main><!-- #main -->
			</div>

		<!-- Do the right sidebar check -->
	</div><!-- .row -->

</div><!-- Container end -->

</div><!-- Wrapper end -->

<?php get_footer(); ?>
