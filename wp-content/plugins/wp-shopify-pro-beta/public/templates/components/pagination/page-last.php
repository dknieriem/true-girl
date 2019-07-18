<?php

/*

@description   Represents the last page link within the pagination

@version       2.0.0
@since         1.0.49
@path          templates/components/pagination/page-last.php

@docs          https://wpshop.io/docs/templates/pagination/page-last

*/

defined('ABSPATH') ?: die;

?>

<a itemprop="url" href="<?= $data->page_href; ?>" class="wps-products-page-last" itemprop="item"><?= $data->page_last_text; ?></a>
