<?php
/**
 * Product custom_type and taxonomies
 * @package amazon-library
 */

/**
 * Our custom product post_type
 */
function aml_type_products() {
	$options = get_option('aml_options', aml_default_options());
	$slug_base = (empty($options['aml_slug_base'])) ? 'library' : $options['aml_slug_base'];
	$slug_product = (empty($options['aml_slug_product'])) ? 'product' : $options['aml_slug_product'];

	$labels = array(
		'name' => __('Products', 'amazon-library'),
		'singular_name' => __('Product', 'amazon-library'),
		'add_new' => __('Add New'),
		'add_new_item' => __('Add New Product', 'amazon-library'),
		'edit' => __('Edit'),
		'edit_item' => __('Edit Product', 'amazon-library'),
		'new_item' => __('New Product', 'amazon-library'),
		'view' => __('View'),
		'view_item' => __('View Product', 'amazon-library'),
		'search_items' => __('Search products', 'amazon-library'),
		'not_found' => __('No products found', 'amazon-library'),
		'not_found_in_trash' => __('No products found in trash', 'amazon-library'),
	);

	$args = array(
			'labels' => $labels,
			'capability_type' => 'product',
			'description' => __('Product information and pictures fetched from Amazon. Similar to Amazon, an "official" review can be entered in the product page while individual users can also provide their own reviews in their shelves.'),
			'supports' => array('title', 'editor', 'revisions'),
			'rewrite' => array('slug' => "$slug_base/$slug_product", 'pages' => false, 'feeds' => false, 'with_front' => false),
			'has_archive' => $slug_base,
			'register_meta_box_cb' => 'aml_meta_boxes',
			'menu_position' => 10,
			'query_var' => true,
			'public' => true,
		);
	register_post_type('aml_product', $args);
}

/**
 * People taxonomy for products
 */
function aml_taxonomy_people() {
	$options = get_option('aml_options', aml_default_options());
	$slug_base = (empty($options['aml_slug_base'])) ? 'library' : $options['aml_slug_base'];
	$slug_person = (empty($options['aml_slug_person'])) ? 'person' : $options['aml_slug_person'];

	$labels = array(
		'name' => __('People', 'amazon-library'),
		'singular_name' => __('Person', 'amazon-library'),
		'search_items' =>  __('Search people', 'amazon-library'),
		'all_items' => __('All people', 'amazon-library'),
		'edit_item' => __('Edit person', 'amazon-library'),
		'update_item' => __('Update person', 'amazon-library'),
		'add_new_item' => __('Add New Person', 'amazon-library'),
		'new_item_name' => __('New Person', 'amazon-library'),
	);

	$capabilities = array(
		'manage_terms' => 'edit_products',
		'edit_terms' => 'edit_product',
		'delete_terms' => 'edit_products',
		'assign_terms' => 'edit_product',
	);

	$args = array(
		'query_var' => 'aml_person',
		'labels' => $labels,
		'capabilities' => $capabilities,
		'hierarchical' => false,
		'rewrite' => array('slug' => "$slug_base/$slug_person", 'pages' => true, 'feeds' => true, 'with_front' => false),
	);
	register_taxonomy('aml_person','aml_product', $args);
}

/**
 * Generic taxonomy for products
 */
function aml_taxonomy_tags() {
	$options = get_option('aml_options', aml_default_options());
	$slug_base = (empty($options['aml_slug_base'])) ? 'library' : $options['aml_slug_base'];
	$slug_tag = (empty($options['aml_slug_tag'])) ? 'tag' : $options['aml_slug_tag'];

	$labels = array(
		'name' => __('Tags'),
		'singular_name' => __('Tag'),
		'search_items' =>  __('Search Tags'),
		'all_items' => __('All Tags'),
		'edit_item' => __('Edit Tag'),
		'update_item' => __('Update Tag'),
		'add_new_item' => __('Add New Tag'),
		'new_item_name' => __('New Tag Name'),
	);

	$capabilities = array(
		'manage_terms' => 'edit_products',
		'edit_terms' => 'edit_product',
		'delete_terms' => 'edit_products',
		'assign_terms' => 'edit_product',
	);

	$args = array(
		'query_var' => 'aml_tag',
		'labels' => $labels,
		'capabilities' => $capabilities,
	 	'hierarchical' => false,
		'rewrite' => array('slug' => "$slug_base/$slug_tag", 'pages' => true, 'feeds' => false, 'with_front' => false),
		'public' => true,
	);
	register_taxonomy( 'aml_tag', 'aml_product', $args);
}

