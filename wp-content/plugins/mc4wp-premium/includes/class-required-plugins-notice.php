<?php

class MC4WP_Required_Plugins_Notice {

	/**
	 * @var array
	 */
	protected $required_plugins = array();

	/**
	 * @var string
	 */
	protected $plugin_name = '';

	/**
	 * The required capability
	 */
	const CAPABILITY = 'install_plugins';

	/**
	 * $dependencies is an array of required plugins, in the following format:
	 *
	 * 'plugin-slug' => array(
	 *    'name' => "Hello Dolly",   // name of plugin
	 *    'version' => "1.0"         // required version
	 * )
	 *
	 * @param string $plugin_name
	 * @param array $required_plugins
	 */
	public function __construct( $plugin_name, array $required_plugins ) {
		$this->plugin_name = $plugin_name;
		$this->required_plugins = $required_plugins;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'admin_notices', array( $this, 'notice' ) );
	}

	/**
	 * Show notice
	 */
	public function notice() {

		// only show to users who have the required capability
		if( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		add_thickbox();

		echo '<div class="notice is-dismissible notice-info">';

		# List of required plugins
		echo '<p>';
		echo sprintf( __( "%s requires the following plugin(s):", 'mailchimp-for-wp' ), '<strong>' . $this->plugin_name . '</strong>' );
		echo '<ul class="ul-square">';
		foreach( $this->required_plugins as $plugin ) {

			$link_class = '';
			$in_wordpress_repo = preg_match( '/wordpress\.org\/plugins\/([\w+-]+)/i', $plugin['url'], $matches );

			if( $in_wordpress_repo && isset( $matches[1] ) ) {
				$slug = $matches[1];
				$plugin['url'] = network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $slug . '&TB_iframe=true&width=600&height=550' );
				$link_class = 'thickbox';
			}

			echo sprintf( '<li><a href="%s" class="%s">%s</a> (' . __( 'version %s or higher', 'mailchimp-for-wp' ) . ')</li>', $plugin['url'], $link_class, $plugin['name'], $plugin['version'] );
		}
		echo '</ul>';

		echo __( 'Either install or update the required plugins. If you already have the required version(s) installed, please do not forget to activate the plugins.', 'mailchimp-for-wp' );
		echo '</p>';

		echo '</div>';
	}

}