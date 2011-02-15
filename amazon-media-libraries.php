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
require_once dirname(__FILE__) . '/shelves.php';
require_once dirname(__FILE__) . '/roles.php';
// require_once dirname(__FILE__) . '/template.php';
// include_once dirname(__FILE__) . '/widgets.php';

if (is_admin()) {
	require_once dirname(__FILE__) . '/amazon.php';
}

/**
 * Checks if install/upgrade needs to run by checking version in db
 * Checks if required options (AWS keys) need to be set
 */
function aml_check() {
	global $wpdb;
	add_option('aml_options', aml_default_options());
	$options = get_option('aml_options');
	$version = (empty($options['aml_version'])) ? 0 : $options['aml_version'];

	// Install/Upgrade has priority
	if (version_compare($version, AML_VERSION, 'lt')) {
// 		require_once dirname(__FILE__) . '/install.php';
// 		aml_installer::process();
// 		create_metadata_table('product');
// 		$wpdb->aml_productmeta = $wpdb->prefix.'productmeta';
 		add_option('aml_options', aml_default_options());
	}
}

function create_metadata_table($type) {
	global $wpdb;
	$table_name = $wpdb->prefix . $type . 'meta';

	if (!empty ($wpdb->charset))
		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	if (!empty ($wpdb->collate))
		$charset_collate .= " COLLATE {$wpdb->collate}";

	  $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
	  	meta_id bigint(20) NOT NULL AUTO_INCREMENT,
	  	{$type}_id bigint(20) NOT NULL default 0,

		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext DEFAULT NULL,

	  	UNIQUE KEY meta_id (meta_id)
	) {$charset_collate};";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
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
