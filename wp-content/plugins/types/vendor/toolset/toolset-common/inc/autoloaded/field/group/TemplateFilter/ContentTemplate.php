<?php

namespace OTGS\Toolset\Common\Field\Group\TemplateFilter;

/**
 * Represents a filter by a Content Template from Toolset.
 *
 * @since Types 3.3
 */
class ContentTemplate implements TemplateFilterInterface {


	/** @var \WP_Post */
	private $template_post;


	/**
	 * ContentTemplate constructor.
	 *
	 * @param \WP_Post $template_post Post holding the content template.
	 */
	public function __construct( \WP_Post $template_post ) {
		$this->template_post = $template_post;
	}


	/**
	 * @param \IToolset_Post $post
	 *
	 * @return bool True if the template matches given post.
	 */
	public function is_match_for_post( \IToolset_Post $post ) {
		return ( $this->template_post->ID === $post->get_assigned_content_template() );
	}
}
