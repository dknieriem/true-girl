<?php

/*

@description   Product header within loop

@version       1.0.1
@since         1.0.49
@path          templates/components/products/loop/header.php

@docs          https://wpshop.io/docs/templates/components/products/loop/header

*/

defined('ABSPATH') ?: die;

?>

<header class="wps-products-header wps-contain wps-row <?= apply_filters('wps_products_header_class', ''); ?>">

  <?php do_action('wps_products_heading_before', $data->query); ?>

	<?php if ( apply_filters('wps_products_heading_show', true) ) { ?>

		<h1 class="wps-products-heading <?= apply_filters('wps_products_heading_class', ''); ?>">
	    <?= $data->heading; ?>
	  </h1>

	<?php } ?>

  <?php do_action('wps_products_heading_after', $data->query); ?>

</header>
