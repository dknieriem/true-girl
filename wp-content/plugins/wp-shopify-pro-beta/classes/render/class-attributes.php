<?php

namespace WPS\Render;

use WPS\Utils;
use WPS\Utils\Data as Utils_Data;

if (!defined('ABSPATH')) {
    exit();
}

class Attributes
{
    public function __construct()
    {
    }

    

    /*

    Formats shortcode attribute

    UNDER TEST

     */
    public function format_shortcode_attr($arg_value)
    {
        if (is_string($arg_value)) {
            if (Utils_Data::contains_comma($arg_value)) {
                return array_filter(Utils::comma_list_to_array($this->to_type(trim($arg_value))));
            } else {
                $this->to_type(trim($arg_value));
            }
        }

        return $this->to_type($arg_value);
    }

    public function to_type($value)
    {
        if ($value === 'true' || $value === 'false') {
            return $this->attr_to_boolean($value);
        }

        if (is_numeric($value)) {
            return $this->attr_to_integer($value);
        }

        return $value;
    }

    /*

    Formats shortcode attributeS

    UNDER TEST

     */
    public function format_shortcode_attrs($shortcode_args)
    {
        if (empty($shortcode_args)) {
            return [];
        }

        foreach ($shortcode_args as $arg_name => $arg_value) {
            $shortcode_args[$arg_name] = $this->format_shortcode_attr($arg_value);
        }

        return $shortcode_args;
    }

    public function standardize_layout_data($shortcode_args)
    {
        if (!isset($shortcode_args) || !$shortcode_args) {
            return [];
        }

        return $this->format_shortcode_attrs($shortcode_args);
    }

    public function combine_user_and_default_atts($atts, $defaults_fn)
    {
        return shortcode_atts($defaults_fn(), $atts);
    }

    public function gather_shortcode_atts($atts, $defaults_method)
    {
        $combined = $this->combine_user_and_default_atts($atts, function () use ($defaults_method) {
            return $defaults_method();
        });

        return $this->standardize_layout_data($combined);
    }

    public function get_attributes($atts, $class, $method)
    {
        return $this->gather_shortcode_atts($atts, [$class, $method]);
    }

    public function attr_to_boolean($attr_val)
    {
        if ($attr_val === 'true' || $attr_val == 1) {
            return true;
        }

        return false;
    }

    public function attr_to_integer($attr_val)
    {
        return (int) $attr_val;
    }

    public function attr_to_string($attr_val)
    {
        if (is_array($attr_val)) {
            return $attr_val;
        }

        if (is_bool($attr_val)) {
            return ($attr_val) ? 'true' : 'false';
        }

        return (string) $attr_val;
    }

    public function has_attr($attributes, $value)
    {
        return isset($attributes[$value]) && !empty($attributes[$value]);
    }

    public function add_boolean_to_query($key, $val)
    {
        if (is_bool($val)) {
            $bool_converted = ($val) ? 'true' : 'false';
        } else {
            $bool_converted = $val;
        }

        return $key . ':' . $bool_converted;
    }

    /*

    Defaults to a phrase query which surrounds each term in double quotes

     */
    public function add_string_to_query($key, $val)
    {
        return $key . ':' . '"' . $val . '"';
    }

    public function query_checks($key, $val, $query)
    {
        if (is_bool($val) || $val === 'true' || $val === 'false') {
            $query .= $this->add_boolean_to_query($key, $val);
        } else {
            $query .= $this->add_string_to_query($key, $val);
        }

        return $query;
    }

    public function add_nested_query($key, $values, $all_attrs)
    {
        $query = '';

        foreach ($values as $val) {
            $query = $this->query_checks($key, $val, $query);

            if ($val !== end($values)) {
                $query .= ' ' . strtoupper($all_attrs['connective']) . ' ';
            }
        }

        return $query;
    }

    public function build_query($filter_params, $all_attrs)
    {

        if (!array_filter($filter_params)) {
            return 'TITLE:*'; // Returns the default query instead
        }

        
        if (isset($filter_params['available_for_sale'])) {
if ($filter_params['available_for_sale'] === 'unavailable') {
           $filter_params['available_for_sale'] = 'true';

        } else if ($filter_params['available_for_sale'] === 'available') {
            $filter_params['available_for_sale'] = 'true';

        } else if ($filter_params['available_for_sale'] === 'any') {
            $filter_params['available_for_sale'] = false;
        }
        }
        

        $query = '';
        $valid_filter_params = array_filter($filter_params);

        foreach ($valid_filter_params as $key => $value) {

            if (is_array($value)) {
                $query .= $this->add_nested_query($key, $value, $all_attrs);
            } else {
                $query = $this->query_checks($key, $value, $query);
            }

            if ($value !== end($valid_filter_params)) {
                $query .= ' ' . strtoupper($all_attrs['connective']) . ' ';
            }
        }

        return $query;
    }

    public function attr($attrs, $attr_name, $default, $override = false)
    {
        if (empty($attrs[$attr_name])) {
            return $default;
        }

        return $this->has_attr($attrs, $attr_name) ? $attrs[$attr_name] : $default;
    }
}