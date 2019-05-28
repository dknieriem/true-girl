<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * This file has to be included at the beginning of all editor layouts
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( tge()->is_request( 'ajax' ) ) {
	return;
}
nocache_headers();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="tge-html">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow"/>
	<?php wp_head(); ?>
</head>
<body>
<div id="wpbody">
	<section id="tge-app">
		<div id="tge-paper" class="joint-theme-modern joint-paper"></div>

		<div class="tge-scroll tge-scroll-top" data-dir="top"></div>
		<div class="tge-scroll tge-scroll-bottom" data-dir="bottom"></div>
		<div class="tge-scroll tge-scroll-left" data-dir="left"></div>
		<div id="tge-add-new-question">
			<span class="tvd-icon-plus tvd-icon-small"></span>
			<?php echo __( 'Add Question', Thrive_Graph_Editor::T ) ?>
		</div>
	</section>

	<?php include( dirname( dirname( __FILE__ ) ) . '/templates/control-panel.php' ) ?>

	<div id="tge-navigator">
		<div class="tge-nav-title">
			<?php echo __( 'Navigator', Thrive_Graph_Editor::T ) ?>
			<span id="tge-nav-control" class="tvd-icon-minus tvd-tooltipped" data-position="top" data-tooltip="<?php echo __( 'Minimize', Thrive_Graph_Editor::T ) ?>"></span>
		</div>
		<div style="position: relative;">
			<div id="tge-nav-paper" class="joint-theme-modern joint-paper"></div>
			<div id="tge-nav-handler"></div>
		</div>
	</div>

	<?php wp_footer() ?>
	<div style="position:absolute; left: -5000px; top: -50000px">
		<div class="wistia_embed wistia_async_f6sh0ulno2 popover=true videoFoam=true"></div>
	</div>
</div>
</body>
</html>
