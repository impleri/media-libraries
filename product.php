<?php
/**
 * product custom_type and related taxonomies
 * @package amazon-library
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * custom product post_type
 */
function aml_product_type() {
	$slug_base = aml_get_option('aml_slug_base');
	$slug_product = aml_get_option('aml_slug_product');

	$labels = array(
		'name' => _x('Products', 'post type general name', 'amazon-library'),
		'singular_name' =>  _x('Product', 'post type singular name', 'amazon-library'),
		'add_new_item' => __('Add New Product', 'amazon-library'),
		'edit_item' => __('Edit Product', 'amazon-library'),
		'new_item' => __('New Product', 'amazon-library'),
		'view_item' => __('View Product', 'amazon-library'),
		'search_items' => __('Search Products', 'amazon-library'),
		'not_found' => __('No products found', 'amazon-library'),
		'not_found_in_trash' => __('No products found in Trash', 'amazon-library'),
	);

	$args = array(
		'description' => __('Product information and pictures fetched from Amazon. Similar to Amazon, an "official" review can be entered in the product page while individual users can also provide their own reviews in their shelves.'),
		'rewrite' => array('slug' => "$slug_base/$slug_product", 'pages' => false, 'feeds' => false, 'with_front' => false),
		'register_meta_box_cb' => 'aml_product_boxes',
		'capability_type' => 'product',
		'supports' => array('title'),
		'has_archive' => $slug_base,
		'map_meta_cap' => true,
		'hierarchical' => true,
		'menu_position' => 10,
		'labels' => $labels,
		'query_var' => true,
		'public' => true,
	);
	register_post_type('aml_product', $args);
	add_filter('archive_template', 'aml_product_archive_template');
	add_filter('single_template', 'aml_product_single_template');
}

/**
 * people taxonomy for products
 */
function aml_people_tax() {
	$slug_base = aml_get_option('aml_slug_base');
	$slug_person = aml_get_option('aml_slug_person');

	$labels = array(
		'name' => _x('People', 'taxonomy general name', 'amazon-library'),
		'singular_name' => _x('Person', 'taxonomy singular name', 'amazon-library'),
		'search_items' =>  __('Search People', 'amazon-library'),
		'popular_items' =>  __('Popular People', 'amazon-library'),
		'all_items' => __('All People', 'amazon-library'),
		'edit_item' => __('Edit Person', 'amazon-library'),
		'update_item' => __('Update Person', 'amazon-library'),
		'add_new_item' => __('Add New Person', 'amazon-library'),
		'new_item_name' => __('New Person', 'amazon-library'),
		'add_or_remove_items' => __('Add or remove people'),
		'choose_from_most_used' => __('Choose from the most used people'),
		'separate_items_with_commas' => __('Separate people\'s names with commas', 'amazon-library'),
	);

	$capabilities = array(
		'manage_terms' => 'edit_products',
		'delete_terms' => 'delete_products',
		'assign_terms' => 'edit_products',
		'edit_terms' => 'edit_products',
	);

	$args = array(
		'rewrite' => array('slug' => "$slug_base/$slug_person", 'pages' => true, 'feeds' => true, 'with_front' => false),
		'capabilities' => $capabilities,
		'query_var' => 'aml_person',
		'hierarchical' => false,
		'labels' => $labels,
	);
	register_taxonomy('aml_person','aml_product', $args);
	add_filter('taxonomy_template', 'aml_person_taxonomy_template');
}

/**
 * callback from registering aml_product to generate meta boxes on an edit page
 */
function aml_product_boxes() {
	 add_meta_box('aml_product_search', __('Search Amazon', 'amazon-library'), 'aml_mb_amazon_search', 'aml_product', 'normal', 'high');
	 add_meta_box('aml_product_meta', __('Additional Information', 'amazon-library'), 'aml_mb_product_meta', 'aml_product', 'side', 'high');
	 wp_enqueue_script( 'aml-product-script', plugins_url('/js/amazon.product.js', __FILE__) );
	 wp_enqueue_style( 'aml-product-style', plugins_url('/css/amazon.product.css', __FILE__) );
}

/**
 * meta-box for Amazon search
 * @todo push html to template functions
 */
function aml_mb_amazon_search() {
	$aml_categories = aml_amazon::$categories;
?>
<div class="aml_search_box">
	<select id="aml_search_type" name="aml_search_type">
	<?php foreach ($aml_categories as $cat) { ?>
		<option value="<?php echo $cat; ?>"><?php _e($cat, 'amazon-library'); ?></option>
	<?php } ?>
	</select>
	<div id="aml_search_stringwrap">
		<label class="hide-if-no-js" style="" id="aml_search_string-prompt-text" for="aml_search_string"><?php _e('Search for...', 'amazon-library'); ?></label>
		<input type="text" size="50" id="aml_search_string" name="aml_search_string" value="" autocomplete="off" />
	</div>
	<div id='aml_search_button' class="button-primary"><?php _e('Search Amazon', 'amazon-library') ?></div>
	<div id='aml_search_reset' class="button-primary"><?php _e('Reset Search', 'amazon-library') ?></div>
	<br style="clear:both" />
</div>
<?php }

/**
 * meta-box for additional meta-data (asin, link, image)
 * @todo push html to template functions
 */
