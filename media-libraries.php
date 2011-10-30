<?php
/**
 * ml root file
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/*
Plugin Name: Media Libraries
Version: 0.9.3 Beta
Plugin URI: http://impleri.net/development/media_libraries/
Description: Allows you to display the media you're reading/watching with cover art fetched automatically from online sources (Amazon is included).
Author: Christopher Roussel
Author URI: http://impleri.net
*/

/**
 * @todo
 * product: one-to-many review (one per user), one-to-many reading
 * reading: many-to-one product, many-to-one review, many-to-many shelf
 * review: many-to-one product (one per user), one-to-many reading
 * shelf: many-to-many reading
 *
 * productmeta: person(*)
 * readingmeta: shelf(1)
 */

// Keep this file short and sweet; leave the clutter for elsewhere!
define('ML_VERSION', '0.9.3');
load_plugin_textdomain( 'media-libraries', false, basename(dirname(__FILE__)) . '/lang' );

// Options and auths need to be loaded first/always
require_once dirname(__FILE__) . '/options.php';

/**
 * Checks if install/upgrade needs to run by checking version in db
 * @todo implement shelves
 * @todo template functions
 * @todo roles and capabilities
 * @todo widgets
 */
function ml_install() {
	$options = get_option('ml_options');

	// Install
	if (false === $options) {
 		add_option('ml_options', ml_default_options());
 		return; // no need to check for upgrade
	}

	// Import goes here
}

/**
 * checks if required options (aws keys) need to be set
 *
 * @return bool true if necessary options are valid
 */
function ml_check() {
	$pass = true;
	$pass = apply_filters('ml-init-check', $pass);
	return $pass;
}

/**
 * initialise media libraries
 */
function ml_init() {
	// Only load the rest of ML if the necessary options are set
	if (!ml_check()) {
		return;
	}

	require_once dirname(__FILE__) . '/functions.php'; // functions
	require_once dirname(__FILE__) . '/roles.php'; // auths first!
	require_once dirname(__FILE__) . '/product.php'; // adds products and people
	require_once dirname(__FILE__) . '/review.php'; // adds reviews
	require_once dirname(__FILE__) . '/usage.php'; // adds readings
	require_once dirname(__FILE__) . '/shelf.php'; // adds shelves
 	require_once dirname(__FILE__) . '/user.php'; // users (front-end only)
// 	include_once dirname(__FILE__) . '/widgets.php'; // the widgets

	// finally amazon connector and ajax if in the admin side (not needed on frontend side)
	if (is_admin()) {
		require_once dirname(__FILE__) . '/ajax.php';
	}

	// also add base css for styling
// 	wp_enqueue_style('ml-style', plugins_url('/css/amazon.css', dirname(__FILE__) ));
}

// all of our hooks come last (i.e. here)
register_activation_hook(basename(dirname(__FILE__)) . '/' . basename(__FILE__), 'ml_install');
add_action('init', 'ml_init');
add_action('admin_menu', 'ml_options_init', 9);

// Pure PHP files should not have a closing PHP tag!!
