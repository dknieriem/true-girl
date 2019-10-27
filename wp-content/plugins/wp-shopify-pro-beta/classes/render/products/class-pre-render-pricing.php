<?php

namespace WPS\Render\Products;

use WPS\Utils;

if (!defined('ABSPATH')) {
   exit();
}

class Pre_Render_Pricing
{
   public $Render_Defaults;
   public $DB_Variants;
   public $Money;

   public function __construct($Render_Defaults, $DB_Variants, $Money)
   {
      $this->Render_Defaults = $Render_Defaults;
      $this->DB_Variants = $DB_Variants;
      $this->Money = $Money;
   }

   /*

	Template: templates/components/loop/item-price

	product, $show_compare_at = false

	*/
   public function wps_products_price($user_params)
   {
      return $this->get_pricing_template($this->gather_products_pricing_params($user_params));
   }

   /*

	We'll always be showing the variant in position 1, first. And only if it's marked as available to purchase.

	*/
   public function gather_products_pricing_params($user_params)
   {

      if (empty($user_params['data']['product'])) {
         return false;
      }

      return $this->build_pricing_params($user_params);
   }

   /*

	Responsible for getting markup for multiple prices

	*/
   public function get_multi_price_markup($first_price, $last_price)
   {
      return apply_filters('wps_products_price_multi_from', '<small class="wps-product-from-price">' . esc_html__('From: ', WPS_PLUGIN_TEXT_DOMAIN) . '</small>') .
         apply_filters('wps_products_price_multi_first', $first_price) .
         apply_filters('wps_products_price_multi_separator', ' <span class="wps-product-from-price-separator">-</span> ') .
         apply_filters('wps_products_price_multi_last', $last_price);
   }

   public function is_real_price_range($show_price_range, $variants_amount)
   {
      return $show_price_range && $this->Money->has_more_than_one_price($variants_amount);
   }

   /*

	Gets range pricing params

	*/
   public function build_pricing_params($params)
   {
      $product = $params['data']['product'];
      $variants_in_stock = $this->DB_Variants->get_in_stock_variants_from_product($product);
      $variants_amount = $this->DB_Variants->get_variants_amount($variants_in_stock);

      if (!empty($params['data']['show_compare_at'])) {
         if (!empty($params['data']['show_price_range'])) {
            $variants_sorted = $this->DB_Variants->sort_variants_by_compare_at_price($variants_in_stock);
         } else {
            $variants_sorted = $this->DB_Variants->sort_variants_by_position($variants_in_stock);
         }

         $first_price = $this->DB_Variants->get_first_variant_compare_at_price($variants_sorted);
         $last_price = $this->DB_Variants->get_last_variant_compare_at_price($variants_sorted, Utils::get_last_index($variants_amount));
      } else {
         if (!empty($params['data']['show_price_range'])) {
            $variants_sorted = $this->DB_Variants->sort_variants_by_price($variants_in_stock);
         } else {
            $variants_sorted = $this->DB_Variants->sort_variants_by_position($variants_in_stock);
         }

         $first_price = $this->DB_Variants->get_first_variant_price($variants_sorted);
         $last_price = $this->DB_Variants->get_last_variant_price($variants_sorted, Utils::get_last_index($variants_amount));
      }

      $first_price_formatted = $this->Money->format_price($first_price, $product->product_id);
      $last_price_formatted = $this->Money->format_price($last_price, $product->product_id);

      $asdfisjd = !empty($params['data']['show_price_range']) ? $params['data']['show_price_range'] : false;

      if ($this->is_real_price_range($asdfisjd, $variants_amount)) {
         $price_markup = $this->get_multi_price_markup($first_price_formatted, $last_price_formatted);
      } else {
         $price_markup = $first_price_formatted;
      }

      // wp_parse_args( $args, $defaults );
      // $args will override defaults


      return $this->Render_Defaults->product_pricing([
            'price' => $price_markup,
            'show_compare_at' => isset($params['data']['show_compare_at']) ? (int) $params['data']['show_compare_at'] : false,
            'show_local' => isset($params['data']['show_local']) ? (int) $params['data']['show_local'] : false,
            'show_price_range' => isset($params['data']['show_price_range']) ? (int) $params['data']['show_price_range'] : false,
            'variants_amount' => $variants_amount,
            'first_price' => $first_price,
            'first_price_formatted' => $first_price_formatted,
            'last_price' => $last_price,
            'last_price_formatted' => $last_price_formatted,
            'product' => $product,
            'custom' => isset($params['data']['custom']) ? $params['data']['custom'] : false,
            'render_from_server' => isset($params['data']['render_from_server']) ? $params['data']['render_from_server'] : false
         ]);
   }

   public function all_variants_prices_match($last_price, $first_price)
   {
      return $this->DB_Variants->check_if_all_variant_prices_match($last_price, $first_price);
   }

   public function get_pricing_template($params)
   {
      return [
         'path' => 'components/products/pricing/pricing',
         'data' => $params
      ];
   }

   public function toggle_show_compare_at($params)
   {
      $params['data']['show_compare_at'] = false;

      return $params;
   }

   public function params_show_compare_at($params)
   {
      return !empty($params['data']['show_compare_at']);
   }

   /*

	$params:

	[
		'product'			=> [],
		'product_id' 	=> false,
		'post_id' 		=> false,
		'path' 				=> '',
		'type' 				=> '',
		'data'				=> [],
		'custom' 			=> false
	]

	*/
   public function pre_render_product_pricing($params)
   {
      $final_params_1 = $this->wps_products_price($params);

      // Setting path, name, and type
      $returning_params = [
         'path' => $final_params_1['path'],
         'type' => $params['type']
      ];

      /*

		Setting data

		show_compare_at will need to show multiple templates with
		different params.

		*/
      if ($this->params_show_compare_at($params)) {
         $final_params_2 = $this->wps_products_price($this->toggle_show_compare_at($params));

         $returning_params['data'] = [$final_params_1['data'], $final_params_2['data']];
      } else {
         $returning_params['data'] = $final_params_1['data'];
      }

      return $returning_params;
   }
}
