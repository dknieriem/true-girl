<?php
$current_auth_id = ( isset( $_GET['author'] ) && $_GET['author'] ) ? ( intval( $_GET['author'] ) ) : 0;

$options           = thrive_get_theme_options();
$sidebar_is_active = _thrive_is_active_sidebar( $options );

$fname = get_the_author_meta( 'first_name', $current_auth_id );
$lname = get_the_author_meta( 'last_name', $current_auth_id );
$desc  = get_the_author_meta( 'description', $current_auth_id );

$user_gplus           = get_the_author_meta( 'gplus', $current_auth_id );
$user_twitter         = get_the_author_meta( 'twitter', $current_auth_id );
$user_facebook        = get_the_author_meta( 'facebook', $current_auth_id );
$user_linkedin        = get_the_author_meta( 'linkedin', $current_auth_id );
$user_xing            = get_the_author_meta( 'xing', $current_auth_id );
$show_social_profiles = explode( ',', get_the_author_meta( 'show_social_profiles', $current_auth_id ) );
$show_social_profiles = array_filter( $show_social_profiles );
if ( empty( $show_social_profiles ) ) { // back-compatibility
	$show_social_profiles = array( 'e', 'fbk', 'twt', 'ggl' );
}

$author_name  = get_the_author_meta( 'display_name', $current_auth_id );
$display_name = empty( $author_name ) ? $fname . " " . $lname : $author_name;

if ( $display_name == "" ) {
	$display_name = get_the_author_meta( 'user_login', $current_auth_id );
}

