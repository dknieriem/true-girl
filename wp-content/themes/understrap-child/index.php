<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package understrap
 */

get_header();

$container   = get_theme_mod( 'understrap_container_type' );
?>

<?php if ( is_front_page() && is_home() ) : ?>
	<?php get_template_part( 'global-templates/hero' ); ?>
<?php endif; ?>

<div class="wrapper" id="index-wrapper">

	<div class="container" id="content" tabindex="-1">

		<!-- Page header -->
		<div class="row">
			<div class="col-md-12 text-center">
				<h1 class="heading heading--white-on-pink d-inline-block">True Girl Blog | Online Resources For Moms</h1>
			</div>
		</div>
		<!-- End page header -->

		<!-- Featured post section -->
		<?php 
			$query = new WP_Query( array( 'category_name' => 'featured', 'posts_per_page' => 1 ) );
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) { ?>
					<?php $query->the_post(); ?>
					<a class="row" href="<?php echo esc_url(get_permalink());?>">
						<div class="featured-post" style="background-image:url('<?php echo wp_get_attachment_url(get_post_thumbnail_id($post->ID, 'large'));?>');">
							
							<p class="featured-post__subtitle">Featured post</p>
							<h1 class="featured-post__title"><?php the_title(); ?></h1>
							<p class="featured-post__meta">written by <?php the_author_meta('nickname', $post->post_author); ?> /  <?php echo get_the_date('F j, Y'); ?></p>
							<div class="featured-post__overlay"></div>
						</div>
					</a>
				<?php }
				/* Restore original Post Data */
				wp_reset_postdata();
			} 
		?>


		
		<!-- End featured post section -->
		
			<?php 
				$query = new WP_Query( array( 'category_name' => 'must-read', 'posts_per_page' => 4 ) );
				if ( $query->have_posts() ) {
			?> 
			<!-- Recent post section -->
			<div class="row mt-5">
				<div class="col-md-12">
					<div class="dotted-hr">
						<h2 class="dotted-hr__text">Dannah's Must-Reads</h2>
					</div>
				</div>
			</div>
			<div class="row">
				<?php while ( $query->have_posts() ) { ?>
						<?php $query->the_post(); ?>
						<?php $featuredImage = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), "medium");?>
						<a class="post-thumb" href="<?php echo esc_url(get_permalink());?>">
							<div style="background-image:url('<?php echo $featuredImage[0];?>');'" class="post-thumb__img"></div>
							<h2 class="post-thumb__title"><?php the_title(); ?></h2>
						</a>
					<?php } ?>
					</div>
					<?php
					/* Restore original Post Data */
					wp_reset_postdata();
				} 
			?>
			
			<?php if ( have_posts() ) : ?>

				<?php /* Start the Loop */ ?>
				<div class="post-card-row">
				<?php while ( have_posts() ) : the_post(); ?>

					<?php

					/*
						* Include the Post-Format-specific template for the content.
						* If you want to override this in a child theme, then include a file
						* called content-___.php (where ___ is the Post Format name) and that will be used instead.
						*/
					get_template_part( 'loop-templates/post-card', get_post_format() );
					?>

				<?php endwhile; ?>
				<div class="blog__sidebar">
					<?php get_template_part( 'sidebar-templates/sidebar', 'blog-sidebar' ); ?>
<?php 	
	/*NOT YET (post launch)
	No lazy loading (use pagination just like dannahgresh.com)
	No search
	No filtering at the top
	No “Much More Help to Explore” section
	No Sidebar on main blog page.
	Keep
	Sidebar on single blog article
	Social Icons
	Newsletter Signup
	Bio Feature (on the single post page)*/
?>
				</div><!-- ./blog__sidebar -->
				</div><!-- ./post-card-row -->
			<?php else : ?>

				<?php get_template_part( 'loop-templates/content', 'none' ); ?>

			<?php endif; ?>


		<!-- The pagination component -->
		<?php understrap_pagination(); ?>

	<!-- Do the right sidebar check -->
		

	</div><!-- .row -->

</div><!-- Container end -->

</div><!-- Wrapper end -->

<?php get_footer(); ?>
