<?php
/**
 * shelf custom_type
 * @package media-libraries
 */

/**
 * @todo
 * BE list boxes: number of readings on the shelf split by status
 * FE template: show thumbnails for all products with product details, and link to last usage
 * FE widgets: random shelf, largest shelf, user's shelves (see older NRR widget)
 */

/**
 * Our custom shelf post_type
 */
function ml_type_shelves() {
	$slug_base = ml_get_option('ml_slug_base');
	$slug_shelf = ml_get_option('ml_slug_shelf');

	$labels = array(
		'name' => _x('Shelves', 'post type general name', 'media-libraries'),
		'singular_name' =>  _x('Shelf', 'post type singular name', 'media-libraries'),
		'add_new_item' => __('Add New Shelf', 'media-libraries'),
		'edit_item' => __('Edit Shelf', 'media-libraries'),
		'new_item' => __('New Shelf', 'media-libraries'),
		'view_item' => __('View Shelf', 'media-libraries'),
		'search_items' => __('Search Shelves', 'media-libraries'),
		'not_found' => __('No shelves found', 'media-libraries'),
		'not_found_in_trash' => __('No shelves found in Trash', 'media-libraries'),
	);

	$args = array(
		'description' => __('Users can organise shelves to show which products they use (e.g. a DVD shelf, a book shelf, etc).'),
		'rewrite' => array('slug' => "$slug_base/$slug_shelf", 'pages' => true, 'feeds' => true, 'with_front' => false),
		'show_in_menu' => 'edit.php?post_type=ml_product',
		'capability_type' => array('shelf', 'shelves'),
		'register_meta_box_cb' => 'ml_shelf_boxes',
		'supports' => array('title', 'author'),
		'map_meta_cap' => true,
		'hierarchical' => true,
		'labels' => $labels,
		'public' => true,
	);
	register_post_type('ml_shelf', $args);

	add_filter('archive_template', 'ml_shelf_archive_template');
	add_filter('single_template', 'ml_shelf_single_template');
}

function ml_shelf_page() {
	include(dirname(__FILE__) . '/shelf-page.php');
}

/**
 * Callback from ml_type_shelves() to generate meta boxes on an edit page
 */
function ml_shelf_boxes() {
	add_meta_box('ml_shelf_hide', __('Products on Shelf', 'media-libraries'), 'ml_shelf_mb_list', 'ml_shelf', 'normal', 'high');
 	add_meta_box('ml_shelf_add', __('Products in Library', 'media-libraries'), 'ml_shelf_mb_add', 'ml_shelf', 'side', 'high');
	wp_enqueue_script('jquery');
	wp_enqueue_script('utils');
	wp_enqueue_script('hoverIntent');
	wp_enqueue_script('common');
	wp_enqueue_script('jquery-color');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-widget');
	wp_enqueue_script('jquery-ui-mouse');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('jquery-ui-droppable');
	wp_enqueue_script( 'ml-shelf-script', plugins_url('/js/media.shelf.js', __FILE__) );
	wp_enqueue_style( 'ml-fresh-style', plugins_url('/css/media.fresh.css', __FILE__) );
	wp_enqueue_style( 'ml-shelf-style', plugins_url('/css/media.shelf.css', __FILE__) );
}

/**
 * Meta Box for shelf display
 */
function ml_shelf_mb_list($post) {
	$meta = get_post_meta($post->ID, 'ml_usage');
	$stati = ml_get_usage_stati();
// 	echo '</div></div>'; // break out of regular post boxes
	echo '<div class="products-liquid"><div id="products-shelves">';
	foreach ($stati as $id => $labels) {
		echo '<div class="products-holder-wrap">';
		echo '<div class="shelf-name">';
		echo '<div class="shelf-name-arrow"><br /></div>';
		echo '<h3>' . $labels['label'] . '<span><img src="' . esc_url(admin_url('images/wpspin_dark.gif')) . '" class="ajax-feedback" title="" alt="" /></span></h3></div>';
		echo '<div id="shelf-' . $post->ID . '-' . $id . '" class="products-droppable">';
		if ($meta) {
			$args = array('post_type' => 'ml_usage', 'post_status' => $id, 'include' => $meta);
			$usages = get_posts($args);
			if (!empty($usages)) {
				foreach ($usages as $use) {
					$product = get_post($use->post_parent);
					ml_product_thumbnail($product, $use);
				}
			}
		}
		else {
			echo '<p>' . __('There are no products listed on this shelf.', 'media-libraries') . '</p>';
		}
		echo '<br class="clear" />';
		echo '</div>';
		echo '</div>';
		echo '<br class="clear" />';
	}
	echo '</div></div>';
	echo '<br class="clear" />';
// 	echo '<div><div>'; // remember to return to original box
}

