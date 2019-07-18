<?php

namespace WPS\Render\Products;

use WPS\Utils\Data;

if (!defined('ABSPATH')) {
    exit();
}

class Defaults
{
    public $plugin_settings;
    public $Render_Attributes;

    public function __construct($plugin_settings, $Render_Attributes)
    {
        $this->plugin_settings = $plugin_settings;
        $this->Render_Attributes = $Render_Attributes;
    }

    public function get_products_filter_params_from_shortcode($attrs)
    {
        return [
            'available_for_sale' => isset($attrs['available_for_sale']) ? $attrs['available_for_sale'] : 'any',
            'created_at' => isset($attrs['created_at']) ? $attrs['created_at'] : false,
            'product_type' => isset($attrs['product_type']) ? $attrs['product_type'] : false,
            'tag' => isset($attrs['tag']) ? $attrs['tag'] : false,
            'title' => isset($attrs['title']) ? $attrs['title'] : false,
            'updated_at' => isset($attrs['updated_at']) ? $attrs['updated_at'] : false,
            'variants_price' => isset($attrs['variants_price']) ? $attrs['variants_price'] : false,
            'vendor' => isset($attrs['vendor']) ? $attrs['vendor'] : false,
            'id' => isset($attrs['product_id']) ? $attrs['product_id'] : false,
        ];
    }

    public function create_product_query($all_attrs)
    {

        $filter_params = $this->get_products_filter_params_from_shortcode($this->Render_Attributes->format_shortcode_attrs($all_attrs));

        if (!isset($all_attrs['connective'])) {

            $defaults = $this->products_component_attributes($all_attrs);

            if (empty($all_attrs)) {
               $all_attrs = [];
            }

            $all_attrs['connective'] = strtoupper($defaults['connective']);
            
        }

        return $this->Render_Attributes->build_query($filter_params, $all_attrs);
    }

    public function product_add_to_cart($attrs)
    {
        return $this->Render_Attributes->standardize_layout_data(
            array_merge(
                $this->all_products_attributes($attrs),
                ['excludes' => apply_filters('wps_products_excludes', $this->Render_Attributes->attr($attrs, 'excludes', ['title', 'pricing', 'description', 'images']))]
           )
        );
    }

    public function product_buy_button($attrs)
    {
        return $this->product_add_to_cart($attrs);
    }

    public function product_title($attrs)
    {
        return $this->Render_Attributes->standardize_layout_data(
            array_merge(
                $this->all_products_attributes($attrs),
                ['excludes' => apply_filters('wps_products_excludes', $this->Render_Attributes->attr($attrs, 'excludes', ['description', 'buy-button', 'images', 'pricing']))]
           )

        );
    }

    public function product_description($attrs)
    {
        return $this->Render_Attributes->standardize_layout_data(
            array_merge(
                $this->all_products_attributes($attrs),
                ['excludes' => apply_filters('wps_products_excludes', $this->Render_Attributes->attr($attrs, 'excludes', ['title', 'buy-button', 'images', 'pricing']))]
           )

        );
    }

    public function product_pricing($attrs)
    {
        return $this->Render_Attributes->standardize_layout_data(
            array_merge(
                $this->all_products_attributes($attrs),
                ['excludes' => apply_filters('wps_products_excludes', $this->Render_Attributes->attr($attrs, 'excludes', ['title', 'buy-button', 'images', 'description']))]
           )

        );
    }

    public function product_gallery($attrs)
    {
        return $this->Render_Attributes->standardize_layout_data(
            array_merge(
                $this->all_products_attributes($attrs),
                ['excludes' => apply_filters('wps_products_excludes', $this->Render_Attributes->attr($attrs, 'excludes', ['title', 'pricing', 'description', 'buy-button']))]
           )
        );
    }

    public function products($attrs)
    {
        return $this->Render_Attributes->standardize_layout_data($this->all_products_attributes($attrs));
    }

