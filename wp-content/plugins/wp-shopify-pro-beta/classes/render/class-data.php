<?php

namespace WPS\Render;

use WPS\Utils\Products as Utils_Products;
use WPS\Transients;
use WPS\Utils;
use WPS\Utils\Sorting as Utils_Sorting;

if (!defined('ABSPATH')) {
   exit();
}

class Data
{
   public $DB_Products;
   public $DB_Images;
   public $DB_Tags;
   public $DB_Variants;
   public $DB_Options;
   public $DB_Collections;
   public $Money;
   public $Render_Products_Defaults;

   public function __construct($DB_Products, $DB_Images, $DB_Tags, $DB_Variants, $DB_Options, $DB_Collections, $Money, $Render_Products_Defaults)
   {
      $this->DB_Products = $DB_Products;
      $this->DB_Images = $DB_Images;
      $this->DB_Tags = $DB_Tags;
      $this->DB_Variants = $DB_Variants;
      $this->DB_Options = $DB_Options;
      $this->DB_Collections = $DB_Collections;
      $this->Money = $Money;
      $this->Render_Products_Defaults = $Render_Products_Defaults;
   }

   public function get_product_data_from_product_id($product_id = false) {
      return $this->get_products_data_from_products_id($product_id);
   }

   /*

	This grabs all product data from post id

	*/
   public function get_products_data_from_products_id($product_id = false)
   {
      $product = new \stdClass();

      $product = $this->DB_Products->get_product_from_product_id($product_id);

      if (empty($product)) {
         return false;
      } else {
         $product = $product[0];
      }

      $product->images = $this->DB_Images->get_images_from_product_id($product_id);
      $product->tags = $this->DB_Tags->construct_only_tag_names($this->DB_Tags->get_tags_from_product_id($product_id));
      $product->variants = $this->DB_Variants->get_in_stock_variants_from_product_id($product_id);
      $product->options = $this->DB_Options->get_options_from_product_id($product_id);
      $product->collections = $this->DB_Collections->get_collections_by_product_id($product->product_id);

      return $product;
   }

   // Responsible for grabbing the default component params
   public function get_defaults($params, $required_data)
   {
      // TODO: Hacky to check for 'products' here ... improve
      if ($params['data_defaults'] === 'products') {
         $method_name = 'product';
      } else {
         $method_name = 'product_' . $params['data_defaults'];
      }

      if (!method_exists($this->Render_Products_Defaults, $method_name)) {
         return false;
      }

      return $this->Render_Products_Defaults->{$method_name}($required_data[$params['data_type']]);
   }

   /*

	$params represents Template Loader params

	*/
   public function normalize($params)
   {

      // Represents $data[] with 'product', 'customer, 'collection', etc
      $required_data = $this->get_required_data($params['data'], $params['data_type']);

      if (!$required_data || !$params['data']) {

         return new \WP_Error( 'error', __( "Missing data. You probably have Lite sync turned on or need to resync your store", "wpshopify" ) );
      }

      // Responsible for grabbing the default component params
      $default_data = $this->get_defaults($params, $required_data);

      // Lands in here if the "defaults" param passed does not match a method name
      if (!$default_data) {
         return false;
      }

      // Basically just adds "product", "collection", etc to defaults
      return wp_parse_args($required_data, $default_data);
   }

   public function has_type($data, $type)
   {
      return !empty($data[$type]);
   }

   public function has_type_id($data, $type)
   {
      return !empty($data[$type . '_id']);
   }

   public function has_post_id($data)
   {
      return !empty($data['post_id']);
   }

   public function get_data_post_id($data)
   {
      return $data['post_id'];
   }

   public function get_data_type_id($data, $type)
   {
      return $data[$type . '_id'];
   }

   public function get_data_from_type_id($data, $type)
   {
      if ($this->has_multiple_type_ids($data, $type)) {
         $data_many = [];

         foreach ($data[$type . '_id'] as $type_id) {
            $data_many[] = $this->{'get_' . $type . '_data_from_' . $type . '_id'}($type_id);
         }

         return $data_many;
      }

      return $this->{'get_' . $type . '_data_from_' . $type . '_id'}($this->get_data_type_id($data, $type));
   }

