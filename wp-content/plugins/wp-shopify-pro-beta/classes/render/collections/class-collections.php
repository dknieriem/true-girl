<?php

namespace WPS\Render;

if (!defined('ABSPATH')) {
   exit();
}

/*

Render: Collections

*/
class Collections
{
   public $Templates;
   public $Render_Data;
   public $Defaults_Collections;

   public function __construct($Templates, $Render_Data, $Defaults_Collections)
   {
      $this->Templates = $Templates;
      $this->Render_Data = $Render_Data;
      $this->Defaults_Collections = $Defaults_Collections;
   }

   /*

	Products: Gallery

	*/
   public function collections($data = [])
   {
      return $this->Templates->load([
         'path' => 'components/collections/collections-all',
         'type' => 'collections',
         'defaults' => 'collections',
         'data' => array_merge($this->Defaults_Collections->collections($data), $data)
      ]);
   }
}
