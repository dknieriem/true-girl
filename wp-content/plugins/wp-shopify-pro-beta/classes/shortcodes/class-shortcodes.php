<?php

namespace WPS;

use WPS\Options;
use WPS\Utils;

defined('ABSPATH') ?: exit();

class Shortcodes
{
    public $Shortcodes_Data;
    public $Render_Products;
    public $Defaults_Products;
    public $Render_Cart;
    public $Defaults_Cart;
    public $DB_Products;
    public $DB_Tags;
    public $Render_Search;
    public $Defaults_search;
    public $Render_Storefront;
    public $Defaults_storefront;
    public $Render_Collections;
    public $Defaults_Collections;
    public $DB_Collections;

    /*

    Initialize the class and set its properties.

     */
    public function __construct(
        $Shortcodes_Data,
        $Render_Products,
        $Defaults_Products,
        $Render_Cart,
        $Defaults_Cart,
        $DB_Products,
        $DB_Tags,
        $Render_Search,
        $Defaults_search,
        $Render_Storefront,
        $Defaults_storefront,
        $Render_Collections,
        $Defaults_Collections,
        $DB_Collections
    ) {
        $this->Shortcodes_Data = $Shortcodes_Data;
        $this->Render_Products = $Render_Products;
        $this->Render_Cart = $Render_Cart;
        $this->Defaults_Products = $Defaults_Products;
        $this->Defaults_Cart = $Defaults_Cart;
        $this->DB_Products = $DB_Products;
        $this->DB_Tags = $DB_Tags;
        $this->Render_Search = $Render_Search;
        $this->Defaults_search = $Defaults_search;
        $this->Render_Storefront = $Render_Storefront;
        $this->Defaults_storefront = $Defaults_storefront;
        $this->Render_Collections = $Render_Collections;
        $this->Defaults_Collections = $Defaults_Collections;
        $this->DB_Collections = $DB_Collections;
    }


