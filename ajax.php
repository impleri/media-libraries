<?php
/**
 * AJAX functions
 * @package amazon-library
 */

// handle js callbacks
function aml_ajax_amazon_search() {
	// validate posted data
	$search = (isset($_POST['search'])) ? $_POST['search'] : '';
	$type = (isset($_POST['type'])) ? $_POST['type'] : '';

	// run amazon query
	$ret = aml_amazon::search($search, $type);

	//return results
	echo $ret;
	die;
}

// handle js callbacks
function aml_shelf_ajax_callback() {
	// validate posted data
	$page = (isset($_POST['page'])) ? $_POST['page'] : '';
	$search = (isset($_POST['search'])) ? $_POST['search'] : '';

	// run amazon query
	$ret = (!empty($search)) ? aml_shelf_search_products($search) : aml_shelf_page($page);

	//return results
	echo $ret;
	die;
}

function aml_shelf_add_product() {
	check_ajax_referer( 'taxinlineeditnonce', '_inline_edit' );

	$taxonomy = sanitize_key( $_POST['taxonomy'] );
	$tax = get_taxonomy( $taxonomy );
	if ( ! $tax )
		die( '0' );

	if ( ! current_user_can( $tax->cap->edit_terms ) )
		die( '-1' );

	set_current_screen( 'edit-' . $taxonomy );

	$wp_list_table = _get_list_table('WP_Terms_List_Table');

	if ( ! isset($_POST['tax_ID']) || ! ( $id = (int) $_POST['tax_ID'] ) )
		die(-1);

	$tag = get_term( $id, $taxonomy );
	$_POST['description'] = $tag->description;

	$updated = wp_update_term($id, $taxonomy, $_POST);
	if ( $updated && !is_wp_error($updated) ) {
		$tag = get_term( $updated['term_id'], $taxonomy );
		if ( !$tag || is_wp_error( $tag ) ) {
			if ( is_wp_error($tag) && $tag->get_error_message() )
				die( $tag->get_error_message() );
			die( __('Item not updated.') );
		}

		echo $wp_list_table->single_row( $tag );
	} else {
		if ( is_wp_error($updated) && $updated->get_error_message() )
			die( $updated->get_error_message() );
		die( __('Item not updated.') );
	}

	exit;
}

function aml_shelf_live_search() {
	$products = get_post_type_object('aml_product');
	if ( ! $products )
		die( '0' );
	if ( ! current_user_can('assign_products') )
		die( '-1' );

	$s = stripslashes( $_GET['q'] );
	$s = trim( $s );
	if ( strlen( $s ) < 2 )
		die; // require 2 chars for matching

	$results = $wpdb->get_col( $wpdb->prepare( "SELECT p.name FROM $wpdb->posts AS p WHERE p.post_type = 'aml_product' AND p.name LIKE (%s)", '%' . like_escape( $s ) . '%' ) );

	echo join( $results, "\n" );
	die;
}

// product post_type ajax : amazon search
add_action('wp_ajax_aml_amazon_search', 'aml_ajax_amazon_search');
//add_action('wp_ajax_aml_amazon_lookup', 'aml_ajax_callback')

// shelf post_type ajax : pagination; (local) product autocomplete; assign product
add_action('wp_ajax_aml_product_search', 'aml_shelf_live_search');
add_action('wp_ajax_aml_shelf_search', 'aml_ajax_callback');
add_action('wp_ajax_aml_shelf_page', 'aml_ajax_callback');
