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
 *
function ml_shelf_ajax_callback() {
	// validate posted data
	$page = (isset($_POST['page'])) ? $_POST['page'] : '';
	$search = (isset($_POST['search'])) ? $_POST['search'] : '';

	// run amazon query
	$ret = (!empty($search)) ? ml_shelf_search_products($search) : ml_shelf_page($page);

	//return results
	echo $ret;
	die;
} */

/**
 * ajax callback to add product to shelf
 * unused. needed?
 *
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
} */

/**
 * ajax callback for product live search in shelf page
 *
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
}*/

/**
 * ajax callback to move/insert/update a usage
 */
function ml_ajax_move_usage() {
	// validate posted data
	$product = (isset($_POST['product'])) ? $_POST['product'] : false;
	$shelf = (isset($_POST['shelf'])) ? $_POST['shelf'] : false;

	if ($product && $shelf) {
		$user = wp_get_current_user();

		// get shelf/status id
		$shelf_parts = explode('-', $shelf);
		$shelf_id = isset($shelf_parts[1]) ? intval($shelf_parts[1]) : false;
		if (!$shelf_id) {
			$args = array('numberposts' => 1, 'post_type' => 'ml_shelf', 'author' => $user->ID);
			$posts = get_posts($args);
			if (!empty($posts)) {
				$shelf_id = $posts[0]->ID;
			}
		}

		$status = $shelf_parts[2];
		$stati = array_keys(ml_get_usage_stati());
		if (!in_array($status, $stati)) {
			$status = $stati[0];
		}

		// get product/usage details
		$prod_parts = explode('-', $product);
		$prod_id = intval($prod_parts[1]);
		$use_id = ($prod_parts[2] == '__i__') ? false : intval($prod_parts[2]);

		$time = current_time('mysql');

		// update/insert
		$args = array('shelf' => $shelf_id, 'post_parent' => $prod_id, 'post_status' => $status);
		if ($use_id) {
			$args['ID'] = $use_id;
		}

		if ((isset($_POST['delete']) && $use_id)) {
			ml_delete_usage($use_id, $shelf_id);
		}
		else {
			$id = ml_post_usage($args);
			$return = array('time' => $time, 'status' => $status);
			if (!$use_id) {
				$return['id'] = $id;
			}
			echo json_encode($return);
		}
	}
	die;
}

/**
 * ajax callback to get product image for review
 */
function ml_ajax_get_image() {
	$product = (isset($_POST['ml_product'])) ? intval($_POST['ml_product']) : 0;
	echo ($product) ? get_post_meta($product, 'ml_image', true) : null;
	die;
}

add_action('wp_ajax_ml_amazon_search', 'ml_ajax_amazon_search');
add_action('wp_ajax_ml_review_product', 'ml_ajax_get_image');
// add_action('wp_ajax_ml_product_search', 'ml_shelf_live_search');
add_action('wp_ajax_ml_shelf_move', 'ml_ajax_move_usage');
