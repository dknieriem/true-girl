<?php

$args = array(
	'id'        => $id,
	'defaults'  => $this->custom_shortcodes[ $id ]['defaults'],
	'code'      => $this->custom_shortcodes[ $id ]['code'],
	'code_type' => $this->custom_shortcodes[ $id ]['code_type'],
	'css'       => $this->custom_shortcodes[ $id ]['css'],
);

$callback = '
	$args              = unserialize(\'' . serialize( $args ) . '\');
	$args[\'atts\']    = $atts;
	$args[\'content\'] = $content;
	$args[\'tag\']     = $tag;

	return su_maker_do_shortcode( $args );
';

$this->shortcode_callbacks[ $id ] = create_function( '$atts, $content, $tag', $callback );
