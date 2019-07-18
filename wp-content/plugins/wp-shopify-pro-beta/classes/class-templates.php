<?php

namespace WPS;

use WPS\Utils;
use WPS\Utils\Products as Utils_Products;
use WPS\Utils\Sorting as Utils_Sorting;
use WPS\Utils\Filtering as Utils_Filtering;
use WPS\DB\Images;

if (!defined('ABSPATH')) {
   exit();
}

class Templates
{
   public $Template_Loader;
   public $DB_Settings_General;
   public $Money;
   public $DB_Variants;
   public $DB_Products;
   public $DB_Images;
   public $DB_Tags;
   public $DB_Options;
   public $DB_Collections;
   public $Render_Data;

   public function __construct($Template_Loader, $DB_Settings_General, $Money, $DB_Variants, $DB_Products, $DB_Images, $DB_Tags, $DB_Options, $DB_Collections, $Render_Data)
   {
      $this->Template_Loader = $Template_Loader;
      $this->DB_Settings_General = $DB_Settings_General;
      $this->Money = $Money;
      $this->DB_Variants = $DB_Variants;
      $this->DB_Products = $DB_Products;
      $this->DB_Images = $DB_Images;
      $this->DB_Tags = $DB_Tags;
      $this->DB_Options = $DB_Options;
      $this->DB_Collections = $DB_Collections;
      $this->Render_Data = $Render_Data;
   }

