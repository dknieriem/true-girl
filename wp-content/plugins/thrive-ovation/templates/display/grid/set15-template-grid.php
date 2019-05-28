<div id="<?php echo $unique_id; ?>" class="tvo-testimonials-display tvo-testimonials-display-grid tvo-set15-template tve_red">
	<?php foreach ( $testimonials as $testimonial ) : ?>
		<?php if ( ! empty( $testimonial ) ) : ?>
			<div class="tvo-item-col tvo-item-s12 tvo-item-m6 tvo-item-l4 ">
				<div class="tvo-testimonial-display-item tvo-apply-background">
					<div class="tvo-item-grid">
						<div class="tvo-image-wrapper">
							<div class="tvo-testimonial-image-cover" style="background-image: url(<?php echo $testimonial['picture_url'] ?>)">
								<img src="<?php echo $testimonial['picture_url'] ?>"
								     class="tvo-testimonial-image tvo-dummy-image">
							</div>
						</div>
						<div class="tvo-testimonial-info">
						<span class="tvo-testimonial-name">
							<?php echo $testimonial['name'] ?>
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
					<svg height="7" width="100%" viewbox="0 0 600 7" preserveAspectRatio="none" class="tvo-testimonial-line tvo-separator-svg-stroke">
						<polyline points="0,7 25,7 35,0 45,7 600 7"></polyline>
					</svg>
					<div class="tvo-testimonial-content">
						<?php if ( ! empty( $config['show_title'] ) ) : ?>
							<h4>
								<?php echo $testimonial['title'] ?>
							</h4>
						<?php endif; ?>
						<div class="tvo-relative">
							<?php echo $testimonial['content'] ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endforeach ?>
</div>

