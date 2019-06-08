<?php

// Google Analytics
// ?utm_source=typesplugin&utm_campaign=types&utm_medium=%CURRENT-SCREEN%&utm_term=EMPTY&utm_content=EMPTY

$urls = array(
	'learn-how-template'               => 'https://toolset.com/documentation/user-guides/benefits-of-templates-for-custom-types/',
	'learn-how-archive'                => 'https://toolset.com/documentation/user-guides/what-archives-are-and-why-they-are-so-important/',
	'learn-how-views'                  => 'https://toolset.com/documentation/user-guides/learn-what-you-can-do-with-views/',
	'learn-how-forms'                  => 'https://toolset.com/home/cred/',
	'learn-how-post-types'             => 'https://toolset.com/documentation/user-guides/create-a-custom-post-type/',
	'learn-how-fields'                 => 'https://toolset.com/documentation/user-guides/using-custom-fields/',
	'learn-how-taxonomies'             => 'https://toolset.com/documentation/user-guides/create-custom-taxonomies/',
	'creating-templates-with-toolset'  => 'https://toolset.com/documentation/user-guides/learn-about-creating-templates-with-toolset/',
	'creating-templates-with-php'      => 'https://toolset.com/documentation/customizing-sites-using-php/creating-templates-single-custom-posts/',
	'creating-archives-with-toolset'   => 'https://toolset.com/documentation/user-guides/learn-about-creating-archives-with-toolset/',
	'creating-archives-with-php'       => 'https://toolset.com/documentation/customizing-sites-using-php/creating-templates-custom-post-type-archives/',
	'how-views-work'                   => 'https://toolset.com/documentation/user-guides/learn-what-you-can-do-with-views/',
	'how-to-add-views-to-layouts'      => 'https://toolset.com/documentation/getting-started-with-toolset/adding-lists-of-contents/',
	'learn-views'                      => 'https://toolset.com/documentation/user-guides/learn-what-you-can-do-with-views/',
	'how-cred-work'                    => 'https://toolset.com/documentation/getting-started-with-toolset/publish-content-from-the-front-end/',
	'how-to-add-forms-to-layouts'      => 'https://toolset.com/documentation/getting-started-with-toolset/publish-content-from-the-front-end/forms-for-creating-content/',
	'learn-cred'                       => 'https://toolset.com/documentation/getting-started-with-toolset/publish-content-from-the-front-end/',
	'free-trial'                       => 'https://toolset.com/?add-to-cart=363363&buy_now=1',
	'adding-custom-fields-with-php'    => 'https://toolset.com/documentation/getting-started-with-toolset/creating-templates-for-displaying-post-types/',
	'themes-compatible-with-layouts'   => 'https://toolset.com/documentation/user-guides/layouts-theme-integration/#popular-integrated-themes',
	'layouts-integration-instructions' => 'https://toolset.com/documentation/user-guides/layouts-theme-integration/#replacing-wp-loop-with-layouts',
	'adding-views-to-layouts'          => 'https://toolset.com/documentation/getting-started-with-toolset/create-and-display-custom-lists-of-content/adding-views-to-designs-with-toolset-layouts/',
	'adding-forms-to-layouts'          => 'https://toolset.com/documentation/user-guides/adding-cred-forms-to-layouts/',
	'using-post-fields'                => 'https://toolset.com/user-guides/using-custom-fields/',
	'adding-fields'                    => 'https://toolset.com/documentation/user-guides/using-custom-fields/#introduction-to-wordpress-custom-fields',
	'displaying-fields'                => 'https://toolset.com/documentation/getting-started-with-toolset/creating-templates-for-displaying-post-types/',
	'adding-user-fields'               => 'https://toolset.com/documentation/user-guides/user-fields/',
	'displaying-user-fields'           => 'https://toolset.com/documentation/user-guides/displaying-wordpress-user-fields/',
	'adding-term-fields'               => 'https://toolset.com/documentation/user-guides/term-fields/',
	'displaying-term-fields'           => 'https://toolset.com/documentation/user-guides/displaying-wordpress-term-fields/',
	'custom-post-types'                => 'https://toolset.com/documentation/user-guides/create-a-custom-post-type/',
	'custom-taxonomy'                  => 'https://toolset.com/documentation/user-guides/create-custom-taxonomies/',
	'post-relationship'                => 'https://toolset.com/documentation/user-guides/creating-post-type-relationships/',
	'compare-toolset-php'              => 'https://toolset.com/landing/toolset-vs-php/',
	'types-fields-api'                 => 'https://toolset.com/documentation/functions/',
	'parent-child'                     => 'https://toolset.com/documentation/user-guides/many-to-many-post-relationship/',
	'custom-post-archives'             => 'https://toolset.com/documentation/user-guides/creating-wordpress-custom-post-archives/',
	'using-taxonomy'                   => 'https://toolset.com/documentation/user-guides/create-custom-taxonomies/',
	'custom-taxonomy-archives'         => 'https://toolset.com/documentation/user-guides/creating-wordpress-custom-taxonomy-archives/',
	'repeating-fields-group'           => 'https://toolset.com/documentation/user-guides/creating-groups-of-repeating-fields-using-fields-tables/',
	'single-pages'                     => 'https://toolset.com/documentation/user-guides/view-templates/',
	'content-templates'                => 'https://toolset.com/documentation/user-guides/view-templates/',
	'views-user-guide'                 => 'https://toolset.com/documentation/getting-started-with-toolset/adding-lists-of-contents/',
	'wp-types'                         => 'https://toolset.com/',
	'date-filters'                     => 'https://toolset.com/documentation/user-guides/date-filters/',
	'getting-started-types'            => 'https://toolset.com/documentation/user-guides/getting-starting-with-types/',
	'displaying-post-reference-fields' => 'https://toolset.com/documentation/post-relationships/how-to-display-related-posts-with-toolset/using-post-reference-fields-to-display-information-from-a-related-post/#displaying-fields-that-belong-to-the-related-post',
	'displaying-repeating-fields-groups' => 'https://toolset.com/documentation/getting-started-with-toolset/creating-and-displaying-repeatable-field-groups/',
	'toolset-account-downloads'                => 'https://toolset.com/account/downloads/',
);

// Visual Composer
if( defined( 'WPB_VC_VERSION' ) ) {
	$urls['learn-how-template']         = 'https://toolset.com/documentation/user-guides/benefits-of-templates-for-custom-types-vc/';
	$urls['creating-templates-with-toolset'] = 'https://toolset.com/documentation/user-guides/benefits-of-templates-for-custom-types-vc/';
}
// Beaver Builder
else if( class_exists( 'FLBuilderLoader' ) ) {
	$urls['learn-how-template']         = 'https://toolset.com/documentation/user-guides/benefits-of-templates-for-custom-types-bb/';
	$urls['creating-templates-with-toolset'] = 'https://toolset.com/documentation/user-guides/benefits-of-templates-for-custom-types-bb/';
}
// Layouts
else if( defined( 'WPDDL_DEVELOPMENT' ) || defined( 'WPDDL_PRODUCTION' ) ) {
	$urls['learn-how-template']         = 'https://toolset.com/documentation/user-guides/benefits-of-templates-for-custom-types-layouts/';
	$urls['creating-templates-with-toolset'] = 'https://toolset.com/documentation/user-guides/benefits-of-templates-for-custom-types-layouts/';
}

return $urls;