<?php

/*

@description   Individual image used for the image gallery on product single pages

@version       2.0.0
@since         1.0.49
@path          templates/components/products/single/img.php

@docs          https://wpshop.io/docs/templates/products/single/img

*/

defined('ABSPATH') ?: die;

?>

<div class="<?= $data->image_type_class; ?>-wrapper wps-col wps-col-<?= $data->amount_of_thumbs; ?>">

	<img
		itemprop="image"
		src="<?= esc_url( $data->custom_sizing ? $data->custom_image_src : $data->image_details->src ); ?>"
		class="wps-product-gallery-img <?= $data->image_type_class; ?>"
		alt="<?= $data->image_details->alt; ?>"
		data-wps-image-variants="<?= $data->variant_ids; ?>">

</div>
