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
	require_once dirname(__FILE__) . '/roles.php'; // auths before post_types
	require_once dirname(__FILE__) . '/product.php'; // adds products and people
	require_once dirname(__FILE__) . '/review.php'; // adds reviews
	require_once dirname(__FILE__) . '/usage.php'; // adds readings
	require_once dirname(__FILE__) . '/shelf.php'; // adds shelves
 	require_once dirname(__FILE__) . '/user.php'; // users (front-end only)
// 	include_once dirname(__FILE__) . '/widgets.php'; // the widgets

	// finally ajax if in the admin side (not needed on frontend side)
	if (is_admin()) {
		require_once dirname(__FILE__) . '/ajax.php';
	}

	wp_enqueue_style('ml-style', plugins_url('/css/media.fresh.css', __FILE__));
	add_filter('page_template', 'ml_page_template');
}

// all of our hooks come last (i.e. here)
register_activation_hook(basename(dirname(__FILE__)) . '/' . basename(__FILE__), 'ml_install');
add_action('init', 'ml_init');

// Pure PHP files should not have a closing PHP tag!!

function ml_dump ($var, $value='') {
	echo '<div>';
	if (!empty($value)) {
		echo '<p>' . $value . ':</p>';
	}
	echo '<pre>' . var_export($var, 1) . '</pre>';
	echo '</div>';
}
