<?php

namespace WPS\Render;

defined('ABSPATH') ?: exit();

/*

Render: Storefront

*/
class Storefront
{
   public $Templates;
   public $Render_Data;

   public function __construct($Templates, $Render_Data)
   {
      $this->Templates = $Templates;
      $this->Render_Data = $Render_Data;
   }

   /*

	Storefront: Storefront

	*/
   public function storefront($data = [])
   {
      return $this->Templates->load([
         'path' => 'components/storefront/storefront',
         'type' => 'storefront',
         'defaults' => 'storefront',
         'data' => $data,
         'skip_required_data' => true
      ]);
   }
}