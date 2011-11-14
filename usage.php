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
	'added'		=> array('label' => 'Unused on Shelf', 'single' => 'Added', 'plural' => 'Added'),
	'using'		=> array('label' => 'Currently Using', 'single' => 'Using', 'plural' => 'Using'),
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
 * callback from registering ml_usage to generate meta boxes on an edit page
 *
function ml_usage_boxes() {
	remove_meta_box('submitdiv', 'ml_usage', 'side');
	add_meta_box('ml_usage_status', __('Status', 'media-libraries'), 'ml_usage_mb_status', 'ml_usage', 'side', 'high');
	add_meta_box('ml_usage_meta', __('Meta', 'media-libraries'), 'ml_usage_mb_meta', 'ml_usage', 'side', 'high');
	wp_enqueue_script('ml-usage-script', plugins_url('/js/media.usage.js', __FILE__));
}*/

/**
 * Hacked post submit form (falls to WP default if not a usage)
 *
 * @param object $post
 *
function ml_usage_mb_status ($post) {
	$added = get_post_meta($post->ID, 'ml_added', true);
	$added = !($added > 0) ? 0 : $added;
	$started = get_post_meta($post->ID, 'ml_started', true);
	$started = !($started > 0) ? 0 : $started;
	$finish = get_post_meta($post->ID, 'ml_finished', true);
	$finish = !($finish > 0) ? 0 : $finish;
	$stati = ml_get_usage_stati();
	$stati_names = array_keys($stati);
	$post->post_status = (in_array($post->post_status, $stati_names)) ? $post->post_status : $stati_names[0];
?>
< div id="ml_usage_time">
<?php
	ml_status_box($post);
 	ml_show_date($added, 'added');
 	ml_show_date($started, 'started');
 	ml_show_date($finish, 'finished');
 	do_action('post_submitbox_misc_actions');
?>
< /div>
< div class="clear"></div>
<?php ml_pubdel_box($post); ?>
<?php
}*/

/**
 * meta-box for product and shelf
 *
 * @param object WP_post
 * @todo push html to template file
 *
function ml_usage_mb_meta ($post) {
	$product = isset($post->post_parent) ? $post->post_parent : 0;
	$shelf = get_post_meta($post->ID, 'ml_shelf', true);

	$p_args = array('post_type' => 'ml_product', 'depth' => 1, 'echo' => 0, 'selected' => $product, 'name' => 'parent_id',  'sort_column'=> 'menu_order,post_title');
	$products = wp_dropdown_pages($p_args);

	$s_args = array('post_type' => 'ml_shelf', 'depth' => 1, 'echo' => 0, 'selected' => $shelf, 'name' => 'ml_shelf',  'sort_column'=> 'menu_order,post_title');
	$shelves = wp_dropdown_pages($s_args);

	$image = ($product) ? '<img src="' . get_post_meta($product, 'ml_image', true) . '" />' : '';
	echo '<div id="ml_product-thumb">' . $image . '</div>';

	if ( !empty($products) ) {
		echo '<p><strong>' . __('Product to Use', 'media-libraries') . '</strong></p>' .
		'<label class="screen-reader-text" for="parent_id">' . __('Product to Use', 'media-libraries') . '</label>' .
		$products;
	}

	if ( !empty($shelves) ) {
		echo '<p><strong>' . __('Shelf for Usage', 'media-libraries') . '</strong></p>' .
		'<label class="screen-reader-text" for="ml_shelf">' . __('Shelf for Usage', 'media-libraries') . '</label>' .
		$shelves;
	}
}*/

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
 * register additional columns for manage usages page
 *
 * @param array columns
 * @return array columns (with additions)
 *
function ml_usage_register_columns ($cols) {
// 	unset($cols['title']);
	$cols = array('product' => 'Product') + $cols;
	$cols['status'] = 'Status';
	$cols['Shelf'] = 'Shelf';
	unset($cols['date']);
	return $cols;
}*/

