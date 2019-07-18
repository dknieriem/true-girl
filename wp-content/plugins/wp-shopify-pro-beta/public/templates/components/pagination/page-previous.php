<?php

/*

@description   Represents the previous page link withinin the pagination

@version       2.0.0
@since         1.0.49
@path          templates/components/pagination/page-previous.php

@docs          https://wpshop.io/docs/templates/pagination/page-previous

*/

defined('ABSPATH') ?: die;

?>

<a itemprop="url" href="<?= $data->page_href; ?>" class="wps-products-page-previous" itemprop="item"><?= $data->page_previous_text; ?></a>
