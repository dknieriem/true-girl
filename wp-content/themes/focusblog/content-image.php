<?php
$options              = thrive_get_theme_options();
$featured_image_data  = thrive_get_post_featured_image( get_the_ID(), "wide" );
$featured_image       = $featured_image_data['image_src'];
$featured_image_alt   = $featured_image_data['image_alt'];
$featured_image_title = $featured_image_data['image_title'];
?>
<?php tha_entry_before(); ?>
	<article <?php if ( is_sticky() ): ?>class="sticky"<?php endif; ?>>
		<?php tha_entry_top(); ?>
		<div
			class="awr imp <?php if ( $options['featured_image_style'] == "wide" && $featured_image ): ?>hasf<?php endif; ?>">
			<a class="cmt acm" href="<?php the_permalink(); ?>#comments"
			   <?php if ( $options['meta_comment_count'] != 1 || get_comments_number() == 0 ): ?>style='display:none;'<?php endif; ?>>
				<?php echo get_comments_number(); ?><span class="trg"></span>
			</a>
			<?php if ( $featured_image ): ?>

				<div class="fwit">
					<a class="psb" href="<?php the_permalink(); ?>">
						<img src="<?php echo $featured_image; ?>" alt="<?php echo $featured_image_alt; ?>"
						     title="<?php echo $featured_image_title; ?>">
					</a>
				</div>
				<h1 class="entry-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h1>
				<div class="clear"></div>
			<?php endif; ?>
		</div>
		<?php tha_entry_bottom(); ?>
	</article>
<?php tha_entry_after(); ?>