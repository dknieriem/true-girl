<div id="<?php echo $unique_id; ?>" class="tvo-testimonials-display tvo-testimonials-display-grid tvo-default-template tve_red">
    <?php foreach ( $testimonials as $testimonial ) : ?>
        <?php if ( ! empty( $testimonial ) ) : ?>
            <div class="tvo-item-col tvo-item-s12 tvo-item-m6 tvo-item-l4 custom-set1-grid">
                <?php if ( ! empty( $config['show_title'] ) ) : ?>
                    <h4>
                        <?php echo $testimonial['title'] ?>
                    </h4>
                <?php endif; ?>
                <div class="tvo-testimonial-display-item tvo-apply-background arrow-box">
                    <div class="tvo-relative tvo-testimonial-content">
                        <?php echo $testimonial['content'] ?>
                    </div>
                </div>
                <div class="info-quote-holder clearfix">
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
            </div>
        <?php endif; ?>
    <?php endforeach ?>
</div>

