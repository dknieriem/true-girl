<?php tha_content_bottom(); ?>

<?php if ( ! is_home() ): ?>
	</div>
<?php endif; ?>
</div>
<div class="clear"></div>
<?php tha_content_after(); ?>
<?php tha_footer_before(); ?>

	<div class="fusion-footer">

		<footer class="fusion-footer-widget-area fusion-widget-area fusion-footer-widget-area-center">
			<div class="fusion-row">
				<div class="fusion-columns fusion-columns-4 fusion-widget-area">
					<div class="fusion-column col-lg-3 col-md-3 col-sm-3">
						<?php if ( function_exists( 'dynamic_sidebar' ) && dynamic_sidebar( 'avada-footer-widget-1' ) ) : ?>
									<?php
									/**
									 * All is good, dynamic_sidebar() already called the rendering.
									 */
									?>
						<?php endif; ?>
					</div>
					<div class="fusion-column col-lg-3 col-md-3 col-sm-3">
						<?php if ( function_exists( 'dynamic_sidebar' ) && dynamic_sidebar( 'avada-footer-widget-2' ) ) : ?>
									<?php
									/**
									 * All is good, dynamic_sidebar() already called the rendering.
									 */
									?>
						<?php endif; ?>
					</div>	
					<div class="fusion-column col-lg-3 col-md-3 col-sm-3">
						<?php if ( function_exists( 'dynamic_sidebar' ) && dynamic_sidebar( 'avada-footer-widget-3' ) ) : ?>
									<?php
									/**
									 * All is good, dynamic_sidebar() already called the rendering.
									 */
									?>
						<?php endif; ?>
					</div>
					<div class="fusion-column fusion-column-last col-lg-3 col-md-3 col-sm-3">
						<?php if ( function_exists( 'dynamic_sidebar' ) && dynamic_sidebar( 'avada-footer-widget-4' ) ) : ?>
									<?php
									/**
									 * All is good, dynamic_sidebar() already called the rendering.
									 */
									?>
						<?php endif; ?>
					</div>	
					<div class="fusion-clearfix"></div>
				</div> <!-- fusion-columns -->
			</div> <!-- fusion-row -->
		</footer> <!-- fusion-footer-widget-area -->

		<?php
		/**
		 * Check if the footer copyright area should be displayed.
		 */
		?>

		<footer id="footer" class="fusion-footer-copyright-area">
			<div class="fusion-row">
				<div class="fusion-copyright-content">
					<div id="skg-copyright">

						<div class="footer-search">
						
							<div class="footer-search-icon">
								<img src="https://mytruegirl.com/wp-content/uploads/2016/09/skg_search_glass-white.png">
							</div>
							
							<div class="footer-search-form">
								<form role="search" class="searchform" method="get" action="https://mytruegirl.com/">
									<div class="search-table">
										<div class="search-field">
											<input type="text" value="" name="s" class="s" placeholder="Search..." />
										</div>
										<div class="search-button">
											<input type="submit" class="searchsubmit" value="" />
										</div>
									</div>
								</form>
							
							</div><!-- /.footer-search-form -->
						
						</div><!-- /.footer-search -->
						
						<div class="footer-copyright">ALL RIGHTS RESERVED | COPYRIGHT &copy; 2019 TRUE GIRL<br />
							<a href="https://mytruegirl.com/promoters/">Promoter Resources</a>
						</div>
						
						<div class="footer-connect">
						
							<div class="footer-connect-icon">
								<a href="https://mytruegirl.com/contact"><img src="https://mytruegirl.com/wp-content/uploads/2016/09/skg_contact_icon-white.png"></a>
							</div>
							
							<div class="footer-connect-text">
								<span class="footer-questions">Questions?!</span> Contact Us Any Time! <br />
								<a href="tel:+1814-234-6072">(814)234-6072</a> | <a href="mailto:info@mytruegirl.com">info@mytruegirl.com</a>
							</div>
						
						</div><!-- /.footer-connect -->
						
					</div><!-- /#skg-copyright -->
					
				</div> <!-- fusion-fusion-copyright-content -->
			</div> <!-- fusion-row -->
		</footer> <!-- #footer -->
	</div> <!-- fusion-footer -->


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