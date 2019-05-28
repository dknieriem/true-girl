<?php
$options = thrive_get_theme_options();

$featured_image      = null;
$featured_image_data = thrive_get_post_featured_image( get_the_ID(), "tt_featured_wide" );
$featured_image      = $featured_image_data['image_src'];

$thrive_meta_postformat_quote_text   = get_post_meta( get_the_ID(), '_thrive_meta_postformat_quote_text', true );
$thrive_meta_postformat_quote_author = get_post_meta( get_the_ID(), '_thrive_meta_postformat_quote_author', true );

$_content = str_replace( '&nbsp;', '', get_the_content() );
$_content = trim( $_content );
?>
<?php tha_entry_before(); ?>
	<div class="art">
		<article <?php if ( is_sticky() ): ?>class="sticky"<?php endif; ?>>
			<?php tha_entry_top(); ?>

			<div
				class="awr lnd <?php if ( $options['featured_image_style'] == "wide" && $featured_image ): ?>hasf<?php endif; ?>">

				<a class="cmt acm" href="<?php the_permalink(); ?>#comments"
				   <?php if ( $options['meta_comment_count'] != 1 || get_comments_number() == 0 ): ?>style='display:none;'<?php endif; ?>>
					<?php echo get_comments_number(); ?><span class="trg"></span>
				</a>

				<?php if ( $featured_image && isset( $featured_image ) ): ?>

					<div class="ind-q ind-qi" style="background-image: url('<?php echo $featured_image; ?>')">
						<div class="quo">
							<h5><?php echo $thrive_meta_postformat_quote_text; ?></h5>
							<p><?php echo $thrive_meta_postformat_quote_author; ?></p>
						</div>
						<?php if ( ! is_singular() && $_content != '' ): ?>
							<a href="<?php the_permalink(); ?>" class="crd">Continue reading</a>
						<?php endif; ?>
					</div>

				<?php else: ?>

					<div class="ind-q ind-di">
						<div class="quo">
							<h5><?php echo $thrive_meta_postformat_quote_text; ?></h5>
							<p><?php echo $thrive_meta_postformat_quote_author; ?></p>
						</div>
						<?php if ( ! is_singular() && $_content != '' ): ?>
							<a href="<?php the_permalink(); ?>" class="crd">Continue reading</a>
						<?php endif; ?>
					</div>

				<?php endif; ?>
			</div>

			<?php tha_entry_bottom(); ?>
		</article>
	</div>
<?php tha_entry_after(); ?>