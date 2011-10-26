<?php
/**
 * ajax functions
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * ajax callback to perform an Amazon search
 */
function ml_ajax_amazon_search() {
	// validate posted data
	$search = (isset($_POST['search'])) ? $_POST['search'] : '';
	$type = (isset($_POST['type'])) ? $_POST['type'] : '';

	// run amazon query
	$ret = ml_amazon::search($search, $type);

	//return results
	echo $ret;
	die;
}

/**
 * ajax callback to view a shelf page
 */
function ml_shelf_ajax_callback() {
	// validate posted data
	$page = (isset($_POST['page'])) ? $_POST['page'] : '';
	$search = (isset($_POST['search'])) ? $_POST['search'] : '';

	// run amazon query
	$ret = (!empty($search)) ? ml_shelf_search_products($search) : ml_shelf_page($page);

	//return results
	echo $ret;
	die;
}

/**
 * ajax callback to add product to shelf
 * unused. needed?
 */
function ml_shelf_add_product() {
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

/**
 * ajax callback for product live search in shelf page
 */
function ml_shelf_live_search() {
	$products = get_post_type_object('ml_product');
	if ( ! $products )
		die( '0' );
	if ( ! current_user_can('assign_products') )
		die( '-1' );

	$s = stripslashes( $_GET['q'] );
	$s = trim( $s );
	if ( strlen( $s ) < 2 )
		die; // require 2 chars for matching

	$results = $wpdb->get_col( $wpdb->prepare( "SELECT p.name FROM $wpdb->posts AS p WHERE p.post_type = 'ml_product' AND p.name LIKE (%s)", '%' . like_escape( $s ) . '%' ) );

	echo join( $results, "\n" );
	die;
}

function ml_ajax_get_image() {
	$product = (isset($_POST['ml_product'])) ? intval($_POST['ml_product']) : 0;
	echo ($product) ? get_post_meta($product, 'ml_image', true) : null;
	die;
}

add_action('wp_ajax_ml_amazon_search', 'ml_ajax_amazon_search');
add_action('wp_ajax_ml_review_product', 'ml_ajax_get_image');
add_action('wp_ajax_ml_product_search', 'ml_shelf_live_search');
add_action('wp_ajax_ml_shelf_search', 'ml_ajax_callback');
add_action('wp_ajax_ml_shelf_page', 'ml_ajax_callback');
