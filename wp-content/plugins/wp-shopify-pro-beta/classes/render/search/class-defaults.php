<?php

namespace WPS\Render\Search;

if (!defined('ABSPATH')) {
    exit();
}

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
    
    public function search_component_attributes($attrs)
    {
        return [
         'render_from_server' => apply_filters('wps_search_render_from_server', $this->Render_Attributes->attr($attrs, 'render_from_server', false)),
         'dropzone_form' => apply_filters('wps_search_dropzone_form', $this->Render_Attributes->attr($attrs, 'dropzone_form', false)),
         'dropzone_payload' => apply_filters('wps_search_dropzone_payload', $this->Render_Attributes->attr($attrs, 'dropzone_payload', false)),
         'dropzone_loader' => apply_filters('wps_search_dropzone_loader', $this->Render_Attributes->attr($attrs, 'dropzone_loader', false)),
         'dropzone_options' => apply_filters('wps_search_dropzone_options', $this->Render_Attributes->attr($attrs, 'dropzone_options', false)),
         'dropzone_sorting' => apply_filters('wps_search_dropzone_sorting', $this->Render_Attributes->attr($attrs, 'dropzone_sorting', false)),
         'dropzone_heading' => apply_filters('wps_search_dropzone_heading', $this->Render_Attributes->attr($attrs, 'dropzone_heading', false)),
         'dropzone_pagination' => apply_filters('wps_search_dropzone_pagination', $this->Render_Attributes->attr($attrs, 'dropzone_pagination', false)),
         'dropzone_page_size' => apply_filters('wps_search_dropzone_page_size', $this->Render_Attributes->attr($attrs, 'dropzone_page_size', false)),
         'dropzone_load_more' => apply_filters('wps_search_dropzone_load_more', $this->Render_Attributes->attr($attrs, 'dropzone_load_more', false)),
         'pagination' => apply_filters('wps_search_pagination', $this->Render_Attributes->attr($attrs, 'pagination', false)),
         'pagination_page_size' => apply_filters('wps_search_pagination_page_size', $this->Render_Attributes->attr($attrs, 'pagination_page_size', false)),
         'pagination_load_more' => apply_filters('wps_search_pagination_load_more', $this->Render_Attributes->attr($attrs, 'pagination_load_more', true)),
         'pagination_hide_initial' => apply_filters('wps_search_pagination_hide_initial', $this->Render_Attributes->attr($attrs, 'pagination_hide_initial', true)),
         'show_pagination' => apply_filters('wps_search_show_pagination', $this->Render_Attributes->attr($attrs, 'show_pagination', false)),
         'no_results_text' => apply_filters('wps_search_no_results_text', $this->Render_Attributes->attr($attrs, 'no_results_text', 'No search results found')),
         'excludes' => apply_filters('wps_search_excludes', $this->Render_Attributes->attr($attrs, 'excludes', [])),
         'connective' => strtoupper(apply_filters('wps_search_connective', $this->Render_Attributes->attr($attrs, 'connective', 'AND'))),
         'items_per_row' => apply_filters('wps_search_items_per_row', $this->Render_Attributes->attr($attrs, 'items_per_row', 3)),
         'limit' => apply_filters('wps_search_limit', $this->Render_Attributes->attr($attrs, 'limit', false)),
         'skip_initial_render' => apply_filters('wps_search_skip_initial_render', $this->Render_Attributes->attr($attrs, 'skip_initial_render', true)),
         'infinite_scroll' => apply_filters('wps_search_infinite_scroll', $this->Render_Attributes->attr($attrs, 'infinite_scroll', false)),
         'infinite_scroll_offset' => apply_filters('wps_search_infinite_scroll_offset', $this->Render_Attributes->attr($attrs, 'infinite_scroll_offset', -200)),
         'data_type' => apply_filters('wps_search_data_type', $this->Render_Attributes->attr($attrs, 'data_type', 'products')),
         'hide_wrapper' => apply_filters('wps_search_hide_wrapper', $this->Render_Attributes->attr($attrs, 'hide_wrapper', false))
      ];
    }

    public function search_query_attributes($attrs)
    {

       $search_by = $this->DB_Settings_General->get_col_value('search_by', 'string');

       if (!empty($attrs['sort_by'])) {
         $search_by = $attrs['sort_by'];
       }
       
       if (!empty($attrs['page_size'])) {
         $page_size = $attrs['page_size'];
       } else {
          $page_size = 10;
       }

        return [
         'query' => apply_filters('wps_search_query', $this->Render_Attributes->attr($attrs, 'query', '*')),
         'sort_by' => apply_filters('wps_search_sort_by', $this->Render_Attributes->attr($attrs, 'sort_by', $search_by)),
         'reverse' => apply_filters('wps_search_reverse', $this->Render_Attributes->attr($attrs, 'reverse', false)),
         'page_size' => apply_filters('wps_search_page_size', $this->Render_Attributes->attr($attrs, 'page_size', $page_size))
      ];
    }


    public function all_search_attributes($attrs)
    {
        return array_merge(
            $this->Products_Defaults->products_settings_attributes($attrs),
            $this->search_query_attributes($attrs),
            $this->search_component_attributes($attrs)
       );
    }

    /*

     Default data for search() template

     */
    public function search($attrs)
    {
        return $this->Render_Attributes->standardize_layout_data($this->all_search_attributes($attrs));
    }
}