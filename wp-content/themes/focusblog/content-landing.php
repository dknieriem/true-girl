<?php
$options = thrive_get_options_for_post( get_the_ID() );

$comment_nb_class     = ( $options['sidebar_alignement'] == "right" ) ? "comment_nb" : "right_comment_nb";
$featured_image_data  = thrive_get_post_featured_image( get_the_ID(), $options['featured_image_style'] );
$featured_image       = $featured_image_data['image_src'];
$featured_image_alt   = $featured_image_data['image_alt'];
$featured_image_title = $featured_image_data['image_title'];

$post_options = get_post_custom( get_the_ID() );

$fname = get_the_author_meta( 'first_name' );
$lname = get_the_author_meta( 'last_name' );

$author_name  = get_the_author_meta( 'display_name' );
$display_name = empty( $author_name ) ? $fname . " " . $lname : $author_name;
?>
<?php tha_entry_before(); ?>
<article>
	<?php tha_entry_top(); ?>
	<div class="awr lnd">
		<?php if ( $options['featured_image_style'] == "wide" && $featured_image ): ?>
			<div class="fwit"><a class="psb"> <img src="<?php echo $featured_image; ?>"
			                                       alt="<?php echo $featured_image_alt; ?>"
			                                       title="<?php echo $featured_image_title; ?>"> </a></div>
		<?php endif; ?>
		<?php if ( isset( $post_options['_thrive_meta_show_post_title'][0] ) && $post_options['_thrive_meta_show_post_title'][0] != 0 ): ?>
			<h1 class="entry-title"><?php the_title(); ?></h1>
		<?php endif; ?>
		<?php if ( $options['featured_image_style'] == "thumbnail" && $featured_image ): ?>
			<img class="afim pst right" src="<?php echo $featured_image; ?>" alt="<?php echo $featured_image_alt; ?>"
			     title="<?php echo $featured_image_title; ?>">
		<?php endif; ?>
		<?php the_content(); ?>
		<?php if ( $options['enable_social_buttons'] == 1 ): ?>
			<?php get_template_part( 'share-buttons' ); ?>
		<?php endif; ?>
		<div class="clear"></div>
		<?php
		wp_link_pages( array(
			'before'         => '<br><p class="ctr pgn">',
			'after'          => '</p>',
			'next_or_number' => 'next_and_number',
			'echo'           => 1
		) );
		?>
	</div>
	<?php
	if ( isset( $options['bottom_previous_next'] ) && $options['bottom_previous_next'] == 1 && get_permalink( get_adjacent_post( false, '', false ) ) != "" && get_permalink( get_adjacent_post( false, '', true ) ) != "" && ! is_page() ):
		?>
		<div class="spr"></div>
		<div class="awr ctr pgn">
			<?php $prev_post = get_adjacent_post( false, '', true ); ?>
			<?php if ( $prev_post ) : ?>
				<a class="page-numbers nxt"
				   href='<?php echo get_permalink( get_adjacent_post( false, '', true ) ); ?>'>&larr;<?php _e( "Previous post", 'thrive' ) ?> </a>
			<?php endif; ?>
			<?php $next_post = get_adjacent_post( false, '', false ); ?>
			<?php if ( $next_post ) : ?>
				<a class="page-numbers prv"
				   href='<?php echo get_permalink( get_adjacent_post( false, '', false ) ); ?>'><?php _e( "Next post", 'thrive' ) ?>&rarr;</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php tha_entry_bottom(); ?>
</article>

<?php
_thrive_render_bottom_related_posts( get_the_ID(), $options );
?>

<?php tha_entry_after(); ?>
<div class="spr"></div>