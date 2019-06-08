<?php

/**
 * Toolset Gutenberg Blocks factory class.
 *
 * @since 2.6.0
 */
class Toolset_Gutenberg_Block_Factory {

	public function __construct() {
		add_action( 'init', array( $this, 'register_common_block_editor_assets' ) );
	}

	/**
	 * Get the Toolset Gutenberg Block.
	 *
	 * @param string $block The name of the block.
	 *
	 * @return null|Toolset_Blocks_Content_Template|Toolset_Blocks_View|Toolset_Blocks_Custom_HTML_Extension|Toolset_Blocks_Paragraph_Extension
	 */
	public function get_block( $block ) {
		$return_block = null;

		switch ( $block ) {
			case Toolset_Blocks_Custom_HTML_Extension::BLOCK_NAME:
				$return_block = new Toolset_Blocks_Custom_HTML_Extension();
				break;
			case Toolset_Blocks_Paragraph_Extension::BLOCK_NAME:
				$return_block = new Toolset_Blocks_Paragraph_Extension();
				break;

		}

		return $return_block;
	}

	/**
	 * Register the needed assets for the Toolset Gutenberg blocks on the editor.
	 *
	 * @since 2.6.0
	 */
	public function register_common_block_editor_assets() {
		$toolset_assets_manager = Toolset_Assets_Manager::get_instance();
		$toolset_assets_manager->register_style(
			'toolset-blocks-react-select-css',
			TOOLSET_COMMON_URL . '/toolset-blocks/assets/css/third-party/react-select.css',
			array(),
			TOOLSET_COMMON_VERSION
		);
	}
}
