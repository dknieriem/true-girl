<?php

class Tho_Product extends TVE_Dash_Product_Abstract {
	protected $tag = 'tho';

	protected $title = 'Thrive Headline Optimizer';

	protected $productIds = array();

	protected $type = 'plugin';

	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl      = THO_ADMIN_URL . '/img/tho-logo-icon.png';
		$this->logoUrlWhite = THO_ADMIN_URL . '/img/tho-logo-icon-white.png';

		$this->description = __( 'Generate reports to find out how well your site is performing.', THO_TRANSLATE_DOMAIN );

		$this->button = array(
			'active' => true,
			'url'    => admin_url( 'admin.php?page=tho_admin_dashboard' ),
			'label'  => __( 'Thrive Headline Optimizer', THO_TRANSLATE_DOMAIN )
		);

		$this->moreLinks = array(
			'support'   => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/forums/forum/plugins/thrive-headline-optimizer',
				'target'     => '_blank',
				'text'       => __( 'Support', THO_TRANSLATE_DOMAIN ),
			),
			'tutorials' => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://thrivethemes.com/thrive-knowledge-base/?section_id=8579',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', THO_TRANSLATE_DOMAIN ),
			),
		);
	}

}