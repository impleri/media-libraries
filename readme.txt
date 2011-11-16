=== Media Libraries ===
Contributors: impleri
Tags: books, videos, music, games, film, movies, reviews, media, widget, amazon, library
Requires at least: 3.0
Tested up to: 3.2

Allows you to organise books, videos, music, and games found on external websites into shelves and create user reviews for them.


== Description ==

**WARNING**: This project is still in development and is not quite ready for production use. Please install at your own risk.

With Media Libraries, you can manage multiple library shelves of your current media, keeping track of when you've last used them, and how often you have used them. If you wish to use the same item on different shelves (e.g. a book you once read for school/work but also once read separately for fun), this is also possible. Users can also provide their own reviews, and library admins can flag reviews as official site reviews to differentiate them from other reviews.

Media Libraries is a complete rewrite of the Now Reading series of plugins ([Rob Miller's Now Reading plugin](http://robm.me.uk/projects/plugins/wordpress/now-reading/ "Original Now Reading Plugin"), [Ben Gunnink's Now Reading Reloaded plugin](http://wordpress.org/extend/plugins/now-reading-reloaded/ "Now Reading Reloaded Plugin"), and [Zack Ajmal's Now Watching plugin](http://wordpress.org/extend/plugins/now-watching/ "Now Watching Plugin")). Users with WordPress 2.6 and below should either upgrade or use Rob Miiller's original plugin. Users with WordPress 2.6 - 2.9 should use the Now Reading Reloaded plugin.

**Requirements**: PHP5 with SimpleXML and SOAP libraries. In very few cases will these not be met automatically.


== Installation ==

1. Upload `media-libraries` to the `/wp-content/plugins/` directory
1. Copy/move `media-libraries/amazon/` directory to the `/wp-content/plugins/` directory
2. Activate the Media Libraries plugin through the 'Plugins' menu in WordPress
3. Activate at least one source provider plugin (Amazon is included) through the 'Plugins' menu in WordPress
4. Make any changes the to provided template files and store them in your theme directory


== Frequently Asked Questions ==

= Why does it take a long time to update the Subversion repository? =

This is simply because I do most of my development on [github](http://github.com/impleri/media-libraries).

= Why does my library page look funny? =

The premade templates (`/templates/`) that are included were made for the default TwentyEleven theme that comes with Wordpress.  If your theme has more or less markup, the templates may look strange in your theme.

My suggestion to those who are having trouble is to open up the template (such as `archive-ml_product.php`) side-by-side with one of your standard theme templates, and make sure that the markup matches.

= What is the difference between a review and a usage? =

The main difference is that a review only occurs one per product per user. A single user cannot write multiple reviews for the same product. However, a user can have multiple usage on multiple shelves (or the same shelf), but each of these will link to only one review. Additionally, usage do not have pages in the frontend. They are listed on review and shelf pages.

== Changelog ==

= 0.9.3 =
* Second beta release
* Renamed to Media Libraries (was Amazon Media Libraries)
* Allow multiple source providers (the Amazon provider is included in the package)
* Added feature to use multiple shelves
* Separated usage (readings/etc) and reviews
* Initial template files and functions
* Better handling of usage times
* Upgraded to Exeu's Amazon WDSL library 1.3.2 (Note: per Amazon, an associate id is necessary for the new WDSL, old ones will be discontinued 2012-02-21)

= 0.9.2 =
* First beta release
* (Re-)added feature to track (multiple) readings/viewings/reviews
* Added feature to handle multiple users
* Uses auth privileges and WP roles to restrict/permit add/manage items rather than user levels

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

== Screenshots ==

1. The admin product listing
2. The admin review entry page
3. A posted product page
4. Options page

== Roadmap ==

= 0.9.5 =
* First release candidate
* Widgets
* Add Single-User and Single-Shelf mode
* Role/auth management
* Import from Now Reading, Now Reading Reloaded, and Now Watching plugins

= 0.9.7 =
* Second release candidate
* UI cleanup


== Template Files ==

The `templates` folder of the Amazon Media Libraries plugin contains a default set of templates (based on TwentyEleven) for displaying your library pages.  These are hacked into the template process, meant to be overridden by copying them into a template folder and modifying them.

