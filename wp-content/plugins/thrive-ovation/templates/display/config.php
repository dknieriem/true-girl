<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<?php if ( ! empty( $config['tags'] ) && empty( $testimonials ) ) : ?>
	<div class="tvo-no-testimonials-in-tags"></div>
<?php endif; ?>


<script type="text/javascript">
	/* apply custom color class */

	var $shortcode = jQuery( '#<?php echo $unique_id; ?>' ),
		$parent = $shortcode.parent();
	if ( ! $shortcode.attr( 'tvo_colors_applied' ) ) {
		$shortcode.attr( 'tvo_colors_applied', true );
		<?php if ( ! empty( $config['color_class'] ) ) : ?>
		var new_class = '<?php echo $config['color_class']; ?>';
		if ( $shortcode.attr( 'class' ) ) {
			$shortcode.attr( 'class', $shortcode.attr( 'class' ).replace( /tve_(\w+)/i, new_class ) );
		} else if ( $parent.attr( 'class' ) ) {
			$parent.attr( 'class', $parent.attr( 'class' ).replace( /tve_(\w+)/i, new_class ) );
		}
		<?php endif;?>

		/* apply custom color for testimonial elements */
		<?php if ( ! empty( $config['custom_css'] ) && is_array( $config['custom_css'] ) ) : ?>
		var tve_custom_colors = <?php echo json_encode( $config['custom_css'] ); ?>;
		for ( var selector in tve_custom_colors ) {
			$parent.closest( '.thrv_tvo_display_testimonials' ).find( selector ).attr( 'data-tve-custom-colour', tve_custom_colors[selector] );
		}
		<?php endif; ?>
	}

</script>