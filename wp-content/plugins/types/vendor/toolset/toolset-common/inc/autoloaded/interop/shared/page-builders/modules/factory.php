<?php

namespace OTGS\Toolset\Common\Interop\Shared;

use OTGS\Toolset\Common\Interop\Handler as handler;

/**
 * Page Builder Modules factory.
 *
 * @since 3.0.5
 */
class PageBuilderModulesFactory {
	/**
	 * Get the Page Builder with modules.
	 *
	 * @param string $page_builder The page builder name.
	 *
	 * @return bool|handler\Elementor\ElementorModules
	 */
	public function get_page_builder( $page_builder ) {
		$return_page_builder = null;

		$views_active = new \Toolset_Condition_Plugin_Views_Active();
		$form_active = new \Toolset_Condition_Plugin_Cred_Active();

		switch ( $page_builder ) {
			case handler\Elementor\ElementorModules::PAGE_BUILDER_NAME:
				if (
					$views_active->is_met() ||
					$form_active->is_met()
				) {
					$return_page_builder = new handler\Elementor\ElementorModules();
				} else {
					$return_page_builder = null;
				}
				break;
		}

		return $return_page_builder;
	}
}