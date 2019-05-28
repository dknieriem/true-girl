<div id="<?php echo $unique_id; ?>" class="tvo-testimonials-display tvo-testimonials-display-grid tvo-set3-template tve_purple">
    <?php foreach ( $testimonials as $testimonial ) : ?>
        <?php if ( ! empty( $testimonial ) ) : ?>
            <div class="tvo-item-col tvo-item-s12 tvo-item-m6 tvo-item-l4 custom-set2-grid">
                <div class="tvo-testimonial-display-item">
                    <div class="tvo-relative tvo-testimonial-content">
                        <?php if ( ! empty( $config['show_title'] ) ) : ?>
                            <h4>
                                <?php echo $testimonial['title'] ?>
                            </h4>
                        <?php endif; ?>
                        <?php echo $testimonial['content'] ?>
                    </div>
                    <div class="tvo-testimonial-quote"></div>
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
            </div>
        <?php endif; ?>
    <?php endforeach ?>
</div>