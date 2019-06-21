<?php
namespace OTGS\Toolset\Maps\Controller\Compatibility;

use Toolset_Addon_Maps_Common;

class MapBlock extends \Toolset_Gutenberg_Block {

	const BLOCK_NAME = 'toolset/map';

	/**
	 * Block initialization.
	 *
	 * @return void
	 */
	public function init_hooks() {
		if ( $this->is_the_right_api_key_entered() ) {
			add_action( 'init', array( $this, 'register_block_editor_assets' ) );
			add_action( 'init', array( $this, 'register_block_type' ) );
		}
	}

	/**
	 * Block editor asset registration.
	 *
	 * @return void
	 */
	public function register_block_editor_assets() {
		$editor_script_dependencies = array( 'wp-editor', 'lodash', 'jquery', 'views-addon-maps-script' );
		$api_used = apply_filters( 'toolset_maps_get_api_used', '' );

		if ( Toolset_Addon_Maps_Common::API_GOOGLE === $api_used ) {
			array_push(
				$editor_script_dependencies,
				'marker-clusterer-script',
				'overlapping-marker-spiderfier'
			);
		};

		$this->toolset_assets_manager->register_script(
			'toolset-map-block-js',
			TOOLSET_ADDON_MAPS_URL . MapsEditorBlocks::TOOLSET_MAPS_BLOCKS_ASSETS_RELATIVE_PATH . '/js/map.block.editor.js',
			$editor_script_dependencies,
			TOOLSET_ADDON_MAPS_VERSION
		);

		if ( function_exists( 'wp_get_jed_locale_data' ) ) {
			$locale = wp_get_jed_locale_data( 'toolset-maps' );
		} elseif ( function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$locale = gutenberg_get_jed_locale_data( 'toolset-maps' );
		} else {
			$locale = array(
				array(
					'domain' => 'toolset-maps',
					'lang' => 'en_US',
				),
			);
		}

		wp_localize_script(
			'toolset-map-block-js',
			'toolset_map_block_strings',
			array(
				'blockName' => self::BLOCK_NAME,
				'blockCategory' => \Toolset_Blocks::TOOLSET_GUTENBERG_BLOCKS_CATEGORY_SLUG,
				'mapCounter' => $this->get_map_counter(),
				'markerCounter' => $this->get_marker_counter(),
				'locale' => $locale,
				'api' => apply_filters( 'toolset_maps_get_api_used', '' ),
			)
		);

		$this->toolset_assets_manager->register_style(
			'toolset-map-block-editor-css',
			TOOLSET_ADDON_MAPS_URL . MapsEditorBlocks::TOOLSET_MAPS_BLOCKS_ASSETS_RELATIVE_PATH . '/css/map.block.editor.css',
			array(),
			TOOLSET_ADDON_MAPS_VERSION
		);
	}

	/**
	 * Server side block registration.
	 *
	 * @return void
	 */
	public function register_block_type() {
		register_block_type(
			self::BLOCK_NAME,
			array(
				'attributes' => array(
					'mapId'      => array(
						'type' => 'string',
						'default' => '',
					),
					'mapMarkerClustering' => array(
						'type' => 'boolean',
						'default' => false,
					),
					'mapDraggable' => array(
						'type' => 'boolean',
						'default' => true,
					),
					'mapScrollable' => array(
						'type' => 'boolean',
						'default' => true,
					),
					'mapDoubleClickZoom' => array(
						'type' => 'boolean',
						'default' => true,
					),
					'mapType' => array(
						'type' => 'string',
						'default' => Toolset_Addon_Maps_Common::$map_defaults['map_type'],
					),
					// TODO: map controls are Google only for the time, implement them when doing google-specific features
					//'mapTypeControl' => array(
					//	'type' => 'boolean',
					//	'default' => true,
					//),
					//'zoomControls' => array(
					//	'type' => 'boolean',
					//	'default' => true,
					//),
					'mapLoadingText' => array(
						'type' => 'string',
						'default' => '',
					),
					'shortcodes' => array(
						'type' => 'string',
					),
					// There is array type, but then Gutenberg goes crazy validating. Instead, we have to serialize
					// arrays ourselves...
					'markerId' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
					'markerAddress' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
					'markerSource' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( 'address' ) ),
					),
					'currentVisitorLocationRenderTime' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( 'immediate' ) ),
					),
					'markerLat' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
					'markerLon' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
					'markerTitle' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
					'popupContent' => array(
						'type' => 'string',
						'default' => wp_json_encode( array( '' ) ),
					),
				),
				'editor_script' => 'toolset-map-block-js',
				'editor_style' => 'toolset-map-block-editor-css',
				'render_callback' => array( $this, 'render_preview' ),
			)
		);
	}

	/**
	 * Renders map & marker shortcodes to HTML.
	 *
	 * @param array $attributes Contains attributes + added shortcodes which are the only thing used.
	 * @param string $content Previous version of rendered HTML. Unused.
	 *
	 * @return string
	 */
	public function render_preview( array $attributes, $content ) {
		// If Views are not active, we need to init shortcode rendering methods...
		if ( ! $this->views_active->is_met() ) {
			require_once TOOLSET_ADDON_MAPS_PATH . '/includes/toolset-maps-views.class.php';
			$views = new \Toolset_Addon_Maps_Views();
			$views->init();
		}

		// First shortcode in the array is the the map shortcode, all others are markers
		$shortcodes = explode("\n", $attributes['shortcodes'] );
		$map_shortcode = array_shift( $shortcodes );

		$output = do_shortcode( $map_shortcode );

		foreach ( $shortcodes as $marker_shortcode ) {
			$output .= do_shortcode( $marker_shortcode );
		}

		return $output;
	}

	/**
	 * @param string $option
	 *
	 * @return mixed
	 */
	private function get_saved_option( $option ) {
		$saved_options = apply_filters( 'toolset_filter_toolset_maps_get_options', array() );

		return $saved_options[$option];
	}

	/**
	 * @return int
	 */
	private function get_map_counter() {
		return $this->get_saved_option( 'map_counter' );
	}

	/**
	 * @return int
	 */
	private function get_marker_counter() {
		return $this->get_saved_option( 'marker_counter' );
	}

	/**
	 * Multi-API aware check for API keys.
	 * @return bool
	 */
	private function is_the_right_api_key_entered() {
		$api_used = apply_filters( 'toolset_maps_get_api_used', '' );

		if ( Toolset_Addon_Maps_Common::API_GOOGLE === $api_used ) {
			$key = apply_filters( 'toolset_filter_toolset_maps_get_api_key', '' );
		} else {
			$key = apply_filters( 'toolset_filter_toolset_maps_get_azure_api_key', '' );
		}
		return !empty( $key );
	}
}
