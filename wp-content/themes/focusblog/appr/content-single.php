<?php
$options = thrive_get_options_for_post( get_the_ID(), array( 'apprentice' => 1 ) );

$featured_image       = null;
$featured_image_alt   = '';
$featured_image_title = '';
$thrive_lesson_type   = get_post_meta( get_the_ID(), '_thrive_meta_appr_lesson_type', true );
if ( has_post_thumbnail( get_the_ID() ) && $thrive_lesson_type != "audio" && $thrive_lesson_type != "video" ) {
	$featured_image_data  = thrive_get_post_featured_image( get_the_ID(), $options['featured_image_style'] );
	$featured_image       = $featured_image_data['image_src'];
	$featured_image_alt   = $featured_image_data['image_alt'];
	$featured_image_title = $featured_image_data['image_title'];
}
$post_date     = thrive_get_post_date( $options['meta_post_date_type'], $options['relative_time'] );
$template_name = _thrive_get_item_template( get_the_ID() );
if ( $template_name == "Landing Page" ) {
	$options['display_meta'] = 0;
}
$current_post_type = get_post_type( get_the_ID() );
?>
<?php tha_entry_before(); ?>

	<article>
		<div class="awr <?php if ( $template_name == "Narrow" || $template_name == "Landing Page" || $template_name == "Full Width" ): ?>lnd<?php endif; ?>">

			<?php if ( $options['show_post_title'] != 0 ): ?>
				<h1 class="entry-title <?php if ( $options['appr_favorites'] == 1 && is_user_logged_in() && $current_post_type == TT_APPR_POST_TYPE_LESSON ): ?>apt left<?php endif; ?>"><?php the_title(); ?></h1>
			<?php endif; ?>

			<?php if ( is_user_logged_in() && $current_post_type == TT_APPR_POST_TYPE_LESSON ): ?>
				<?php if ( $options['appr_favorites'] == 1 ): ?>
					<div class="fav right" id="tt-favorite-lesson">
						<a class="heart left<?php if ( _thrive_appr_check_favorite( get_the_ID() ) ): ?> fill<?php endif; ?>"></a>
						<!--  <span class="left">
                    <?php if ( _thrive_appr_check_favorite( get_the_ID() ) ): ?>
                        <?php _e( "Remove from Favorites", 'thrive' ); ?>
                    <?php else: ?>
                        <?php _e( "Mark as Favorite", 'thrive' ); ?>
                    <?php endif; ?>
                </span>-->
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				<?php endif; ?>
			<?php endif; ?>


			<?php if ( ( $options['featured_image_style'] == "wide" || $options['featured_image_style'] == "thumbnail" ) && $featured_image ): ?>
				<img src="<?php echo $featured_image; ?>" alt="<?php echo $featured_image_alt; ?>"
				     title="<?php echo $featured_image_title; ?>"
				     class="<?php if ( $options['featured_image_style'] == "wide" ): ?>fwI<?php else: ?>alignleft afim<?php endif; ?>"/>
			<?php endif; ?>

			<?php the_content(); ?>

			<div class="clear"></div>

			<?php get_template_part( 'appr/download-box' ); ?>

			<?php if ( $options['enable_social_buttons'] == 1 ): ?>
				<?php get_template_part( 'share-buttons' ); ?>
			<?php endif; ?>

			<?php if ( is_user_logged_in() && $current_post_type == TT_APPR_POST_TYPE_LESSON ): ?>
				<?php if ( $options['appr_progress_track'] == 1 ): ?>
					<?php
					$current_lesson_progress = _thrive_appr_get_progress( get_the_ID() );
					if ( $current_lesson_progress == THRIVE_APPR_PROGRESS_NEW ) {
						_thrive_appr_set_progress( get_the_ID(), 0, THRIVE_APPR_PROGRESS_STARTED );
					}
					?>
					<div class="acl clearfix">
						<input id="completed-lesson" type="checkbox" value="completedLesson" name="completedLesson"
						       <?php if ( $current_lesson_progress == THRIVE_APPR_PROGRESS_COMPLETED ): ?>checked<?php endif; ?> />
						<label for="completed-lesson">
							<div><a></a></div>
							<span><?php echo $options['appr_completed_text']; ?></span>
						</label>
					</div>

				<?php endif; ?>

			<?php endif; ?>
		</div>
		<?php if ( isset( $options['display_meta'] ) && $options['display_meta'] == 1 ): ?>
			<footer>
				<ul>
					<?php if ( isset( $options['meta_author_name'] ) && $options['meta_author_name'] == 1 ): ?>
						<li>
							<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php echo get_the_author(); ?></a>
						</li>
					<?php endif; ?>
					<?php if ( isset( $options['meta_post_date'] ) && $options['meta_post_date'] == 1 ): ?>
						<li>
							<?php echo $post_date; ?>
						</li>
					<?php endif; ?>
					<?php if ( isset( $options['appr_meta_post_category'] ) && $options['appr_meta_post_category'] == 1 ): ?>
						<?php
						$categories = wp_get_post_terms( get_the_ID(), "apprentice" );
						if ( $categories && count( $categories ) > 0 ): ?>
							<li>

								<a href="#"><?php _e( "Categories", 'thrive' ) ?> ↓</a>
								<ul class="clear">
									<?php foreach ( $categories as $key => $cat ): ?>
										<li>
											<a href="<?php echo get_term_link( $cat ); ?>"><?php echo $cat->name; ?></a>
										</li>
									<?php endforeach; ?>

								</ul>
							</li>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( isset( $options['appr_meta_post_tags'] ) && $options['appr_meta_post_tags'] == 1 ): ?>
						<?php
						$posttags = wp_get_post_terms( get_the_ID(), "apprentice-tag" );
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
			</footer>
		<?php endif; ?>
		<div class="clear"></div>
		<?php tha_entry_bottom(); ?>

	</article>
<?php tha_entry_after(); ?>