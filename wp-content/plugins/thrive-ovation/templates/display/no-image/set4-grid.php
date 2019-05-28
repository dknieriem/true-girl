<div id="<?php echo $unique_id; ?>" class="tvo-testimonials-display tvo-testimonials-display-grid tvo-default-template tve_red">
    <?php foreach ( $testimonials as $testimonial ) : ?>
        <?php if ( ! empty( $testimonial ) ) : ?>
            <div class="tvo-item-col tvo-item-s12 tvo-item-m6 tvo-item-l4 custom-set4-grid">
                <?php if ( ! empty( $config['show_title'] ) && ! empty( $testimonial['title'] ) ) : ?>
                    <h4>
                        <div class="tvo-testimonial-quote"></div>
                        <?php echo $testimonial['title'] ?>
                    </h4>
                <?php endif; ?>
                <div class="tvo-testimonial-display-item tvo-apply-background arrow-box">
                    <div class="tvo-relative tvo-testimonial-content">
                        <?php echo $testimonial['content'] ?>
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
        <?php endif; ?>
    <?php endforeach ?>
</div>