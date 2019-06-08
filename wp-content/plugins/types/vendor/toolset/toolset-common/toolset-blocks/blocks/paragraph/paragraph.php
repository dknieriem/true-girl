<?php

/**
 * Handles the extension of the core Paragraph Gutenberg block to include the button for the
 * Fields and Views shortcodes.
 *
 * @since 2.6.0
 */
class Toolset_Blocks_Paragraph_Extension extends Toolset_Gutenberg_Block {

	const BLOCK_NAME = 'toolset/paragraph';

	/**
	 * Initialize hooks for the core Paragraph editor (Gutenberg) block.
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'register_block_editor_assets' ) );

		add_action( 'init', array( $this, 'register_block_type' ) );

		// Hook scripts function into block editor hook.
		add_action( 'enqueue_block_editor_assets', array( $this, 'blocks_editor_scripts' ) );

		// Hook scripts function into block editor hook.
		add_action( 'enqueue_block_assets', array( $this, 'blocks_scripts' ) );
	}

	/**
	 * Register the needed assets for the Toolset Gutenberg blocks
	 *
	 * @since 2.6.0
	 */
	public function register_block_editor_assets() {
		$this->toolset_assets_manager->register_script(
			'toolset-paragraph-block-js',
			$this->constants->constant( 'TOOLSET_COMMON_URL' ) . '/toolset-blocks/assets/js/paragraph.block.editor.js',
			array( 'wp-editor' ),
			$this->constants->constant( 'TOOLSET_COMMON_VERSION' )
		);

		/**
		 * Filter to allow extending the buttons in the toolbar of the core Paragraph block.
		 *
		 * @param array $extension_buttons The buttons information to be used for the extension.
		 *
		 * @since 3.2.5
		 */
		$extension_buttons = apply_filters( 'toolset_filter_extend_the_core_paragraph_block', array() );

		if (
			$this->types_active->is_met() &&
			! $this->views_active->is_met()
		) {
			$extension_buttons['types'] = array(
				'clickCallback' => 'window.Toolset.Types.shortcodeGUI.openMainDialog',
			);
		}

		if ( $this->cred_active->is_met() ) {
			$extension_buttons['cred'] = array(
				'clickCallback' => 'window.Toolset.CRED.shortcodeGUI.openCredDialog',
			);
		}

		wp_localize_script(
			'toolset-paragraph-block-js',
			'toolset_paragraph_block_strings',
			array(
				'extensionButtons' => $extension_buttons,
			)
		);

		$this->toolset_assets_manager->register_style(
			'toolset-paragraph-block-editor-css',
			$this->constants->constant( 'TOOLSET_COMMON_URL' ) . '/toolset-blocks/assets/css/paragraph.block.editor.css',
			array(),
			$this->constants->constant( 'TOOLSET_COMMON_VERSION' )
		);
	}

	/**
	 * Enqueue assets, needed on the editor side, for the Toolset Gutenberg blocks
	 *
	 * @since 2.6.0
	 */
	public function blocks_editor_scripts() {
		do_action( 'toolset_enqueue_scripts', array( 'toolset-paragraph-block-js' ) );
		do_action( 'toolset_enqueue_styles', array( 'toolset-paragraph-block-editor-css' ) );
	}

	/**
	 * Enqueue assets, needed on the frontend side, for the Toolset Gutenberg blocks
	 *
	 * @since 2.6.0
	 */
	public function blocks_scripts(){}

	/**
	 * Register block type. We can use this method to register the editor & frontend scripts as well as the render callback.
	 *
	 * @note For now the scripts registration is disabled as it creates console errors on the classic editor.
	 *
	 * @since 2.6.0
	 */
	public function register_block_type(){}
}