   /*

	Template: components/products/loop/loop-start

	*/
   public function wps_products_loop_start($query)
   {
      $data = [
         'query' => $query
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/loop/start');
   }

   /*

	Template: components/products/loop/loop-end

	*/
   public function wps_products_loop_end($query)
   {
      $data = [
         'query' => $query
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/loop/end');
   }

   /*

	Template: components/products/loop/item-start

	*/
   public function wps_products_item_start($product, $args, $customArgs)
   {
      // Related products will always override
      if (isset($args->wps_related_products_items_per_row) && $args->wps_related_products_items_per_row !== false) {
         $items_per_row = $args->wps_related_products_items_per_row;
      } else {
         $items_per_row = apply_filters('wps_products_items_per_row', 3);
      }

      // Shortcode will always override wps_related_products_items_per_row filter
      if (isset($customArgs['items-per-row']) && $customArgs['items-per-row'] !== false) {
         $items_per_row = $customArgs['items-per-row'];
      } else {
         $items_per_row = apply_filters('wps_products_items_per_row', 3);
      }

      $data = [
         'product' => $product,
         'args' => $args,
         'custom_args' => $customArgs,
         'items_per_row' => $items_per_row
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/loop/item', 'start');
   }

   /*

	Template: components/products/loop/item-end

	*/
   public function wps_products_item_end($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/loop/item', 'end');
   }

   /*

	Template: components/products/loop/item

	*/
   public function wps_products_item($product, $args, $settings)
   {
      $data = [
         'product' => $product,
         'args' => $args,
         'settings' => $settings
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/loop/item');
   }

   /*

	Template: components/products/loop/item-link-start

	*/
   public function wps_products_item_link_start($product, $settings)
   {
      $data = [
         'product' => $product,
         'settings' => $settings
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/loop/item-link', 'start');
   }

   /*

	Template: components/products/loop/item-link-end

	*/
   public function wps_products_item_link_end($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/loop/item-link', 'end');
   }

   /*

	Template: components/products/loop/item-img

	*/
   public function wps_products_img($product)
   {
      $image = Images::get_image_details_from_product($product);
      $data = [];

      if (!Images::has_placeholder($image->src)) {
         // If single, then we're on the related products section
         if (is_singular(WPS_PRODUCTS_POST_TYPE_SLUG)) {
            $custom_sizing = apply_filters('wps_related_products_images_sizing', $this->DB_Settings_General->get_related_products_images_sizing_toggle());

            if ($custom_sizing) {
               $data['custom_image_src'] = $this->get_related_products_custom_sized_image_url($image);
            }
         } else {
            $custom_sizing = apply_filters('wps_products_images_sizing', $this->DB_Settings_General->get_products_images_sizing_toggle());

            if ($custom_sizing) {
               $data['custom_image_src'] = $this->get_products_custom_sized_image_url($image);
            }
         }
      } else {
         $custom_sizing = false;
      }

      $data['product'] = $product;
      $data['image'] = $image;
      $data['custom_sizing'] = $custom_sizing;

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/loop/item', 'img');
   }

   /*

	Template: components/products/loop/item-title

	*/
   public function wps_products_description($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/loop/item', 'description');
   }

   /*

	Template: components/products/loop/item-add-to-cart

	*/
   public function wps_products_add_to_cart($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/loop/item-add-to', 'cart');
   }

   public function price_multi_from_default()
   {
      return '<small class="wps-product-from-price">' . esc_html__('From: ', WPS_PLUGIN_TEXT_DOMAIN) . '</small>';
   }

   public function price_multi_separator_default()
   {
      return ' <span class="wps-product-from-price-separator">-</span> ';
   }

   public function add_price_wrapper($price_markup, $type)
   {
      return '<div class="wps-price-wrapper">' . $price_markup . '</div>';
   }

   /*

	Responsible for getting markup for multiple prices

	*/
   public function get_multi_price_markup($first_price, $last_price)
   {
      return apply_filters('wps_products_price_multi_from', $this->price_multi_from_default()) .
         apply_filters('wps_products_price_multi_first', $this->add_price_wrapper($first_price, 'from')) .
         apply_filters('wps_products_price_multi_separator', $this->price_multi_separator_default()) .
         apply_filters('wps_products_price_multi_last', $this->add_price_wrapper($last_price, 'to'));
   }

   public function wps_products_price_wrapper_start()
   {
      return $this->Template_Loader->set_template_data([])->get_template_part('components/products/add-to-cart/price-wrapper', 'start');
   }

   public function wps_products_price_wrapper_end()
   {
      return $this->Template_Loader->set_template_data([])->get_template_part('components/products/add-to-cart/price-wrapper', 'end');
   }

   /*

	Gets range pricing params

	*/
   public function get_range_pricing_params($variants_in_stock, $product, $show_compare_at)
   {
      if ($show_compare_at) {
         $variants_sorted = $this->DB_Variants->sort_variants_by_compare_at_price($variants_in_stock);
         $variants_amount = $this->DB_Variants->get_variants_amount($variants_sorted);

         $first_price = $this->DB_Variants->get_first_variant_compare_at_price($variants_sorted);
         $last_price = $this->DB_Variants->get_last_variant_compare_at_price($variants_sorted, Utils::get_last_index($variants_amount));
      } else {
         $variants_sorted = $this->DB_Variants->sort_variants_by_price($variants_in_stock);
         $first_price = $this->DB_Variants->get_first_variant_price($variants_sorted);
         $variants_amount = $this->DB_Variants->get_variants_amount($variants_in_stock);
         $last_price = $this->DB_Variants->get_last_variant_price($variants_sorted, Utils::get_last_index($variants_amount));
      }

      return [
         'show_compare_at' => $show_compare_at,
         'show_price_range' => true,
         'show_local' => $this->DB_Settings_General->get_col_value('pricing_local_currency_toggle', 'bool'),
         'variants_amount' => $variants_amount,
         'first_price' => $first_price,
         'first_price_formatted' => $this->Money->format_price($first_price, $product->product_id),
         'last_price' => $last_price,
         'last_price_formatted' => $this->Money->format_price($last_price, $product->product_id),
         'product' => $product
      ];
   }

   /*

	Gets single pricing params

	*/
   public function get_single_pricing_params($variants, $product, $show_compare_at)
   {
      $variants_amount = $this->DB_Variants->get_variants_amount($variants);

      if ($show_compare_at) {
         // Here we would always want to prefer to show the largest which would always be the last
         $first_price = $this->DB_Variants->get_last_variant_compare_at_price($variants, Utils::get_last_index($variants_amount));
         $last_price = $first_price;
      } else {
         $first_price = $this->DB_Variants->get_first_variant_price($variants);
         $last_price = $this->DB_Variants->get_last_variant_price($variants, Utils::get_last_index($variants_amount));
      }

      $first_price_formatted = $this->Money->format_price($first_price, $product->product_id);
      $last_price_formatted = $this->Money->format_price($last_price, $product->product_id);

      return [
         'show_compare_at' => $show_compare_at,
         'show_price_range' => false,
         'show_local' => $this->DB_Settings_General->get_col_value('pricing_local_currency_toggle', 'bool'),
         'variants_amount' => $variants_amount,
         'first_price' => $first_price,
         'first_price_formatted' => $first_price_formatted,
         'last_price' => $last_price,
         'last_price_formatted' => $last_price_formatted,
         'product' => $product
      ];
   }

   /*

	Template: components/products/loop/header

	*/
   public function wps_products_header($query)
   {
      $heading = apply_filters('wps_products_heading', $this->DB_Settings_General->get_products_heading());

      $data = [
         'query' => $query,
         'heading' => $heading
      ];

      if (!is_singular(WPS_PRODUCTS_POST_TYPE_SLUG)) {
         return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/loop/header');
      }
   }

   /*

	Template: components/products/add-to-cart/meta-start

	*/
   public function wps_products_meta_start($product)
   {
      $product->url = get_permalink($product->post_id);

      $data = [
         'product' => $product,
         'filtered_options' => Utils::normalize_option_values($product->variants)
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/add-to-cart/meta', 'start');
   }

   /*

	Template: components/products/add-to-cart/meta-end

	*/
   public function wps_products_meta_end($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/add-to-cart/meta', 'end');
   }

   /*

	Template: components/products/add-to-cart/quantity

	*/
   public function wps_products_quantity($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/add-to-cart/quantity');
   }

   /*

	Template: components/products/action-groups/action-groups-start

	*/
   public function wps_products_actions_group_start($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/action-groups/start');
   }

   /*

	Template: components/products/add-to-cart/options

	This only runs if available variants exist. Variants are NOT filtered, only checked.

	*/
   public function wps_products_options($product)
   {
      // Filtering the variants
      $product->variants = Utils::only_available_variants($product->variants);

      $button_color = apply_filters('wps_products_variant_button_color', $this->DB_Settings_General->get_variant_color());

      // Only show product options if more than one variant exists, otherwise just show add to cart button
      if (count($product->variants) > 1) {
         $data = [
            'product' => $product,
            'button_width' => Utils::get_options_button_width($product->options),
            'button_color' => $button_color !== WPS_DEFAULT_VARIANT_COLOR ? $button_color : '',
            'sorted_options' => Utils::get_sorted_options($product),
            'option_number' => 1,
            'variant_number' => 0
         ];

         return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/add-to-cart/options');
      }
   }

   /*

	Template: components/products/add-to-cart/button-add-to-cart

	*/
   public function wps_products_button_add_to_cart($product)
   {
      $button_width = Utils_Products::add_to_cart_button_width($product);
      $button_color = apply_filters('wps_products_add_to_cart_button_color', $this->DB_Settings_General->get_add_to_cart_color());
      $button_text = apply_filters('wps_products_add_to_cart_button_text', WPS_DEFAULT_ADD_TO_CART_TEXT);

      $data = [
         'product' => $product,
         'button_width' => $button_width,
         'button_color' => $button_color !== WPS_DEFAULT_ADD_TO_CART_COLOR ? $button_color : WPS_DEFAULT_ADD_TO_CART_COLOR,
         'button_text' => $button_text !== WPS_DEFAULT_ADD_TO_CART_TEXT ? $button_text : WPS_DEFAULT_ADD_TO_CART_TEXT
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/add-to-cart/button-add-to', 'cart');
   }

   /*

	Template: components/products/action-groups/action-groups-end

	*/
   public function wps_products_actions_group_end($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/action-groups/end');
   }

   /*

	Template: components/products/add-to-cart/notice-inline

	*/
   public function wps_products_notice_inline($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/notices/add-to', 'cart');
   }

   /*

	Template: components/products/loop/no-results

	*/
   public function wps_products_no_results($args)
   {
      $data = [
         'args' => $args
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/notices/no', 'results');
   }

   /*

	Template: components/pagination/start

	*/
   public function wps_products_pagination_start()
   {
      $data = [];

      ob_start();
      $this->Template_Loader->set_template_data($data)->get_template_part('components/pagination/start');
      $output = ob_get_clean();
      return $output;
   }

   /*

	Template: components/products/related/start

	*/
   public function wps_products_related_start()
   {
      $data = [];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/related/start');
   }

   /*

	Template: components/products/related/end

	*/
   public function wps_products_related_end()
   {
      $data = [];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/related/end');
   }

   /*

	Template: components/products/related/heading-start

	*/
   public function wps_products_related_heading()
   {
      $heading = apply_filters('wps_products_related_heading_text', $this->DB_Settings_General->get_related_products_heading());

      $data = [
         'heading' => $heading
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/related/heading');
   }

   /*

	Template: components/pagination/end

	*/
   public function wps_products_pagination_end()
   {
      $data = [];

      ob_start();
      $this->Template_Loader->set_template_data($data)->get_template_part('components/pagination/end');
      $output = ob_get_clean();
      return $output;
   }

   /*

	Single Template for related products

	*/
   public function wps_related_products()
   {
      if (!apply_filters('wps_products_related_show', true)) {
         return;
      }

      if (!is_singular(WPS_PRODUCTS_POST_TYPE_SLUG)) {
         return;
      }

      $data = [];

      return $this->Template_Loader->set_template_data($data)->get_template_part('products', 'related');
   }

   /*

	Template: components/collections/loop/loop-start

	*/
   public function wps_collections_loop_start()
   {
      $data = [];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/loop/start');
   }

   /*

	Template: components/collections/loop/loop-end

	*/
   public function wps_collections_loop_end()
   {
      $data = [];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/loop/end');
   }

   /*

	Template: components/collections/loop/item-start

	*/
   public function wps_collections_item_start($collection, $args, $customArgs)
   {
      // Shortcode will always override wps_related_products_items_per_row filter
      if (isset($customArgs['items-per-row']) && $customArgs['items-per-row'] !== false) {
         $items_per_row = $customArgs['items-per-row'];
      } else {
         $items_per_row = apply_filters('wps_collections_items_per_row', 3);
      }

      $data = [
         'collection' => $collection,
         'args' => $args,
         'custom_args' => $customArgs,
         'items_per_row' => $items_per_row
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/loop/item', 'start');
   }

   /*

	Template: components/collections/loop/item-end

	*/
   public function wps_collections_item_end($collection)
   {
      $data = [
         'collection' => $collection
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/loop/item', 'end');
   }

   /*

	Template: components/collections/loop/item

	*/
   public function wps_collections_item($collection)
   {
      $data = [
         'collection' => $collection
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/loop/item');
   }

   /*

	Template: components/collections/loop/item-link-start

	*/
   public function wps_collections_item_before($collection)
   {
      $data = [
         'collection' => $collection,
         'settings' => $this->DB_Settings_General->get()
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/loop/item-link', 'start');
   }

   /*

	Template: components/collections/loop/item-link-end

	*/
   public function wps_collections_item_after($collection)
   {
      $data = [
         'collection' => $collection
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/loop/item-link', 'end');
   }

   /*

	Template: components/collections/loop/item-img

	*/
   public function wps_collections_img($collection)
   {
      $image = Images::get_image_details_from_collection($collection);

      $data = [
         'collection' => $collection,
         'image' => $image
      ];

      if (!Images::has_placeholder($image->src)) {
         $custom_sizing = apply_filters('wps_collections_images_sizing', $this->DB_Settings_General->get_collections_images_sizing_toggle());

         if ($custom_sizing) {
            $data['custom_image_src'] = $this->get_collections_custom_sized_image_url($image);
         }
      } else {
         $custom_sizing = false;
      }

      $data['custom_sizing'] = $custom_sizing;

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/loop/item', 'img');
   }

   /*

	Template: components/collections/loop/item-title

	*/
   public function wps_collections_title($collection)
   {
      $data = [
         'collection' => $collection
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/loop/item', 'title');
   }

   /*

	Template: components/collections/loop/no-results

	*/
   public function wps_collections_no_results($args)
   {
      $data = [
         'args' => $args
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/notices/no', 'results');
   }

   /*

	Template: components/collections/loop/header

	*/
   public function wps_collections_header($collections)
   {
      $heading = apply_filters('wps_collections_heading', $this->DB_Settings_General->get_collections_heading());

      $data = [
         'collections' => $collections,
         'heading' => $heading
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/loop/header');
   }

   /*

	Template: components/products/action-groups/action-groups-start

	*/
   public function wps_product_single_actions_group_start($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/action-groups/start');
   }

   /*

	Template: components/products/single/content

	*/
   public function wps_product_single_content($product)
   {
      if (is_object($product) && property_exists($product, 'body_html') && !empty($product->body_html)) {
         $data = [
            'product' => $product
         ];

         return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/content');
      } else {
         $data = [
            'type' => 'product'
         ];

         return $this->Template_Loader->set_template_data($data)->get_template_part('components/notices/no', 'description');
      }
   }

   /*

	Template: components/products/single/header

	*/
   public function wps_product_single_header($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/header');
   }

   /*

	Template: components/products/single/header

	*/
   public function wps_product_single_heading($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/heading');
   }

   /*

	Template: components/products/single/imgs

	*/
   public function wps_product_single_imgs($product)
   {
      $product->images = Utils::sort_product_images_by_position($product->images);

      $data = [
         'product' => $product,
         'settings' => $this->DB_Settings_General->get(),
         'images' => $product->images,
         'index' => 0,
         'amount_of_thumbs' => count($product->images)
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/imgs');
   }

   /*

	Template: components/products/single/img

	*/
   public function wps_product_single_img($data, $image)
   {
      $data->image_type_class = 'wps-product-gallery-img-thumb';
      $data->image_details = Images::get_image_details_from_image($image, $data->product);

      if ($data->amount_of_thumbs === 1) {
         $data->amount_of_thumbs = 3;
      }

      if ($data->amount_of_thumbs > 8) {
         $data->amount_of_thumbs = 6;
      }

      $data->variant_ids = Images::get_variants_from_image($image);

      $custom_sizing = apply_filters('wps_products_images_sizing', $this->DB_Settings_General->get_products_images_sizing_toggle());

      $data->custom_sizing = $custom_sizing;

      if ($custom_sizing) {
         $data->custom_image_src = $this->get_products_custom_sized_image_url($image);
      }

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/img');
   }

   /*

	Template: components/products/single/imgs-feat

	*/
   public function wps_product_single_imgs_feat_placeholder($data)
   {
      $data->image_type_class = 'wps-product-gallery-img-feat';
      $data->plugin_settings->plugin_url = WPS_PLUGIN_URL;

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/imgs-feat', 'placeholder');
   }

   /*

	Template: components/products/single/imgs-feat

	*/
   public function wps_product_single_imgs_feat($data, $image)
   {
      $image_details = Images::get_image_details_from_image($image, $data->product);

      $data->image_details = $image_details;
      $data->image_type_class = 'wps-product-gallery-img-feat';

      $data->variant_ids = Images::get_variants_from_image($image);

      if (empty($data->image_details->alt)) {
         $data->image_details->alt = $data->product->title;
      }

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/imgs-feat');
   }

   /*

	Template: components/products/single/info-start

	*/
   public function wps_product_single_info_start($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/info', 'start');
   }

   /*

	Template: components/products/single/info-end

	*/
   public function wps_product_single_info_end($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/info', 'end');
   }

   /*

	Template: components/products/single/gallery-start

	*/
   public function wps_product_single_gallery_start($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/gallery', 'start');
   }

   /*

	Template: components/products/single/gallery-end

	*/
   public function wps_product_single_gallery_end($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/gallery', 'end');
   }

   /*

	Template: components/products/single/start

	*/
   public function wps_product_single_start($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/start');
   }

   /*

	Template: components/products/single/end

	*/
   public function wps_product_single_end($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/end');
   }

   /*

	Template: components/notices/out-of-stock

	*/
   public function wps_products_notice_out_of_stock($product)
   {
      $data = [
         'product' => $product
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/notices/out-of', 'stock');
   }

   /*

	Template: components/collections/single/start

	*/
   public function wps_collection_single_start($collection)
   {
      $data = [
         'collection' => $collection
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/single/start');
   }

   /*

	Template: components/collections/single/header

	*/
   public function wps_collection_single_header($collection)
   {
      $data = [
         'collection' => $collection
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/single/header');
   }

   /*

	Template: components/collections/single/img

	*/
   public function wps_collection_single_img($collection)
   {
      $data = [
         'collection' => $collection,
         'image' => Images::get_image_details_from_collection($collection)
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/single/img');
   }

   /*

	Template: components/collections/single/content

	*/
   public function wps_collection_single_content($collection)
   {
      if (is_object($collection) && property_exists($collection, 'body_html') && !empty($collection->body_html)) {
         $data = [
            'collection' => $collection
         ];

         return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/single/content');
      } else {
         $data = [
            'type' => 'collection'
         ];

         return $this->Template_Loader->set_template_data($data)->get_template_part('components/notices/no', 'description');
      }
   }

   /*

	Template: components/collections/single/products

	*/
   public function wps_collection_single_products($collection, $products)
   {
      $data = [
         'products' => $products,
         'collection' => $collection
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/single/products');
   }

   /*

	Template: components/collections/single/products

	*/
   public function wps_collection_single_products_heading()
   {
      $data = [];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/single/products', 'heading');
   }

   /*

	Template: components/collections/single/end

	*/
   public function wps_collection_single_end($collection)
   {
      $data = [
         'collection' => $collection
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/single/end');
   }

   /*

	Template: components/collections/single/heading

	*/
   public function wps_collection_single_heading($collection)
   {
      $data = [
         'collection' => $collection
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/single/heading');
   }

   /*

	Template: components/collections/single/product

	*/
   public function wps_collection_single_product($product)
   {
      $data = [
         'product' => $product,
         'settings' => $this->DB_Settings_General->get_all_rows()[0]
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/single/product');
   }

   /*

	Template: components/collections/single/products-list

	*/
   public function wps_collection_single_products_list($collection, $products)
   {
      if (!is_array($products) || empty($products)) {
         return $this->Template_Loader->get_template_part('components/notices/no', 'results');
      }

      $data = [
         'products' => $products,
         'collection' => $collection,
         'show_compare_at' => $this->DB_Settings_General->get_col_value('products_compare_at', 'bool'),
         'show_local' => $this->DB_Settings_General->get_col_value('pricing_local_currency_toggle', 'bool')
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/collections/single/products', 'list');
   }

   /*

	Template: components/pagination/breadcrumbs

	*/
   public function wps_breadcrumbs($shortcodeData = false)
   {
      
      if (apply_filters('wps_breadcrumbs_show', $this->DB_Settings_General->show_breadcrumbs())) {
         $data = [];

         return $this->Template_Loader->set_template_data($data)->get_template_part('components/pagination/breadcrumbs');
      }
   }

   /*

	Template: components/products/single/thumbs-start

	*/
   public function wps_product_single_thumbs_start()
   {
      $data = [];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/thumbs', 'start');
   }

   /*

	Template: components/products/single/thumbs-end

	*/
   public function wps_product_single_thumbs_end()
   {
      $data = [];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/products/single/thumbs', 'end');
   }

   /*

	Template: components/cart/cart-counter

	*/
   public function wps_cart_counter($custom_color = false)
   {
      if (!$custom_color) {
         $button_color = apply_filters('wps_products_cart_counter_button_color', $this->DB_Settings_General->get_cart_counter_color());
      } else {
         $button_color = $custom_color;
      }

      $data = [
         'button_color' => $button_color !== WPS_DEFAULT_CART_COUNTER_COLOR ? $button_color : ''
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/cart/cart', 'counter');
   }


   /*

	Template - cart-button-checkout

	*/
   public function wps_cart_checkout_btn()
   {
      $button_color = apply_filters('wps_products_checkout_button_color', $this->DB_Settings_General->get_col_value('checkout_color', 'string'));
      $button_target = apply_filters('wps_cart_checkout_button_target', $this->DB_Settings_General->get_col_value('checkout_button_target', 'string'));

      $data = [
         'checkout_base_url' => WPS_CHECKOUT_BASE_URL,
         'button_color' => $button_color !== WPS_DEFAULT_VARIANT_COLOR ? $button_color : '',
         'button_target' => $button_target
      ];

      return $this->Template_Loader->set_template_data($data)->get_template_part('components/cart/cart-button', 'checkout');
   }

   /*

	Template - cart-button-checkout

	*/
   public function wps_cart_terms()
   {
      if (apply_filters('wps_cart_terms_show', $this->DB_Settings_General->enable_cart_terms())) {
         $data = [
            'terms_content' => $this->DB_Settings_General->cart_terms_content()
         ];

         return $this->Template_Loader->set_template_data($data)->get_template_part('components/cart/cart', 'terms');
      }
   }

   /*

	Template - components/cart/button
	Shortcode [wps_cart]

	TODO: Think about using shortcode_atts for [wps_products] and [wps_collections] as well.

	*/
   // public function wps_cart_shortcode($atts)
   // {
   //    $shortcode_output = '';

   //    // Need to cast string to proper boolean
   //    if (is_array($atts) && isset($atts['counter']) && $atts['counter'] === 'false') {
   //       $atts['counter'] = false;
   //    }

   //    $atts = shortcode_atts(
   //       [
   //          'counter' => true
   //       ],
   //       $atts,
   //       'wps_cart'
   //    );

   //    ob_start();
   //    $this->Template_Loader->set_template_data($atts)->get_template_part('components/cart/cart-icon', 'wrapper');
   //    $cart = ob_get_contents();
   //    ob_end_clean();

   //    $shortcode_output .= $cart;

   //    return $shortcode_output;
   // }

   public function is_cart_loaded($cart_loaded_db_response)
   {
      if (Utils::array_not_empty($cart_loaded_db_response) && isset($cart_loaded_db_response[0]->cart_loaded)) {
         $cart_loaded = $cart_loaded_db_response[0]->cart_loaded;
      } else {
         $cart_loaded = false;
      }

      return $cart_loaded;
   }

   /*

	Template - components/cart/cart

	This is slow. We should think of a better way to do this.

	*/
   public function wps_shop()
   {

      echo '<div id="wps-shop"></div>';
   }

   /*

	Template - components/notices/notice

	*/
   public function wps_notice()
   {
      $data = [];

      if ($this->is_cart_loaded($this->DB_Settings_General->get_column_single('cart_loaded'))) {
         return $this->Template_Loader->set_template_data($data)->get_template_part('components/notices/not', 'found');
      }
   }

   public function shortcode_template($attrs, $path, $name)
   {
      ob_start();
      $this->Template_Loader->set_template_data($attrs)->get_template_part($path, $name);
      $content = ob_get_contents();
      ob_end_clean();

      return $content;
   }

   /*

	Template - collections-all
	Shortcode [wps_collections]

	*/
   public function wps_collections_shortcode($atts)
   {
      $shortcode_output = '';
      $shortcodeArgs = Utils::wps_format_collections_shortcode_args($atts);

      $data = [
         'shortcodeArgs' => $shortcodeArgs,
         'is_shortcode' => true
      ];

      ob_start();
      $this->Template_Loader->set_template_data($data)->get_template_part('collections', 'all');
      $collections = ob_get_contents();
      ob_end_clean();

      $shortcode_output .= $collections;

      return $shortcode_output;
   }

   /*

	Main Template - products-single

	*/
   public function wps_single_template($template)
   {
      if (is_singular(WPS_PRODUCTS_POST_TYPE_SLUG)) {
         return $this->Template_Loader->get_template_part('products', 'single', false);
      }

      if (is_singular(WPS_COLLECTIONS_POST_TYPE_SLUG)) {
         return $this->Template_Loader->get_template_part('collections', 'single', false);
      }

      return $template;
   }

   /*

	Main Template products-all

	*/
   public function wps_all_template($template)
   {
      if (is_post_type_archive(WPS_PRODUCTS_POST_TYPE_SLUG)) {
         return $this->Template_Loader->get_template_part('products', 'all', false);
      }

      if (is_post_type_archive(WPS_COLLECTIONS_POST_TYPE_SLUG)) {
         return $this->Template_Loader->get_template_part('collections', 'all', false);
      }

      return $template;
   }

   public function get_shortcode_data($data)
   {
      if (empty($data)) {
         $data = new \stdClass();
         $data->shortcodeArgs = [];
         $data->is_shortcode = false;
      } else {
         if (!isset($data->shortcodeArgs) || $data->shortcodeArgs['post_type'] !== 'wps_products' || $data->shortcodeArgs['post_type'] !== 'wps_collections') {
            return $data;
         }

         $data->shortcodeArgs = !empty($data->shortcodeArgs) ? $data->shortcodeArgs : [];
         $data->is_shortcode = isset($data->is_shortcode) && $data->is_shortcode ? $data->is_shortcode : false;
      }

      return $data;
   }

   /*

	Show / Hide Header

	*/
   public function show_header($shortcodeData = false)
   {
      if (empty($shortcodeData) || empty($shortcodeData->is_shortcode)) {
         get_header('wps');
      }
   }

   /*

	Show / Hide Footer

	*/
   public function show_footer($shortcodeData = false)
   {
      if (empty($shortcodeData) || empty($shortcodeData->is_shortcode)) {
         get_footer('wps');
      }
   }

   /*

	Get Collection Products

	*/
   public function get_collection_products_data($post_id)
   {
      $collection = $this->DB_Collections->get_collection_by_post_id($post_id);

      $products = [];

      if ($this->DB_Collections->has_collection($collection)) {
         $products = $this->DB_Products->get_products_by_collection_id($collection[0]->collection_id);

         /*

			Get the variants / feat image and add them to the products

			*/
         foreach ($products as $key => $product) {
            $product->variants = $this->DB_Variants->get_all_variants_from_post_id($product->post_id);
            $product->feat_image = $this->DB_Images->get_feat_image_by_post_id($product->post_id);
         }
      }

      return $products;
   }

   /*

	Helper for getting a custom sized image URL

	*/
   public function get_products_custom_sized_image_url($image)
   {
      $custom_width = $this->DB_Settings_General->get_products_images_sizing_width();
      $custom_height = $this->DB_Settings_General->get_products_images_sizing_height();
      $custom_crop = $this->DB_Settings_General->get_products_images_sizing_crop();
      $custom_scale = $this->DB_Settings_General->get_products_images_sizing_scale();

      return $this->DB_Images->add_custom_sizing_to_image_url([
         'src' => $image->src,
         'width' => $custom_width,
         'height' => $custom_height,
         'crop' => $custom_crop,
         'scale' => $custom_scale
      ]);
   }

   /*

	Helper for getting a custom sized image URL

	*/
   public function get_collections_custom_sized_image_url($image)
   {
      $custom_width = $this->DB_Settings_General->get_collections_images_sizing_width();
      $custom_height = $this->DB_Settings_General->get_collections_images_sizing_height();
      $custom_crop = $this->DB_Settings_General->get_collections_images_sizing_crop();
      $custom_scale = $this->DB_Settings_General->get_collections_images_sizing_scale();

      return $this->DB_Images->add_custom_sizing_to_image_url([
         'src' => $image->src,
         'width' => $custom_width,
         'height' => $custom_height,
         'crop' => $custom_crop,
         'scale' => $custom_scale
      ]);
   }

   /*

	Helper for getting a custom sized image URL

	*/
   public function get_related_products_custom_sized_image_url($image)
   {
      $custom_width = $this->DB_Settings_General->get_related_products_images_sizing_width();
      $custom_height = $this->DB_Settings_General->get_related_products_images_sizing_height();
      $custom_crop = $this->DB_Settings_General->get_related_products_images_sizing_crop();
      $custom_scale = $this->DB_Settings_General->get_related_products_images_sizing_scale();

      return $this->DB_Images->add_custom_sizing_to_image_url([
         'src' => $image->src,
         'width' => $custom_width,
         'height' => $custom_height,
         'crop' => $custom_crop,
         'scale' => $custom_scale
      ]);
   }

   /*

	Hooks

	*/
   public function hooks()
   {

      /*

		Cart & Breadcrumbs

		*/
      add_action('wps_breadcrumbs', [$this, 'wps_breadcrumbs']);
      
      add_action('wp_footer', [$this, 'wps_shop']);

      add_action('wps_cart_counter', [$this, 'wps_cart_counter']);
      add_action('wps_cart_checkout_btn', [$this, 'wps_cart_checkout_btn']);
      add_action('wps_cart_terms', [$this, 'wps_cart_terms']);

      /*

		Main Templates

		*/
      add_filter('single_template', [$this, 'wps_single_template']);
      add_filter('archive_template', [$this, 'wps_all_template']);

      /*

		Collections

		*/
      add_action('wps_collections_header', [$this, 'wps_collections_header']);
      add_action('wps_collections_loop_start', [$this, 'wps_collections_loop_start']);
      add_action('wps_collections_loop_end', [$this, 'wps_collections_loop_end']);
      add_action('wps_collections_item_start', [$this, 'wps_collections_item_start'], 10, 3);
      add_action('wps_collections_item_end', [$this, 'wps_collections_item_end']);
      add_action('wps_collections_item', [$this, 'wps_collections_item']);
      add_action('wps_collections_item_before', [$this, 'wps_collections_item_before']);
      add_action('wps_collections_item_after', [$this, 'wps_collections_item_after']);
      add_action('wps_collections_img', [$this, 'wps_collections_img']);
      add_action('wps_collections_title', [$this, 'wps_collections_title']);
      add_action('wps_collections_no_results', [$this, 'wps_collections_no_results']);
      add_action('wps_collection_single_start', [$this, 'wps_collection_single_start']);
      add_action('wps_collection_single_header', [$this, 'wps_collection_single_header']);
      add_action('wps_collection_single_img', [$this, 'wps_collection_single_img']);
      add_action('wps_collection_single_content', [$this, 'wps_collection_single_content']);
      add_action('wps_collection_single_products', [$this, 'wps_collection_single_products'], 10, 3);
      add_action('wps_collection_single_products_list', [$this, 'wps_collection_single_products_list'], 10, 3);
      add_action('wps_collection_single_products_heading', [$this, 'wps_collection_single_products_heading']);
      add_action('wps_collection_single_end', [$this, 'wps_collection_single_end']);
      add_action('wps_collection_single_product', [$this, 'wps_collection_single_product']);
      add_action('wps_collection_single_heading', [$this, 'wps_collection_single_heading'], 10);

      /*

		Products

		*/
      add_action('wps_products_header', [$this, 'wps_products_header']);
      add_action('wps_products_loop_start', [$this, 'wps_products_loop_start']);
      add_action('wps_products_loop_end', [$this, 'wps_products_loop_end']);
      add_action('wps_products_item_start', [$this, 'wps_products_item_start'], 10, 3);
      add_action('wps_products_item_end', [$this, 'wps_products_item_end']);
      add_action('wps_products_item', [$this, 'wps_products_item'], 10, 3);
      add_action('wps_products_item_link_start', [$this, 'wps_products_item_link_start'], 10, 2);
      add_action('wps_products_item_link_end', [$this, 'wps_products_item_link_end']);
      add_action('wps_products_img', [$this, 'wps_products_img']);

      add_action('wps_products_description', [$this, 'wps_products_description']);

      add_action('wps_products_compare_at_price', [$this, 'wps_products_price'], 10, 2);

      add_action('wps_products_price_wrapper_start', [$this, 'wps_products_price_wrapper_start']);
      add_action('wps_products_price_wrapper_end', [$this, 'wps_products_price_wrapper_end']);

      add_action('wps_products_no_results', [$this, 'wps_products_no_results']);
      add_action('wps_products_add_to_cart', [$this, 'wps_products_add_to_cart']);
      add_action('wps_products_meta_start', [$this, 'wps_products_meta_start']);
      add_action('wps_products_quantity', [$this, 'wps_products_quantity']);
      add_action('wps_products_options', [$this, 'wps_products_options']);

      add_action('wps_products_actions_group_start', [$this, 'wps_products_actions_group_start']);
      add_action('wps_products_actions_group_end', [$this, 'wps_products_actions_group_end']);
      add_action('wps_products_notice_inline', [$this, 'wps_products_notice_inline']);
      add_action('wps_products_meta_end', [$this, 'wps_products_meta_end']);
      add_action('wps_products_related_start', [$this, 'wps_products_related_start']);
      add_action('wps_products_related_end', [$this, 'wps_products_related_end']);
      add_action('wps_products_related_heading', [$this, 'wps_products_related_heading']);
      add_action('wps_products_notice_out_of_stock', [$this, 'wps_products_notice_out_of_stock']);
      add_action('wps_product_single_after', [$this, 'wps_related_products']);
      add_action('wps_product_single_actions_group_start', [$this, 'wps_product_single_actions_group_start']);
      add_action('wps_product_single_content', [$this, 'wps_product_single_content']);
      add_action('wps_product_single_header', [$this, 'wps_product_single_header']);
      add_action('wps_product_single_heading', [$this, 'wps_product_single_heading']);
      add_action('wps_product_single_img', [$this, 'wps_product_single_img'], 10, 2);
      add_action('wps_product_single_imgs', [$this, 'wps_product_single_imgs']);
      add_action('wps_product_single_imgs_feat_placeholder', [$this, 'wps_product_single_imgs_feat_placeholder']);
      add_action('wps_product_single_imgs_feat', [$this, 'wps_product_single_imgs_feat'], 10, 2);
      add_action('wps_product_single_info_start', [$this, 'wps_product_single_info_start']);
      add_action('wps_product_single_info_end', [$this, 'wps_product_single_info_end']);
      add_action('wps_product_single_gallery_start', [$this, 'wps_product_single_gallery_start']);
      add_action('wps_product_single_gallery_end', [$this, 'wps_product_single_gallery_end']);
      add_action('wps_product_single_start', [$this, 'wps_product_single_start']);
      add_action('wps_product_single_end', [$this, 'wps_product_single_end']);
      add_action('wps_product_single_thumbs_start', [$this, 'wps_product_single_thumbs_start']);
      add_action('wps_product_single_thumbs_end', [$this, 'wps_product_single_thumbs_end']);

      add_filter('wps_products_pagination_start', [$this, 'wps_products_pagination_start']);
      add_filter('wps_products_pagination_end', [$this, 'wps_products_pagination_end']);
   }

   /*

	Init

	*/
   public function init()
   {
      $this->hooks();
   }
}
