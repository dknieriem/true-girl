<?php

namespace WPS\Utils;

if (!defined('ABSPATH')) {
   exit();
}

class Sorting
{
   /*

	Generic function to sort by a specific key / value

	*/
   public static function sort_by($items, $type)
   {
      usort($items, [__CLASS__, 'sort_by_' . $type]);

      return $items;
   }

   public static function sort_by_type($a, $b, $type)
   {
      $a_value = (int) $a->$type;
      $b_value = (int) $b->$type;

      if ($a_value == $b_value) {
         return 0;
      }

      return $a_value < $b_value ? -1 : 1;
   }

   public static function sort_by_string($a, $b, $prop)
   {
      if (is_object($a) && isset($a->$prop)) {
         return strcmp($a->$prop, $b->$prop);
      }

      return;
   }

   public static function sort_by_position($a, $b)
   {
      return self::sort_by_type($a, $b, 'position');
   }

   public static function sort_by_price($a, $b)
   {
      return self::sort_by_type($a, $b, 'price');
   }

   public static function sort_by_compare_at_price($a, $b)
   {
      return self::sort_by_type($a, $b, 'compare_at_price');
   }

   public static function sort_by_title($a, $b)
   {
      return self::sort_by_string($a, $b, 'title');
   }

   public static function sort_by_updated_at($a, $b)
   {
      return self::sort_by_string($a, $b, 'updated_at');
   }

   public static function sort_by_created_at($a, $b)
   {
      return self::sort_by_string($a, $b, 'created_at');
   }

   public static function reverse($items)
   {
      return array_reverse($items);
   }
}
