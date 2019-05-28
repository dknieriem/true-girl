<?php tha_content_bottom(); ?>
<?php
$options        = thrive_get_options_for_post();
$num            = 0;
$active_footers = array();
while ( $num < 4 ) {
	$num ++;
	if ( is_active_sidebar( 'footer-' . $num ) ) {
		array_push( $active_footers, 'footer-' . $num );
	}
}
$num_cols = count( $active_footers );

switch ( $num_cols ) {
	case 0:
		$f_class = "";
		break;
	case 1:
		$f_class = "";
		break;
	case 2:
		$f_class = "colm twc";
		break;
	case 3:
		$f_class = "colm oth";
		break;
}
?>
<?php if ( ! is_home() ): ?>
	</div>
<?php endif; ?>
</div>
<div class="clear"></div>
<?php tha_content_after(); ?>
<?php tha_footer_before(); ?>
<footer>
	<?php tha_footer_top(); ?>
	<div class="wrp cnt">
		<section class="ftw">
			<?php
			$num = 0;
			foreach ( $active_footers as $name ):
				$num ++;
				?>
				<div class="<?php echo $f_class; ?> <?php echo ( $num == $num_cols ) ? 'lst' : ''; ?>">
					<?php dynamic_sidebar( $name ); ?>
				</div>
			<?php endforeach; ?>
		</section>

		<div class="clear"></div>
		<?php if ( has_nav_menu( "footer" ) ): ?>
			<section class="copyright">
				<?php wp_nav_menu( array(
					'theme_location' => 'footer',
					'depth'          => 1,
					'menu_class'     => 'footer_menu'
				) ); ?>
			</section>
		<?php endif; ?>
		<p class="credits">
			<?php if ( isset( $options['footer_copyright'] ) && $options['footer_copyright'] ): ?>
				<?php echo str_replace( '{Y}', date( 'Y' ), $options['footer_copyright'] ); ?>
			<?php endif; ?>
			<?php if ( isset( $options['footer_copyright_links'] ) && $options['footer_copyright_links'] == 1 ): ?>
				&nbsp;&nbsp;-&nbsp;&nbsp;Designed by <a href="//www.thrivethemes.com" target="_blank"
				                                        style="text-decoration: underline;">Thrive Themes</a>
				| Powered by <a style="text-decoration: underline;" href="//www.wordpress.org"
				                target="_blank">WordPress</a>
			<?php endif; ?>
		</p>

	</div>
	<?php tha_footer_bottom(); ?>
</footer>
<?php tha_footer_after(); ?>

<?php if ( isset( $options['analytics_body_script'] ) && $options['analytics_body_script'] != "" ): ?>
	<?php echo $options['analytics_body_script']; ?>
<?php endif; ?>
<?php wp_footer(); ?>
<?php tha_body_bottom(); ?>
</body>
</html>