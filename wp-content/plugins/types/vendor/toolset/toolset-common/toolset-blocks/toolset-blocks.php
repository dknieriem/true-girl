<?php
/**
 * Handles the creation and initialization of the all the Gutenberg integration stuff.
 *
 * @since 2.6.0
 */
class Toolset_Blocks {
	const TOOLSET_GUTENBERG_BLOCKS_CATEGORY_SLUG = 'toolset';

	public function load_blocks() {
		$gutenberg_active = new Toolset_Condition_Plugin_Gutenberg_Active();

		if ( ! $gutenberg_active->is_met() ) {
			return;
		}

		$this->init_hooks();

		$toolset_blocks = array(
			// Toolset_Blocks_Custom_HTML_Extension::BLOCK_NAME,
			Toolset_Blocks_Paragraph_Extension::BLOCK_NAME,
		);

		$factory = new Toolset_Gutenberg_Block_Factory();
		new Toolset_Gutenberg_Block_REST_Helper();

		foreach ( $toolset_blocks as $toolset_block_name ) {
			$block = $factory->get_block( $toolset_block_name );
			if ( null !== $block ) {
				$block->init_hooks();
			};
		}
	}

	/**
	 * Initialize common hooks for the Toolset Gutenberg blocks.
	 */
	public function init_hooks() {
		add_filter( 'block_categories', array( $this, 'register_toolset_block_category' ) );
	}

	/**
	 * Registers the Toolset Gutenberg blocks category.
	 *
	 * @param array $categories The array with the categories of the Gutenberg widgets.
	 *
	 * @return array
	 */
	public function register_toolset_block_category( $categories ) {
		if ( ! array_search( 'toolset', array_column( $categories, 'slug' ), true ) ) {
			$categories = array_merge(
				$categories,
				array(
					array(
						'slug'  => 'toolset',
						'title' => __( 'Toolset', 'wpv-views' ),
					),
				)
			);
		}

		return $categories;
	}
}
