<?php

/**
 * Custom shortcode callbacks.
 *
 * @since        1.5.5
 * @package      Shortcodes_Ultimate_Maker
 * @subpackage   Shortcodes_Ultimate_Maker/includes
 */

/**
 * Callback for custom shortcodes.
 *
 * Parses attributes, content and shortcode code. Calls three other methods
 * depending on $args[code_type].
 *
 * Uses serialized args for compatibility with PHP 5.2 create_function function.
 * Also, uses base64-encoded code for the same reason.
 *
 * @uses su_maker_do_html_shortcode() to process 'html' code.
 * @uses su_maker_do_return_shortcode() to process 'php_return' code.
 * @uses su_maker_do_echo_shortcode() to process 'php_echo' code.
 *
 * @since  1.5.5
 * @param mixed   $args Array with shortcode data (array can be serialized).
 * @return string       Shortcode markup.
 */
function su_maker_do_shortcode( $args ) {

	$args = maybe_unserialize( $args );

	$output = '';

	$attributes = shortcode_atts( $args['defaults'], $args['atts'], $args['id'] );
	$content    = do_shortcode( $args['content'] );
	$code       = base64_decode( $args['code'] );
	$css        = base64_decode( $args['css'] );

	if (
		! empty( $css ) &&
		! did_action( 'su/maker/custom_css/' . $args['id'] )
	) {

		$output .= "\n<style>\n";
		$output .= $css;
		$output .= "\n</style>\n";

		do_action( 'su/maker/custom_css/' . $args['id'] );

	}

	if ( $args['code_type'] === 'html' ) {
		$output .= su_maker_do_html_shortcode( $attributes, $content, $code );
	}

	elseif ( $args['code_type'] === 'php_return' ) {
		$output .= su_maker_do_return_shortcode( $attributes, $content, $code );
	}

	elseif ( $args['code_type'] === 'php_echo' ) {
		$output .= su_maker_do_echo_shortcode( $attributes, $content, $code );
	}

	return $output;

}

/**
 * Handle shortcode with 'html' code type.
 *
 * @since  1.5.5
 * @access private
 * @param mixed   $attributes Parsed shortcode attributes.
 * @param string  $content    Shortcode content.
 * @param string  $code       Shortcode code to execute.
 * @return string             Shortcode markup.
 */
function su_maker_do_html_shortcode( $attributes, $content, $code ) {

	foreach ( $attributes as $id => $value ) {
		$code = str_replace( '{{' . $id . '}}', $value, $code );
	}

	$code = str_replace( '{{content}}', $content, $code );

	return do_shortcode( $code );

}

/**
 * Handle shortcode with 'php_return' code type.
 *
 * @since  1.5.5
 * @access private
 * @param mixed   $attributes Parsed shortcode attributes.
 * @param string  $content    Shortcode content.
 * @param string  $code       Shortcode code to execute.
 * @return string             Shortcode markup.
 */
function su_maker_do_return_shortcode( $attributes, $content, $code ) {

	extract( $attributes );
	return eval( $code );

}

/**
 * Handle shortcode with 'php_echo' code type.
 *
 * @since  1.5.5
 * @access private
 * @param mixed   $attributes Parsed shortcode attributes.
 * @param string  $content    Shortcode content.
 * @param string  $code       Shortcode code to execute.
 * @return string             Shortcode markup.
 */
function su_maker_do_echo_shortcode( $attributes, $content, $code ) {

	extract( $attributes );

	ob_start();
	eval( $code );
	$return = ob_get_contents();
	ob_end_clean();

	return $return;

}
