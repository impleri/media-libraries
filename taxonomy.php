<?php
/**
 * URL/mod_rewrite functions
 * @package amazon-library
 * $Rev$
 * $Date$
 */

/**
 * Creates our taxonomies using WP taxonomy & post type APIs
 */
function aml_taxonomy_init() {
	aml_taxonomy_products();
	aml_taxonomy_authors();
	aml_taxonomy_tags();
// 	aml_extra_rewrite();
	aml_capabilities();
}
add_action('init', 'aml_taxonomy_init');

// amazon product taxonomy
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
			'supports' => array('title', 'editor', 'revisions', 'thumbnail'),
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

// product author
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

// More WP rewrites (not covered in taxonomies above)
function nrm_extra_rewrite() {
	global $wp_rewrite;
	$options = get_option('aml_options', aml_default_options());
	$slug_base = (empty($options['aml_slug_base'])) ? 'library' : $options['aml_slug_base'];
	$slug_user = (empty($options['aml_slug_user'])) ? 'user' : $options['aml_slug_user'];

	$wp_rewrite->add_rule("$wp_rewrite->root/$slug_user/([^/]+)/", $wp_rewrite->index.'?aml_user=$matches[1]');
}

// NOT WORKING
// deprecated
function aml_extra_rewrite() {
	global $wp_rewrite;

	$options = get_option('aml_options', aml_default_options());
	$slug_base = (empty($options['aml_slug_base'])) ? 'library' : $options['aml_slug_base'];

	add_rewrite_rule("$wp_rewrite->root/$slug_base/([^/]+)/([^/]+)/", $wp_rewrite->index.'?aml_author=$matches[1]&aml_product=$matches[2]');
	add_rewrite_rule("$wp_rewrite->root/$slug_base/([^/]+)/", $wp_rewrite->index.'?aml_author=$matches[1]');
}

// WP capabilities, roles, auths, etc
function aml_capabilities() {
	global $wp_roles;

	$wp_roles->add_cap('administrator', 'read_products');
	$wp_roles->add_cap('administrator', 'delete_product');
	$wp_roles->add_cap('administrator', 'publish_products');
	$wp_roles->add_cap('administrator', 'edit_product');
	$wp_roles->add_cap('administrator', 'edit_products');
	$wp_roles->add_cap('administrator', 'edit_others_products');
	$wp_roles->add_cap('administrator', 'read_private_products');

	$wp_roles->add_cap('editor', 'read_products');
	$wp_roles->add_cap('editor', 'delete_product');
	$wp_roles->add_cap('editor', 'publish_products');
	$wp_roles->add_cap('editor', 'edit_product');
	$wp_roles->add_cap('editor', 'edit_products');
	$wp_roles->add_cap('editor', 'edit_others_products');

	$wp_roles->add_cap('author', 'read_products');
	$wp_roles->add_cap('author', 'publish_products');
	$wp_roles->add_cap('author', 'edit_product');
	$wp_roles->add_cap('author', 'edit_products');

	$wp_roles->add_cap('contributor', 'read_products');

	$wp_roles->add_cap('subscriber', 'read_products');
}

// WP meta boxes
function aml_meta_boxes() {
	 add_meta_box('aml_amazon_search', __('Search Amazon', 'amazon-library'), 'aml_mb_amazon_search', 'aml_product', 'normal', 'high');
	 add_meta_box('aml_amazon_lookup', __('Lookup Amazon Item', 'amazon-library'), 'aml_mb_amazon_lookup', 'aml_product', 'normal', 'high');
	 add_action('wp_ajax_aml_amazon_search', 'aml_amazon_search_callback');
	 add_action('wp_ajax_aml_lookup_search', 'aml_amazon_lookup_callback');
	 add_action('admin_head', 'aml_amazon_ajax_js');
}

// display meta box
function aml_mb_amazon_search() {}

// output javascript
function aml_amazon_ajax_js() { ?>
<script type="text/javascript" >
jQuery(document).ready(function(jQuery) {
	jQuery("li.category-name a").click( function() {
		jQuery(this).unbind('click').bind('click', function(){return false;});

		var li = jQuery(this).parent();
		var data = {
			action: 'aml_amazon_search',
			type: 'Books',
			search: '',
		};

		jQuery.post(ajaxurl, data, function(response) {
			li.append($(response));
		});
	});

	jQuery("li.category-name a").click( function() {
		jQuery(this).unbind('click').bind('click', function(){return false;});

		var li = jQuery(this).parent();
		var data = {
			action: 'aml_amazon_lookup',
			search: '',
		};

		jQuery.post(ajaxurl, data, function(response) {
			li.append($(response));
		});
	});
});
</script>
<?php }

// handle js search callback
function aml_amazon_search_callback() {
	// validate posted data
	$search = $_POST['whatever'];
	$type = $_POST['whatever'];

	// run amazon query
	$ret = aml_amazon::search($search, $type);

	//return results

	die();
}

// handle js lookup callback
function aml_amazon_lookup_callback() {
	// validate posted data
	$search = $_POST['whatever'];

	// run amazon query
	$ret = aml_amazon::lookup($search);

	//return results

	die();
}

?>