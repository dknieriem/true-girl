<?php
namespace OTGS\Toolset\Maps\Controller\Compatibility;

/**
 * Class MapsEditorBlockFactory
 *
 * @since 1.7
 * @package OTGS\Toolset\Maps\Controller\Compatibility
 */
class MapsEditorBlockFactory {
	/**
	 * Get the Toolset Maps editor (Gutenberg) Block.
	 *
	 * @param string $block The name of the block.
	 *
	 * @return null|
	 */
	public function get_block( $block ) {
		$return_block = null;

		switch ( $block ) {
			case MapBlock::BLOCK_NAME:
				$return_block = new MapBlock();
				break;
		}

		return $return_block;
	}
}
