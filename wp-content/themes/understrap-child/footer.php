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

<?php get_template_part( 'sidebar-templates/sidebar', 'footerfull' ); ?>

<div class="footer" id="wrapper-footer">

	<footer class="site-footer" id="colophon">

	<div class="<?php echo esc_attr( $container ); ?>">

		<div class="row">
			<div class="col-md-4">
				<h3 class="footer__heading">Keep in touch</h3>
				<!-- <form class="footer__email-signup-form">
					<label for="inputPassword2" class="sr-only">Join the mailing list</label>
					<input class="footer__input" placeholder="Email address">
					<button type="submit" class="footer__submit-button">Join</button>
				</form> -->
				<?php echo do_shortcode('[mc4wp_form id="6103"]');?>
				<a class="footer__social-link" href="https://www.facebook.com/dannahgresh.skg/"><i class="fa fa-facebook"></i></a>
				<a class="footer__social-link" href="https://www.instagram.com/dannah_gresh/?hl=en"><i class="fa fa-instagram"></i></a>
				<a class="footer__social-link" href="https://twitter.com/dannahgresh"><i class="fa fa-twitter"></i></a>
				<p class="footer__text mt-5">863 Benner Pike Unit 200<br />
				State College, PA 16801</p>
				<p class="footer__text"><a href="mailto:info@purefreedom.org">info@purefreedom.org</a></p>
			</div>

			<div class="col-md-4">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/bttb-logo.png" alt="" class="footer__logo" />
					<h3 class="footer__heading">Born to be Brave</h3>
					<p class="footer__text">A one-night father/son adventure where you'll learn to lead your son in becoming a godly man.</p>
					<a href="http://borntobebravetour.com/" class="footer__link">Visit the Born to be Brave site <i class="fa fa-arrow-right"></i></a>

			</div><!--col end -->

			<div class="col-md-4">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/skg-logo.png" alt="" class="footer__logo" />
					<h3 class="footer__heading">Secret Keeper Girl</h3>
					<p class="footer__text">SKG offers the most fun a mom and daughter will ever have connecting and talking about true beauty, and modesty.</p>
					<a href="https://www.secretkeepergirl.com/" class="footer__link">Visit the Secret Keeper Girl site <i class="fa fa-arrow-right"></i></a>
			</div>
			

		</div><!-- row end -->

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

