<?php
$options           = thrive_get_theme_options();
$sidebar_is_active = _thrive_is_active_sidebar( $options );
$next_page_link    = get_next_posts_link();
$prev_page_link    = get_previous_posts_link();
?>
<?php get_header(); ?>
<div class="<?php echo _thrive_get_main_wrapper_class( $options ); ?>">
	<?php if ( $options['sidebar_alignement'] == "left" && $sidebar_is_active ): ?>
		<?php get_sidebar(); ?>
	<?php endif; ?>

	<?php if ( $sidebar_is_active ): ?>
	<div class="bSeCont"><?php endif; ?>

		<section class="<?php echo _thrive_get_main_section_class( $options ); ?>">

			<?php if ( $options['blog_layout'] == "masonry_full_width" || $options['blog_layout'] == "masonry_sidebar" ): ?>
				<div class="mry-g"></div>
			<?php endif; ?>

			<?php if ( have_posts() ): ?>

				<?php
				//loop through posts
				$position = 1;
				while ( have_posts() ):
					?>
					<?php the_post(); ?>

					<?php get_template_part( 'content', _thrive_get_post_content_template( $options ) ); ?>

					<?php if ( thrive_check_blog_focus_area( $position ) ): ?>
					<?php if ( strpos( $options['blog_layout'], 'masonry' ) === false && strpos( $options['blog_layout'], 'grid' ) === false ): ?>
						<?php thrive_render_top_focus_area( "between_posts", $position ); ?>
						<div class="spr"></div>
					<?php endif; ?>
				<?php endif; ?>

					<?php
					$position ++;
				endwhile;
				?>

				<?php if ( _thrive_check_focus_area_for_pages( "blog", "bottom" ) ): ?>
					<?php if ( strpos( $options['blog_layout'], 'masonry' ) === false && strpos( $options['blog_layout'], 'grid' ) === false ): ?>
						<?php thrive_render_top_focus_area( "bottom", "blog" ); ?>
						<div class="spr"></div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( $next_page_link || $prev_page_link && ( $next_page_link != "" || $prev_page_link != "" ) ): ?>
					<div class="clear"></div>
					<?php if ( $options['blog_layout'] != "masonry_full_width" && $options['blog_layout'] != "masonry_sidebar" ): ?>
						<div class="clear"></div>
						<div class="awr ctr pgn">
							<?php thrive_pagination(); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

			<?php else: ?>
				<!--No contents-->
			<?php endif ?>
		</section>

		<?php if ( $sidebar_is_active ): ?></div><?php endif; ?>

	<?php if ( $options['sidebar_alignement'] == "right" && $sidebar_is_active ): ?>
		<?php get_sidebar(); ?>
	<?php endif; ?>

</div>
<div class="clear"></div>
<?php if ( $options['blog_layout'] == "masonry_full_width" || $options['blog_layout'] == "masonry_sidebar" ): ?>
	<div class="wrp cnt">
		<?php if ( $next_page_link || $prev_page_link && ( $next_page_link != "" || $prev_page_link != "" ) ): ?>
			<div class="clear"></div>
			<div class="awr ctr pgn">
				<?php thrive_pagination(); ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
<?php get_footer(); ?>
