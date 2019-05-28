<div id="<?php echo $unique_id; ?>" class="tvo-testimonials-display tvo-testimonials-display-no-image tvo-default-template tve_red">
    <?php foreach ( $testimonials as $testimonial ) : ?>
        <?php if ( ! empty( $testimonial ) ) : ?>
            <div class="custom-set4">
                <?php if ( ! empty( $config['show_title'] ) && ! empty( $testimonial['title'] ) ) : ?>
                    <h4>
                        <div class="tvo-testimonial-quote"></div>
                        <?php echo $testimonial['title'] ?>
                    </h4>
                <?php endif; ?>
                <div class="tvo-testimonial-display-item tvo-apply-background tvo-testimonial-content arrow-box">
                    <?php if (empty( $testimonial['title'] ) ) : ?>
                        <div class="tvo-testimonial-quote" id="no-title-white-quote"></div>
                    <?php endif; ?>
                    <?php echo $testimonial['content'] ?>
                </div>
                <div class="tvo-testimonial-info">
                    <span class="tvo-testimonial-name">
                        <?php echo $testimonial['name'] ?>
                    </span>
                    <?php if ( ! empty( $testimonial['role'] ) ) : ?>
                        ,
                    <?php endif; ?>
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