$next_page_link = get_next_posts_link();
$prev_page_link = get_previous_posts_link();
?>
<?php get_header(); ?>
<div class="<?php echo _thrive_get_main_wrapper_class( $options ); ?>">
	<?php if ( $options['sidebar_alignement'] == "left" && $sidebar_is_active ): ?>
		<?php get_sidebar(); ?>
	<?php endif; ?>
	<?php if ( $sidebar_is_active ): ?>
	<div class="bSeCont">
		<?php endif; ?>
		<?php
		/* Queue the first post, that way we know
		 * what author we're dealing with (if that is the case).
		 *
		 * We reset this later so we can run the loop
		 * properly with a call to rewind_posts().
		 */
		the_post();
		?>
		<?php if ( $options['blog_layout'] == "grid_full_width" ): ?>
			<section class="bSe">
				<article>
					<div class="awr aut">
						<div class="left">
							<?php echo get_avatar( get_the_author_meta( 'user_email' ), 98 ); ?>
							<?php if ( ! empty( $user_facebook ) || ! empty( $user_gplus ) || ! empty( $user_twitter ) || ! empty( $user_linkedin ) || ! empty( $user_xing ) ): ?>
								<ul class="left">
									<?php if ( ( ! empty( $user_facebook ) && in_array( 'fbk', $show_social_profiles ) ) || empty( $show_social_profiles[0] ) ): ?>
										<li>
											<a href="<?php echo _thrive_get_social_link( $user_facebook, 'fbk' ); ?>"
											   target="_blank" class="fbk"></a>
										</li>
									<?php endif; ?>
									<?php if ( ( ! empty( $user_linkedin ) && in_array( 'lnk', $show_social_profiles ) ) || empty( $show_social_profiles[0] ) ): ?>
										<li>
											<a href="<?php echo _thrive_get_social_link( $user_linkedin, 'lnk' ); ?>"
											   target="_blank" class="lnk"></a>
										</li>
									<?php endif; ?>
									<?php if ( ( ! empty( $user_xing ) && in_array( 'xing', $show_social_profiles ) ) || empty( $show_social_profiles[0] ) ): ?>
										<li>
											<a href="<?php echo _thrive_get_social_link( $user_xing, 'xing' ); ?>"
											   target="_blank" class="xing"></a>
										</li>
									<?php endif; ?>
									<?php if ( ( ! empty( $user_twitter ) && in_array( 'twt', $show_social_profiles ) ) || empty( $show_social_profiles[0] ) ): ?>
										<li>
											<a href="<?php echo _thrive_get_social_link( $user_twitter, 'twt' ); ?>"
											   target="_blank" class="twt"></a>
										</li>
									<?php endif; ?>
									<?php if ( ( ! empty( $user_gplus ) && in_array( 'ggl', $show_social_profiles ) ) || empty( $show_social_profiles[0] ) ): ?>
										<li>
											<a href="<?php echo _thrive_get_social_link( $user_gplus, 'ggl' ); ?>"
											   target="_blank" class="ggl"></a>
										</li>
									<?php endif; ?>
								</ul>
							<?php endif; ?>
							<div class="clear"></div>
							<span><?php echo $display_name; ?></span>
						</div>
						<div class="right">
							<p>
								<?php printf( __( 'Author Archives: %s', 'thrive' ), $display_name ); ?>
							</p>
						</div>
						<div class="clear"></div>
					</div>
				</article>
			</section>
		<?php endif; ?>
		<section class="<?php echo _thrive_get_main_section_class( $options ); ?>">
			<?php if ( $options['blog_layout'] != "grid_full_width" ): ?>
				<?php if ( strpos( $options['blog_layout'], 'masonry' ) !== false ): ?>
					<div class="mry-g"></div>
					<div class="mry-i">
				<?php endif; ?>

				<article>
					<div class="awr aut">
						<div class="left">
							<?php echo get_avatar( get_the_author_meta( 'user_email' ), 98 ); ?>
							<?php if ( ! empty( $user_facebook ) || ! empty( $user_gplus ) || ! empty( $user_twitter ) || ! empty( $user_linkedin ) || ! empty( $user_xing ) ): ?>
								<ul class="left">
									<?php if ( ( ! empty( $user_facebook ) && in_array( 'fbk', $show_social_profiles ) ) || empty( $show_social_profiles[0] ) ): ?>
										<li>
											<a href="<?php echo _thrive_get_social_link( $user_facebook, 'fbk' ); ?>"
											   target="_blank" class="fbk"></a>
										</li>
									<?php endif; ?>
									<?php if ( ( ! empty( $user_linkedin ) && in_array( 'lnk', $show_social_profiles ) ) || empty( $show_social_profiles[0] ) ): ?>
										<li>
											<a href="<?php echo _thrive_get_social_link( $user_linkedin, 'lnk' ); ?>"
											   target="_blank" class="lnk"></a>
										</li>
									<?php endif; ?>
									<?php if ( ( ! empty( $user_xing ) && in_array( 'xing', $show_social_profiles ) ) || empty( $show_social_profiles[0] ) ): ?>
										<li>
											<a href="<?php echo _thrive_get_social_link( $user_xing, 'xing' ); ?>"
											   target="_blank" class="xing"></a>
										</li>
									<?php endif; ?>
									<?php if ( ( ! empty( $user_twitter ) && in_array( 'twt', $show_social_profiles ) ) || empty( $show_social_profiles[0] ) ): ?>
										<li>
											<a href="<?php echo _thrive_get_social_link( $user_twitter, 'twt' ); ?>"
											   target="_blank" class="twt"></a>
										</li>
									<?php endif; ?>
									<?php if ( ( ! empty( $user_gplus ) && in_array( 'ggl', $show_social_profiles ) ) || empty( $show_social_profiles[0] ) ): ?>
										<li>
											<a href="<?php echo _thrive_get_social_link( $user_gplus, 'ggl' ); ?>"
											   target="_blank" class="ggl"></a>
										</li>
									<?php endif; ?>
								</ul>
							<?php endif; ?>
							<div class="clear"></div>
							<span><?php echo $display_name; ?></span>
						</div>
						<div class="right">
							<p>
								<?php printf( __( 'Author Archives: %s', 'thrive' ), $display_name ); ?>
							</p>
						</div>
						<div class="clear"></div>
					</div>
				</article>
				<div class="spr"></div>
				<?php if ( strpos( $options['blog_layout'], 'masonry' ) !== false ): ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php
			/* Since we called the_post() above, we need to
			 * rewind the loop back to the beginning that way
			 * we can run the loop properly, in full.
			 */
			rewind_posts();
			?>
			<?php if ( $options['blog_layout'] == "masonry_full_width" || $options['blog_layout'] == "masonry_sidebar" ): ?>
				<div class="mry-g"></div>
			<?php endif; ?>
			<?php if ( have_posts() ): ?>
				<?php while ( have_posts() ): ?>
					<?php the_post(); ?>
					<?php get_template_part( 'content', _thrive_get_post_content_template( $options ) ); ?>
				<?php endwhile; ?>
				<div class="clear"></div>
				<?php if ( $next_page_link || $prev_page_link && ( $next_page_link != "" || $prev_page_link != "" ) ): ?>
					<?php if ( strpos( $options['blog_layout'], 'masonry' ) === false ): ?>
						<div class="clear"></div>
						<div class="awr ctr pgn">
							<?php thrive_pagination(); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			<?php else: ?>
				<div class="art">
					<article>
						<div class="awr">
							<div class="no_content_msg">
								<h2 class="upp"><?php _e( "Sorry, but no results were found.", 'thrive' ); ?></h2>

								<p class="ncm_comment">
									<b>
										<?php _e( "YOU CAN RETURN", 'thrive' ); ?> <a
											href="<?php echo home_url( '/' ); ?>"><?php _e( "HOME", 'thrive' ) ?></a> <?php _e( "OR SEARCH FOR THE PAGE YOU WERE LOOKING FOR.", 'thrive' ) ?>
									</b>
								</p>

								<form action="<?php echo home_url( '/' ); ?>" method="get">
									<input type="text" placeholder="<?php _e( " Search Here", 'thrive' ); ?>"
									       class="search_field"
									       name="s"/>
									<input type="submit" value="<?php _e( "SEARCH", 'thrive' ); ?>" class="submit_btn"/>
								</form>
							</div>
						</div>
					</article>
				</div>
			<?php endif ?>
			<?php if ( _thrive_check_focus_area_for_pages( "archive", "bottom" ) ): ?>
				<?php if ( strpos( $options['blog_layout'], 'masonry' ) === false && strpos( $options['blog_layout'], 'grid' ) === false ): ?>
					<?php thrive_render_top_focus_area( "bottom", "archive" ); ?>
					<div class="spr"></div>
				<?php endif; ?>
			<?php endif; ?>
		</section>
		<?php if ( $sidebar_is_active ): ?>
	</div>
<?php endif; ?>

	<?php if ( $options['sidebar_alignement'] == "right" && $sidebar_is_active ): ?>
		<?php get_sidebar(); ?>
	<?php endif; ?>
</div>
<div class="clear"></div>
<?php if ( strpos( $options['blog_layout'], 'masonry' ) !== false ): ?>
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
