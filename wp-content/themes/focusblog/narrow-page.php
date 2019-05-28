<?php
/*
  Template Name: Narrow Page
 */
?>
<?php
$options = thrive_get_theme_options();

$main_content_class = ( $options['sidebar_alignement'] == "right" ) ? "main_content" : "right_main_content";

$next_page_link = get_next_posts_link();
$prev_page_link = get_previous_posts_link();
?>
<?php get_header(); ?>

	<section class="bSe">

		<?php if ( have_posts() ): ?>

			<?php while ( have_posts() ): ?>

				<?php the_post(); ?>

				<?php get_template_part( 'content', 'narrow' ); ?>

			<?php endwhile; ?>

		<?php else: ?>
			<div class="no_content_msg">
				<p><?php _e( "Sorry, but no results were found.", 'thrive' ); ?></p>
				<p class="ncm_comment"><?php _e( "YOU CAN RETURN", 'thrive' ); ?> <a
						href="<?php echo home_url( '/' ); ?>"><?php _e( "HOME", 'thrive' ) ?></a> <?php _e( "OR SEARCH FOR THE PAGE YOU WERE LOOKING FOR.", 'thrive' ) ?>
				</p>
				<form action="<?php echo home_url( '/' ); ?>" method="get">
					<input type="text" placeholder="<?php _e( "Search Here", 'thrive' ); ?>" class="search_field"
					       name="s"/>
					<input type="submit" value="<?php _e( "Search", 'thrive' ); ?>" class="submit_btn"/>
				</form>
			</div>

		<?php endif ?>
		<?php
		if ( thrive_check_bottom_focus_area() ):
			thrive_render_top_focus_area( "bottom" );
		endif;
		?>

		<?php if ( isset( $options['bottom_about_author'] ) && $options['bottom_about_author'] == 1 && ! is_page() ): ?>
			<?php get_template_part( 'authorbox' ); ?>
		<?php endif; ?>

		<?php if ( ! post_password_required() && ( ! is_page() || ( is_page() && $options['comments_on_pages'] != 0 ) ) ) : ?>
			<?php comments_template( '', true ); ?>
		<?php elseif ( ( ! comments_open() || post_password_required() ) && get_comments_number() > 0 ): ?>
			<?php comments_template( '/comments-disabled.php' ); ?>
		<?php endif; ?>
	</section>

<?php get_footer(); ?>