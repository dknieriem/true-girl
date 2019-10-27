<?php

/*

@description   Represents the next page link within the pagination

@version       2.0.0
@since         1.0.49
@path          templates/components/pagination/page-next.php

@docs          https://wpshop.io/docs/templates/pagination/page-next

*/

defined('ABSPATH') ?: die;

?>

<a itemprop="url" href="<?= $data->page_href ?>" class="wps-products-page-next" itemprop="item"><?= $data->page_next_text; ?></a>
