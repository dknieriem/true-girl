<?php
/*
 * This file has to be included at the end of all editor layouts
 */

if ( ! empty( $is_ajax_render ) ) {
	/**
	 * If AJAX-rendering the contents, we need to only output the html part,
	 * and do not include any of the custom css / fonts etc needed - used in the state manager
	 */
	return;
}
?>

<div id="tve_page_loader" class="tve_page_loader"<?php echo ! tve_ult_is_editor_page() ? ' style="display: none"' : '' ?>>
	<div class="tve_loader_inner"><img src="<?php echo tve_editor_css() ?>/images/loader.gif" alt=""/></div>
</div>

<?php include trailingslashit( dirname( __FILE__ ) ) . 'states.php'; ?>

<?php do_action( 'get_footer' ) ?>
<?php wp_footer() ?>
</body>
</html>
