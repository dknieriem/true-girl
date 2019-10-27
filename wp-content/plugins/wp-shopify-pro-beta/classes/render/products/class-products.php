<?php

namespace WPS\Render;

if (!defined('ABSPATH')) {
    exit();
}

/*

Render: Products

 */
class Products
{
    public $Templates;
    public $Render_Data;
    public $Defaults_Products;

    public function __construct($Templates, $Render_Data, $Defaults_Products)
    {
        $this->Templates = $Templates;
        $this->Render_Data = $Render_Data;
        $this->Defaults_Products = $Defaults_Products;

    }

    /*

    Products: Add to cart

    Mandatory params:

    'path'
    'name'
    'type'
    'defaults'
    'data'

     */
    public function add_to_cart($data = [])
    {
        return $this->Templates->load([
            'path' => 'components/products/buy-button/buy',
            'name' => 'button',
            'type' => 'products',
            'defaults' => 'add_to_cart',
            'data' => array_merge($this->Defaults_Products->product_add_to_cart($data), $data)
        ]);
    }

    /*

    Simple alias for above add_to_cart

     */
    public function buy_button($data = [])
    {
        return $this->add_to_cart($data);
    }

    /*

    Products: Title

     */
    public function title($data = [])
    {
        return $this->Templates->load([
            'path' => 'components/products/title/title',
            'type' => 'products',
            'defaults' => 'title',
            'data' => array_merge($this->Defaults_Products->product_title($data), $data)
        ]);
    }

    /*

    Products: Description

     */
    public function description($data = [])
    {

        return $this->Templates->load([
            'path' => 'components/products/description/description',
            'type' => 'products',
            'defaults' => 'description',
            'data' => array_merge($this->Defaults_Products->product_description($data), $data),
        ]);
    }

    /*

    Products: Pricing

     */
    public function pricing($data = [])
    {
        return $this->Templates->load([
            'path' => 'components/products/pricing/pricing',
            'type' => 'products',
            'defaults' => 'pricing',
            'data' => array_merge($this->Defaults_Products->product_pricing($data), $data),
            'combine' => true,
            'pre_render' => [
                'class_name' => 'Pre_Render_Pricing',
                'method_name' => 'pre_render_product_pricing',
            ],
        ]);
    }

    /*

    Products: Gallery

     */
    public function gallery($data = [])
    {
        return $this->Templates->load([
            'path' => 'components/products/gallery/gallery',
            'type' => 'products',
            'defaults' => 'gallery',
            'data' => array_merge($this->Defaults_Products->product_gallery($data), $data),
            'pre_render' => [
                'class_name' => 'Pre_Render_Gallery',
                'method_name' => 'pre_render_product_gallery',
            ],
        ]);
    }

    /*

    Products: Gallery

     */
    public function products($data = [])
    {
        return $this->Templates->load([
            'path' => 'components/products/products-all',
            'type' => 'products',
            'defaults' => 'products',
            'cache_key' => 'wp_shopify_shortcode_wps_products_',
            'data' => array_merge($this->Defaults_Products->products($data), $data)
        ]);
    }
}
