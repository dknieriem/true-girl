<?php
$options       = thrive_get_theme_options();
$sidebar_class = ( $options['sidebar_alignement'] == "right" || $options['sidebar_alignement'] == "left" ) ? $options['sidebar_alignement'] : "";
?>
<?php tha_sidebars_before(); ?>
	<div class="sAsCont">
		<?php tha_sidebar_top(); ?>
		<aside class="sAs <?php echo $sidebar_class; ?>">
			<?php if ( _thrive_check_is_woocommerce_page() ): ?>
				<?php if ( ! dynamic_sidebar( 'sidebar-woo' ) ) : ?>
					<!--IF THE WOO COMMERCE SIDEBAR IS NOT REGISTERED-->
				<?php endif; // end post sidebar widget area  ?>
			<?php elseif ( ! is_page() ): ?>
				<?php if ( ! dynamic_sidebar( 'sidebar-1' ) ) : ?>
					<!--IF THE MAIN SIDEBAR IS NOT REGISTERED-->
				<?php endif; // end post sidebar widget area  ?>
			<?php else: ?>
				<?php if ( ! dynamic_sidebar( 'sidebar-2' ) ) : ?>
					<!--IF THE MAIN SIDEBAR IS NOT REGISTERED-->
				<?php endif; // end page sidebar widget area  ?>
			<?php endif; ?>

		</aside>
		<?php tha_sidebar_bottom(); ?>
	</div>
<?php tha_sidebars_after(); ?>