<?php
namespace OTGS\Toolset\Maps\Model\Shortcode;

/**
 * Class ShortcodeAbstract
 * @package OTGS\Toolset\Maps\Model\Shortcode
 * @since 1.6
 */
abstract class ShortcodeAbstract implements ShortcodeInterface {
	protected $atts = array();
	protected $content = '';
	protected $shortcode_tag = '';

	/**
	 * Prepares shortcode properties for easy usage in child classes
	 * @param array|string $atts
	 * @param string $content
	 * @param string $shortcode_tag
	 */
	public function __construct( $atts, $content, $shortcode_tag ) {
		$this->atts = wp_parse_args( $atts, static::get_defaults() );
		$this->content = $content;
		$this->shortcode_tag = $shortcode_tag;
	}
}