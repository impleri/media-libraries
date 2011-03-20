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

// NOTE: use register_post_status for the statuses!

/**
 * Review custom post_type
 */
function aml_type_review() {
	$slug_base = aml_get_option('aml_slug_base');
	$slug_review = aml_get_option('aml_slug_review');

	$labels = array(
		'name' => __('Reviews', 'amazon-library'),
		'singular_name' => __('Review', 'amazon-library'),
		'add_new' => __('Add New'),
		'add_new_item' => __('Add New Review', 'amazon-library'),
		'edit' => __('Edit'),
		'edit_item' => __('Edit Review', 'amazon-library'),
		'new_item' => __('New Review', 'amazon-library'),
		'view' => __('View'),
		'view_item' => __('View Review', 'amazon-library'),
		'search_items' => __('Search Reviews', 'amazon-library'),
		'not_found' => __('No reviews found', 'amazon-library'),
		'not_found_in_trash' => __('No reviews found in trash', 'amazon-library'),
	);

	$args = array(
			'labels' => $labels,
			'capability_type' => 'review',
			'description' => __('A single review/use of a product (e.g. reading a book, watching a DVD, listening to music, etc)'),
			'supports' => array('title', 'author', 'editor', 'revisions'),
			'rewrite' => array('slug' => "$slug_base/$slug_review", 'pages' => false, 'feeds' => false, 'with_front' => false),
// 			'has_archive' => $slug_base,
			'register_meta_box_cb' => 'aml_review_mb',
			'query_var' => true,
			'public_queryable' => true,
			'public' => true,
			'show_ui' => true,
		);
	register_post_type('aml_review', $args);
}

/**
 * Generic taxonomy for reviews
 */
function aml_review_tags() {
	$slug_base = aml_get_option('slug_base');
	$slug_tag = aml_get_option('slug_tag');

	$labels = array(
		'name' => _x('Tags', 'taxonomy general name', 'amazon-library'),
		'singular_name' => _x('Tag', 'taxonomy singular name', 'amazon-library'),
	);

	$capabilities = array(
		'manage_terms' => 'manage_tags',
		'edit_terms' => 'edit_tags',
		'delete_terms' => 'edit_tags',
		'assign_terms' => 'edit_reviews',
	);

	$args = array(
		'rewrite' => array('slug' => "$slug_base/$slug_tag", 'pages' => true, 'feeds' => false, 'with_front' => false),
		'capabilities' => $capabilities,
		'query_var' => 'aml_tag',
	 	'hierarchical' => false,
		'labels' => $labels,
		'public' => true,
	);
	register_taxonomy( 'aml_tag', 'aml_review', $args);
}

/**
 * Meta-boxes for review page
 */
function aml_review_mb() {
	 add_meta_box('aml_review_meta_product', __('Product', 'amazon-library'), 'aml_review_product', 'aml_review', 'side', 'high'); // should only show up if a product isn't specified
 	 add_meta_box('aml_review_meta_details', __('Reading Details', 'amazon-library'), 'aml_review_meta', 'aml_review', 'side', 'normal'); // start date, stop date, status, rating
}

/**
 * Product select (and rating) meta-box
 */
function aml_review_product() {
	global $post;

	// Product ID can come from three locations: GET (if clicked from product list), Meta (if already saved), or AJAX (if searching on page)
	$product = 0;
	$meta_product = intval(get_post_meta($post->ID, 'aml_product', true));
	$post_product = intval($_REQUEST['aml_product']);

	// check for saved product first
	if ($meta_product > 1) {
		$product = $meta_product;
	}
	// then check for product in GET
	elseif ($post_product > 1) {
		$product = $post_product;
	}

	$rating = get_post_meta($post->ID, 'aml_rating', true);

	// Verify
	echo'<input type="hidden" name="ch_link_url_noncename" id="ch_link_url_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
?>
<div id="aml_product-thumb"></div>
<div id="aml_product_search">
	<label id="aml_image-prompt-text" for="aml_image"><?php _e('Link to image', 'amazon-library'); ?></label>
	<input type="text" size="50" id="aml_image" name="aml_image" value="" autocomplete="off" />
</div>
<?php review_stars(); ?>
<?php }

/**
 * Product usage meta-box
 */
function aml_review_meta() {
	global $post;

	$post_type = $post->post_type;
	$post_type_object = get_post_type_object($post_type);
	$can_publish = current_user_can($post_type_object->cap->publish_posts);

	$added = get_post_meta($post->ID, 'aml_added', true);
	$began = get_post_meta($post->ID, 'aml_started', true);
	$finish = get_post_meta($post->ID, 'aml_finished', true);

	// Verify
	echo'<input type="hidden" name="ch_link_url_noncename" id="ch_link_url_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';

	aml_show_date($added, 'added', $can_publish);
	aml_show_date($began, 'started', $can_publish);
	aml_show_date($finish, 'finished', $can_publish);
?>
<div id="aml_linkwrap">
	<label id="aml_link-prompt-text" for="aml_link"><?php _e('Amazon product link', 'amazon-library'); ?></label>
	<input type="text" size="50" id="aml_link" name="aml_link" value="" autocomplete="off" />
</div>
<?php }

function aml_review_mb_postback ($post_id) {
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

/**
 * Register additional columns for manage products page
 */
function aml_review_register_columns($cols) {
	$cols['type'] = 'Category';
	$cols['image'] = 'Image';
	$cols['people'] = 'People';
	$cols['tags'] = 'Tags';
	$cols['connect'] = 'Connections';
	return $cols;
}

/**
 * Display additional columns for manage products page
 */
function aml_review_display_columns ($name, $post_id) {
	global $post;

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
 * Register the actions for our product post_type
 */
function aml_init_review() {
	aml_type_review();
	if (aml_get_option('use_tags')) {
		aml_review_tags();
	}
	if (aml_get_option('use_categories')) {
		register_taxonomy_for_object_type('category', 'aml_review');
	}
	add_action('manage_aml_review_posts_custom_column', 'aml_review_display_columns', 10, 2);
	add_action('manage_edit-aml_review_columns', 'aml_review_register_columns');
	add_action('right_now_content_table_end', 'aml_product_right_now');
	add_action('save_post', 'aml_review_mb_postback');
	wp_enqueue_script('aml-review-script', plugins_url('/js/jquery.rating.js', dirname(__FILE__) ));
	wp_enqueue_style('aml-review-style', plugins_url('/css/jquery.rating.css', dirname(__FILE__) ));
}

?>