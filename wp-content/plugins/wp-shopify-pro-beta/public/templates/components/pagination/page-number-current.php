<?php

/*

@description   Represents the 'current' page number in the pagination

@version       2.0.0
@since         1.0.49
@path          templates/components/pagination/page-number-current.php

@docs          https://wpshop.io/docs/templates/pagination/page-number-current

*/

defined('ABSPATH') ?: die;

?>

<span itemprop="identifier" class="wps-products-page-current"><?= $data->page_number; ?></span>