    public function products_settings_attributes($attrs)
    {

      
        return [
         'add_to_cart_button_color' => apply_filters('wps_products_add_to_cart_button_color', $this->Render_Attributes->attr($attrs, 'add_to_cart_button_color', Data::coerce($this->plugin_settings['general']->add_to_cart_color, 'string'))),
         'variant_button_color' => apply_filters('wps_products_variant_button_color', $this->Render_Attributes->attr($attrs, 'variant_button_color', Data::coerce($this->plugin_settings['general']->variant_color, 'string'))),
         'hide_quantity' => apply_filters('wps_products_hide_quantity', $this->Render_Attributes->attr($attrs, 'hide_quantity', false)),
         'add_to_cart_button_text' => apply_filters('wps_products_add_to_cart_button_text', $this->Render_Attributes->attr($attrs, 'add_to_cart_button_text', WPS_DEFAULT_ADD_TO_CART_TEXT)),
         'min_quantity' => apply_filters('wps_products_min_quantity', $this->Render_Attributes->attr($attrs, 'min_quantity', 1)),
         'max_quantity' => apply_filters('wps_products_max_quantity', $this->Render_Attributes->attr($attrs, 'max_quantity', false)),
         'show_quantity_label' => apply_filters('wps_products_show_quantity_label', $this->Render_Attributes->attr($attrs, 'show_quantity_label', true)),
         'quantity_label_text' => apply_filters('wps_products_quantity_label_text', $this->Render_Attributes->attr($attrs, 'quantity_label_text', 'Quantity')),
         'show_price_range' => apply_filters('wps_products_show_price_range', $this->Render_Attributes->attr($attrs, 'show_price_range', Data::coerce($this->plugin_settings['general']->products_show_price_range, 'bool'))),
         'show_compare_at' => apply_filters('wps_products_show_compare_at', $this->Render_Attributes->attr($attrs, 'show_compare_at', Data::coerce($this->plugin_settings['general']->products_compare_at, 'bool'))),
         'show_featured_only' => apply_filters('wps_products_show_featured_only', $this->Render_Attributes->attr($attrs, 'show_featured_only', false)),
         'show_zoom' => apply_filters('wps_products_show_zoom', $this->Render_Attributes->attr($attrs, 'show_zoom', null))
      ];


    }

    public function products_query_attributes($attrs)
    {
        return [
         'query' => apply_filters('wps_products_query', $this->Render_Attributes->attr($attrs, 'query', $this->create_product_query($attrs))),
         'sort_by' => apply_filters('wps_products_sort_by', $this->Render_Attributes->attr($attrs, 'sort_by', 'TITLE')),
         'reverse' => apply_filters('wps_products_reverse', $this->Render_Attributes->attr($attrs, 'reverse', false)),
         'page_size' => apply_filters('wps_products_page_size', $this->Render_Attributes->attr($attrs, 'page_size', Data::coerce($this->plugin_settings['general']->num_posts, 'int'))),
      ];
    }