    public function shortcode($fn)
    {
        ob_start();

        $fn();

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public function combine_user_and_default_atts($atts, $defaults_fn)
    {
        return shortcode_atts($defaults_fn(), $atts);
    }

    public function gather_shortcode_atts($atts, $defaults_method)
    {
        $combined = $this->combine_user_and_default_atts($atts, function () use ($atts, $defaults_method) {
            return $defaults_method($atts);
        });

        return $this->Shortcodes_Data->standardize_layout_data($combined);
    }

    public function attr_to_bool($attr_val)
    {
        if ($attr_val === 'true' || $attr_val == 1) {
            return true;
        }

        return false;
    }

    public function attr_to_int($attr_val)
    {
        return (int) $attr_val;
    }

    public function attr_to_string($attr_val)
    {
        if (is_array($attr_val)) {
            return $attr_val;
        }

        if (is_bool($attr_val)) {
            return ($attr_val) ? 'true' : 'false';
        }

        return (string) $attr_val;
    }

    public function get_attributes($atts, $class, $method)
    {
        return $this->gather_shortcode_atts($atts, [$class, $method]);
    }

    public function get_ids_from_shortcode($atts, $type)
    {
        return $this->shortcode_filters($atts, $type);
    }

    public function normalize_ids($ids)
    {
        if (empty($ids)) {
            return [];
        }

        if (!is_array($ids)) {
            return [$ids];
        }

        return $ids;
    }

    public function refine_product_ids($ids)
    {
        return array_unique(array_merge($ids['title'], $ids['vendor'], $ids['description'], $ids['product_type'], $ids['slug'], $ids['tag'], $ids['product_id'], $ids['post_id']), SORT_REGULAR);
    }

    public function refine_collection_ids($ids)
    {
        return array_unique(array_merge($ids['title'], $ids['description'], $ids['collection_id'], $ids['post_id']), SORT_REGULAR);
    }

    public function get_ids_from_collection_shortcode($atts)
    {
        $ids = [];
        $ids['title'] = $this->DB_Collections->get_collection_ids_from_titles($atts['title']);
        $ids['description'] = $this->DB_Collections->get_collection_ids_from_description($atts['description']);
        $ids['post_id'] = $this->DB_Collections->get_collection_ids_from_post_ids($atts['post_id']);
        $ids['collection_id'] = $this->normalize_ids($atts['collection_id']);

        return $this->refine_collection_ids($ids);
    }

    public function get_ids_from_product_shortcode($atts)
    {

        $ids = [];

         if (!$this->has_filter_params_product_attrs($atts)) {
            $ids['product_id'] = [];
         } else {
            $ids['product_id'] = $this->normalize_ids(!empty($atts['product_id']) ? $atts['product_id'] : false);
         }

         $ids['title'] = $this->DB_Products->get_product_ids_from_titles(!empty($atts['title']) ? $atts['title'] : false);
         $ids['vendor'] = $this->DB_Products->get_product_ids_from_vendors(!empty($atts['vendor']) ? $atts['vendor'] : false);
         $ids['description'] = $this->DB_Products->get_product_ids_from_description(!empty($atts['description']) ? $atts['description'] : false);
         $ids['product_type'] = $this->DB_Products->get_product_ids_from_types(!empty($atts['product_type']) ? $atts['product_type'] : false);
         $ids['slug'] = $this->DB_Products->get_product_ids_from_handles(!empty($atts['slug']) ? $atts['slug'] : false);
         $ids['tag'] = $this->DB_Tags->get_product_ids_from_tag(!empty($atts['tag']) ? $atts['tag'] : false);
         $ids['post_id'] = $this->DB_Products->get_product_ids_from_post_ids(!empty($atts['post_id']) ? $atts['post_id'] : false);

        return $this->refine_product_ids($ids);
    }

    public function shortcode_filters($atts, $type)
    {
        switch ($type) {
            case 'products':
                return $this->get_ids_from_product_shortcode($atts);
                break;
            case 'collections':
                return $this->get_ids_from_collection_shortcode($atts);
                break;
            default:
                # code...
                break;
        }
    }

    /*

    Responsible for processing the [wps_products_title] shortcode

     */
    public function shortcode_wps_products_title($shortcode_atts)
    {
        return $this->shortcode(function () use ($shortcode_atts) {
            $this->Render_Products->title($this->Defaults_Products->product_title($shortcode_atts));
        });
    }

    public function shortcode_wps_products_description($shortcode_atts)
    {
        return $this->shortcode(function () use ($shortcode_atts) {
            $this->Render_Products->description($this->Defaults_Products->product_description($shortcode_atts));
        });
    }

    public function shortcode_wps_products_pricing($shortcode_atts)
    {
        return $this->shortcode(function () use ($shortcode_atts) {
            $this->Render_Products->pricing($this->Defaults_Products->product_pricing($shortcode_atts));
        });
    }

    public function shortcode_wps_products_buy_button($shortcode_atts)
    {
        return $this->shortcode(function () use ($shortcode_atts) {
            $this->Render_Products->buy_button($this->Defaults_Products->product_buy_button($shortcode_atts));
        });
    }

        /*

    Responsible for processing the [wps_products] shortcode

     */
    public function shortcode_wps_products($shortcode_atts)
    {
        return $this->shortcode(function () use ($shortcode_atts) {
            $this->Render_Products->products($this->Defaults_Products->products($shortcode_atts));
        });
    }

    public function shortcode_wps_product_gallery($shortcode_atts)
    {
        return $this->shortcode(function () use ($shortcode_atts) {
            $this->Render_Products->gallery($this->Defaults_Products->product_gallery($shortcode_atts));
        });
    }

    public function shortcode_wps_cart_icon($shortcode_atts)
    {
        return $this->shortcode(function () use ($shortcode_atts) {
            $this->Render_Cart->cart_icon($this->Defaults_Cart->cart_icon($shortcode_atts));
        });
    }

    public function shortcode_wps_search($shortcode_atts)
    {
        return $this->shortcode(function () use ($shortcode_atts) {
            $this->Render_Search->search($this->Defaults_search->search($shortcode_atts));
        });
    }

    public function shortcode_wps_storefront($shortcode_atts)
    {
        return $this->shortcode(function () use ($shortcode_atts) {
            $this->Render_Storefront->storefront($this->Defaults_storefront->storefront($shortcode_atts));
        });
    }

    public function product_filter_params()
    {
        return ['title', 'vendor', 'description', 'product_type', 'slug', 'tag', 'product_id', 'post_id'];
    }

    public function has_filter_params_product_attrs($shortcode_attrs)
    {
        return $this->has_filter_params($shortcode_attrs, $this->product_filter_params());
    }

    public function accumulating_collection_attrs()
    {
        return ['title', 'description', 'collection_id', 'post_id'];
    }

    public function has_filter_params($shortcode_attrs, $accumulation_keys)
    {
        $found = false;

        if (empty($shortcode_attrs)) {
            return $found;
        }

        foreach ($shortcode_attrs as $key => $value) {
            if (in_array($key, $accumulation_keys)) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    public function has_accumulating_collection_attrs($shortcode_attrs)
    {
        return $this->has_filter_params($shortcode_attrs, $this->accumulating_collection_attrs());
    }

    public function get_collections_filter_params_from_shortcode($attrs)
    {
        return [
            'updated_at' => $attrs['updated_at'],
            'title' => $attrs['title'],
            'collection_type' => $attrs['collection_type'],
        ];
    }

    public function get_products_filter_params_from_shortcode($attrs)
    {
        return [
            'available_for_sale' => $attrs['available_for_sale'],
            'created_at' => $attrs['created_at'],
            'product_type' => $attrs['product_type'],
            'tag' => $attrs['tag'],
            'title' => $attrs['title'],
            'updated_at' => $attrs['updated_at'],
            'variants_price' => $attrs['variants_price'],
            'vendor' => $attrs['vendor'],
            'id' => $attrs['product_id'],
        ];
    }



    public function get_ids_from_collection_defaults($limit)
    {
        return $this->DB_Collections->get_collection_ids($limit);
    }

    public function add_boolean_to_query($key, $val)
    {
        if (is_bool($val)) {
            $bool_converted = ($val) ? 'true' : 'false';
        } else {
            $bool_converted = $val;
        }

        return $key . ':' . $bool_converted;
    }

    /*

    Defaults to a phrase query which surrounds each term in double quotes

     */
    public function add_string_to_query($key, $val)
    {
        return $key . ':' . '"' . $val . '"';
    }

    public function query_checks($key, $val, $query)
    {
        if (is_bool($val) || $val === 'true' || $val === 'false') {
            $query .= $this->add_boolean_to_query($key, $val);
        } else {
            $query .= $this->add_string_to_query($key, $val);
        }

        return $query;
    }

    public function add_nested_query($key, $values, $all_attrs)
    {
        $query = '';

        foreach ($values as $val) {
            $query = $this->query_checks($key, $val, $query);

            if ($val !== end($values)) {
                $query .= ' ' . strtoupper($all_attrs['connective']) . ' ';
            }
        }

        return $query;
    }

    public function build_query($filter_params, $all_attrs)
    {

        if (!array_filter($filter_params)) {
            return $all_attrs['query']; // Returns the default query instead
        }

        $query = '';
        $valid_filter_params = array_filter($filter_params);

        foreach ($valid_filter_params as $key => $value) {
            if (is_array($value)) {
                $query .= $this->add_nested_query($key, $value, $all_attrs);
            } else {
                $query = $this->query_checks($key, $value, $query);
            }

            if ($value !== end($valid_filter_params)) {
                $query .= ' ' . strtoupper($all_attrs['connective']) . ' ';
            }
        }

        return $query;
    }

    public function create_collections_query($all_attrs)
    {
        $filter_params = $this->get_collections_filter_params_from_shortcode($all_attrs);

        return $this->build_query($filter_params, $all_attrs);
    }

    public function create_product_query($all_attrs)
    {
        $filter_params = $this->get_products_filter_params_from_shortcode($all_attrs);

        return $this->build_query($filter_params, $all_attrs);
    }

    public function get_default_product_query_params($all_shortcode_attrs)
    {

        // return $this->DB_Products->get_product_ids($limit);
        return [
            'query' => $all_shortcode_attrs['query_params']['query'],
            'first' => $all_shortcode_attrs['query_params']['page_size'],
            'reverse' => $all_shortcode_attrs['query_params']['reverse'],
            'sort_by' => $all_shortcode_attrs['query_params']['sort_by'],
        ];
    }

    public function find_id_from_single_collection($shortcode_atts, $all_atts)
    {
        if (!empty($shortcode_atts['collection_id'])) {
            return $shortcode_atts['collection_id'][0];
        }

        $ids = $this->get_ids_from_shortcode($all_atts, 'collections');

        if (count($ids) > 1) {
            return [$ids[0]];
        }

        return $ids;
    }

    public function gather_products_attrs($shortcode_atts)
    {
        $products_only_attrs = [];

        if (!empty($shortcode_atts)) {
            foreach ($shortcode_atts as $key => $value) {
                if (strpos($key, 'products_') !== false) {
                    $products_only_attrs[$key] = $value;
                }
            }
        }

        return $products_only_attrs;
    }

    public function add_products_attrs_to_collections($all_atts, $products_only_attrs)
    {
        if (empty($products_only_attrs)) {

            return $all_atts;
        } else {
            foreach ($products_only_attrs as $key => $value) {
                $without_prefix = str_replace('products_', '', $key);
                $all_atts['products'][$without_prefix] = $value;
            }
        }
        
        return $all_atts;
    }

    public function normalize_collections_attributes($shortcode_atts)
    {
        $all_atts = $this->Defaults_Collections->collections($shortcode_atts);

        $products_only_attrs = $this->gather_products_attrs($shortcode_atts);

        $all_atts = $this->add_products_attrs_to_collections($all_atts, $products_only_attrs);

        if (isset($all_atts['single'])) {
            $single = $this->attr_to_bool($all_atts['single']);
        } else {
            $single = false;
        }

        if (is_array($all_atts)) {
            $all_atts['single'] = $single;

            if (isset($all_atts['items_per_row'])) {
                if ($single) {
                    $all_atts['items_per_row'] = 1;
                } else {
                    $all_atts['items_per_row'] = $this->attr_to_int($all_atts['items_per_row']);
                }
            }
        }

        return $all_atts;
    }

    public function shortcode_wps_collections($shortcode_atts)
    {
        $all_atts = $this->normalize_collections_attributes($shortcode_atts);
        
        return $this->shortcode(function () use ($all_atts) {
            $this->Render_Collections->collections($all_atts);
        });
    }

    public function wps_cart()
    {
        $this->Render_Cart->cart_icon([
            'type' => 'fixed',
        ]);
    }


    public function hooks()
    {
        add_shortcode('wps_products', [$this, 'shortcode_wps_products']);
        add_shortcode('wps_products_title', [$this, 'shortcode_wps_products_title']);
        add_shortcode('wps_products_description', [$this, 'shortcode_wps_products_description']);
        add_shortcode('wps_products_pricing', [$this, 'shortcode_wps_products_pricing']);
        add_shortcode('wps_products_buy_button', [$this, 'shortcode_wps_products_buy_button']);
        add_shortcode('wps_products_gallery', [$this, 'shortcode_wps_product_gallery']);
        add_shortcode('wps_collections', [$this, 'shortcode_wps_collections']);
        add_shortcode('wps_search', [$this, 'shortcode_wps_search']);
        add_shortcode('wps_storefront', [$this, 'shortcode_wps_storefront']);
        add_shortcode('wps_cart_icon', [$this, 'shortcode_wps_cart_icon']);

        add_action('wp_footer', [$this, 'wps_cart']);
    }

    /*

    Init

     */
    public function init()
    {
        $this->hooks();
    }
}