/**
 * display additional columns for manage usages page
 *
 * @param string column name
 * @param int post id
 *
function ml_usage_display_columns ($name, $post_id) {
	$post = get_post($post_id);
	switch ($name) {
		case 'product':
			$parent = isset($post->post_parent) ? $post->post_parent : 0;
			if ($parent) {
				$product = get_post($parent);
				$image = get_post_meta($parent, 'ml_image', true);
				$image = (empty($image)) ? '' : '<br /><img src="'.$image.'" class="image_pusage" />';
				echo $product->post_title.'<div class="image">'.$image.'</div>';
			}
			break;
		case 'status':
			$times = array(
				'added' => 'Added to Shelf',
				'started' => 'Began Review',
				'finished' => 'Review Finished',
			);
			$stati = ml_get_usage_stati();
			echo (isset($stati[$post->post_status])) ? $stati[$post->post_status]['label'] : '';
			foreach ($times as $label => $string) {
				$time = get_post_meta($post_id, 'ml_'.$label, true);
				$datef = __('M j, Y @ G:i');
				$stamp = __('<b>%1$s</b>');
				$date = date_i18n($datef, strtotime($time));
				echo '<br />' . __($string, 'media-libraries') . ': <span id="timestamp-' . $label . '">' . sprintf($stamp, $date) . '</span>';
			}
			break;
		case 'connect':
			break;
	}
}*/

/* function ml_page_help() {
	$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
	if ('ml_usage' == $post_type) {
		$screen = get_current_screen();
		add_contextual_help($screen, '<p>' .
			__('Pages are similar to Posts in that they have a title, body text, and associated metadata, but they are different in that they are not part of the chronological blog stream, kind of like permanent posts. Pages are not categorized or tagged, but can have a hierarchy. You can nest Pages under other Pages by making one the &#8220;Parent&#8221; of the other, creating a group of Pages.') . '</p>' .
			'<p>' . __('Creating a Page is very similar to creating a Post, and the screens can be customized in the same way using drag and drop, the Screen Options tab, and expanding/collapsing boxes as you choose. The Page editor mostly works the same Post editor, but there are some Page-specific features in the Page Attributes box:') . '</p>' .
			'<p>' . __('<strong>Parent</strong> - You can arrange your pages in hierarchies. For example, you could have an &#8220;About&#8221; page that has &#8220;Life Story&#8221; and &#8220;My Dog&#8221; pages under it. There are no limits to how many levels you can nest pages.') . '</p>' .
			'<p>' . __('<strong>Template</strong> - Some themes have custom templates you can use for certain pages that might have additional features or custom layouts. If so, you&#8217;ll see them in this dropdown menu.') . '</p>' .
			'<p>' . __('<strong>Order</strong> - Pages are usually ordered alphabetically, but you can choose your own order by entering a number (1 for first, etc.) in this field.') . '</p>' .
			'<p><strong>' . __('For more information:') . '</strong></p>' .
			'<p>' . __('<a href="http://codex.wordpress.org/Pages_Add_New_SubPanel" target="_blank">Documentation on Adding New Pages</a>') . '</p>' .
			'<p>' . __('<a href="http://codex.wordpress.org/Pages_Pages_SubPanel#Editing_Individual_Pages" target="_blank">Documentation on Editing Pages</a>') . '</p>' .
			'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
		);
	}
} */

/**
 * initialise and register the actions for usage post_type
 */
function ml_init_usage() {
	require_once dirname(__FILE__) . '/usage-template.php';
	ml_usage_type();
	ml_usage_stati();

// 	add_action('manage_ml_usage_posts_custom_column', 'ml_usage_display_columns', 10, 2);
// 	add_action('manage_edit-ml_usage_columns', 'ml_usage_register_columns');
// 	add_action('right_now_content_table_end', 'ml_usage_right_now');
// 	add_action('save_post', 'ml_usage_meta_postback');
// 	add_action('admin_head-edit.php', 'ml_page_help');
}

ml_init_usage();
