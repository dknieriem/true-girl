<?php
global $post;
$lazy_load_comments = thrive_get_theme_options( "comments_lazy" );
$enable_fb_comments = thrive_get_theme_options( "enable_fb_comments" );
$fb_app_id          = thrive_get_theme_options( "fb_app_id" );
?>

<?php if ( $lazy_load_comments == 1 ): ?>
	<script type="text/javascript">
		_thriveCurrentPost = <?php echo json_encode( get_the_ID() ); ?>;
	</script>


<?php endif; ?>
<?php tha_comments_before(); ?>
<?php if ( $enable_fb_comments != "only_fb" ): ?>
	<article id="comments">
		<?php if ( comments_open() && ! post_password_required() ) : ?>
			<div class="ctb">
				<span
						class="ftx txt_thrive_link_to_comments"><?php _e( "Click Here to Leave a Comment Below", 'thrive' ); ?></span>
				<span class="cmt lcm" href=""> <span
							class="str"></span> <?php echo get_comments_number(); ?> <?php _e( "comments", 'thrive' ); ?> </span>
				<div class="clear"></div>
			</div>
		<?php endif; ?>
		<div class="awr">
			<?php if ( $lazy_load_comments != 1 ):
				thrive_theme_comment_nav();
			endif; ?>

			<div class="cmb" style="margin-left: 0px;" id="thrive_container_list_comments">
				<?php if ( $lazy_load_comments != 1 ): ?>
					<?php wp_list_comments( array( 'callback' => 'thrive_comments' ) ); ?>
				<?php endif; ?>
			</div><!-- /comment_list -->

			<?php if ( $lazy_load_comments != 1 ):
				thrive_theme_comment_nav();
			endif; ?>

			<?php if ( comments_open() && ! post_password_required() ) : ?>
				<?php if ( $lazy_load_comments == 1 ): ?>
					<div class="ctb ctr" style="display: none;" id="thrive_container_preload_comments">
						<img class="preloader" src="<?php echo get_template_directory_uri() ?>/images/loading.gif"
							 alt=""/>
					</div>
				<?php endif; ?>
				<div class="lrp" id="thrive_container_form_add_comment"
				     <?php if ( $lazy_load_comments == 1 ): ?>style="display:none;"<?php endif; ?>>
					<h4><?php _e( "Leave a Reply:", 'thrive' ); ?></h4>
					<form action="<?php echo site_url( '/wp-comments-post.php' ) ?>" method="post"
						  id="commentform">
						<?php if ( ! is_user_logged_in() ): ?>
							<input type="text"
								   placeholder="<?php _e( "Name", "thrive" ) ?><?php if ( get_option( "require_name_email" ) == 1 ) { ?>*<?php } ?>"
								   id="author" author="author" class="text_field author" name="author"/>
							<input type="text"
								   placeholder="<?php _e( "Email", "thrive" ) ?><?php if ( get_option( "require_name_email" ) == 1 ) { ?>*<?php } ?>"
								   id="email" author="email" class="text_field email" name="email"/>
							<input type="text" placeholder="<?php _e( "Website", "thrive" ) ?>" id="website"
								   author="website" class="text_field website lst" name="url"/>
						<?php endif; ?>
						<textarea id="comment" name="comment" class="textarea"></textarea>
						<?php
						//						WP ReCaptcha Intergration filter - displays a recaptcha integration if the WP ReCaptcha plugin is active
						echo apply_filters( 'comments_recaptcha_html', '' );
						?>
						<?php
						/**
						 * For Subscribe to Comments Reloaded
						 *
						 * The 'comment_form_submit_field' filter needs to be applied because the Theme Form is not constructed via comment_form function
						 */
						apply_filters( 'comment_form_submit_field', '' );
						?>
						<input type="submit" value="<?php _e( "SUBMIT", 'thrive' ); ?>"/>
						<?php comment_id_fields(); ?>
						<?php do_action( 'comment_form', $post->ID ); ?>
					</form>
				</div>
			<?php elseif ( ( ! comments_open() || post_password_required() ) && get_comments_number() > 0 ): ?>
				<div class="no_comm">
					<h4 class="ctr">
						<?php _e( "Comments are closed", 'thrive' ); ?>
					</h4>
				</div>
			<?php endif; ?>
		</div>
	</article>
	<div id="comment-bottom"></div>
<?php endif; ?>

<?php if ( ( $enable_fb_comments == "only_fb" || $enable_fb_comments == "both_fb_regular" || ( ! comments_open() && $enable_fb_comments == "fb_when_disabled" ) ) && ! empty( $fb_app_id ) ) : ?>
	<article id="comments_fb" style="min-height: 100px; border: 1px solid #ccc;">
		<div class="fb-comments" data-href="<?php echo get_permalink( get_the_ID() ); ?>"
			 data-numposts="<?php echo thrive_get_theme_options( "fb_no_comments" ) ?>" data-width="100%"
			 data-colorscheme="<?php echo thrive_get_theme_options( "fb_color_scheme" ) ?>"></div>
	</article>
<?php endif; ?>
<?php tha_comments_after(); ?>

