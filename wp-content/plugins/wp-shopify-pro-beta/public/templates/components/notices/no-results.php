<?php

/*

@description   Notice for when a template fails to load

@version       1.0.1
@since         1.0.49
@path          templates/components/notices/no-results.php

@docs          https://wpshop.io/docs/templates/components/notices/no-results

*/

defined('ABSPATH') ?: exit();

$error = isset($data->error) ? $data->error : '';
?>

<div class="wps-notice-inline wps-notice-warning <?= apply_filters('wps_products_no_results_class', '') ?>">
  <p><?= __('Failed to load template: <code>' . $data->path . '/' . $data->name . '</code>. ' . $error, WPS_PLUGIN_TEXT_DOMAIN) ?></p>
</div>
