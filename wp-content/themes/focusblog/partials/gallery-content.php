<?php
$options                               = thrive_get_options_for_post( get_the_ID() );
$thrive_meta_postformat_gallery_images = trim( get_post_meta( get_the_ID(), '_thrive_meta_postformat_gallery_images', true ), "," );
$thrive_gallery_ids                    = explode( ",", $thrive_meta_postformat_gallery_images );

if ( count( $thrive_gallery_ids ) > 0 ):
	$first_img_url = wp_get_attachment_url( $thrive_gallery_ids[0] );
	?>
	<div id="thrive-gallery-header-<?php echo get_the_ID(); ?>" class=""
	     data-count="<?php echo count( $thrive_gallery_ids ); ?>" data-index="0">
		<div class="hui" style="background-image: url('<?php echo trim( $first_img_url ); ?>');">
			<img id="thive-gallery-dummy" class="tt-dmy gallery-dmy" src="<?php echo trim( $first_img_url ); ?>" alt=""/>
		</div>
		<div class="gnav clearfix">
			<div class="gwrp">
				<a class="gprev" href=""></a>
				<ul class="clearfix">
					<?php
					$length            = sizeof( $thrive_gallery_ids );
					foreach ( $thrive_gallery_ids as $key => $id ):
						$img_url = wp_get_attachment_url( $id );
						$data_position = $key + 1;
						if ( $img_url ):
							?>
							<li id="li-thrive-gallery-item-<?php echo $key; ?>">
								<a class="thrive-gallery-item" href=""
								   style="background-image: url('<?php echo trim( $img_url ); ?>');"
								   data-src="<?php echo trim( $img_url ); ?>"
								   data-index="<?php echo $key; ?>"
								   data-caption=""
								   data-position="<?php echo $data_position . "/" . $length; ?>"></a>
							</li>
							<?php
						endif;
					endforeach;
					?>
				</ul>
				<a class="gnext" href=""></a>
			</div>
		</div>
	</div>
<?php endif; ?>