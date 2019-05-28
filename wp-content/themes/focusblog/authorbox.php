<?php
$fname         = get_the_author_meta( 'first_name' );
$lname         = get_the_author_meta( 'last_name' );
$desc          = get_the_author_meta( 'description' );
$thrive_social = array_filter( array(
	"twt"  => get_the_author_meta( 'twitter' ),
	"fbk"  => get_the_author_meta( 'facebook' ),
	"ggl"  => get_the_author_meta( 'gplus' ),
	"lnk"  => get_the_author_meta( 'linkedin' ),
	"xing" => get_the_author_meta( 'xing' )
) );

$author_name          = get_the_author_meta( 'display_name' );
$show_social_profiles = explode( ',', get_the_author_meta( 'show_social_profiles' ) );
$show_social_profiles = array_filter( $show_social_profiles );
if ( empty( $show_social_profiles ) ) { // back-compatibility
	$show_social_profiles = array( 'e', 'fbk', 'twt', 'ggl' );
}
$display_name = empty( $author_name ) ? $fname . " " . $lname : $author_name;

?>
<article>
	<div class="awr aut">
		<div class="left">
			<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
				<?php echo get_avatar( get_the_author_meta( 'user_email' ), 98 ); ?>
			</a>
			<ul class="left">
				<?php
				foreach ( $thrive_social as $service => $url ):
					if ( in_array( $service, $show_social_profiles ) || empty( $show_social_profiles[0] ) ) {
						$url = _thrive_get_social_link( $url, $service );
						?>
						<li>
							<a href="<?php echo $url; ?>" class="<?php echo $service; ?>" target="_blank"></a>
						</li>
						<?php
					}
				endforeach;
				?>
			</ul>
			<div class="clear"></div>

		</div>
		<div class="right">
			<h5 class="aut"><?php //_e("Author", 'thrive')  ?><?php echo $display_name; ?></h5>
			<div class="mspr">&nbsp;</div>
			<p>
				<?php echo $desc; ?>
			</p>
		</div>
		<div class="clear"></div>
	</div>
</article>
<div class="spr"></div>
