<?php
/**
 * media shelves
 * @package amazon-library
 * @author Christopher Roussel <christopher@impleri.net>
 */

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
			'register_meta_box_cb' => 'aml_review_mb',
			'public_queryable' => true,
			'hierarchical' => true,
			'query_var' => true,
			'show_ui' => true,
			'public' => true,
		);
	register_post_type('aml_review', $args);
	add_filter('archive_template', 'aml_review_archive_template');
	add_filter('single_template', 'aml_review_single_template');
}

/**
 * Review custom stati
 */
function aml_review_stati() {
	register_post_status( 'added', array(
		'label'       => _x('Yet to use', 'post', 'amazon-library'),
		'public'      => true,
	) );
	register_post_status( 'onhold', array(
		'label'       => _x('On Hold', 'post', 'amazon-library'),
		'public'      => true,
	) );
	register_post_status( 'using', array(
		'label'       => _x('Currently using', 'post', 'amazon-library'),
		'public'      => true,
	) );
	register_post_status( 'finished', array(
		'label'       => _x('Finished', 'post', 'amazon-library'),
		'public'      => true,
	) );
}

/**
 * Generic taxonomy for reviews (all of this just to rename 'post tags' to simply 'tags'!)
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
	add_filter('taxonomy_template', 'aml_tags_taxonomy_template');
}

/**
 * Register meta-box for review page
 */
function aml_review_mb() {
	 add_meta_box('aml_review_meta', __('Product', 'amazon-library'), 'aml_review_meta', 'aml_review', 'side', 'high');
}

/**
 * Review details meta-box
 *
 * @param object WP_post
 */
function aml_review_meta ($post) {
	$rating = get_post_meta($post->ID, 'aml_rating', true);
	$added = get_post_meta($post->ID, 'aml_added', true);
	$started = get_post_meta($post->ID, 'aml_started', true);
	$finish = get_post_meta($post->ID, 'aml_finished', true);
	// todo: need to add post_status!!

	$post_type_object = get_post_type_object($post->post_type);
	$can_publish = current_user_can($post_type_object->cap->publish_posts);
	$pages = wp_dropdown_pages(array('post_type' => 'aml_product', 'selected' => $post->post_parent, 'name' => 'parent_id', 'show_option_none' => __('(no parent)'), 'sort_column'=> 'menu_order, post_title', 'echo' => 0));

	echo'<input type="hidden" name="aml_review_product_nonce" id="aml_review_product_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';

	if ( ! empty($pages) ) { ?>
		<p><strong><?php _e('Parent') ?></strong></p>
		<label class="screen-reader-text" for="parent_id"><?php _e('Review', 'amazon-library'); ?></label>
		<?php echo $pages; ?>
<?php } // end empty pages check
	?>
	<div id="aml_product-thumb"></div>
	<?php
	review_stars();
	aml_show_date($added, 'added', $can_publish);
	aml_show_date($started, 'started', $can_publish);
	aml_show_date($finish, 'finished', $can_publish);
}

/**
 * Review details meta-box postback
 *
 * @param int Post ID
 */
function aml_review_mb_postback ($post_id) {
	global $post;

	// Verify
	if ( !wp_verify_nonce( $_POST["aml_review_product_nonce"], basename(__FILE__)) || !current_user_can( 'edit_review', $post_id ) ) {
		return $post_id;
	}
	$stati = get_available_post_statuses('aml_review');

	// need to validate/clean
	$rating = $_REQUEST['aml_rating'];
	$added = $_REQUEST['aml_added'];
	$started = $_REQUEST['aml_started'];
	$finished = $_REQUEST['aml_finished'];
	// these should be done automatically by WP
// 	$status = $_REQUEST['post_status'];
// 	$parent = $_REQUEST['parent_id'];

	// New, Update, and Delete
	aml_update_meta('aml_rating', $post_id, $rating);
	aml_update_meta('aml_added', $post_id, $added);
	aml_update_meta('aml_started', $post_id, $started);
	aml_update_meta('aml_finished', $post_id, $finished);
}

/**
 * Register additional columns for manage reviews page
 *
 * @param array Columns
 * @return array Columns with our additions
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
 *
 * @param string Column name
 * @param int Post ID
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
	aml_review_stati();
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