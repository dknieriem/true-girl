<?php

$this->shortcode_callbacks[ $id ] = function( $atts, $content, $tag ) {

	$id = $this->maybe_remove_prefix( $tag );

	$args = array(
		'id'        => $id,
		'atts'      => $atts,
		'content'   => $content,
		'tag'       => $tag,
		'defaults'  => $this->custom_shortcodes[ $id ]['defaults'],
		'code'      => $this->custom_shortcodes[ $id ]['code'],
		'code_type' => $this->custom_shortcodes[ $id ]['code_type'],
		'css'       => $this->custom_shortcodes[ $id ]['css'],
	);

	return su_maker_do_shortcode( $args );

};
