=== Amazon Media Libraries ===
Contributors: impleri
Tags: books, videos, music, media, widget, amazon, library
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 1.0.0

Allows you to display books, DVDs, CDs, any kind of media found on Amazon that you're reading/watching/whatever.

== Description ==

Amazon Media Libraries is a complete rewrite of the Now Reading series of plugins [Rob Miller's Now Reading plugin](http://robm.me.uk/projects/plugins/wordpress/now-reading/ "Original Now Reading Plugin"). Users with WordPress 2.6 and below should either upgrade or use Rob Miiller's original plugin. Users with WordPress 2.6 - 2.9 should use the Now Reading Reloaded plugin.

With Amazon Media Libraries, you can manage multiple libraries/shelves of your current media, keeping track of when you've last used them, and how often you have used them.

**Requirements**: PHP5, SimpleXML, and SOAP libraries. In very few cases will these not be met automatically.

== Installation ==

1. Upload `amazon-media-libraries` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Make any changes the to provided template files and store them in your theme directory (see the "Template Files" section)

== Frequently Asked Questions ==

= When will you implement feature X? =

New feature development is planned, but I have no clear timeline as the maintenance of this plugin is purely a hobby.

= Why does my library page look funny? =

The premade templates (`/templates/`) that are included were made for the default TwentyTen theme that comes with Wordpress.  If your theme has more or less markup, the templates may look strange in your theme. Also included in the `/contrib/` folder are template files for the old WP theme, Kubrick, as well as the popular K2.

My suggestion to those who are having trouble is to open up the template (such as `archive-library.php`) side-by-side with one of your standard theme templates, and make sure that the markup matches.

== Screenshots ==

1. Adding/Editing a book
2. Managing a book
3. Library view

== Changelog ==

= 0.9.0 =
* First public release
* Rewrite of NRR
* Now using the official Amazon library for searching/lookup
* Now using WP taxonomies instead of separate tables
* Uses auth privileges (manage_library) and WP roles to restrict/permit add/manage items rather than user levels
* Added feature to handle multiple media types (books, DVD, CD)
* Added feature to track multiple readings/viewings
* Added feature to use multiple shelves
* Added feature to handle multiple users (separate plugin included)
* Display multiple book authors separately
* Template files added for k2 and TwentyTen templates
* Manage page now does quick edits
* URL rewriting modified so that a (custom) menu names can used

== Template Files ==

The `templates` folder of the Amazon Media Libraries plugin contains a default set of templates for displaying your product data in various places (sidebar, library, etc.).  These are automatically copied to your template directories if WP can write to those directories.