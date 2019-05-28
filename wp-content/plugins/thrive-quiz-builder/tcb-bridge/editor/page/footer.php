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


<?php include trailingslashit( dirname( __FILE__ ) ) . 'states.php'; ?>
<?php include tqb()->plugin_path( 'tcb-bridge/editor-lightbox/leanmodal-template.php' ); ?>

<?php do_action( 'get_footer' ) ?>
<?php wp_footer() ?>
</body>
</html>
