<?php
/**
 * product usage
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * @todo
 * BE list boxes: status, shelf, times (top row for stati, filter by shelf)
 * BE edit boxes: status, times, shelf
 */

/**
 * usage post_type
 */
function ml_usage_type() {
	$labels = array(
		'name' => __('Uses', 'media-libraries'),
		'singular_name' => __('Usage', 'media-libraries'),
		'add_new' => __('Add New'),
		'add_new_item' => __('Add New Usage', 'media-libraries'),
		'edit' => __('Edit'),
		'edit_item' => __('Edit Usage', 'media-libraries'),
		'new_item' => __('New Usage', 'media-libraries'),
		'view' => __('View'),
		'view_item' => __('View Usage', 'media-libraries'),
		'search_items' => __('Search Usage', 'media-libraries'),
		'not_found' => __('No usages found', 'media-libraries'),
		'not_found_in_trash' => __('No usages found in trash', 'media-libraries'),
	);

	$args = array(
		'description' => __('A single use of a product (e.g. reading a book, watching a DVD, listening to music, etc)'),
		'supports' => array('author'),
// 		'show_in_menu' => 'edit.php?post_type=ml_product',
// 		'register_meta_box_cb' => 'ml_usage_boxes',
		'capability_type' => 'usage',
		'map_meta_cap' => true,
		'hierarchical' => false,
		'labels' => $labels,
		'public' => false,
	);
	register_post_type('ml_usage', $args);
}


/**
 * usage stati
 *
 * @todo restrict these stati to ml_review type
 */
function ml_usage_stati() {
	$stati = ml_get_usage_stati();
	foreach ($stati as $name => $args) {
		register_post_status( $name, array(
			'label'			=> _x($args['label'], 'post', 'media-libraries'),
			'label_count'	=> _n_noop($args['single'] . ' <span class="count">(%s)</span>', $args['plural'] . ' <span class="count">(%s)</span>' ),
			'public'		=> true,
		) );
	}
}

function ml_get_usage_stati() {
	return array(
	'added'		=> array('label' => 'Unused', 'single' => 'Added', 'plural' => 'Added'),
	'using'		=> array('label' => 'Using', 'single' => 'Started', 'plural' => 'Using'),
	'onhold'	=> array('label' => 'On Hold', 'single' => 'Held', 'plural' => 'Held'),
	'finished'	=> array('label' => 'Finished', 'single' => 'Finished', 'plural' => 'Finished'),
	);
}

function ml_post_usage ($args) {
	$user = wp_get_current_user();
	$times = array_keys(ml_get_usage_stati());
	$now = current_time('mysql');
	$product = get_post($args['post_parent']);
	$title = $product->post_title;

	$default_args = array('post_type' => 'ml_usage', 'post_status' => $times[0], 'post_author' => $user->ID, 'post_title' => $title);
	$args = wp_parse_args($args, $default_args);

	// set time for current status if none there
	if (!isset($args[$args['post_status']])) {
		$args[$args['post_status']] = $now;
	}

	// add/update post
	$id = wp_insert_post($args);

	// set up initial usage metadata
	if (!isset($args['ID'])) {
		// connect to a shelf
		add_post_meta($id, 'ml_shelf', $args['shelf'], true);
		add_post_meta($args['shelf'], 'ml_usage', $id);

		// initial usage times
		foreach ($times as $time) {
			$gmt = get_gmt_from_date($now);
			add_post_meta($id, 'ml_'.$time, $now, true);
			add_post_meta($id, 'ml_'.$time.'_gmt', $gmt, true);
		}
	}

	// set time from post
	foreach ($times as $time) {
		if (isset($args[$time])) {
			$gmt = get_gmt_from_date($args[$time]);
			update_post_meta($id, 'ml_'.$time, $args[$time]);
			update_post_meta($id, 'ml_'.$time.'_gmt', $gmt);
		}
	}

	return $id;
}

function ml_delete_usage ($usage, $shelf) {
	$times = array_keys(ml_get_usage_stati());
	delete_post_meta($usage, 'ml_shelf');
	delete_post_meta($shelf, 'ml_usage', $usage);
	foreach ($times as $time) {
		$gmt = get_gmt_from_date($now);
		delete_post_meta($usage, 'ml_'.$time);
		delete_post_meta($usage, 'ml_'.$time.'_gmt');
	}
	wp_delete_post($usage);
}

/**
 * callback to process posted metadata
 *
 * @param int post id
 *
function ml_usage_meta_postback ($post_id) {
	$req = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : '';
	if (('ml_usage' == $req) && current_user_can('edit_usage', $post_id)) {
		$shelf = (isset($_POST['ml_shelf'])) ? intval($_POST['ml_shelf']) : null;
		if ($shelf) {
			$orig_shelf = get_post_meta($post_id, 'ml_shelf', true);
			if ($orig_shelf > 0 && $shelf != $orig_shelf) {
				update_post_meta($post_id, 'ml_shelf', $shelf);
			}
			else {
				add_post_meta($post_id, 'ml_shelf', $shelf, true);
			}
			add_post_meta($shelf, 'ml_usage', $post_id);
		}


		$times = array('added', 'started', 'finished');
		foreach ($times as $time) {
			$jj = (isset($_POST['jj-'.$time])) ? intval($_POST['jj-'.$time]) : 0;
			$mm = (isset($_POST['mm-'.$time])) ? intval($_POST['mm-'.$time]) : 0;
			$aa = (isset($_POST['aa-'.$time])) ? intval($_POST['aa-'.$time]) : 0;
			$hh = (isset($_POST['hh-'.$time])) ? intval($_POST['hh-'.$time]) : 0;
			$mn = (isset($_POST['mn-'.$time])) ? intval($_POST['mn-'.$time]) : 0;
			$ss = (isset($_POST['ss-'.$time])) ? intval($_POST['ss-'.$time]) : 0;
			$jj = ($jj > 31) ? 31 : $jj;
			$jj = ($jj <= 0) ? date('j') : $jj;
			$mm = ($mm <= 0) ? date('n') : $mm;
			$aa = ($aa <= 0) ? date('Y') : $aa;
			$hh = ($hh > 23) ? $hh-24 : $hh;
			$hh = ($hh < 0) ? 0 : $hh;
			$mn = ($mn > 59) ? $mn-60 : $mn;
			$mn = ($mn < 0) ? 0 : $mn;
			$ss = ($ss > 59) ? $ss-60 : $ss;
			$ss = ($ss < 0) ? 0 : $ss;
			$set = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $aa, $mm, $jj, $hh, $mn, $ss);
			$gmt = get_gmt_from_date($set);
			ml_update_meta('ml_'.$time, $post_id, $set);
			ml_update_meta('ml_'.$time.'_gmt', $post_id, $gmt);
		}
	}
}*/

/**
 * initialise and register the actions for usage post_type
 */
function ml_init_usage() {
	ml_usage_type();
	ml_usage_stati();
}

ml_init_usage();