   public function has_multiple_type_ids($data, $type)
   {
      return is_array($data[$type . '_id']);
   }

   public function has_multiple_post_ids($data)
   {
      return is_array($data['post_id']);
   }

   public function has_empty_data($data_of_type)
   {
      $data_of_type_copy = $data_of_type;

      return empty(array_filter((array) $data_of_type_copy));
   }

   public function has_many_items($data)
   {
      return Utils::is_multi_array($data);
   }

   /*

	Predicate Fn

	*/
   public function array_of_ids_not_passed($data, $type)
   {
      if ($this->has_type_id($data, $type)) {
         return !is_array($data[$type . '_id']);
      }

      if ($this->has_post_id($data)) {
         return !is_array($data['post_id']);
      }

      return false;
   }

   public function wrap_ids_in_array($data, $type)
   {
      if ($this->has_type_id($data, $type)) {
         $data[$type . '_id'] = [$data[$type . '_id']];
      } elseif ($this->has_post_id($data)) {
         $data['post_id'] = [$data['post_id']];
      }

      return $data;
   }

   /*

	Responsible for finding the required render object $product, $collection, etc

	*/
   public function get_required_data($data, $type)
   {
      if ($this->array_of_ids_not_passed($data, $type)) {
         $data = $this->wrap_ids_in_array($data, $type);
      }

      $data_of_type = false;

      // If a product object is passed in, just use that directly
      if ($this->has_type($data, $type)) {
         return $data;
      }

      if ($this->has_type_id($data, $type)) {
         $data_of_type = $this->get_data_from_type_id($data, $type);
      }

      if (!$this->has_empty_data($data_of_type)) {
         $data[$type] = $data_of_type;

         return $data;
      }

      return false;
   }



public function refine_product_ids($ids)
    {
        return array_unique(array_merge($ids['title'], $ids['vendor'], $ids['description'], $ids['product_type'], $ids['slug'], $ids['tag'], $ids['products_id'], $ids['post_id']), SORT_REGULAR);
    }

    public function refine_collection_ids($ids)
    {
        return array_unique(array_merge($ids['title'], $ids['description'], $ids['collection_id'], $ids['post_id']), SORT_REGULAR);
    }

    public function product_filter_params()
    {
        return ['title', 'vendor', 'description', 'product_type', 'slug', 'tag', 'products_id', 'post_id'];
    }

    public function has_filter_params_product_attrs($shortcode_attrs)
    {
        return $this->has_filter_params($shortcode_attrs, $this->product_filter_params());
    }

    public function accumulating_collection_attrs()
    {
        return ['title', 'description', 'collection_id', 'post_id'];
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

    public function get_ids_from_product_shortcode($atts)
    {

        $ids = [];

         if (!$this->has_filter_params_product_attrs($atts)) {
            $ids['products_id'] = [];
         } else {
            $ids['products_id'] = $this->normalize_ids(!empty($atts['products_id']) ? $atts['products_id'] : []);
         }


         $ids['title'] = !empty($atts['title']) ? $this->DB_Products->get_product_ids_from_titles($atts['title']) : [];
         $ids['vendor'] = !empty($atts['vendor']) ? $this->DB_Products->get_product_ids_from_vendors($atts['vendor']) : [];
         $ids['description'] = !empty($atts['description']) ? $this->DB_Products->get_product_ids_from_description($atts['description']) : [];
         $ids['product_type'] = !empty($atts['product_type']) ? $this->DB_Products->get_product_ids_from_types($atts['product_type']) : [];
         $ids['slug'] = !empty($atts['slug']) ? $this->DB_Products->get_product_ids_from_handles($atts['slug']) : [];
         $ids['tag'] = !empty($atts['tag']) ? $this->DB_Tags->get_product_ids_from_tag($atts['tag']) : [];
         $ids['post_id'] = !empty($atts['post_id']) ? $this->DB_Products->get_product_ids_from_post_ids($atts['post_id']) : [];

        return $this->refine_product_ids($ids);
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

public function get_ids_from_shortcode($atts, $type)
    {
        return $this->shortcode_filters($atts, $type);
    }













}
