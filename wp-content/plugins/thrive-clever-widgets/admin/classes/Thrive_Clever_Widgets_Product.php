<?php

class Thrive_Clever_Widgets_Product extends TVE_Dash_Product_Abstract
{
    protected $tag = 'tcw';

    protected $title = 'Clever Widgets';

    protected $type = 'plugin';

    protected $productIds = array();

    public function __construct($data = array())
    {
        parent::__construct($data);

        $this->logoUrl = plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/css/images/clever-widgets-logo.png';
        $this->logoUrlWhite = plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/css/images/clever-widgets-logo-white.png';

        $this->description = __('Show highly relevant widget content based on categories, tags, pages and more.', 'thrive-cw');

        $this->button = array(
            'label' => __('Set up widgets', 'thrive-cw'),
            'url' => admin_url('widgets.php'),
            'active' => true
        );

        $this->moreLinks = array(
            'tutorials' => array(
                'class' => 'tve-leads-tutorials',
                'icon_class' => 'tvd-icon-graduation-cap',
                'href' => 'https://thrivethemes.com/thrive-knowledge-base/?section_id=2653',
                'target' => '_blank',
                'text' => __('Tutorials', 'thrive-leads'),
            ),
            'support' => array(
                'class' => 'tve-leads-tutorials',
                'icon_class' => 'tvd-icon-life-bouy',
                'href' => 'https://thrivethemes.com/forums/forum/plugins/clever-widgets/',
                'target' => '_blank',
                'text' => __('Support', 'thrive-leads'),
            ),
        );


    }
}
