<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-focusblog
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // No direct access
}
$options = thrive_get_theme_options();
?>
<?php get_header(); ?>

<section class="bSe fullWidth">
	<div class="awr">
		<article>
			<div class="err">
				<span class="left">404</span>
				<p class="left lostp">
					<?php _e( "Ooops!", 'thrive' ); ?><br/>
					<b><?php _e( "The page you are looking for seems to be missing. Perhaps searching can help.", 'thrive' ); ?></b>
				</p>
				<div class="clear"></div>
				<div class="spr"></div>
				<form action="<?php echo home_url( '/' ); ?>" method="get">
					<input id="search-field" type="text" placeholder="<?php _e( "Search", 'thrive' ); ?>" name="s">
					<button id="search-big-button" class="sBn" type="submit"><b><?php _e( "SEARCH", 'thrive' ); ?></b>
					</button>
					<div class="clear"></div>
				</form>
				<?php if ( ! empty( $options['404_custom_text'] ) ): ?>
					<p><?php echo do_shortcode( $options['404_custom_text'] ); ?></p>
				<?php endif; ?>

				<?php
				if ( isset( $options['404_display_sitemap'] ) && $options['404_display_sitemap'] == "on" ):
					$categories = get_categories( array( 'parent' => 0 ) );
					$pages = get_pages();
					?>
					<div class="csc">
						<div class="colm thc">
							<h3><?php _e( "Categories", 'thrive' ); ?></h3>
							<ul class="tt_sitemap_list">
								<?php foreach ( $categories as $cat ): ?>
									<li>
										<a href='<?php echo get_category_link( $cat->term_id ); ?>'><?php echo $cat->name; ?></a>
										<?php
										$subcats = get_categories( array( 'child_of' => $cat->term_id ) );
										if ( count( $subcats ) > 0 ):
											?>
											<ul>
												<?php foreach ( $subcats as $subcat ): ?>
												<li>
													<a href='<?php echo get_category_link( $subcat->term_id ); ?>'><?php echo $subcat->name; ?></a>
													<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div class="colm thc">
							<h3><?php _e( "Archives", 'thrive' ); ?></h3>
							<ul>
								<?php wp_get_archives(); ?>
							</ul>
						</div>
						<div class="colm thc lst">
							<h3><?php _e( "Pages", 'thrive' ); ?></h3>
							<ul class="tt_sitemap_list">
								<?php foreach ( $pages as $page ): ?>
									<li>
										<a href='<?php echo get_page_link( $page->ID ); ?>'><?php echo get_the_title( $page->ID ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div class="clear"></div>
					</div>
				<?php endif; ?>

			</div>
		</article>
	</div>
</section>
<?php get_footer(); ?>
