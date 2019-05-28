<?php
$thrive_meta_postformat_video_type        = get_post_meta( get_the_ID(), '_thrive_meta_postformat_video_type', true );
$thrive_meta_postformat_video_youtube_url = get_post_meta( get_the_ID(), '_thrive_meta_postformat_video_youtube_url', true );
$thrive_meta_postformat_video_vimeo_url   = get_post_meta( get_the_ID(), '_thrive_meta_postformat_video_vimeo_url', true );
$thrive_meta_postformat_video_custom_url  = get_post_meta( get_the_ID(), '_thrive_meta_postformat_video_custom_url', true );
$vimeo_embed_class                        = '';
$youtube_attrs                            = array(
	'hide_logo'       => get_post_meta( get_the_ID(), '_thrive_meta_postformat_video_youtube_hide_logo', true ),
	'hide_controls'   => get_post_meta( get_the_ID(), '_thrive_meta_postformat_video_youtube_hide_controls', true ),
	'hide_related'    => get_post_meta( get_the_ID(), '_thrive_meta_postformat_video_youtube_hide_related', true ),
	'hide_title'      => get_post_meta( get_the_ID(), '_thrive_meta_postformat_video_youtube_hide_title', true ),
	'autoplay'        => get_post_meta( get_the_ID(), '_thrive_meta_postformat_video_youtube_autoplay', true ),
	'hide_fullscreen' => get_post_meta( get_the_ID(), '_thrive_meta_postformat_video_youtube_hide_fullscreen', true ),
	'video_width'     => 1080
);

if ( $thrive_meta_postformat_video_type == "youtube" ) {
	$video_code = _thrive_get_youtube_embed_code( $thrive_meta_postformat_video_youtube_url, $youtube_attrs );
} elseif ( $thrive_meta_postformat_video_type == "vimeo" ) {
	$video_code        = _thrive_get_vimeo_embed_code( $thrive_meta_postformat_video_vimeo_url );
	$vimeo_embed_class = "v-cep";
} else {
	if ( strpos( $thrive_meta_postformat_video_custom_url, "<" ) !== false ) { //if embeded code or url
		$video_code = $thrive_meta_postformat_video_custom_url;
	} else {
		$video_code = do_shortcode( "[video src='" . $thrive_meta_postformat_video_custom_url . "']" );
	}
}
$wistiaVideoCode = ( strpos( $video_code, "wistia" ) !== false ) ? ' wistia-video-container' : '';
if ( $featured_image ):
	?>
	<div class="pvf <?php echo $vimeo_embed_class; ?>" style="background-image: url('<?php echo $featured_image; ?>')">
		<img class="tt-dmy" src="" alt="">

		<div class="scvps<?php echo $wistiaVideoCode; ?>">
			<div class="vdc lv">
				<div class="ltx">
					<div class="pvb">
						<a></a>
					</div>
				</div>
			</div>
			<div class="vdc lv video-container" style="display:none;">
				<div class="vwr">
					<?php echo $video_code; ?>
				</div>
			</div>
		</div>
	</div>
<?php else: ?>
	<?php if ( ! empty( $video_code ) ): ?>
		<div class="bt fw vp">
			<div
				class="<?php if ( ! ( $thrive_meta_postformat_video_type == "custom" || $thrive_meta_postformat_video_type == "custom_embed" ) ): ?>rve<?php endif; ?> pv">
				<div class="ovr"></div>
				<?php echo $video_code; ?>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>