=== Amazon Media Libraries ===
Contributors: impleri
Tags: books, videos, music, media, widget, amazon, library
Requires at least: 3.0
Tested up to: 3.1

Allows you to display books, DVDs, CDs, any kind of media found on Amazon that you're reading/watching/whatever.


== Description ==

**WARNING**: This project is still in development and is currently not ready for production use. Please install at your own risk.

Amazon Media Libraries is a complete rewrite of the Now Reading series of plugins ([Rob Miller's Now Reading plugin](http://robm.me.uk/projects/plugins/wordpress/now-reading/ "Original Now Reading Plugin"), [Ben Gunnink's Now Reading Reloaded plugin] (http://wordpress.org/extend/plugins/now-reading-reloaded/ "Now Reading Reloaded Plugin"), and [Zack Ajmal's Now Watching plugin] (http://wordpress.org/extend/plugins/now-watching/ "Now Watching Plugin") . Users with WordPress 2.6 and below should either upgrade or use Rob Miiller's original plugin. Users with WordPress 2.6 - 2.9 should use the Now Reading Reloaded plugin.

With Amazon Media Libraries, you can manage multiple libraries/shelves of your current media, keeping track of when you've last used them, and how often you have used them.

**Requirements**: PHP5 with SimpleXML and SOAP libraries. In very few cases will these not be met automatically.


== Installation ==

1. Upload `amazon-media-libraries` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Make any changes the to provided template files and store them in your theme directory


== Frequently Asked Questions ==

= Why does my library page look funny? =

The premade templates (`/templates/`) that are included were made for the default TwentyTen theme that comes with Wordpress.  If your theme has more or less markup, the templates may look strange in your theme. Also included in the `/contrib/` folder are template files for the old WP theme, Kubrick, as well as the popular K2.

My suggestion to those who are having trouble is to open up the template (such as `archive-library.php`) side-by-side with one of your standard theme templates, and make sure that the markup matches.


== Screenshots ==

1. Adding/Editing a book
2. Managing a book
3. Library view


== Changelog ==

= 0.9.1 =
* Second public alpha release
* Product search, add, edit, etc actually work as intended.
* Dashboard addition of product counts
* Naming conventions changed to reflect products rather than books

= 0.9.0 =
* First public alpha release
* Begin rewrite of NRR
* Now using the official Amazon library for searching/lookup
* Now using WP taxonomies and custom post_type instead of separate tables
* Added feature to handle multiple media types (books, DVD, CD)
* Display multiple book authors separately
* Manage page now does quick edits
* URL rewriting modified so that a (custom) menu names can used


== Roadmap ==

= 0.9.2 =
* Third alpha release
* Added feature to use multiple shelves
* Added feature to handle multiple users
* Uses auth privileges and WP roles to restrict/permit add/manage items rather than user levels

= 0.9.3 =
* First beta release
* (Re-)added feature to track (multiple) readings/viewings/reviews

= 0.9.4 =
* Second beta release
* (Re-)added template files for k2 and TwentyTen templates

= 0.9.5 =
* Third beta release
* Widgets
* UI cleanup

= 0.9.6 =
* First release candidate
* Import from Now Reading, Now Reading Reloaded, and Now Watching plugins


== Template Files ==

The `templates` folder of the Amazon Media Libraries plugin contains a default set of templates for displaying your product data in various places (sidebar, library, etc.).  These are automatically copied to your template directories if WP can write to those directories.
