<div
		class="tvo-testimonials-display tvo-testimonials-display-slider tvo-set2-template tve_red tvo-apply-background">
	<div id="<?php echo $unique_id; ?>">
		<div class="thrlider-slider">
			<?php foreach ( $testimonials as $testimonial ) : ?>
				<?php if ( ! empty( $testimonial ) ) : ?>
					<div class="thrlider-slide">
						<div class="tvo-testimonial-display-item custom-set6-slider">
							<div class="quote-title-holder">
								<div class="tvo-testimonial-quote"></div>
								<hr>
								<?php if ( ! empty( $config['show_title'] ) ) : ?>
									<h4>
										<?php echo $testimonial['title'] ?>
									</h4>
								<?php endif; ?>
							</div>

							<div class="tvo-relative tvo-testimonial-content">
								<?php echo $testimonial['content'] ?>
								<hr>
								<div class="tvo-testimonial-info">
                                    <span class="tvo-testimonial-name">
                                        <?php echo $testimonial['name']; ?>
                                    </span>
									<?php if ( ! empty( $config['show_role'] ) ) : ?>
										<span class="tvo-testimonial-role">
                                            <?php $role_wrap_before = empty( $config['show_site'] ) || empty( $testimonial['website_url'] ) ? '' : '<a href="' . $testimonial['website_url'] . '">';
                                            $role_wrap_after        = empty( $config['show_site'] ) || empty( $testimonial['website_url'] ) ? '' : '</a>';
                                            echo $role_wrap_before . $testimonial['role'] . $role_wrap_after; ?>
                                        </span>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach ?>
		</div>
		<span class="thrlider-prev"></span>
		<span class="thrlider-next"></span>
	</div>
</div>
<script type="text/javascript">
	jQuery( document ).ready( function () {
		setTimeout( function () {
			jQuery( '#<?php echo $unique_id; ?>' ).thrlider( {
				nav: true,
			} );
		}, 200 );
	} );
</script>
