<?php

/*

@description   Range that displays above page numbers. This is set to the following structure: Page X of Y.

@version       2.0.0
@since         1.0.49
@path          templates/components/pagination/counter.php

@docs          https://wpshop.io/docs/templates/pagination/counter

*/

defined('ABSPATH') ?: die;

?>

<div itemprop="description" class="wps-products-page-counter"><?= sprintf( __( 'Page %s of %s' ), $data->page_number, $data->max_pages ); ?></div>
