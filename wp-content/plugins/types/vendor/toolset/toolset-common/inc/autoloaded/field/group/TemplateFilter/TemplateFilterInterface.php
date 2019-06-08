<?php

namespace OTGS\Toolset\Common\Field\Group\TemplateFilter;

/**
 * Represents an object used for deciding whether a post has a certain template assigned to it.
 * Multiple types of templates can be supported (initial implementation covers native page templates and
 * Content Templates).
 *
 * Specifically, this is being used when determining what field groups should be displayed for a particular post
 * in Toolset_Field_Group_Post::get_groups_for_element().
 *
 * @since Types 3.3
 */
interface TemplateFilterInterface {

	/**
	 * @param \IToolset_Post $post
	 *
	 * @return bool True if the template matches given post.
	 */
	public function is_match_for_post( \IToolset_Post $post );

}
