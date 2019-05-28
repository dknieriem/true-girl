<?php
$thrive_meta_postformat_quote_text   = get_post_meta( get_the_ID(), '_thrive_meta_postformat_quote_text', true );
$thrive_meta_postformat_quote_author = get_post_meta( get_the_ID(), '_thrive_meta_postformat_quote_author', true );
?>

<?php if ( $featured_image ): ?>

	<div class="ind-q ind-qi" style="background-image: url('<?php echo $featured_image; ?>')">
		<div class="quo">
			<h5><?php echo $thrive_meta_postformat_quote_text; ?></h5>
			<p><?php echo $thrive_meta_postformat_quote_author; ?></p>
		</div>
		<?php if ( ! is_singular() ): ?>
			<a href="<?php the_permalink(); ?>" class="crd">Continue reading</a>
		<?php endif; ?>
	</div>

<?php else: ?>

	<div class="ind-q ind-di">
		<div class="quo">
			<h5><?php echo $thrive_meta_postformat_quote_text; ?></h5>
			<p><?php echo $thrive_meta_postformat_quote_author; ?></p>
		</div>
		<?php if ( ! is_singular() ): ?>
			<a href="<?php the_permalink(); ?>" class="crd">Continue reading</a>
		<?php endif; ?>
	</div>

<?php endif; ?>        