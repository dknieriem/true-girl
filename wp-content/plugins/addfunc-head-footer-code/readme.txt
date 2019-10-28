
=== AddFunc Head & Footer Code ===

Contributors: AddFunc,joerhoney
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7AF7P3TFKQ2C2
Tags: head code, footer code, add to head, per page, tracking code, Google Analytics, javascript, meta tags, wp_head, wp_footer, body tag code, opening body tag
Requires at least: 3.0.1
Tested up to: 5.2
Stable tag: 2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily add code to your head, footer and/or immediately after the opening body tag, site-wide and/or on any individual page/post.

== Description ==

Allows administrators to add code to the `<head>` and/or footer of an individual post (or page or other content) and/or site-wide. Ideal for scripts such as Google Analytics conversion tracking code and any other general or page-specific JavaScript. A very simple, reliable and lightweight plugin.

== Installation ==

1. Upload the entire `/addfunc-head-footer-code` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the *Plugins* menu in WordPress
3. Add code site-wide in *Settings>Head & Footer Code* or on individual Pages/Posts using the Head & Footer Code meta box when in edit mode in a page (or any post type)

== Frequently Asked Questions ==

= Can I add Google Analytics or other tracking code snippets to the ⟨head⟩ tag of my website? =

Yep! That's what AddFunc Head & Footer Code is made for.

= Can I add code snippets to the ⟨head⟩ tag or footer of a specific Post or Page? =

Yep! That's what this plugin is made for.

= Does AddFunc Head & Footer Code work on custom content types, the same as they do on Posts and Pages? =

For the site wide code yes, as long as the theme is set up properly with the `wp_head()` and `wp_footer()` functions. For individual custom content type "entries" (as we'll call them) it will work as long as the custom content type (or custom post type more precisely) is set up in the standard way, so that WordPress will recognize that it needs to add all the usual meta boxes to it. Basically, it will work in a standard setup.

= Where does the Head Code output? =

Wherever `wp_head()` is located in your theme.

= Where does the Body Start Code output? =

Immediately after the opening `body` tag. Note: other plugins can be made to do the same thing and one of them has to insert before the other, so other plugins could theoretically insert code before AddFunc Head & Footer Code's Body Start Code.

= Where does the Footer Code output? =

Wherever `wp_footer()` is located in your theme.

= Will AddFunc Head & Footer Code work if there is no `wp_head()` or `wp_footer()` in my theme? =

Wherever one of those functions is missing, your code will not be output there. But omitting one of them does not stop the other ones from working. AddFunc Head & Footer Code will also save your code regardless. It just can't output your code without the presence of those functions in the theme.

= Does AddFunc have a website? =

Yes. [addfunc.com](http://addfunc.com/)

== Screenshots ==

1. Simply paste your code into one of these three fields and it will be included on every page of your website.

2. Add your code to these fields respectively and it will output specifically to this page, post or custom post type. Optionally replace or remove the site-wide code on any individual post or page.

== Changelog ==

= 2.3 =
23 May 2019

*   Fixes a PHP notice, which appeared on the login dialog when WP_DEBUG was ON. (I think I actually got it this time.)

= 2.2 =
23 Apr 2019

*   Fixes a PHP notice, which appeared on the login dialog when WP_DEBUG was ON. Edit: not fixed, just a different error.

= 2.1 =
28 Nov 2018

*   Removes the metabox from post types which are not public.

= 2 =
19 Sep 2018

*   Adds... drumroll please... Body Start Code!
    -   Insert code immediately after the opening `body` tag!
    -   Fields for individual page/post code as well as site-wide code.
    -   Optionally replace site-wide code with individual page/post code.
    -   All behaves the same as head code and footer code, except for priority (no priority setting).
    -   Note: There is no standard for opening `body` tag code in WordPress yet (contact me if I'm wrong). If there ever is, AddFunc Head & Footer Code may change so as to conform to the standard. We don't think this will effect performance in anyway, but if it does, we will let you know through this change log. If necessary, we may even leave this feature in place along with the new standardized method.

= 1.5.1 =
17 Sep 2018

*   Removes individual post output on various pages:
    -   archive
    -   author
    -   category
    -   tag
    -   home
    -   search
    -   404

= 1.5 =
13 Sep 2017

*   Adds ability to set the output priority of the head code. Thanks to John Irvine at [RD Technology Solutions](http://rdtechsol.com/) for the suggestion.
*   Adds ability to set the output priority of the footer code.
*   Changes the metabox to a lower priority so it sits at the bottom by default instead of the top. Thanks to [@enfueggo](https://wordpress.org/support/topic/lowering-metabox-priority/) for the suggestion.

= 1.4 =
3 Apr 2017

*   Adds option for individual post code to replace the site-wide code:
    -   Head and footer managed independently of each other.
    -   Individual post code appends to site-wide by default.
    -   Check the *Replace Site-wide Head/Footer Code* checkbox to replace or remove the Site-wide code for the respective area.
*   Fixes post meta fields:
    -   No longer saves post meta fields when not needed.
    -   Deletes post meta fields if empty when saved/updated.

= 1.3 =
23 Jun 2015

*   Corrects the generated path to options.php, so that the settings page can be found even on installs where the plugins directory is not at the standard /wp-content/plugins location.

= 1.2 =
19 Jun 2015

*   Discovered addfunc-head-footer-code.php was saved with text encoding Western (Mac OS Roman). ~>:( Changed to Unicode (UTF-8).
*   This was probably changed during a recent update on the plugin's tags (the tags for the WordPress Plugin Repository), so maybe two weeks ago. Previous downloads should have been UTF-8.
*   Also changed version 1.1 to UTF-8 because leaving a Mac OS Roman version in the repository would be pointless. So 1.1 and 1.2 are the same, except for the readme.txt.

= 1.1 =
28 Nov 2014

*   Fixes meta box nounce.
*   Changes all references to Average (including but not limited to "avrg", "average", etc.) to relate to AddFunc (changing project name).
*   Changes a few other function and variable names for namespacing purposes.
*   Submitted to the WordPress Plugin Repository under the name AddFunc.

= 1.0 =
7 Aug 2014

*   Includes readme.txt.
*   Submitted to the WordPress Plugin Repository.

= 0.4.1 =
6 Aug 2014

*   Code cleaned up (mostly comments removed).
*   Excludes unnecessary file: style.css.

= 0.4 =
8 Jul 2014

*   Bug fix: replaced "my-meta-box-id" with "avrghdftrcdMetaBox" (duh).

= 0.3 =
27 Oct 2013

*   Hid Head & Footer Code meta box from non-admin users.

= 0.2 =
15 Oct 2013

*   Adds a Head & Footer Code settings page for site-wide code (for admins only).

= 0.1 =
14 Aug 2013

*   Adds Head & Footer Code meta box to all pages, posts and cusom post types.
*   Saves Head & Footer Code entry to the database as custom fields.
*   Outputs code to the website in `wp_head()` and `wp_footer()`.

== Upgrade Notice ==