/**
 * Meta Box for product list
 */
function ml_shelf_mb_add() {
	echo '<div id="available-products" class="products-holder-wrap">';
	echo '<div class="product-holder">';
	echo '<div id="product-list">';
	$args = array('numberposts' => -1, 'post_type' => 'ml_product');
	$products = get_posts($args);
	if ($products) {
		foreach ($products as $product) {
			 ml_product_thumbnail($product);
		}
	}
	echo '<br class="clear" />';
	echo '</div>';
	echo '<br class="clear" />';
	echo '</div>';
	echo '<br class="clear" />';
	echo '</div>';
}

/**
 * Register additional columns for manage products page
 */
function ml_shelf_register_columns($cols) {
	$cols['summary'] = 'Summary';
	return $cols;
}

/**
 * Display additional columns for manage products page
 */
function ml_shelf_display_columns ($name, $post_id) {
	global $post;

	switch ($name) {
		case 'summary':
			// print total number of usages in shelf by status

			break;
	}
}

/**
 * Display shelves in the right now meta box
 */
function ml_shelf_right_now() {
	$num_posts = wp_count_posts('ml_shelf');
	$num = number_format_i18n($num_posts->publish);
	$text = _n('Shelf', 'Shelves', intval($num_posts->publish), 'media-libraries');
	if (current_user_can('edit_shelves')) {
		$num = '<a href="/wp-admin/edit.php?post_type=ml_shelf">' . $num . '</a>';
		$text = '<a href="/wp-admin/edit.php?post_type=ml_shelf">' . $text . '</a>';
	}

	echo '<tr>';
	echo '<td class="first b b-tags">'.$num.'</td>';
	echo '<td class="t tags">' . $text . '</td>';
	echo '</tr>';
}

/**
 * Callback to get a page of products
 *
function ml_shelf_get_page ($args) {
	$post_id = (isset($args['post_id'])) ? $args['post_id'] : get_the_ID();
	$page = (isset($args['page'])) ? intval($args['page']) : 1;
	$perpage = (isset($args['perpage'])) ? intval($args['perpage']) : 10;
	$product_ids = get_post_meta($post_id, 'ml_products', false);
	$args = array('include' => $product_ids);
	$prod_db = get_posts($args);
	$products = $prod_db->posts;
	$max_pages = $prod_db->max_num_pages;

	$paginate = '<div class="aml-paginate">';
	$paginate .= ($page < 1) ? '' : '<div class="aml-paginate-prev">' . __('Previous page', 'media-libraries') . '</div>';
	$paginate .= ($page >= $max_pages) ? '' : '<div class="aml-paginate-next">' . __('Next page', 'media-libraries') . '</div>';
	$paginate .= '</div>';

	$html = '';
	if (is_array($products)) {
		foreach ($products as $prod) {
			$html .= ml_product_thumbnail($prod, '<li class="ml_product">', '</li>');
		}
	}
	return (empty($html)) ? __('No products found on the self.', 'media-libraries') : '<ul>' . $html . $paginate . '</ul>';
}

function &ml_get_products ($args=null) {
	$defaults = array(
		'numberposts' => 5, 'offset' => 0, 'orderby' => 'post_date',
		'order' => 'DESC', 'post_type' => 'ml_product', 'suppress_filters' => true,
		'post_status' => 'publish', 'ignore_sticky_posts' => true,
	);

	$r = wp_parse_args( $args, $defaults );
	if ( ! empty($r['numberposts']) && empty($r['posts_per_page']) )
		$r['posts_per_page'] = $r['numberposts'];

	$get_posts = new WP_Query;
	$get_posts->query($r);
	return $get_posts;
} */

/**
 * Register the actions for our product post_type
 */
function ml_init_shelf() {
 	require_once dirname(__FILE__) . '/shelf-template.php';
	ml_type_shelves();

	add_action('manage_ml_shelf_posts_custom_column', 'ml_shelf_display_columns', 10, 2);
	add_action('manage_edit-ml_shelf_columns', 'ml_shelf_register_columns');
	add_action('right_now_content_table_end', 'ml_shelf_right_now');
}

ml_init_shelf();
