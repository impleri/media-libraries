<?php
/**
 * WP taxonomies
 * @package amazon-library
 * $Rev$
 * $Date$
 */

/**
 * Our custom product post_type
 */
function aml_taxonomy_products() {
	$options = get_option('aml_options', aml_default_options());
	$slug_base = (empty($options['aml_slug_base'])) ? 'library' : $options['aml_slug_base'];
	$slug_product = (empty($options['aml_slug_product'])) ? 'book' : $options['aml_slug_product'];

	$labels = array(
		'name' => __('Books', 'amazon-library'),
		'singular_name' => __('Book', 'amazon-library'),
		'add_new' => __('Add New'),
		'add_new_item' => __('Add New Book', 'amazon-library'),
		'edit' => __('Edit'),
		'edit_item' => __('Edit Book', 'amazon-library'),
		'new_item' => __('New Book', 'amazon-library'),
		'view' => __('View'),
		'view_item' => __('View Book', 'amazon-library'),
		'search_items' => __('Search Books', 'amazon-library'),
		'not_found' => __('No books found', 'amazon-library'),
		'not_found_in_trash' => __('No books found in Trash', 'amazon-library'),
	);

	$args = array(
			'labels' => $labels,
			'capability_type' => 'product',
			'description' => __('Book information and pictures fetched from Amazon'),
			'supports' => array('title', 'editor', 'revisions'),
			'rewrite' => array('slug' => "$slug_base/$slug_product", 'pages' => true, 'feeds' => true, 'with_front' => false),
			'has_archive' => $slug_base,
			'register_meta_box_cb' => 'aml_meta_boxes',
			'query_var' => true,
			'public_queryable' => true,
			'public' => true,
			'show_ui' => true,
		);
	register_post_type('aml_product', $args);
}

function aml_taxonomy_authors() {
	$options = get_option('aml_options', aml_default_options());
	$slug_base = (empty($options['aml_slug_base'])) ? 'library' : $options['aml_slug_base'];
	$slug_author = (empty($options['aml_slug_author'])) ? 'author' : $options['aml_slug_author'];

	$labels = array(
		'name' => __('Authors', 'amazon-library'),
		'singular_name' => __('Author', 'amazon-library'),
		'search_items' =>  __('Search Authors', 'amazon-library'),
		'all_items' => __('All Authors', 'amazon-library'),
		'edit_item' => __('Edit Author', 'amazon-library'),
		'update_item' => __('Update Author', 'amazon-library'),
		'add_new_item' => __('Add New Author', 'amazon-library'),
		'new_item_name' => __('New Author', 'amazon-library'),
	);

	$capabilities = array(
		'manage_terms' => 'manage_products',
		'edit_terms' => 'edit_products',
		'delete_terms' => 'manage_products',
		'assign_terms' => 'edit_product',
	);

	$args = array(
		'query_var' => 'aml_author',
		'labels' => $labels,
		'capabilities' => $capabilities,
		'hierarchical' => false,
		'rewrite' => array('slug' => "$slug_base/$slug_author", 'pages' => true, 'feeds' => true, 'with_front' => false),
	);
	register_taxonomy('aml_author','aml_product', $args);
}

// product tags
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
		'manage_terms' => 'manage_products',
		'edit_terms' => 'edit_products',
		'delete_terms' => 'manage_products',
		'assign_terms' => 'edit_product',
	);

	$args = array(
		'query_var' => 'aml_tag',
		'labels' => $labels,
		'capabilities' => $capabilities,
	 	'hierarchical' => false,
		'rewrite' => array('slug' => "$slug_base/$slug_tag", 'pages' => true, 'feeds' => false, 'with_front' => false),
		'public' => true,
		'show_ui' => true,
	);
	register_taxonomy( 'aml_tag', 'aml_product', $args);
}

// Callback for custom post_type metaboxes
function aml_product_boxes() {
	 add_meta_box('aml_product_search', __('Search Amazon', 'amazon-library'), 'aml_product_mb_search', 'aml_product', 'normal', 'high');
	 add_meta_box('aml_product_lookup', __('Lookup Amazon Item', 'amazon-library'), 'aml_product_mb_lookup', 'aml_product', 'normal', 'high');
	 add_meta_box('aml_product_meta', __('Additional Information', 'amazon-library'), 'aml_producr_mb_meta', 'aml_product', 'side', 'high');
	 add_action('save_post', 'aml_product_meta_postback');
	 wp_enqueue_script('aml-meta-script', plugins_url('/js/amazon.product.js', dirname(__FILE__) ));
	 wp_enqueue_style('aml-meta-style', plugins_url('/css/amazon.css', dirname(__FILE__) ));
}

// display meta box
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
<div id='aml_search_button' class="button-primary"><?php _e('Search Amazon', 'amazon-library') ?></div>
<div id='aml_search_reset' class="button-primary"><?php _e('Reset Search', 'amazon-library') ?></div>
<?php }

function aml_product_mb_lookup() {
?>
<div id="aml_lookup_stringwrap">
	<label class="hide-if-no-js" style="" id="aml_lookup_string-prompt-text" for="aml_lookup_string"><?php _e('Enter ASIN', 'amazon-library'); ?></label>
	<input type="text" size="50" id="aml_lookup_string" name="aml_lookup_string" value="" autocomplete="off" />
</div>
<div id='aml_lookup_button' class="button-primary"><?php _e('Lookup on Amazon', 'amazon-library') ?></div>
<div id='aml_lookup_reset' class="button-primary"><?php _e('Reset Lookup', 'amazon-library') ?></div>
<?php }

// Image, ASIN, Rating, Extra Meta-Data
function aml_product_mb_meta() {
	global $post;
	$asin = get_post_meta($post->ID, 'aml_asin', true);
	$link = get_post_meta($post->ID, 'aml_link', true);
	$image = get_post_meta($post->ID, 'aml_image', true);
	$rating = get_post_meta($post->ID, 'aml_rating', true);

	// Verify
	echo'<input type="hidden" name="ch_link_url_noncename" id="ch_link_url_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
?>
<div id="aml_image_preview"></div>
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

function aml_product_meta_postback ($post_id) {
	global $post;

	// Verify
	if ( !wp_verify_nonce( $_POST["ch_link_url_noncename"], plugin_basename(__FILE__) )) {
		return $post_id;
	}
	if ( !current_user_can( 'edit_product', $post_id )) {
		return $post_id;
	}

	$data = $_POST['ch_link_url'];

	// New, Update, and Delete
	if(get_post_meta($post_id, 'ch_link_url') == "")
		add_post_meta($post_id, 'ch_link_url', $data, true);
	elseif($data != get_post_meta($post_id, 'ch_link_url', true))
		update_post_meta($post_id, 'ch_link_url', $data);
	elseif($data == "")
		delete_post_meta($post_id, 'ch_link_url', get_post_meta($post_id, 'ch_link_url', true));
}

function aml_product_columns ($name, $post_id) {

}

function aml_product_bulk_box ($column, $post_type) {

}

function aml_product_quick_box ($column, $post_type) {

}

add_action('manage_aml_product_posts_custom_column', 'aml_product_columns');
add_action('bulk_edit_custom_box', 'aml_product_bulk_box');
add_action('quick_edit_custom_box', 'aml_product_quick_box');


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
