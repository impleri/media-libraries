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

// Options and auths need to be loaded for install/check/init
require_once dirname(__FILE__) . '/options.php';
require_once dirname(__FILE__) . '/roles.php';

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

	$version = aml_get_option('aml_version', 0);
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
	$aws_key = aml_get_option('aml_amazon_id');
	$aws_secret = aml_get_option('aml_secret_key');

	return true;
}

/**
 * Creates our taxonomies using WP taxonomy & post type APIs
 */
function aml_init() {
	// Only load the rest of AML if the necessary options are set
	if (!aml_check()) {
		return;
	}

	// first the product custom post_type
	require_once dirname(__FILE__) . '/product.php'; // adds products and people
	require_once dirname(__FILE__) . '/product-template.php';
	aml_init_product();

	// second, the review custom post_type
	require_once dirname(__FILE__) . '/review.php'; // adds reviews/uses, tags, and categories
	require_once dirname(__FILE__) . '/review-template.php';
	aml_init_review();

	// third, the shelf taxonomy, if enabled
	if (aml_get_option('aml_multi_user', 1)) {
		require_once dirname(__FILE__) . '/shelf.php'; // adds shelves
		require_once dirname(__FILE__) . '/shelf-template.php';
// 		require_once dirname(__FILE__) . '/user.php'; // users (front-end only)
		aml_init_shelf();
	}

	// then widgets
// 	include_once dirname(__FILE__) . '/widgets.php';

	// finally amazon connector and ajax if in the admin side (not needed on frontend side)
	if (is_admin()) {
		require_once dirname(__FILE__) . '/amazon.php';
		require_once dirname(__FILE__) . '/ajax.php';
	}

	// also add base css for styling
// 	wp_enqueue_style('aml-style', plugins_url('/css/amazon.css', dirname(__FILE__) ));
}

register_activation_hook(basename(dirname(__FILE__)) . '/' . basename(__FILE__), 'aml_install');
add_action('init', 'aml_init');

// Pure PHP files should not have a closing PHP tag!!
