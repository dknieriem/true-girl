<?php

/*

@description   Represents a normal page number withinin the pagination

@version       2.0.0
@since         1.0.49
@path          templates/components/pagination/page-number.php

@docs          https://wpshop.io/docs/templates/pagination/page-number

*/

defined('ABSPATH') ?: die;

?>

<a itemprop="url" href="<?= $data->page_href; ?>" class="wps-products-page-inactive" itemprop="item"><?= $data->page_number; ?></a>
