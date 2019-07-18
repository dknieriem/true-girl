<?php

namespace WPS\Render\Storefront;

defined('ABSPATH') ?: exit();

class Defaults
{
    public $DB_Settings_General;
    public $Render_Attributes;
    public $Products_Defaults;

    public function __construct($DB_Settings_General, $Render_Attributes, $Products_Defaults)
    {
        $this->DB_Settings_General = $DB_Settings_General;
        $this->Render_Attributes = $Render_Attributes;
        $this->Products_Defaults = $Products_Defaults;
    }

    public function storefront_query_attributes($attrs)
    {
        return [
         'query' => apply_filters('wps_storefront_query', $this->Render_Attributes->attr($attrs, 'query', '*')),
         'sort_by' => apply_filters('wps_storefront_sort_by', $this->Render_Attributes->attr($attrs, 'sort_by', 'TITLE')),
         'reverse' => apply_filters('wps_storefront_reverse', $this->Render_Attributes->attr($attrs, 'reverse', false)),
         'page_size' => apply_filters('wps_storefront_page_size', $this->Render_Attributes->attr($attrs, 'page_size', 10))
];
    }

    public function storefront_settings_attributes($attrs)
    {
        return [
         'show_tags' => apply_filters('wps_storefront_show_tags', $this->Render_Attributes->attr($attrs, 'show_tags', true)),
         'show_vendors' => apply_filters('wps_storefront_show_vendors', $this->Render_Attributes->attr($attrs, 'show_vendors', true)),
         'show_types' => apply_filters('wps_storefront_show_types', $this->Render_Attributes->attr($attrs, 'show_types', true)),
         'show_selections' => apply_filters('wps_storefront_show_selections', $this->Render_Attributes->attr($attrs, 'show_selections', true)),
         'show_sorting' => apply_filters('wps_storefront_show_sorting', $this->Render_Attributes->attr($attrs, 'show_sorting', true)),
         'show_pagination' => apply_filters('wps_storefront_show_pagination', $this->Render_Attributes->attr($attrs, 'show_pagination', true)),
         'show_options_heading' => apply_filters('wps_storefront_show_options_heading', $this->Render_Attributes->attr($attrs, 'show_options_heading', true))
      ];
    }

    public function storefront_component_attributes($attrs)
    {
        return [
         'render_from_server' => apply_filters('wps_storefront_render_from_server', $this->Render_Attributes->attr($attrs, 'render_from_server', false)),
         'dropzone_payload' => apply_filters('wps_storefront_dropzone_payload', $this->Render_Attributes->attr($attrs, 'dropzone_payload', false)),
         'dropzone_options' => apply_filters('wps_storefront_dropzone_options', $this->Render_Attributes->attr($attrs, 'dropzone_options', false)),
         'dropzone_selections' => apply_filters('wps_storefront_dropzone_selections', $this->Render_Attributes->attr($attrs, 'dropzone_selections', false)),
         'dropzone_sorting' => apply_filters('wps_storefront_dropzone_sorting', $this->Render_Attributes->attr($attrs, 'dropzone_sorting', true)),
         'dropzone_heading' => apply_filters('wps_storefront_dropzone_heading', $this->Render_Attributes->attr($attrs, 'dropzone_heading', false)),
         'dropzone_pagination' => apply_filters('wps_storefront_dropzone_pagination', $this->Render_Attributes->attr($attrs, 'dropzone_pagination', false)),
         'dropzone_page_size' => apply_filters('wps_storefront_dropzone_page_size', $this->Render_Attributes->attr($attrs, 'dropzone_page_size', true)),
         'dropzone_load_more' => apply_filters('wps_storefront_dropzone_load_more', $this->Render_Attributes->attr($attrs, 'dropzone_load_more', true)),
         'dropzone_loader' => apply_filters('wps_storefront_dropzone_loader', $this->Render_Attributes->attr($attrs, 'dropzone_loader', false)),
         'dropzone_notices' => apply_filters('wps_storefront_dropzone_notices', $this->Render_Attributes->attr($attrs, 'dropzone_notices', false)),
         'pagination' => apply_filters('wps_storefront_pagination', $this->Render_Attributes->attr($attrs, 'pagination', true)),
         'pagination_page_size' => apply_filters('wps_storefront_pagination_page_size', $this->Render_Attributes->attr($attrs, 'pagination_page_size', true)),
         'pagination_load_more' => apply_filters('wps_storefront_pagination_load_more', $this->Render_Attributes->attr($attrs, 'pagination_load_more', true)),
         'no_results_text' => apply_filters('wps_storefront_no_results_text', $this->Render_Attributes->attr($attrs, 'no_results_text', 'No results found')),
         'excludes' => apply_filters('wps_storefront_excludes', $this->Render_Attributes->attr($attrs, 'excludes', [])),
         'items_per_row' => apply_filters('wps_storefront_items_per_row', $this->Render_Attributes->attr($attrs, 'items_per_row', 3)),
         'limit' => apply_filters('wps_storefront_limit', $this->Render_Attributes->attr($attrs, 'limit', false)),
         'skip_initial_render' => apply_filters('wps_storefront_skip_initial_render', $this->Render_Attributes->attr($attrs, 'skip_initial_render', false)),
         'infinite_scroll' => apply_filters('wps_storefront_infinite_scroll', $this->Render_Attributes->attr($attrs, 'infinite_scroll', false)),
         'infinite_scroll_offset' => apply_filters('wps_storefront_infinite_scroll_offset', $this->Render_Attributes->attr($attrs, 'infinite_scroll_offset', -200)),
         'data_type' => apply_filters('wps_storefront_data_type', $this->Render_Attributes->attr($attrs, 'data_type', 'products')),
         'hide_wrapper' => apply_filters('wps_storefront_hide_wrapper', $this->Render_Attributes->attr($attrs, 'hide_wrapper', false))
         ];
    }

    public function all_storefront_attributes($attrs)
    {
        return array_merge(
            $this->Products_Defaults->products_settings_attributes($attrs),
            $this->storefront_query_attributes($attrs),
            $this->storefront_settings_attributes($attrs),
            $this->storefront_component_attributes($attrs)
      );
    }
    /*

     Default data for filters() template

     */
    public function storefront($attrs)
    {
        return $this->Render_Attributes->standardize_layout_data($this->all_storefront_attributes($attrs));
    }
}
