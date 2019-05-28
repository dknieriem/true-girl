<div id="<?php echo $unique_id; ?>" class="tvo-testimonials-display tvo-testimonials-display-no-image tvo-default-template tve_red">
    <?php foreach ( $testimonials as $testimonial ) : ?>
        <?php if ( ! empty( $testimonial ) ) : ?>
            <div class="custom-set1 clearfix">
                <?php if ( ! empty( $config['show_title'] ) ) : ?>
                    <h4>
                        <?php echo $testimonial['title'] ?>
                    </h4>
                <?php endif; ?>
                <div class="tvo-testimonial-display-item tvo-apply-background tvo-relative tvo-testimonial-content arrow-box">
                    <?php echo $testimonial['content'] ?>
                </div>
                <div class="tvo-testimonial-info">
                    <span class="tvo-testimonial-name">
                        <?php echo $testimonial['name'] ?>
                    </span>
                    <?php if ( ! empty( $config['show_role'] ) ) : ?>
                        <?php if ( ! empty( $testimonial['role'] ) ) : ?>
                            ...
                        <?php endif; ?>
                        <span class="tvo-testimonial-role">
                            <?php $role_wrap_before = empty( $config['show_site'] ) || empty( $testimonial['website_url'] ) ? '' : '<a href="' . $testimonial['website_url'] . '">';
                            $role_wrap_after        = empty( $config['show_site'] ) || empty( $testimonial['website_url'] ) ? '' : '</a>';
                            echo $role_wrap_before . $testimonial['role'] . $role_wrap_after; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="tvo-testimonial-quote"></div>
            </div>
        <?php endif; ?>
    <?php endforeach ?>
</div>