function aml_mb_product_meta() {
	global $post;
	$type = get_post_meta($post->ID, 'aml_type', true);
	$asin = get_post_meta($post->ID, 'aml_asin', true);
	$link = get_post_meta($post->ID, 'aml_link', true);
	$image = get_post_meta($post->ID, 'aml_image', true);
	$image_preview = (empty($image)) ? '' : '<img src="' . $image . '" alt="preview" />';
	$aml_categories = aml_amazon::$categories;
?>
<div id="aml_image_preview"><?php echo $image_preview; ?></div>
<div id="aml_imagewrap">
	<label id="aml_image-prompt-text" for="aml_image"><?php _e('Link to image', 'amazon-library'); ?></label>
	<input type="text" size="50" id="aml_image" name="aml_image" value="<?php echo $image; ?>" autocomplete="off" />
</div>

<label for="aml_type"><?php _e('Amazon product type', 'amazon-library'); ?></label>
<select id="aml_type" name="aml_type">
<?php foreach ($aml_categories as $cat) { ?>
	<option value="<?php echo $cat; ?>"<?php selected($cat, $type); ?>><?php _e($cat, 'amazon-library'); ?></option>
<?php } ?>
</select>

<div id="aml_asinwrap">
	<label id="aml_asin-prompt-text" for="aml_asin"><?php _e('ASIN Number', 'amazon-library'); ?></label>
	<input type="text" size="50" id="aml_asin" name="aml_asin" value="<?php echo $asin; ?>" autocomplete="off" />
</div>

<div id="aml_linkwrap">
	<label id="aml_link-prompt-text" for="aml_link"><?php _e('Amazon product link', 'amazon-library'); ?></label>
	<input type="text" size="50" id="aml_link" name="aml_link" value="<?php echo $link; ?>" autocomplete="off" />
</div>
<?php
}

/**
 * callback to process posted metadata
 *
 * @param int post id
 */
function aml_product_meta_postback ($post_id) {
	$req = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : '';
	if ( ('aml_product' != $req) || !current_user_can( 'edit_product', $post_id ) ) {
		return $post_id;
	}

	$image = (isset($_REQUEST['aml_image'])) ? $_REQUEST['aml_image'] : null;
	$asin = (isset($_REQUEST['aml_asin'])) ? $_REQUEST['aml_asin'] : null;
	$type = (isset($_REQUEST['aml_type'])) ? $_REQUEST['aml_type'] : null;
	$link = (isset($_REQUEST['aml_link'])) ? $_REQUEST['aml_link'] : null;

	aml_update_meta('aml_asin', $post_id, $asin);
	aml_update_meta('aml_type', $post_id, $type);
	aml_update_meta('aml_link', $post_id, $link);
	aml_update_meta('aml_image', $post_id, $image);
}

/**
 * register additional columns for manage products page
 *
 * @param array columns
 * @return array columns (with additions)
 */
function aml_product_register_columns ($cols) {
	$cols['type'] = 'Category';
	$cols['image'] = 'Image';
	$cols['people'] = 'People';
	$cols['tags'] = 'Tags';
	$cols['connect'] = 'Connections';
	return $cols;
}

/**
 * display additional columns for manage products page
 *
 * @param string column name
 * @param int post id
 */
function aml_product_display_columns ($name, $post_id) {
	$post = get_post($post_id);

	switch ($name) {
		case 'type':
			$type = get_post_meta($post_id, 'aml_type', true);
			if (!empty($type)) {
				_e($type, 'amazon-library');
			}
			break;
		case 'image':
			$link = get_post_meta($post_id, 'aml_link', true);
			$image = get_post_meta($post_id, 'aml_image', true);
			$asin = get_post_meta($post_id, 'aml_asin', true);
			$asin = (empty($asin)) ? '' : '<div class="caption">'.$asin.'</div>';
			if (empty($image)) {
				$img = $asin;
				$asin = '';
			}
			else {
				$img = '<img src="'.$image.'" class="image_preview" />'.$asin;
			}
			if (empty($img)) {
				$img = '';
			}
			$img = (!empty($link)) ? '<a href="'.$link.'">'.$img.'</a>' : $img;

			echo '<div class="image">'.$img.'</div>';
			break;
		case 'people':
			$terms = get_the_term_list($post_id, 'aml_person', '', ', ');
			echo $terms;
			break;
		case 'tags':
			$terms = get_the_term_list($post_id, 'aml_tag', '', ', ');
			echo $terms;
			break;
		case 'connect':
			break;
	}
}

/**
 * display counts in the diashboard
 * @todo push html to template functions
 */
function aml_product_right_now() {
	$num_posts = wp_count_posts('aml_product');
	$num = number_format_i18n($num_posts->publish);
	$text = _n('Product', 'Products', intval($num_posts->publish), 'amazon-library');
	if (current_user_can('edit_products')) {
		$num = '<a href="/wp-admin/edit.php?post_type=aml_product">' . $num . '</a>';
		$text = '<a href="/wp-admin/edit.php?post_type=aml_product">' . $text . '</a>';
	}

	echo '<tr>';
	echo '<td class="first b b-tags">'.$num.'</td>';
	echo '<td class="t tags">' . $text . '</td>';
	echo '</tr>';
}

/**
 * initialise and register the actions for product post_type
 */
function aml_init_product() {
	aml_product_type();
	aml_people_tax();
	add_action('manage_aml_product_posts_custom_column', 'aml_product_display_columns', 10, 2);
	add_action('manage_edit-aml_product_columns', 'aml_product_register_columns');
	add_action('right_now_content_table_end', 'aml_product_right_now');
	add_action('save_post', 'aml_product_meta_postback');
}

?>