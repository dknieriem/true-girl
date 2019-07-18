<?php

namespace WPS\Render;

if (!defined('ABSPATH')) {
   exit();
}

/*

Render: Products

*/
class Cart
{
   public $Templates;
   public $Render_Data;

   public function __construct($Templates, $Render_Data, $Defaults_Cart)
   {
      $this->Templates = $Templates;
      $this->Render_Data = $Render_Data;
      $this->Defaults_Cart = $Defaults_Cart;
   }

   /*

	Cart: Cart

	*/
   public function cart_icon($data = [])
   {

      return $this->Templates->load([
         'path' => 'components/cart/icon/wrapper',
         'type' => 'cart',
         'defaults' => 'cart',
         'data' => array_merge($this->Defaults_Cart->cart_icon($data), $data),
         'skip_required_data' => true
      ]);
   }

}
