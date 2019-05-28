<?php

/**
 * Class TQB_Product
 */
class TQB_Product extends TVE_Dash_Product_Abstract {
	/**
	 * Tag of the product
	 *
	 * @var string tag.
	 */
	protected $tag = 'tqb';

	/**
	 * Name of the product displayed in Dashboard
	 *
	 * @var string title
	 */
	protected $title = 'Thrive Quiz Builder';

	/**
	 * Type of product
	 *
	 * @var string type of the product
	 */
	protected $type = 'plugin';

	/**
	 * TQB_Product constructor.
	 *
	 * @param array $data info used in dashboard.
	 */
	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl      = tqb()->plugin_url( 'assets/images/tqb-logo.png' );
		$this->logoUrlWhite = tqb()->plugin_url( 'assets/images/tqb-logo-white.png' );
		$this->productIds   = array();

		$this->description = __( 'Engage your visitors with a fun quiz and find out more about them.', Thrive_Quiz_Builder::T );

		$this->button = array(
			'active' => true,
			'url'    => admin_url( 'admin.php?page=tqb_admin_dashboard' ),
			'label'  => __( 'Quiz Builder Dashboard', Thrive_Quiz_Builder::T ),
		);

		$this->moreLinks = array(
			'support'   => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/forums/forum/plugins/thrive-quiz-builder/',
				'target'     => '_blank',
				'text'       => __( 'Support', Thrive_Quiz_Builder::T ),
			),
			'tutorials' => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://thrivethemes.com/thrive-knowledge-base/thrive-quiz-builder/',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', Thrive_Quiz_Builder::T ),
			),
		);
	}
}
