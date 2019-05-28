<?php

function thrive_meta_post_format_options( $post ) {

	wp_nonce_field( plugin_basename( __FILE__ ), 'thrive_noncename_post_format' );

	$thrive_meta_postformat_video_type                    = get_post_meta( $post->ID, '_thrive_meta_postformat_video_type', true );
	$thrive_meta_postformat_video_youtube_url             = get_post_meta( $post->ID, '_thrive_meta_postformat_video_youtube_url', true );
	$thrive_meta_postformat_video_youtube_hide_related    = get_post_meta( $post->ID, '_thrive_meta_postformat_video_youtube_hide_related', true );
	$thrive_meta_postformat_video_youtube_hide_logo       = get_post_meta( $post->ID, '_thrive_meta_postformat_video_youtube_hide_logo', true );
	$thrive_meta_postformat_video_youtube_hide_controls   = get_post_meta( $post->ID, '_thrive_meta_postformat_video_youtube_hide_controls', true );
	$thrive_meta_postformat_video_youtube_hide_title      = get_post_meta( $post->ID, '_thrive_meta_postformat_video_youtube_hide_title', true );
	$thrive_meta_postformat_video_youtube_autoplay        = get_post_meta( $post->ID, '_thrive_meta_postformat_video_youtube_autoplay', true );
	$thrive_meta_postformat_video_youtube_hide_fullscreen = get_post_meta( $post->ID, '_thrive_meta_postformat_video_youtube_hide_fullscreen', true );
	$thrive_meta_postformat_video_vimeo_url               = get_post_meta( $post->ID, '_thrive_meta_postformat_video_vimeo_url', true );
	$thrive_meta_postformat_video_custom_url              = get_post_meta( $post->ID, '_thrive_meta_postformat_video_custom_url', true );
	$thrive_meta_postformat_quote_text                    = get_post_meta( $post->ID, '_thrive_meta_postformat_quote_text', true );
	$thrive_meta_postformat_quote_author                  = get_post_meta( $post->ID, '_thrive_meta_postformat_quote_author', true );
	$thrive_meta_postformat_audio_type                    = get_post_meta( $post->ID, '_thrive_meta_postformat_audio_type', true );
	$thrive_meta_postformat_audio_file                    = get_post_meta( $post->ID, '_thrive_meta_postformat_audio_file', true );
	$thrive_meta_postformat_audio_soundcloud_url          = get_post_meta( $post->ID, '_thrive_meta_postformat_audio_soundcloud_url', true );
	$thrive_meta_postformat_audio_soundcloud_autoplay     = get_post_meta( $post->ID, '_thrive_meta_postformat_audio_soundcloud_autoplay', true );
	$thrive_meta_postformat_gallery_images                = get_post_meta( $post->ID, '_thrive_meta_postformat_gallery_images', true );

	require( get_template_directory() . "/inc/templates/admin-post-format-options.php" );
}

add_action( 'save_post', 'thrive_save_post_formats_data' );

