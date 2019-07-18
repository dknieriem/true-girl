<?php

/*

@description   Featured image used within the image gallery on product single pages

@version       2.0.0
@since         1.0.49
@path          templates/components/products/single/img-feat.php

@docs          https://wpshop.io/docs/templates/products/single/img-feat

*/

defined('ABSPATH') ?: die;

?>

<div class="<?= $data->image_type_class; ?>-wrapper">

	<img
		itemprop="image"
		src="<?= esc_url($data->image_details->src); ?>"
		class="wps-product-gallery-img <?= $data->image_type_class; ?>"
		alt="<?= esc_attr__($data->image_details->alt); ?>"
		data-wps-image-variants="<?= $data->variant_ids; ?>">

</div>
