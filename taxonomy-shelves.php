<?php
/**
 * media shelves
 * @package amazon-library
 * $Rev$
 * $Date$
 */

/**
 * Our custom product post_type
 */
function aml_taxonomy_shelves() {
	$options = get_option('aml_options', aml_default_options());
	$slug_base = (empty($options['aml_slug_base'])) ? 'library' : $options['aml_slug_base'];
	$slug_shelf = (empty($options['aml_slug_shelf'])) ? 'shelf' : $options['aml_slug_shelf'];

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
			'supports' => array('title', 'author'),
			'rewrite' => array('slug' => "$slug_base/$slug_shelf", 'pages' => true, 'feeds' => true, 'with_front' => false),
			'has_archive' => false,
			'register_meta_box_cb' => 'aml_shelves_mb',
			'query_var' => true,
			'public_queryable' => true,
			'public' => true,
			'show_ui' => true,
		);
	register_post_type('aml_product', $args);
}

// Callback for custom post_type metaboxes
function aml_shelves_mb() {
	 add_meta_box('aml_shelf_products', __('Products on Shelf', 'amazon-library'), 'aml_shelf_mb_products', 'aml_shelf', 'normal', 'high'); // Each product should have a link to manage uses and add a use
	 add_meta_box('aml_shelf_search', __('Search Amazon for New Product', 'amazon-library'), 'aml_shelf_mb_search', 'aml_shelf', 'normal', 'high'); // search should first return results from the local library with a link at end to search AMZ instead
// 	 add_meta_box('aml_shelf_meta', __('Additional Information', 'amazon-library'), 'aml_shelf_mb_meta', 'aml_shelf', 'side', 'high');
// 	 add_action('save_post', 'aml_shelf_meta_postback');
	 wp_enqueue_script('aml-meta-script', plugins_url('/js/amazon.shelf.js', dirname(__FILE__) ));
	 wp_enqueue_style('aml-meta-style', plugins_url('/css/amazon.css', dirname(__FILE__) ));
}

// display meta box
function aml_shelf_mb_search() {
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

function aml_shelf_mb_lookup() {
?>
<div id="aml_lookup_stringwrap">
	<label class="hide-if-no-js" style="" id="aml_lookup_string-prompt-text" for="aml_lookup_string"><?php _e('Enter ASIN', 'amazon-library'); ?></label>
	<input type="text" size="50" id="aml_lookup_string" name="aml_lookup_string" value="" autocomplete="off" />
</div>
<div id='aml_lookup_button' class="button-primary"><?php _e('Lookup on Amazon', 'amazon-library') ?></div>
<div id='aml_lookup_reset' class="button-primary"><?php _e('Reset Lookup', 'amazon-library') ?></div>
<?php }

function aml_shelf_columns ($name, $post_id) {
	// Link to manage all uses for the shelf
	// link to record a new usage for a product on the self
}

function aml_shelf_bulk_box ($column, $post_type) {

}

function aml_shelf_quick_box ($column, $post_type) {

}

add_action('manage_aml_shelf_posts_custom_column', 'aml_shelf_columns');
add_action('bulk_edit_custom_box', 'aml_shelf_bulk_box');
add_action('quick_edit_custom_box', 'aml_shelf_quick_box');

?>