/**
 * Callback from aml_type_products() to generate meta boxes on an edit page
 */
function aml_product_boxes() {
	 add_meta_box('aml_product_search', __('Search Amazon', 'amazon-library'), 'aml_product_mb_search', 'aml_product', 'normal', 'high');
// 	 add_meta_box('aml_product_lookup', __('Lookup Amazon Item', 'amazon-library'), 'aml_product_mb_lookup', 'aml_product', 'normal', 'high');
	 add_meta_box('aml_product_meta', __('Additional Information', 'amazon-library'), 'aml_producr_mb_meta', 'aml_product', 'side', 'high');
	 add_action('save_post', 'aml_product_meta_postback');
	 wp_enqueue_script('aml-meta-script', plugins_url('/js/amazon.product.js', dirname(__FILE__) ));
	 wp_enqueue_style('aml-meta-style', plugins_url('/css/amazon.css', dirname(__FILE__) ));
}

/**
 * Meta Box for Amazon search
 */
function aml_product_mb_search() {
	$aml_categories = aml_amazon::$categories;
?>
<select id="aml_type" name="aml_type">
<?php foreach ($aml_categories as $cat) { ?>
<option value="<?php echo $cat; ?>"><?php _e($cat, 'amazon-library'); ?></option>
<?php } ?>
</select>
<div id="aml_search_stringwrap">
	<label class="hide-if-no-js" style="" id="aml_search_string-prompt-text" for="aml_search_string"><?php _e('Search for...', 'amazon-library'); ?></label>
	<input type="text" size="50" id="aml_search_string" name="aml_search_string" value="" autocomplete="off" />
</div>
<span id='aml_search_button' class="button-primary"><?php _e('Search Amazon', 'amazon-library') ?></span>
<span id='aml_search_reset' class="button-primary"><?php _e('Reset Search', 'amazon-library') ?></span>
<?php }


/**
 * Meta Box for additional meta-data (ASIN, link, image)
 */
function aml_product_mb_meta() {
	global $post;
	$asin = get_post_meta($post->ID, 'aml_asin', true);
	$link = get_post_meta($post->ID, 'aml_link', true);
	$image = get_post_meta($post->ID, 'aml_image', true);

	$image_preview = (empty($image)) ? '' : '<img src="' . $image . '" alt="preview" />';

	// Verify
	echo'<input type="hidden" name="aml_product_meta_nonce" id="aml_product_meta_nonce" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
?>
<div id="aml_image_preview"><?php echo $image_preview; ?></div>
<div id="aml_imagewrap">
	<label id="aml_image-prompt-text" for="aml_image"><?php _e('Link to image', 'amazon-library'); ?></label>
	<input type="text" size="50" id="aml_image" name="aml_image" value="" autocomplete="off" />
</div>

<div id="aml_asinwrap">
	<label id="aml_asin-prompt-text" for="aml_asin"><?php _e('ASIN Number', 'amazon-library'); ?></label>
	<input type="text" size="50" id="aml_asin" name="aml_asin" value="" autocomplete="off" />
</div>

<div id="aml_linkwrap">
	<label id="aml_link-prompt-text" for="aml_link"><?php _e('Amazon product link', 'amazon-library'); ?></label>
	<input type="text" size="50" id="aml_link" name="aml_link" value="" autocomplete="off" />
</div>
<?php }

/**
 * Callback to process posted metadata
 */
