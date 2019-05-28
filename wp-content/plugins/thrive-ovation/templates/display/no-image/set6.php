<div id="<?php echo $unique_id; ?>" class="tvo-testimonials-display tvo-testimonials-display-no-image tvo-set3-template tve_red">
	<?php foreach ( $testimonials as $testimonial ) : ?>
		<?php if ( ! empty( $testimonial ) ) : ?>
			<div class="tvo-testimonial-display-item tvo-apply-background custom-set6">
				<div class="tvo-testimonial-quote"></div>
				<hr>
				<?php if ( ! empty( $config['show_title'] ) ) : ?>
					<h4>
						<?php echo $testimonial['title'] ?>
					</h4>
				<?php endif; ?>
				<div class="tvo-relative tvo-testimonial-content">
					<?php echo $testimonial['content'] ?>
				</div>
				<hr>
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
		<?php endif; ?>
	<?php endforeach ?>
</div>