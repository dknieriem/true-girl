<?php

$thrive_meta_postformat_audio_type                  = get_post_meta( get_the_ID(), '_thrive_meta_postformat_audio_type', true );
$thrive_meta_postformat_audio_file                  = get_post_meta( get_the_ID(), '_thrive_meta_postformat_audio_file', true );
$thrive_meta_postformat_audio_soundcloud_embed_code = get_post_meta( get_the_ID(), '_thrive_meta_postformat_audio_soundcloud_embed_code', true );
if ( $thrive_meta_postformat_audio_type != "soundcloud" ) {
	echo do_shortcode( "[audio src='" . $thrive_meta_postformat_audio_file . "'][/audio]" );
} else {
	echo $thrive_meta_postformat_audio_soundcloud_embed_code;
}

