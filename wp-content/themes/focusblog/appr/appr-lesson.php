<?php
$options = thrive_get_options_for_post( get_the_ID(), array( 'apprentice' => 1 ) );

$sidebar_is_active  = is_active_sidebar( 'sidebar-appr' );
$main_content_class = $options['sidebar_alignement'] == "left" ? "right" : ( $options['sidebar_alignement'] == "right" ? "left" : "fullWidth" );
if ( ! $sidebar_is_active ) {
	$main_content_class = "fullWidth";
}
$post_format     = get_post_format();
$lesson_comments = thrive_get_theme_options( 'appr_page_comments' );
?>


<?php get_template_part( "appr/header" ); ?>

<?php if ( $options['sidebar_alignement'] == "left" && $sidebar_is_active ): ?>
	<?php get_template_part( "appr/sidebar" ); ?>
<?php endif; ?>
<?php if ( $sidebar_is_active ): ?>
	<div class="bSeCont">
<?php endif; ?>
	<section class="bSe <?php echo $main_content_class; ?>">

		<?php if ( have_posts() ): ?>

			<?php while ( have_posts() ): ?>

				<?php the_post(); ?>

				<?php get_template_part( "appr/content-single" ); ?>

				<?php if ( thrive_check_bottom_focus_area() ):
					thrive_render_top_focus_area( "bottom" );
				endif; ?>
				<?php if ( isset( $options['appr_bottom_about_author'] ) && $options['appr_bottom_about_author'] == 1 ): ?>
					<?php get_template_part( 'authorbox' ); ?>
				<?php endif; ?>
				<?php if ( ! empty( $lesson_comments ) ) : ?>
					<?php if ( ! post_password_required() && $options['appr_page_comments'] ) : ?>
						<?php comments_template( '', true ); ?>
					<?php elseif ( ( ! comments_open() ) && get_comments_number() > 0 ): ?>
						<?php comments_template( '/comments-disabled.php' ); ?>
					<?php endif; ?>
				<?php endif ?>
				<div class="spr"></div>
				<?php
				$next_lesson_link = _thrive_get_next_prev_lesson_link( get_the_ID(), true );
				$prev_lesson_link = _thrive_get_next_prev_lesson_link( get_the_ID(), false );
				if ( isset( $options['bottom_previous_next'] ) && $options['bottom_previous_next'] == 1 && ( $next_lesson_link != false || $prev_lesson_link != false ) ): ?>
					<div class="awr ctr pgn">
						<?php if ( $prev_lesson_link ): ?>
							<a class="page-numbers nxt"
							   href='<?php echo $prev_lesson_link; ?>'><?php _e( "Previous lesson", 'thrive' ); ?> </a>
						<?php endif; ?>
						<?php if ( $next_lesson_link ): ?>
							<a class="page-numbers prv"
							   href='<?php echo $next_lesson_link; ?>'><?php _e( "Next lesson", 'thrive' ) ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

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

	</section>
<?php if ( $sidebar_is_active ): ?>
	</div>
<?php endif; ?>

<?php if ( $options['sidebar_alignement'] == "right" && $sidebar_is_active ): ?>
	<?php get_template_part( "appr/sidebar" ); ?>
<?php endif; ?>
	<div class="clear"></div>


<?php get_template_part( "appr/footer" ); ?>