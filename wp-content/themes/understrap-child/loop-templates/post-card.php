<?php $featuredImage = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), "medium"); ?>


    <div class="post-card">
        <?php if ($featuredImage) { ?>
            <a href="<?php echo esc_url( get_permalink()); ?>"><div class="post-card__heading" style="background: #3d3d3d url('<?php echo $featuredImage[0];?>') no-repeat 50% 50%; background-size:cover;"></div></a>
        <?php } else { ?>
            <a href="<?php echo esc_url( get_permalink()); ?>"><div class="post-card__heading" style="background: #3d3d3d url('<?php echo get_site_url();?>/wp-content/uploads/2018/09/mom-daughter.jpg') no-repeat 50% 50%; background-size:cover;"></div></a>
        <?php } ?>            
        <div class="post-card__body">
            <h3 class="post-card__title"><a class="post-card__link" href="<?php echo esc_url( get_permalink()); ?>" ><?php the_title(); ?></a></h3>
            <p class="post-card__text"><?php echo(get_the_excerpt());?></p>
            <div class="post-card__card-button-wrapper">
                <a class="post-card__card-button" style="display:inline-block;" href="<?php echo get_permalink(); ?>">Read more</a>
            </div>
            
        </div>
    </div>