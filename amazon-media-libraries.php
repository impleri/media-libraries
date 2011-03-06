<?php
/*
Plugin Name: Amazon Media Libraries
Version: 0.9.2 Alpha
Plugin URI: http://impleri.net/development/amazon_media_libraries/
Description: Allows you to display the media you're reading/watching with cover art fetched automatically from Amazon.
Author: Christopher Roussel
Author URI: http://impleri.net
*/

// Keep this file short and sweet; leave the clutter for elsewhere!
define('AML_VERSION', '0.9.2');
load_plugin_textdomain( 'amazon-library', false, basename(dirname(__FILE__)) . '/lang' );

// Options need to be loaded for install/check/init
require_once dirname(__FILE__) . '/options.php';

/**
 * Checks if install/upgrade needs to run by checking version in db
 */
function aml_install() {
	$options = get_option('aml_options');

	// Install
	if (false === $options) {
 		add_option('aml_options', aml_default_options());
 		return; // no need to check for upgrade
	}

	$version = (empty($options['aml_version'])) ? 0 : $options['aml_version'];
	// Upgrade
	if (version_compare($version, AML_VERSION, 'lt')) {
		require dirname(__FILE__) . '/upgrade.php';
		return;
	}
}

/**
 * Checks if required options (AWS keys) need to be set
 */
function aml_check() {
	$options = get_option('aml_options');

	return true;
}

/**
 * Creates our taxonomies using WP taxonomy & post type APIs
 */
function aml_init() {
	if (!aml_check()) {
		return;
	}

	// Only load the rest of AML if the necessary options are set
	require_once dirname(__FILE__) . '/products.php';
	require_once dirname(__FILE__) . '/shelves.php';
	require_once dirname(__FILE__) . '/roles.php';
//		require_once dirname(__FILE__) . '/template.php';
//		include_once dirname(__FILE__) . '/widgets.php';

	if (is_admin()) {
		require_once dirname(__FILE__) . '/amazon.php';
		require_once dirname(__FILE__) . '/ajax.php';
	}

	//wp_enqueue_style('aml-style', plugins_url('/css/amazon.css', dirname(__FILE__) ));
}

register_activation_hook(basename(dirname(__FILE__)) . '/' . basename(__FILE__), 'aml_install');
add_action('init', 'aml_init');

// Pure PHP files should not have a closing PHP tag!!
