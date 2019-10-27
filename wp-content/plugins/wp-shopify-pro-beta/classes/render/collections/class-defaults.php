<?php

namespace WPS\Render\Collections;

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

    public function create_collections_query($all_attrs)
    {
        $filter_params = $this->get_collections_filter_params_from_shortcode($this->Render_Attributes->format_shortcode_attrs($all_attrs));

         if (!isset($all_attrs['connective'])) {
            $defaults = $this->collections_component_attributes($all_attrs);

            if (empty($all_attrs)) {
               $all_attrs = [];
            }
            
            $all_attrs['connective'] = strtoupper($defaults['connective']);
        }

        return $this->Render_Attributes->build_query($filter_params, $all_attrs);
    }

    public function get_collections_filter_params_from_shortcode($attrs)
    {
        return [
            'updated_at' => isset($attrs['updated_at']) ? $attrs['updated_at'] : false,
            'title' => isset($attrs['title']) ? $attrs['title'] : false,
            'collection_type' =>  isset($attrs['collection_type']) ? $attrs['collection_type'] : false,
        ];
    }

    public function collections_query_attributes($attrs)
    {
        return [
         'query' => apply_filters('wps_collections_query', $this->create_collections_query($attrs)),
         'sort_by' => apply_filters('wps_collections_sort_by', $this->Render_Attributes->attr($attrs, 'sort_by', 'TITLE')),
         'reverse' => apply_filters('wps_collections_reverse', $this->Render_Attributes->attr($attrs, 'reverse', false)),
         'page_size' => apply_filters('wps_collections_page_size', $this->Render_Attributes->attr($attrs, 'page_size', 10))
      ];
    }

    public function collections_settings_attributes($attrs)
    {
        return [
         'single' => apply_filters('wps_collections_single', $this->Render_Attributes->attr($attrs, 'single', false)),
         'collection_type' => apply_filters('wps_collections_query_collection_type', $this->Render_Attributes->attr($attrs, 'collection_type', false))
       ];
    }

    public function collections_component_attributes($attrs)
    {
        return [
          'collection_id' => apply_filters('wps_collections_query_collection_id', $this->Render_Attributes->attr($attrs, 'collection_id', false)),
         'post_id' => apply_filters('wps_collections_post_id', $this->Render_Attributes->attr($attrs, 'post_id', false)),
         'connective' => apply_filters('wps_collections_connective', $this->Render_Attributes->attr($attrs, 'connective', 'AND')),
         'title' => apply_filters('wps_collections_query_title', $this->Render_Attributes->attr($attrs, 'title', false)),
         'render_from_server' => apply_filters('wps_collections_render_from_server', $this->Render_Attributes->attr($attrs, 'render_from_server', false)),
         'updated_at' => apply_filters('wps_collections_query_updated_at', $this->Render_Attributes->attr($attrs, 'updated_at', false)),
         'collection' => apply_filters('wps_collections_collection', $this->Render_Attributes->attr($attrs, 'collection', false)),
         'items_per_row' => apply_filters('wps_collections_items_per_row', $this->Render_Attributes->attr($attrs, 'items_per_row', 4)),
         'limit' => apply_filters('wps_collections_limit', $this->Render_Attributes->attr($attrs, 'limit', false)),
         'post_meta' => apply_filters('wps_collections_post_meta', $this->Render_Attributes->attr($attrs, 'post_meta', false)),
         'excludes' => apply_filters('wps_collections_excludes', $this->Render_Attributes->attr($attrs, 'excludes', [])),
         'pagination' => apply_filters('wps_collections_pagination', $this->Render_Attributes->attr($attrs, 'pagination', true)),
         'pagination_page_size' => apply_filters('wps_collections_pagination_page_size', $this->Render_Attributes->attr($attrs, 'pagination_page_size', false)),
         'pagination_load_more' => apply_filters('wps_collections_pagination_load_more', $this->Render_Attributes->attr($attrs, 'pagination_load_more', true)),
         'dropzone_pagination' => apply_filters('wps_collections_dropzone_pagination', $this->Render_Attributes->attr($attrs, 'dropzone_pagination', false)),
         'dropzone_page_size' => apply_filters('wps_collections_dropzone_page_size', $this->Render_Attributes->attr($attrs, 'dropzone_page_size', false)),
         'dropzone_load_more' => apply_filters('wps_collections_dropzone_load_more', $this->Render_Attributes->attr($attrs, 'dropzone_load_more', false)),
         'skip_initial_render' => apply_filters('wps_collections_skip_initial_render', $this->Render_Attributes->attr($attrs, 'skip_initial_render', false)),
         'dropzone_collection_title' => apply_filters('wps_collections_dropzone_collection_title', $this->Render_Attributes->attr($attrs, 'dropzone_collection_title', false)),
         'dropzone_collection_image' => apply_filters('wps_collections_dropzone_collection_image', $this->Render_Attributes->attr($attrs, 'dropzone_collection_image', false)),
         'dropzone_collection_description' => apply_filters('wps_collections_dropzone_collection_description', $this->Render_Attributes->attr($attrs, 'dropzone_collection_description', false)),
         'dropzone_collection_products' => apply_filters('wps_collections_dropzone_collection_products', $this->Render_Attributes->attr($attrs, 'dropzone_collection_products', false)),
         'infinite_scroll' => apply_filters('wps_collections_infinite_scroll', $this->Render_Attributes->attr($attrs, 'infinite_scroll', false)),
         'infinite_scroll_offset' => apply_filters('wps_collections_infinite_scroll_offset', $this->Render_Attributes->attr($attrs, 'infinite_scroll_offset', -200)),
         'data_type' => apply_filters('wps_collections_data_type', $this->Render_Attributes->attr($attrs, 'data_type', 'collections')),
         'is_singular' => is_singular(WPS_COLLECTIONS_POST_TYPE_SLUG),
         'hide_wrapper' => apply_filters('wps_collections_hide_wrapper', $this->Render_Attributes->attr($attrs, 'hide_wrapper', false))
       ];
    }
    
    public function all_collections_attributes($attrs)
    {
        if (empty($attrs['products'])) {
            $attrs_prods = [
               'sort_by' => 'collection_default',
               'reverse' => false,
               'page_size' => 10,
               'query' => ''
            ];
        } else {
            $attrs_prods = $attrs['products'];
        }

        return array_merge(
            ['products' => $this->Products_Defaults->all_products_attributes($attrs_prods)],
            $this->collections_query_attributes($attrs),
            $this->collections_settings_attributes($attrs),
            $this->collections_component_attributes($attrs)
         );
    }
    
 
    public function collections($attrs)
    {
        return $this->Render_Attributes->standardize_layout_data($this->all_collections_attributes($attrs));
    }
}