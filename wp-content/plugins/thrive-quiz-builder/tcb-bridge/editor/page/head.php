<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( ! empty( $is_ajax_render ) ) {
	/**
	 * If AJAX-rendering the contents, we need to only output the html part,
	 * and do not include any of the custom css / fonts etc needed - used in the state manager
	 */
	return;
}
?>
<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>
	<meta name="robots" content="noindex, nofollow"/>
	<title>
		<?php /* Genesis wraps the meta title into another <title> tag using this hook: genesis_doctitle_wrap. the following line makes sure this isn't called */ ?>
		<?php /* What if they change the priority at which this hook is registered ? :D */ ?>
		<?php remove_filter( 'wp_title', 'genesis_doctitle_wrap', 20 ) ?>
		<?php wp_title( '' ); ?>
	</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<?php echo TCB_Hooks::tqb_editor_output_custom_css( $variation, false ); ?>
	<?php wp_head(); ?>
</head>
<body>
