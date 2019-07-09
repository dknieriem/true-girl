'use strict';

var Lucy = require('./third-party/lucy.js');
var config = {
	algoliaAppId: 'CGLHJ0181U',
	algoliaAppKey: '8fa2f724a6314f9a0b840c85b05b943e',
	algoliaIndexName: 'mc4wp_kb',
	links: [
		{
			text: "<span class=\"dashicons dashicons-book\"></span> Knowledge Base",
			href: "https://kb.mc4wp.com/"
		},
		{
			text: "<span class=\"dashicons dashicons-editor-code\"></span> Code Snippets",
			href: "https://github.com/ibericode/mc4wp-snippets"
		},
		{
			text: "<span class=\"dashicons dashicons-editor-break\"></span> Changelog (free plugin)",
			href: "https://wordpress.org/plugins/mailchimp-for-wp/#developers"
		},
		{
			text: "<span class=\"dashicons dashicons-editor-break\"></span> Changelog (Premium plugin)",
			href: "https://account.mc4wp.com/changelog/premium"
		}
	],
	contactLink: 'mailto:support@mc4wp.com'
};

// grab from WP dumped var.
if( window.lucy_config ) {
	config.emailLink = window.lucy_config.email_link;
}

var lucy = new Lucy(
	config.algoliaAppId,
	config.algoliaAppKey,
	config.algoliaIndexName,
	config.links,
	config.contactLink
);
