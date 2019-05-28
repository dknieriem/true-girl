<?php
$options = thrive_get_options_for_post( get_the_ID() );

$comment_nb_class     = ( $options['sidebar_alignement'] == "right" ) ? "comment_nb" : "right_comment_nb";
$featured_image_data  = thrive_get_post_featured_image( get_the_ID(), $options['featured_image_style'] );
$featured_image       = $featured_image_data['image_src'];
$featured_image_alt   = $featured_image_data['image_alt'];
$featured_image_title = $featured_image_data['image_title'];

$post_options = get_post_custom( get_the_ID() );
$post_format  = get_post_format();

if ( isset( $options['meta_post_date'] ) && $options['meta_post_date'] == 1 && isset( $options['meta_post_date_type'] ) ) {
	$post_date = thrive_get_post_date( $options['meta_post_date_type'], $options['relative_time'] );
}

$fname = get_the_author_meta( 'first_name' );
$lname = get_the_author_meta( 'last_name' );

$author_name  = get_the_author_meta( 'display_name' );
$display_name = empty( $author_name ) ? $fname . " " . $lname : $author_name;
?>
<?php tha_entry_before(); ?>
<article>
	<?php tha_entry_top(); ?>
	<div class="awr">
		<a class="cmt acm" href="<?php the_permalink(); ?>#comments"
		   <?php if ( $options['meta_comment_count'] != 1 || get_comments_number() == 0 ): ?>style='display:none;'<?php endif; ?>>
			<?php echo get_comments_number(); ?> <span class="trg"></span>
		</a>
		<?php if ( $options['featured_image_style'] == "wide" && $featured_image && $post_format != 'gallery' && $post_format != 'video' && $post_format != 'quote' ): ?>
			<div class="fwit"><a class="psb"> <img src="<?php echo $featured_image; ?>"
			                                       alt="<?php echo $featured_image_alt; ?>"
			                                       title="<?php echo $featured_image_title; ?>"> </a></div>
		<?php endif; ?>

		<?php
		if ( $post_format == 'gallery' ) {
			require_once 'partials/gallery-content.php';
		} else if ( $post_format == 'video' ) {
			require_once 'partials/video-content.php';
		} else if ( $post_format == 'quote' ) {
			require_once 'partials/quote-content.php';
		}
		?>

		<?php if ( $options['show_post_title'] != 0 ): ?>
			<h1 class="entry-title"><?php the_title(); ?></h1>
		<?php endif; ?>

		<?php if ( $post_format == 'audio' ): ?>
			<?php require_once 'partials/audio-content.php'; ?>
		<?php endif; ?>

		<?php if ( $options['featured_image_style'] == "thumbnail" && $featured_image && isset( $featured_image ) ): ?>
			<!--<a class="aIm pst right"> <img src="<?php echo $featured_image; ?>" alt="" title=""></a>-->
			<img class="afim pst right" src="<?php echo $featured_image; ?>" alt="<?php echo $featured_image_alt; ?>"
			     title="<?php echo $featured_image_title; ?>">
		<?php endif; ?>
		<?php the_content(); ?>
		<?php if ( $options['enable_social_buttons'] == 1 ): ?>
			<?php get_template_part( 'share-buttons' ); ?>
		<?php endif; ?>
		<div class="clear"></div>
		<?php
		wp_link_pages( array(
			'before'         => '<br><p class="ctr pgn">',
			'after'          => '</p>',
			'next_or_number' => 'next_and_number',
			'echo'           => 1
		) );
		?>
	</div>
	<?php if ( isset( $options['display_meta'] ) && $options['display_meta'] == 1 && is_single() && get_post_type() == "post" ): ?>
		<footer>
			<ul>
				<?php if ( isset( $options['meta_author_name'] ) && $options['meta_author_name'] == 1 ): ?>
					<li><a
							href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php echo get_the_author(); ?></a>
					</li>
				<?php endif; ?>
				<?php if ( isset( $options['meta_post_date'] ) && $options['meta_post_date'] == 1 ): ?>
					<li>
						<?php echo $post_date; ?>
					</li>
				<?php endif; ?>
				<?php if ( isset( $options['meta_post_category'] ) && $options['meta_post_category'] == 1 ): ?>
					<?php
					$categories = get_the_category();
					if ( $categories ):
						?>
						<?php if ( count( $categories ) > 1 ): ?>
						<li>
							<a href="#"><?php _e( "Categories", 'thrive' ) ?> ↓</a>
							<ul class="clear">
								<?php foreach ( $categories as $category ): ?>
									<li>
										<a href="<?php echo get_category_link( $category->term_id ); ?>"><?php echo $category->cat_name; ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</li>
					<?php elseif ( isset( $categories[0] ) ): ?>
						<li><a
								href="<?php echo get_category_link( $categories[0]->term_id ); ?>"><?php echo $categories[0]->cat_name; ?></a>
						</li>
					<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ( isset( $options['meta_post_tags'] ) && $options['meta_post_tags'] == 1 ): ?>
					<?php
					$posttags = get_the_tags();
					if ( $posttags ):
						?>
						<li>
							<a href="#"><?php _e( "Tags", 'thrive' ) ?> ↓</a>
							<ul class="clear">
								<?php foreach ( $posttags as $tag ): ?>
									<li>
										<a href="<?php echo get_tag_link( $tag->term_id ); ?>"><?php echo $tag->name; ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</li>
					<?php endif; ?>
				<?php endif; ?>
			</ul>
			<div class="clear"></div>
		</footer>
	<?php endif; ?>
	<?php tha_entry_bottom(); ?>
</article>

<?php _thrive_render_bottom_related_posts( get_the_ID(), $options ); ?>

<?php tha_entry_after(); ?>
<div class="spr"></div>