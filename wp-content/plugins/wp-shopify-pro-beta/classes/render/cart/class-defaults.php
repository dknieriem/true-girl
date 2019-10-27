<?php

namespace WPS\Render\Cart;

use WPS\Utils\Data;

if (!defined('ABSPATH')) {
    exit();
}

class Defaults
{
    public $plugin_settings;
    public $Render_Attributes;

    public function __construct($plugin_settings, $Render_Attributes)
    {
        $this->plugin_settings = $plugin_settings;
        $this->Render_Attributes = $Render_Attributes;
    }

    

    public function all_cart_icon_attributes($attrs)
    {
        return [
         'render_from_server' => apply_filters('wps_cart_render_from_server', $this->Render_Attributes->attr($attrs, 'render_from_server', false)),
         'icon' => apply_filters('wps_cart_icon_image', $this->Render_Attributes->attr($attrs, 'icon', false)),
         'icon_color' => apply_filters('wps_cart_icon_color', $this->Render_Attributes->attr($attrs, 'icon_color', Data::coerce($this->plugin_settings['general']->cart_icon_color, 'string'))),
         'type' => apply_filters('wps_cart_type', $this->Render_Attributes->attr($attrs, 'type', 'inline')),
         'counter_background_color' => apply_filters('wps_cart_counter_background_color', $this->Render_Attributes->attr($attrs, 'counter_background_color', Data::coerce($this->plugin_settings['general']->cart_counter_color, 'string'))),
         'counter_text_color' => apply_filters('wps_cart_counter_text_color', $this->Render_Attributes->attr($attrs, 'counter_text_color', '#000')),
         'show_counter' => apply_filters('wps_cart_show_counter', $this->Render_Attributes->attr($attrs, 'show_counter', true)),
         'data_type' => apply_filters('wps_cart_data_type', $this->Render_Attributes->attr($attrs, 'data_type', false)),
         'hide_wrapper' => apply_filters('wps_cart_hide_wrapper', $this->Render_Attributes->attr($attrs, 'hide_wrapper', false))
      ];
    }

    public function cart_icon($attrs)
    {
        return $this->Render_Attributes->standardize_layout_data($this->all_cart_icon_attributes($attrs));
    }
}