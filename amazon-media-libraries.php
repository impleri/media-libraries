<?php
/*
Plugin Name: Amazon Media Libraries
Version: 0.9.2 Alpha
Plugin URI: http://impleri.net/development/amazon_library/
Description: Allows you to display the media you're reading/watching with cover art fetched automatically from Amazon.
Author: Christopher Roussel
Author URI: http://impleri.net
$Rev$
$Date$
*/

// Keep this file short and sweet; leave the clutter for elsewhere!
define('AML_VERSION', '0.9.2');
load_plugin_textdomain( 'amazon-library', false, basename(dirname(__FILE__)) . '/lang' );

// Include frontend functionality
require_once dirname(__FILE__) . '/options.php';
require_once dirname(__FILE__) . '/products.php';
require_once dirname(__FILE__) . '/roles.php';
//require_once dirname(__FILE__) . '/reading.php';
// require_once dirname(__FILE__) . '/template.php';
// include_once dirname(__FILE__) . '/widgets.php';

if ( is_admin() || defined('DOING_AJAX') ) {
	require_once dirname(__FILE__) . '/amazon.php';
}

/**
 * Checks if install/upgrade needs to run by checking version in db
 * Checks if required options (AWS keys) need to be set
 */
function aml_check() {
	add_option('aml_options', aml_default_options());
	$options = get_option('aml_options');
	$version = (empty($options['aml_version'])) ? 0 : $options['aml_version'];

	// Install/Upgrade has priority
	if (version_compare($version, AML_VERSION, 'lt')) {
// 		require_once dirname(__FILE__) . '/install.php';
// 		aml_installer::process();
 		add_option('aml_options', aml_default_options());
	}
}

/**
 * Creates our taxonomies using WP taxonomy & post type APIs
 */
function aml_init() {
	aml_capabilities();
	//wp_enqueue_style('aml-style', plugins_url('/css/amazon.css', dirname(__FILE__) ));
	//wp_enqueue_style('aml-meta-style', plugins_url('/css/amazon.meta.css', dirname(__FILE__) ));
}

register_activation_hook(basename(dirname(__FILE__)) . '/' . basename(__FILE__), 'aml_check');
add_action('init', 'aml_init');

// Pure PHP files should not have a closing PHP tag!!
