<?php
/**
 * Shelf custom_type
 * @package amazon-library
 */

/**
 * Our custom shelf post_type
 */
function aml_type_shelves() {
	$slug_base = aml_get_option('aml_slug_base');
	$slug_shelf = aml_get_option('aml_slug_shelf');

	$labels = array(
		'name' => _x('Shelves', 'post type general name', 'amazon-library'),
		'singular_name' =>  _x('Shelf', 'post type singular name', 'amazon-library'),
		'add_new_item' => __('Add New Shelf', 'amazon-library'),
		'edit_item' => __('Edit Shelf', 'amazon-library'),
		'new_item' => __('New Shelf', 'amazon-library'),
		'view_item' => __('View Shelf', 'amazon-library'),
		'search_items' => __('Search Shelves', 'amazon-library'),
		'not_found' => __('No shelves found', 'amazon-library'),
		'not_found_in_trash' => __('No shelves found in Trash', 'amazon-library'),
	);

	$args = array(
			'description' => __('Users can organise shelves to show which products they use (e.g. a DVD shelf, a book shelf, etc).'),
			'rewrite' => array('slug' => "$slug_base/$slug_shelf", 'pages' => true, 'feeds' => true, 'with_front' => false),
			'show_in_menu' => 'edit.php?post_type=aml_product',
			'capability_type' => array('shelf', 'shelves'),
			'register_meta_box_cb' => 'aml_shelf_boxes',
			'supports' => array('title', 'author'),
			'map_meta_cap' => true,
			'menu_position' => 2,
			'labels' => $labels,
			'query_var' => true,
			'public' => true,
		);
	register_post_type('aml_shelf', $args);
	add_filter('archive_template', 'aml_shelf_archive_template');
	add_filter('single_template', 'aml_shelf_single_template');
}

/**
 * Callback from aml_type_shelves() to generate meta boxes on an edit page
 */
function aml_shelf_boxes() {
	 add_meta_box('aml_shelf_list', __('Products on Shelf', 'amazon-library'), 'aml_shelf_mb_list', 'aml_shelf', 'normal', 'high');
	 add_meta_box('aml_shelf_add', __('Add Product to Shelf', 'amazon-library'), 'aml_shelf_mb_add', 'aml_shelf', 'normal', 'low');
	 wp_enqueue_script('suggest');
	 wp_enqueue_script( 'aml-shelf-script', plugins_url('/js/amazon.shelf.js', __FILE__) );
	 wp_enqueue_style( 'aml-shelf-style', plugins_url('/css/amazon.shelf.css', __FILE__) );
}

/**
 * Meta Box for shelf display
 */
function aml_shelf_mb_list($post) {
	$args = array('post_id' => $post->ID);
	$products = aml_shelf_page($args);
?>
<div class="aml_shelf_box">
	<?php echo $products; ?>
</div>
<?php }

/**
 * Meta Box for product search
 */
function aml_shelf_mb_add() {
?>
<div class="aml_search_box">
	<div id="aml_search_stringwrap">
		<label class="hide-if-no-js" style="" id="aml_search_string-prompt-text" for="aml_search_string"><?php _e('Search for...', 'amazon-library'); ?></label>
		<input type="text" size="50" id="aml_search_string" name="aml_search_string" value="" autocomplete="off" />
	</div>
	<div id='aml_search_button' class="button-primary"><?php _e('Search Products', 'amazon-library') ?></div>
	<div id='aml_search_reset' class="button-primary"><?php _e('Reset Search', 'amazon-library') ?></div>
	<br style="clear:both" />
</div>
<?php }

/**
 * Register additional columns for manage products page
 */
function aml_shelf_register_columns($cols) {
	$cols['details'] = 'Details';
	$cols['reviews'] = 'Reviews';
	return $cols;
}

/**
 * Display additional columns for manage products page
 */
function aml_shelf_display_columns ($name, $post_id) {
	global $post;

	switch ($name) {
		case 'details':
			// print total number of products

			// print name of last product added
			break;
		case 'reviews':
			// print total number of reviews

			// print name of last review entered

			// link to list of reviews attached to the shelf
			break;
	}
}

/**
 * Display shelves in the right now meta box
 */
function aml_shelf_right_now() {
	$num_posts = wp_count_posts('aml_shelf');
	$num = number_format_i18n($num_posts->publish);
	$text = _n('Shelf', 'Shelves', intval($num_posts->publish), 'amazon-library');
	if (current_user_can('edit_shelves')) {
		$num = '<a href="/wp-admin/edit.php?post_type=aml_shelf">' . $num . '</a>';
		$text = '<a href="/wp-admin/edit.php?post_type=aml_shelf">' . $text . '</a>';
	}

	echo '<tr>';
	echo '<td class="first b b-tags">'.$num.'</td>';
	echo '<td class="t tags">' . $text . '</td>';
	echo '</tr>';
}

/**
 * callback to search products in db
 */
function aml_shelf_search_products ($lookup) {}

/**
 * Callback to get a page of products
 */
function aml_shelf_page ($args) {
	$post_id = (isset($args['post_id'])) ? $args['post_id'] : get_the_ID();
	$page = (isset($args['page'])) ? intval($args['page']) : 1;
	$perpage = (isset($args['perpage'])) ? intval($args['perpage']) : 10;
	$product_ids = get_post_meta($post_id, 'aml_products', false);
	$args = array('include' => $product_ids);
	$prod_db = get_posts($args);
	$products = $prod_db->posts;
	$max_pages = $prod_db->max_num_pages;

	$paginate = '<div class="aml-paginate">';
	$paginate .= ($page < 1) ? '' : '<div class="aml-paginate-prev">' . __('Previous page', 'amazon-library') . '</div>';
	$paginate .= ($page >= $max_pages) ? '' : '<div class="aml-paginate-next">' . __('Next page', 'amazon-library') . '</div>';
	$paginate .= '</div>';

	$html = '';
	if (is_array($products)) {
		foreach ($products as $prod) {
			$html .= product_shelf_image($prod, '<li class="aml_product">', '</li>');
		}
	}
	return (empty($html)) ? __('No products found on the self.', 'amazon-library') : '<ul>' . $html . $paginate . '</ul>';
}

function &aml_get_products ($args=null) {
	$defaults = array(
		'numberposts' => 5, 'offset' => 0, 'orderby' => 'post_date',
		'order' => 'DESC', 'post_type' => 'aml_product', 'suppress_filters' => true,
		'post_status' => 'publish', 'ignore_sticky_posts' => true,
	);

	$r = wp_parse_args( $args, $defaults );
	if ( ! empty($r['numberposts']) && empty($r['posts_per_page']) )
		$r['posts_per_page'] = $r['numberposts'];

	$get_posts = new WP_Query;
	$get_posts->query($r);
	return $get_posts;
}

/**
 * Register the actions for our product post_type
 */
function aml_init_shelf() {
	aml_type_shelves();
	add_action('manage_aml_shelf_posts_custom_column', 'aml_shelf_display_columns', 10, 2);
	add_action('manage_edit-aml_shelf_columns', 'aml_shelf_register_columns');
	add_action('right_now_content_table_end', 'aml_shelf_right_now');
}
add_action('init', 'aml_init_product');

?>