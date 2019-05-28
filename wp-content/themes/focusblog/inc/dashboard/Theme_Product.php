<?php

/**
 * Created by PhpStorm.
 * User: Sala
 * Date: 10-Dec-15
 * Time: 17:02
 */
class Theme_Product extends TVE_Dash_Product_Abstract {
	protected $tag = 'focusblog';

	protected $title = 'FocusBlog Theme';

	protected $productIds = array();

	protected $type = 'theme';

	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl = get_template_directory_uri() . '/images/logo.png';
		$this->logoUrlWhite = get_template_directory_uri() . '/images/logo-white.png';

		$this->description = __( 'Blogging theme designed for speed, readability and reader engagement.', 'thrive' );

		$this->button = array(
			'label'  => __( 'Theme Options', 'thrive' ),
			'url'    => admin_url( 'admin.php?page=thrive_admin_options' ),
			'active' => true
		);

		$this->moreLinks = array(
			'templates' => array(
				'class'      => 'tve-page-templates',
				'icon_class' => 'tvd-icon-file-text',
				'href'       => 'admin.php?page=thrive_admin_page_templates',
				'text'       => __( 'Page Templates', 'thrive' ),
			),
			'tutorials' => array(
				'class'      => 'tve-theme-tutorials',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://thrivethemes.com/members/focusblog/',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', 'thrive' ),
			),
			'support'   => array(
				'class'      => 'tve-theme-tutorials',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/forums/forum/all-themes/focusblog/',
				'target'     => '_blank',
				'text'       => __( 'Support', 'thrive' ),
			),
		);
	}
}