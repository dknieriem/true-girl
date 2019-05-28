<div id="<?php echo $unique_id; ?>" class="tvo-testimonials-display tvo-testimonials-display-grid tvo-set5-template tve_red">
	<?php foreach ( $testimonials as $testimonial ) : ?>
		<?php if ( ! empty( $testimonial ) ) : ?>
			<div class="tvo-item-col tvo-item-s12 tvo-item-m6 tvo-item-l4 ">
				<div class="tvo-testimonial-display-item">
					<svg width="280px" height="140px">
						<defs>
							<clippath id="path">
								<path
									d="M 40 0 L 280 0 L 240 140 L 0 140"></path>
							</clippath>
						</defs>
						<foreignObject clip-path="url(#path)" width="100%" height="100%">
							<div class="tvo-testimonial-image-cover" style="background-image: url(<?php echo $testimonial['picture_url'] ?>)">
								<img src="<?php echo $testimonial['picture_url'] ?>"
								     class="tvo-testimonial-image tvo-dummy-image">
							</div>
						</foreignObject>
					</svg>
					<div class="tvo-apply-background tvo-testimonial-content">
						<div class="tvo-testimonial-quote"></div>
						<?php if ( ! empty( $config['show_title'] ) ) : ?>
							<h4>
								<?php echo $testimonial['title'] ?>
							</h4>
						<?php endif; ?>
						<p>
							<?php echo $testimonial['content'] ?>
						</p>
					</div>
					<div class="tvo-testimonial-info">
						<span class="tvo-testimonial-name">
							<?php echo $testimonial['name'] ?>
						</span> -
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
		<?php endif; ?>
	<?php endforeach ?>
</div>

