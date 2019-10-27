<?php

/*

@description   Quantity component

@version       1.0.1
@since         1.0.49
@path          templates/components/products/add-to-cart/quantity.php

@docs          https://wpshop.io/docs/templates/components/products/add-to-cart/quantity

*/

defined('ABSPATH') ?: die(); ?>

<div
	class="wps-component wps-component-products-quantity"
	data-wps-is-component-wrapper
	data-wps-product-id="<?= $data->product->product_id ?>"
	data-wps-post-id="<?= $data->product->post_id ?>">

	<div
		class="wps-form-control wps-row wps-m-0 wps-product-quantity-wrapper <?= apply_filters('wps_products_quantity_class', '') ?>">

		<div class="wps-quantity-input wps-quantity-label-wrapper" data-wps-is-ready="0">

			<label for="wps-product-quantity">
		    <?= apply_filters('wps_products_quantity_label', esc_html__('Quantity', WPS_PLUGIN_TEXT_DOMAIN)) ?>
		  </label>

		</div>

		<div class="wps-quantity-input wps-quantity-input-wrapper" data-wps-is-ready="0">
			<input type="number" name="wps-product-quantity" class="wps-product-quantity wps-form-input" value="1" min="0">
		</div>


	</div>

</div>
