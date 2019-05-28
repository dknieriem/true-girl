<?php

// @codingStandardsIgnoreFile

/**
 * Class TVO_Product
 */
class TVO_Product extends TVE_Dash_Product_Abstract {
	protected $tag = 'tvo';

	protected $title = 'Thrive Ovation';

	protected $productIds = array();

	protected $type = 'plugin';

	/**
	 * TVO_Product constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl = TVO_ADMIN_URL . '/img/tvo-logo-icon.png';
		$this->logoUrlWhite = TVO_ADMIN_URL . '/img/tvo-logo-icon-white.png';

		$this->description = __( 'Thrive Ovation is a testimonial management plugin.', TVO_TRANSLATE_DOMAIN );

		$this->button = array(
			'active' => true,
			'url'    => admin_url( 'admin.php?page=tvo_admin_dashboard' ),
			'label'  => __( 'Thrive Ovation', TVO_TRANSLATE_DOMAIN ),
		);

		$this->moreLinks = array(
			'support'   => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/forums/forum/plugins/thrive-ovation',
				'target'     => '_blank',
				'text'       => __( 'Support', TVO_TRANSLATE_DOMAIN ),
			),
			'tutorials' => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://thrivethemes.com/thrive-knowledge-base/thrive-ovation/',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', TVO_TRANSLATE_DOMAIN ),
			),
		);
	}
}