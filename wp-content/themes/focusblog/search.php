<?php
$options           = thrive_get_theme_options();
$sidebar_is_active = _thrive_is_active_sidebar( $options );

$exclude_woo_pages = array(
	intval( get_option( 'woocommerce_cart_page_id' ) ),
	intval( get_option( 'woocommerce_checkout_page_id' ) ),
	intval( get_option( 'woocommerce_pay_page_id' ) ),
	intval( get_option( 'woocommerce_thanks_page_id' ) ),
	intval( get_option( 'woocommerce_myaccount_page_id' ) ),
	intval( get_option( 'woocommerce_edit_address_page_id' ) ),
	intval( get_option( 'woocommerce_view_order_page_id' ) ),
	intval( get_option( 'woocommerce_terms_page_id' ) )
);

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
		<?php if ( $options['blog_layout'] == "grid_full_width" ): ?>
			<section class="bSe">
				<article>
					<div class="awr arh">
						<h4><?php _e( "Results", 'thrive' ); ?></h4>
                        <span>
                            <i>
	                            <?php printf( __( 'Search Results for: %s', 'thrive' ), '' . get_search_query() . '' ); ?>
                            </i>
                        </span>
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
					<div class="awr arh">
						<h4><?php _e( "Results", 'thrive' ); ?></h4>
                            <span>
                                <i>
	                                <?php printf( __( 'Search Results for: %s', 'thrive' ), '' . get_search_query() . '' ); ?>
                                </i>
                            </span>
					</div>
				</article>
				<div class="spr"></div>
				<?php if ( strpos( $options['blog_layout'], 'masonry' ) !== false ): ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( have_posts() ): ?>
				<?php while ( have_posts() ): ?>
					<?php the_post(); ?>
					<?php if ( in_array( get_the_ID(), $exclude_woo_pages ) ): continue; endif; ?>
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
