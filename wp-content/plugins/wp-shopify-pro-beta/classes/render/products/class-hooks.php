<?php

namespace WPS\Render\Products;

if (!defined('ABSPATH')) {
   exit();
}

class Hooks
{
   public $Render_Products;

   public function __construct($Render_Products)
   {
      $this->Render_Products = $Render_Products;
   }

   /*

	Template: templates/components/add-to-cart/button-add-to-cart

	*/
   public function wps_products_buy_button($product)
   {
      return $this->Render_Products->add_to_cart([
         'product' => $product
      ]);
   }

   /*

	Template: templates/components/add-to-cart/button-add-to-cart

	*/
   // public function wps_products_title($product)
   // {
   //    return $this->Render_Products->title([
   //       'product' => $product
   //    ]);
   // }

   /*

	Template: templates/components/add-to-cart/button-add-to-cart

	*/
   public function wps_products_description($product)
   {
      return $this->Render_Products->description([
         'product' => $product
      ]);
   }

   /*

	Template: templates/components/add-to-cart/button-add-to-cart

	*/
   public function wps_products_pricing($product)
   {
      return $this->Render_Products->pricing([
         'product' => $product
      ]);
   }

   /*

	Template: templates/components/add-to-cart/button-add-to-cart

	*/
   public function wps_products_quantity($product)
   {
      return $this->Render_Products->quantity([
         'product' => $product
      ]);
   }

   /*

	Template: templates/components/add-to-cart/button-add-to-cart

	*/
   public function wps_products_options($product)
   {
      return $this->Render_Products->options([
         'product' => $product
      ]);
   }

   /*

	Template: templates/components/add-to-cart/button-add-to-cart

	*/
   public function wps_product_images($product)
   {
      return $this->Render_Products->options([
         'product' => $product
      ]);
   }

   /*

	Hooks

	*/
   public function hooks()
   {
      add_action('wps_products_button_add_to_cart', [$this, 'wps_products_buy_button']);
      // add_action('wps_products_title', [$this, 'wps_products_title']);
      add_action('wps_products_description', [$this, 'wps_products_description']);
      add_action('wps_products_pricing', [$this, 'wps_products_pricing']);
      add_action('wps_products_compare_at_price', [$this, 'wps_products_pricing'], 10, 2);
      add_action('wps_products_quantity', [$this, 'wps_products_quantity']);
      add_action('wps_products_options', [$this, 'wps_products_options']);
      add_action('wps_product_images', [$this, 'wps_product_images']);
   }

   /*

	Init

	*/
   public function init()
   {
      $this->hooks();
   }
}
