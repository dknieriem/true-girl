<?php

namespace WPS\Render;

if (!defined('ABSPATH')) {
   exit();
}

/*

Render: Search

*/
class Search
{
   public $Templates;
   public $Render_Data;

   public function __construct($Templates, $Render_Data)
   {
      $this->Templates = $Templates;
      $this->Render_Data = $Render_Data;
   }

   /*

	Search: Search

	*/
   public function search($data = [])
   {
      return $this->Templates->load([
         'path' => 'components/search/search',
         'type' => 'search',
         'defaults' => 'search',
         'data' => $data,
         'skip_required_data' => true
      ]);
   }
}
