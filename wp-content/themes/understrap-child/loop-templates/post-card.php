<?php $featuredImage = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), "large"); ?>

    <div class="post-card">
        <a href="<?php echo esc_url( get_permalink()); ?>"><div class="post-card__heading">
            <?php if ($featuredImage) { ?>
                <img src="<?php echo $featuredImage[0];?>">
            <?php } else { ?>
                <img src="<?php echo get_site_url();?>/wp-content/uploads/2018/09/mom-daughter.jpg">
            <?php } ?>  
            </div></a>
        <div class="post-card__body">
            <h3 class="post-card__title"><a class="post-card__link" href="<?php echo esc_url( get_permalink()); ?>" ><?php the_title(); ?></a></h3>
            <p class="post-card__text"><?php echo(get_excerpt(100));?></p>
        </div>
    </div>