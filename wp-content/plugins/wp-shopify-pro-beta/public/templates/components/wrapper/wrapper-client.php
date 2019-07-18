<?php

use WPS\Options;
use WPS\Utils;

defined('ABSPATH') ?: exit();

$component_options = $data->data;

if (isset($component_options['products']['reverse'])) {

    if ($component_options['products']['reverse'] === 'true' || $component_options['reverse'] == 1) {
        $reverse = true;
    } else {
        $reverse = false;
    }
} else {
    $reverse = false;
}

if (isset($component_options['reverse'])) {

    if ($component_options['reverse'] === 'true' || $component_options['reverse'] == 1) {
        $reverse_main = true;
    } else {
        $reverse_main = false;
    }
} else {
    $reverse_main = false;
}


if (isset($component_options['products'])) {

    $connection_params = [
        'query' => isset($component_options['products']['query']) ? $component_options['products']['query'] : false,
        'sort_by' => isset($component_options['products']['sort_by']) ? $component_options['products']['sort_by'] : false,
        'reverse' => $reverse,
        'page_size' => isset($component_options['products']['page_size']) ? $component_options['products']['page_size'] : false,
    ];
} else {
    $connection_params = false;
}

$both = [
    'componentQueryParams' => [
        'query' => isset($component_options['query']) ? $component_options['query'] : false,
        'sort_by' => isset($component_options['sort_by']) ? $component_options['sort_by'] : false,
        'reverse' => $reverse_main,
        'page_size' => isset($component_options['page_size']) ? $component_options['page_size'] : false,
    ],
    'componentConnectionParams' => $connection_params,
    'componentOptions' => $component_options
];

$component_hash = Utils::hash($data->data, true);

$component_options_name = 'wp_shopify_component_options_' . $component_hash;

Options::update($component_options_name, $both);

?>

<div 
   data-wps-is-client-component-wrapper 
   data-wps-client-component-type="<?=$data->type;?>" 
   data-wps-component-options-id="<?=$component_hash;?>"
   data-wps-hide-component-wrapper="<?=$component_options['hide_wrapper']; ?>" 
   class="wps-client-component wps-container">

   <span class="wps-loading-placeholder"><?='Loading ' . $data->type . ' ⌛️ ...'?></span>

</div>