function thrive_save_post_formats_data( $post_id ) {

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['post_type'] ) && 'post' != $_POST['post_type'] ) {
		return;
	}
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
		return;
	}

	// Secondly we need to check if the user intended to change this value.
	if ( ! isset( $_POST['thrive_noncename_post_format'] ) || ! wp_verify_nonce( $_POST['thrive_noncename_post_format'], plugin_basename( __FILE__ ) ) ) {
		return;
	}
	$post_data   = $_POST;
	$check_ifset = array(
		'thrive_meta_postformat_video_youtube_hide_related',
		'thrive_meta_postformat_video_youtube_hide_logo',
		'thrive_meta_postformat_video_youtube_hide_controls',
		'thrive_meta_postformat_video_youtube_hide_title',
		'thrive_meta_postformat_video_youtube_autoplay',
		'thrive_meta_postformat_video_youtube_hide_fullscreen',
		'thrive_meta_postformat_audio_soundcloud_autoplay',
		'thrive_meta_postformat_gallery_images',
		'thrive_meta_postformat_video_type',
		'thrive_meta_postformat_video_youtube_url',
		'thrive_meta_postformat_video_vimeo_url',
		'thrive_meta_postformat_video_custom_url',
		'thrive_meta_postformat_quote_text',
		'thrive_meta_postformat_quote_author',
		'thrive_meta_postformat_audio_type',
		'thrive_meta_postformat_audio_file',
		'thrive_meta_postformat_audio_soundcloud_url'
	);

	foreach ( $check_ifset as $key ) {
		if ( ! isset( $post_data[ $key ] ) ) {
			$post_data[ $key ] = "";
		}
	}

	$thrive_meta_postformat_video_type                    = ( $post_data['thrive_meta_postformat_video_type'] );
	$thrive_meta_postformat_video_youtube_url             = ( $post_data['thrive_meta_postformat_video_youtube_url'] );
	$thrive_meta_postformat_video_youtube_hide_related    = ( $post_data['thrive_meta_postformat_video_youtube_hide_related'] );
	$thrive_meta_postformat_video_youtube_hide_logo       = ( $post_data['thrive_meta_postformat_video_youtube_hide_logo'] );
	$thrive_meta_postformat_video_youtube_hide_controls   = ( $post_data['thrive_meta_postformat_video_youtube_hide_controls'] );
	$thrive_meta_postformat_video_youtube_hide_title      = ( $post_data['thrive_meta_postformat_video_youtube_hide_title'] );
	$thrive_meta_postformat_video_youtube_autoplay        = ( $post_data['thrive_meta_postformat_video_youtube_autoplay'] );
	$thrive_meta_postformat_video_youtube_hide_fullscreen = ( $post_data['thrive_meta_postformat_video_youtube_hide_fullscreen'] );
	$thrive_meta_postformat_video_vimeo_url               = ( $post_data['thrive_meta_postformat_video_vimeo_url'] );
	$thrive_meta_postformat_video_custom_url              = ( $post_data['thrive_meta_postformat_video_custom_url'] );
	$thrive_meta_postformat_quote_text                    = ( $post_data['thrive_meta_postformat_quote_text'] );
	$thrive_meta_postformat_quote_author                  = ( $post_data['thrive_meta_postformat_quote_author'] );
	$thrive_meta_postformat_audio_type                    = ( $post_data['thrive_meta_postformat_audio_type'] );
	$thrive_meta_postformat_audio_file                    = ( $post_data['thrive_meta_postformat_audio_file'] );
	$thrive_meta_postformat_audio_soundcloud_url          = ( $post_data['thrive_meta_postformat_audio_soundcloud_url'] );
	$thrive_meta_postformat_audio_soundcloud_autoplay     = ( $post_data['thrive_meta_postformat_audio_soundcloud_autoplay'] );
	$thrive_meta_postformat_gallery_images                = ( $post_data['thrive_meta_postformat_gallery_images'] );

	add_post_meta( $post_id, '_thrive_meta_postformat_video_type', $thrive_meta_postformat_video_type, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_video_type', $thrive_meta_postformat_video_type );
	add_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_url', $thrive_meta_postformat_video_youtube_url, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_url', $thrive_meta_postformat_video_youtube_url );
	add_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_related', $thrive_meta_postformat_video_youtube_hide_related, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_related', $thrive_meta_postformat_video_youtube_hide_related );
	add_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_logo', $thrive_meta_postformat_video_youtube_hide_logo, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_logo', $thrive_meta_postformat_video_youtube_hide_logo );
	add_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_controls', $thrive_meta_postformat_video_youtube_hide_controls, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_controls', $thrive_meta_postformat_video_youtube_hide_controls );
	add_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_title', $thrive_meta_postformat_video_youtube_hide_title, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_title', $thrive_meta_postformat_video_youtube_hide_title );
	add_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_autoplay', $thrive_meta_postformat_video_youtube_autoplay, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_autoplay', $thrive_meta_postformat_video_youtube_autoplay );
	add_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_fullscreen', $thrive_meta_postformat_video_youtube_hide_fullscreen, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_video_youtube_hide_fullscreen', $thrive_meta_postformat_video_youtube_hide_fullscreen );
	add_post_meta( $post_id, '_thrive_meta_postformat_video_vimeo_url', $thrive_meta_postformat_video_vimeo_url, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_video_vimeo_url', $thrive_meta_postformat_video_vimeo_url );
	add_post_meta( $post_id, '_thrive_meta_postformat_video_custom_url', $thrive_meta_postformat_video_custom_url, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_video_custom_url', $thrive_meta_postformat_video_custom_url );
	add_post_meta( $post_id, '_thrive_meta_postformat_quote_text', $thrive_meta_postformat_quote_text, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_quote_text', $thrive_meta_postformat_quote_text );
	add_post_meta( $post_id, '_thrive_meta_postformat_quote_author', $thrive_meta_postformat_quote_author, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_quote_author', $thrive_meta_postformat_quote_author );
	add_post_meta( $post_id, '_thrive_meta_postformat_audio_type', $thrive_meta_postformat_audio_type, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_audio_type', $thrive_meta_postformat_audio_type );
	add_post_meta( $post_id, '_thrive_meta_postformat_audio_file', $thrive_meta_postformat_audio_file, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_audio_file', $thrive_meta_postformat_audio_file );
	add_post_meta( $post_id, '_thrive_meta_postformat_audio_soundcloud_url', $thrive_meta_postformat_audio_soundcloud_url, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_audio_soundcloud_url', $thrive_meta_postformat_audio_soundcloud_url );
	add_post_meta( $post_id, '_thrive_meta_postformat_audio_soundcloud_autoplay', $thrive_meta_postformat_audio_soundcloud_autoplay, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_audio_soundcloud_autoplay', $thrive_meta_postformat_audio_soundcloud_autoplay );
	add_post_meta( $post_id, '_thrive_meta_postformat_gallery_images', $thrive_meta_postformat_gallery_images, true ) or
	update_post_meta( $post_id, '_thrive_meta_postformat_gallery_images', $thrive_meta_postformat_gallery_images );

	//get and save the soundcloud embed code
	if ( ! empty( $thrive_meta_postformat_audio_soundcloud_url ) ) {
		$soundcloudParams = array(
			'url'       => $thrive_meta_postformat_audio_soundcloud_url,
			'auto_play' => ( $thrive_meta_postformat_audio_soundcloud_autoplay == 1 ) ? "true" : "false",
			'format'    => 'json'
		);
		if ( ! class_exists( 'ThriveSoundcloud' ) ) {
			include get_template_directory() . '/inc/libs/ThriveSoundcloud.php';
		}
		$thriveSoundcloud = new ThriveSoundcloud();
		$response         = $thriveSoundcloud->url( $soundcloudParams );

		if ( $response && isset( $response->html ) ) {
			add_post_meta( $post_id, '_thrive_meta_postformat_audio_soundcloud_embed_code', $response->html, true ) or
			update_post_meta( $post_id, '_thrive_meta_postformat_audio_soundcloud_embed_code', $response->html );
		}
	}
}

?>
