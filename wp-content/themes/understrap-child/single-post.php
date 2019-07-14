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
	<div class="row post-footer">
		<div class="col-md-9 author-info">
				<?php 
						$authorId = get_post_field( 'post_author', $post_id ); 
						$authorPhotoUrl = do_shortcode('[types usermeta="author-photo" size="thumbnail" output="raw"][/types]');
					?>
					<div class="author-info__photo" style="background-image:url('<?php echo $authorPhotoUrl; ?>');" class="post-intro__author-photo"></div>
	<?php if (get_the_author_meta('user_description') ): ?>
			<div class="author-info__bio">
			<h4>Author Bio</h4>
			<p class="author-info__description"><?php esc_textarea(the_author_meta('user_description')); ?></p>
			</div>
    <?php endif; ?>
        
    </div><!-- col-md-9 author-info -->
	</div><!-- row post-footer -->

</div><!-- Container end -->

</div><!-- Wrapper end -->

<?php get_footer(); ?>
