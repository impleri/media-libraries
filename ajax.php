<?php
/**
 * ajax functions
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * ajax callback to perform an Amazon search
 */
function ml_ajax_search() {
	// validate posted data
	$search = (isset($_POST['search'])) ? $_POST['search'] : '';
	$type = (isset($_POST['type'])) ? $_POST['type'] : '';

	// run query
	$results = '';
	$results = appliy_filters('ml-do-search', $results, $search, $type);
// 	$ret = ml_amazon::search($search, $type);

	//return results
	echo $ret;
	die;
}

/**
 * ajax callback to move/insert/update a usage
 */
function ml_ajax_save_usage() {
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
	$product = (isset($_POST['product'])) ? intval($_POST['product']) : 0;
	$image = ($product) ? get_post_meta($product, 'ml_image', true) : '';
	echo $image;
	die;
}

add_action('wp_ajax_ml_search', 'ml_ajax_search');
add_action('wp_ajax_ml_product_image', 'ml_ajax_get_image');
add_action('wp_ajax_ml_usage_save', 'ml_ajax_save_usage');