    public function products_component_attributes($attrs)
    {

        return [
            'product' => apply_filters('wps_products_product', $this->Render_Attributes->attr($attrs, 'product', false)),
            'product_id' => apply_filters('wps_products_product_id', $this->Render_Attributes->attr($attrs, 'product_id', false)),
            'post_id' => apply_filters('wps_products_post_id', $this->Render_Attributes->attr($attrs, 'post_id', false)),
            'available_for_sale' => apply_filters('wps_products_available_for_sale', $this->Render_Attributes->attr($attrs, 'available_for_sale', 'any')),
            'created_at' => apply_filters('wps_products_created_at', $this->Render_Attributes->attr($attrs, 'created_at', false)),
            'product_type' => apply_filters('wps_products_product_type', $this->Render_Attributes->attr($attrs, 'product_type', false)),
            'tag' => apply_filters('wps_products_tag', $this->Render_Attributes->attr($attrs, 'tag', false)),
            'title' => apply_filters('wps_products_query_title', $this->Render_Attributes->attr($attrs, 'title', false)),
            'post_meta' => apply_filters('wps_products_post_meta', $this->Render_Attributes->attr($attrs, 'post_meta', false)),
            'updated_at' => apply_filters('wps_products_query_updated_at', $this->Render_Attributes->attr($attrs, 'updated_at', false)),
            'variants_price' => apply_filters('wps_products_variants_price', $this->Render_Attributes->attr($attrs, 'variants_price', false)),
            'vendor' => apply_filters('wps_products_vendor', $this->Render_Attributes->attr($attrs, 'vendor', false)),
            'connective' => apply_filters('wps_products_connective', $this->Render_Attributes->attr($attrs, 'connective', 'OR')),
            'render_from_server' => apply_filters('wps_products_render_from_server', $this->Render_Attributes->attr($attrs, 'render_from_server', false)),
            'limit' => apply_filters('wps_products_limit', $this->Render_Attributes->attr($attrs, 'limit', false)),
            'random' => apply_filters('wps_products_random', $this->Render_Attributes->attr($attrs, 'random', false)),
            'excludes' => apply_filters('wps_products_excludes', $this->Render_Attributes->attr($attrs, 'excludes', [])),
            'items_per_row' => apply_filters('wps_products_items_per_row', $this->Render_Attributes->attr($attrs, 'items_per_row', 3)),
            'no_results_text' => apply_filters('wps_products_no_results_text', $this->Render_Attributes->attr($attrs, 'no_results_text', 'No products found')),
            'pagination' => apply_filters('wps_products_pagination', $this->Render_Attributes->attr($attrs, 'pagination', true)),
            'pagination_page_size' => apply_filters('wps_products_pagination_page_size', $this->Render_Attributes->attr($attrs, 'pagination_page_size', false)),
            'pagination_load_more' => apply_filters('wps_products_pagination_load_more', $this->Render_Attributes->attr($attrs, 'pagination_load_more', true)),
            'dropzone_pagination' => apply_filters('wps_products_dropzone_pagination', $this->Render_Attributes->attr($attrs, 'dropzone_pagination', false)),
            'dropzone_page_size' => apply_filters('wps_products_dropzone_page_size', $this->Render_Attributes->attr($attrs, 'dropzone_page_size', false)),
            'dropzone_load_more' => apply_filters('wps_products_dropzone_load_more', $this->Render_Attributes->attr($attrs, 'dropzone_load_more', false)),
            'dropzone_product_buy_button' => apply_filters('wps_products_dropzone_product_buy_button', $this->Render_Attributes->attr($attrs, 'dropzone_product_buy_button', false)),
            'dropzone_product_title' => apply_filters('wps_products_dropzone_product_title', $this->Render_Attributes->attr($attrs, 'dropzone_product_title', false)),
            'dropzone_product_description' => apply_filters('wps_products_dropzone_product_description', $this->Render_Attributes->attr($attrs, 'dropzone_product_description', false)),
            'dropzone_product_pricing' => apply_filters('wps_products_dropzone_product_pricing', $this->Render_Attributes->attr($attrs, 'dropzone_product_pricing', false)),
            'dropzone_product_gallery' => apply_filters('wps_products_dropzone_product_gallery', $this->Render_Attributes->attr($attrs, 'dropzone_product_gallery', false)),
            'skip_initial_render' => apply_filters('wps_products_skip_initial_render', $this->Render_Attributes->attr($attrs, 'skip_initial_render', false)),
            'data_type' => apply_filters('wps_products_data_type', $this->Render_Attributes->attr($attrs, 'data_type', 'products')),
            'infinite_scroll' => apply_filters('wps_products_infinite_scroll', $this->Render_Attributes->attr($attrs, 'infinite_scroll', false)),
            'infinite_scroll_offset' => apply_filters('wps_products_infinite_scroll_offset', $this->Render_Attributes->attr($attrs, 'infinite_scroll_offset', -200)),
            'is_singular' => is_singular(WPS_PRODUCTS_POST_TYPE_SLUG),
            'hide_wrapper' => apply_filters('wps_products_hide_wrapper', $this->Render_Attributes->attr($attrs, 'hide_wrapper', false))
       ];
    }

    public function all_products_attributes($attrs)
    {
        return array_merge(
            $this->products_query_attributes($attrs),
            $this->products_settings_attributes($attrs),
            $this->products_component_attributes($attrs)
        );

    }
}