function aml_product_meta_postback ($post_id) {
	// verify nonce and auth
	if ( !wp_verify_nonce($_POST["aml_product_meta_nonce"], plugin_basename(__FILE__)) || !current_user_can('edit_product', $post_id) ) {
		return;
	}

	$image = $_POST['aml_image'];
	$asin = $_POST['aml_asin'];
	$link = $_POST['aml_link'];

	$old_asin = get_post_meta($post_id, 'aml_asin', true);
	$old_link = get_post_meta($post_id, 'aml_link', true);
	$old_image = get_post_meta($post_id, 'aml_image', true);

	if(empty($image)) {
		delete_post_meta($post_id, 'aml_image', $old_image);
	}
	else {
		update_post_meta($post_id, 'aml_image', $image, $old_image);
	}

	if(empty($asin)) {
		delete_post_meta($post_id, 'aml_image', $old_asin);
	}
	else {
		update_post_meta($post_id, 'aml_image', $asin, $old_asin);
	}

	if(empty($link)) {
		delete_post_meta($post_id, 'aml_image', $old_link);
	}
	else {
		update_post_meta($post_id, 'aml_image', $link, $old_link);
	}
}

/**
 * Register additional columns for manage products page
 */
function aml_product_register_columns($cols) {
	$cols['people'] = 'People';
	$cols['image'] = 'Image';
	return $cols;
}


/**
 * Display additional columns for manage products page
 */
function aml_product_display_columns ($name, $post_id) {
	global $post;

	switch ($name) {
		case 'people':
			$terms = wp_get_object_terms($post_id, 'aml_person');
			if (is_array($terms)) {
				echo implode(', ', $terms);
			}
			break;
		case 'image':
			$link = get_post_meta($post_id, 'aml_link', true);
			$image = get_post_meta($post_id, 'aml_image', true);
			$img = (empty($image)) ? '' : '<img src="' . $image . '" class="image_preview" />';

			break;
	}

}

function aml_product_bulk_box ($column, $post_type) {

}

function aml_product_quick_box ($column, $post_type) {

}

function aml_product_right_now() {
	$num_posts = wp_count_posts('aml_product');
	$num = number_format_i18n($num_posts->publish);
	$text = _n('Product', 'Products', intval($num_posts->publish), 'amazon-libraries');
	if (current_user_can( 'm
	anage_products')) {
		$num = '<a href="/wp-admin/edit.php?post_type=aml_product">' . $num . '</a>';
		$text = '<a href="/wp-admin/edit.php?post_type=aml_product">' . $text . '</a>';
	}

	echo '<tr>';
	echo '<td class="first b b-tags">'.$num.'</td>';
	echo '<td class="t tags">' . $text . '</td>';
	echo '</tr>';
}

add_action('manage_edit-aml_product_columns', 'aml_product_register_columns');
add_action('manage_aml_product_posts_custom_column', 'aml_product_display_columns');
add_action('bulk_edit_custom_box', 'aml_product_bulk_box');
add_action('quick_edit_custom_box', 'aml_product_quick_box');
add_action('right_now_content_table_end', 'aml_product_right_now');

/**
 * Extra rewrite rules. Kept around for reference until stable release
 * @deprecated
 */
function aml_extra_rewrite() {
	global $wp_rewrite;

	$options = get_option('aml_options', aml_default_options());
	$slug_base = (empty($options['aml_slug_base'])) ? 'library' : $options['aml_slug_base'];

	add_rewrite_rule("$wp_rewrite->root/$slug_base/([^/]+)/([^/]+)/", $wp_rewrite->index.'?aml_author=$matches[1]&aml_product=$matches[2]');
	add_rewrite_rule("$wp_rewrite->root/$slug_base/([^/]+)/", $wp_rewrite->index.'?aml_author=$matches[1]');
}

/**
 * Meta Box for Amazon lookup
 * @deprecated
 */
function aml_product_mb_lookup() {
?>
<div id="aml_lookup_stringwrap">
	<label class="hide-if-no-js" style="" id="aml_lookup_string-prompt-text" for="aml_lookup_string"><?php _e('Enter ASIN', 'amazon-library'); ?></label>
	<input type="text" size="50" id="aml_lookup_string" name="aml_lookup_string" value="" autocomplete="off" />
</div>
<div id='aml_lookup_button' class="button-primary"><?php _e('Lookup on Amazon', 'amazon-library') ?></div>
<div id='aml_lookup_reset' class="button-primary"><?php _e('Reset Lookup', 'amazon-library') ?></div>
<?php }
