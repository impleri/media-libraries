<?php
/**
 * media shelves
 * @package amazon-library
 * $Rev$
 * $Date$
 */

// $nr_statuses = apply_filters('nr_statuses', array(
//     'unread'	=> __('Yet to read', NRTD),
//     'onhold'	=> __('On Hold', NRTD),
//     'reading'	=> __('Currently reading', NRTD),
//     'rereading'	=> __('Currently re-reading', NRTD),
//     'read'		=> __('Finished', NRTD)
// ));

/**
 * Our custom product post_type
 */
function aml_taxonomy_readings() {
	$options = get_option('aml_options', aml_default_options());
	$slug_base = (empty($options['aml_slug_base'])) ? 'library' : $options['aml_slug_base'];
	$slug_product = (empty($options['aml_slug_product'])) ? 'book' : $options['aml_slug_product'];

	$labels = array(
		'name' => __('Uses', 'amazon-library'),
		'singular_name' => __('Use', 'amazon-library'),
		'add_new' => __('Add New'),
		'add_new_item' => __('Add New Use', 'amazon-library'),
		'edit' => __('Edit'),
		'edit_item' => __('Edit Use', 'amazon-library'),
		'new_item' => __('New Use', 'amazon-library'),
		'view' => __('View'),
		'view_item' => __('View Use', 'amazon-library'),
		'search_items' => __('Search Uses', 'amazon-library'),
		'not_found' => __('No uses found', 'amazon-library'),
		'not_found_in_trash' => __('No uses found in Trash', 'amazon-library'),
	);

	$args = array(
			'labels' => $labels,
			'capability_type' => 'reading',
			'description' => __('A single use of a media product (e.g. reading a book, watching a DVD, listening to music, etc)'),
			'supports' => array('title', 'author', 'editor', 'revisions'),
			'rewrite' => array('slug' => "$slug_base/$slug_product", 'pages' => true, 'feeds' => true, 'with_front' => false),
			'has_archive' => $slug_base,
			'register_meta_box_cb' => 'aml_usage_mb',
			'query_var' => true,
			'public_queryable' => true,
			'public' => true,
			'show_ui' => true,
		);
	register_post_type('aml_use', $args);
}

// Callback for custom post_type metaboxes
function aml_shelves_mb() {
	 add_meta_box('aml_use_shelf_products', __('Products on Shelf', 'amazon-library'), 'aml_shelf_mb_products', 'aml_use_shelf', 'normal', 'high'); // should only show up if a product isn't specified
 	 add_meta_box('aml_shelf_meta', __('Reading Details', 'amazon-library'), 'aml_shelf_mb_meta', 'aml_shelf', 'side', 'high'); // start date, stop date, status, rating
// 	 add_action('save_post', 'aml_shelf_meta_postback');
	 wp_enqueue_script('aml-meta-script', plugins_url('/js/amazon.reading.js', dirname(__FILE__) ));
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

// Image, ASIN, Rating, Extra Meta-Data
function aml_shelf_mb_meta() {
	global $post;
	$review = get_post_meta($post->ID, 'aml_image', true);
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

function aml_shelf_meta_postback ($post_id) {
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

function aml_shelf_columns ($name, $post_id) {

}

function aml_shelf_bulk_box ($column, $post_type) {

}

function aml_shelf_quick_box ($column, $post_type) {

}

add_action('manage_aml_shelf_posts_custom_column', 'aml_shelf_columns');
add_action('bulk_edit_custom_box', 'aml_shelf_bulk_box');
add_action('quick_edit_custom_box', 'aml_shelf_quick_box');

?>