<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package understrap
 */

$the_theme = wp_get_theme();
$container = get_theme_mod( 'understrap_container_type' );
?>

<div class="footer" id="wrapper-footer">

	<footer class="site-footer" id="colophon">

	<div class="<?php echo esc_attr( $container ); ?>">

		<!-- <div class="row"> -->
			<?php get_template_part( 'sidebar-templates/sidebar', 'footerfull' ); ?>
			

		<!-- </div> --><!-- row end -->

		<div class="row">
			<div class="col-md-12">
				<p class="footer__copyright">Copyright <?php echo date('Y'); ?> Dannah Gresh | <a href="#" class="footer__link">Privacy Policy</a></p>
			</div>
		</div>

	</div><!-- container end -->
	</footer><!-- #colophon -->

</div><!-- wrapper end -->

</div><!-- #page we need this extra closing tag here -->

<?php wp_footer(); ?>

</body>

</html>

