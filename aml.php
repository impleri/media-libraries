<?php
/*
Plugin Name: Amazon Media Libraries
Version: 0.9.0
Plugin URI: http://impleri.net/development/amazon_library/
Description: Allows you to display the media you're reading/watching with cover art fetched automatically from Amazon.
Author: Christopher Roussel
Author URI: http://impleri.net
$Rev$
$Date$
*/

// Keep this file short and sweet; leave the clutter for elsewhere!
define('AML_VERSION', '5.9.0');
load_plugin_textdomain( 'amazon-library', false, basename(dirname(__FILE__)) . '/lang' );

// Include frontend functionality
require_once dirname(__FILE__) . '/options.php';
require_once dirname(__FILE__) . '/taxonomy.php';
require_once dirname(__FILE__) . '/template.php';
include_once dirname(__FILE__) . '/widgets.php';

if (is_admin()) {
	require_once dirname(__FILE__) . '/amazon.lib.php';
}

$nr_statuses = apply_filters('nr_statuses', array(
    'unread'	=> __('Yet to read', NRTD),
    'onhold'	=> __('On Hold', NRTD),
    'reading'	=> __('Currently reading', NRTD),
    'rereading'	=> __('Currently re-reading', NRTD),
    'read'		=> __('Finished', NRTD)
));


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
register_activation_hook(basename(dirname(__FILE__)) . '/' . basename(__FILE__), 'aml_check');
add_action('init','aml_check');

// Pure PHP files should not have a closing PHP tag!!