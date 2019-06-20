=== Shortodes Ultimate: Shortcode Creator ===
Requires at least: 3.5
Tested up to: 5.0
Author: Vladimir Anokhin
License: license.txt
Stable tag: trunk
Contributors: Vladimir Anokhin
Tags: shortcodes-ultimate-add-on


== Description ==

Provides UI for creating custom shortcodes. [Add-on page](https://getshortcodes.com/add-ons/shortcode-creator/).

= System requirements =

* WordPress 3.5 or higher
* Shortcodes Ultimate 5.0 or higher
* PHP 5.2 or higher

= Support =

* [Getting started](http://docs.getshortcodes.com/category/27-common-questions)
* [Add-on documentation](http://docs.getshortcodes.com/category/28-shortcode-creator-add-on)
* [Get support](https://getshortcodes.com/support/)


== Installation ==

This add-on is distributed as a regular zipped plugin. You can install it like any other plugin. Navigate to Dashboard -> Plugins -> Add New -> Upload plugin. Then select downloaded zip-archive and click on "Install now" button. [Learn more](http://docs.getshortcodes.com/article/56-how-to-install-add-on).


== Changelog ==

= 1.5.11 =
Fixes:
* Plugin updates now also available from within iThemes sync control panel
* Plugin updates will be visible even without license key
* Fullscreen mode for shortcode code editor

= 1.5.10 =
* Added: default attribute value can now contain new lines

= 1.5.9 =
* Fixed: fatal error on plugin activation on PHP < 5.5

= 1.5.8 =
* Fixed: issue in dropdown options field (too angry sanitization)
* Fixed: validation of shortcode tag name field
* Improved: compatibility with 'Plugin Organizer'
* Added: 'Install core plugin' notice

= 1.5.7 =
* Fixed: PHP warning at settings page, when license key is saved

= 1.5.6 =
__IMPORTANT:__ this add-on requires __Shortcodes Ultimate version 5.0.0__ (or higher). Please update Shortcodes Ultimate before updating this add-on. [Upgrade guide](http://docs.getshortcodes.com/article/77-full-guidance-for-update-of-shortcodes-ultimate-from-version-4-to-version-5).

* Fixed: bug preventing replacement of default shortcodes with custom ones;
* Fixed: bug when after plugin update some attributes of custom shortcodes are removed;
* Fixed: PHP Warning: strpos(): Empty needle on line 310;
* Fixed: 'Invalid license key' error;
* Fixed: license key deactivation error;
* Added: saved license key is now hidden at plugin settings page;
* Updated: 'ru_RU' translation.

= 1.5.5 =
* Improved: compatibility with Shortcodes Ultimate 5+
* Improved: brand new shortcode editing interface
* Added: compatibility with PHP 7.2 (where create_function() marked as deprecated)
* Added: possibility to add custom CSS to custom shortcodes
* Fixed: code editor now accepts escaped symbols, like <code>\'</code>

= 1.5.4 =
* Compatibility with WP 4.4

= 1.5.3 =
* Added: new attribute type - icon
* Added: new attribute type - image_source

= 1.5.2 =
* Fixed: code field escaping. Double backticks doesn't stripped anymore

= 1.5.1 =
* Added su_ prefix at Custom shortcodes listing

= 1.5.0 =
* Auto-updates that works!

= 1.4.2 =
* Fixed dropdown options list (at shortcode editing page). Now it accepts values with dashes

= 1.4.1 =
* Improved variables insertion mechanism
* Added support for nested shortcodes in HTML mode
* Minor UX improvements
* Added NL locale
* New autoupdate system
* Fixed default content field validation, now it accepts html tags

= 1.1.1 =
* Small fix in auto-update script

= 1.1.0 =
* Updated import/export mechanism
* Included auto-update script

= 1.0.3 =
* Updated support links
* Small fixes
* Updated import/export mechanism
* Improved UX

= 1.0.0 =
* Initial release
