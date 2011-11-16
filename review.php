<?php
/**
 * product reviews and related taxonomies
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * @todo
 * BE list boxes: official status, rating
 * BE quick edit box: product drop down, star rating, official/sticky
 * FE template: book details (with link to main book page and link to product source), tags, usage summary
 * Comments? Link to other reviews?
 */

/**
 * review post_type
 */
function ml_review_type() {
	$slug_base = ml_get_option('ml_slug_base');
	$slug_review = ml_get_option('ml_slug_review');

	$labels = array(
		'name' => __('Reviews', 'media-libraries'),
		'singular_name' => __('Review', 'media-libraries'),
		'add_new' => __('Add New'),
		'add_new_item' => __('Add New Review', 'media-libraries'),
		'edit' => __('Edit'),
		'edit_item' => __('Edit Review', 'media-libraries'),
		'new_item' => __('New Review', 'media-libraries'),
		'view' => __('View'),
		'view_item' => __('View Review', 'media-libraries'),
		'search_items' => __('Search Reviews', 'media-libraries'),
		'not_found' => __('No reviews found', 'media-libraries'),
		'not_found_in_trash' => __('No reviews found in trash', 'media-libraries'),
	);

	$args = array(
		'description' => __('A review of a product'),
		'rewrite' => array('slug' => "$slug_base/$slug_review", 'pages' => false, 'feeds' => false, 'with_front' => false),
		'supports' => array('title', 'author', 'editor', 'revisions'),
		'show_in_menu' => 'edit.php?post_type=ml_product',
		'register_meta_box_cb' => 'ml_review_boxes',
		'capability_type' => 'review',
		'map_meta_cap' => true,
		'hierarchical' => false,
		'query_var' => true,
		'labels' => $labels,
		'show_ui' => true,
		'public' => true,
	);
	register_post_type('ml_review', $args);
	add_filter('archive_template', 'ml_review_archive_template');
	add_filter('single_template', 'ml_review_single_template');
}

/**
 * generic taxonomy for reviews (all of this just to rename 'post tags' to simply 'tags'!)
 */
function ml_tag_tax() {
	$slug_base = ml_get_option('slug_base');
	$slug_tag = ml_get_option('slug_tag');

	$labels = array(
		'name' => _x('Tags', 'taxonomy general name', 'media-libraries'),
		'singular_name' => _x('Tag', 'taxonomy singular name', 'media-libraries'),
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
		'query_var' => 'ml_tag',
	 	'hierarchical' => false,
		'labels' => $labels,
		'public' => true,
	);
	register_taxonomy( 'ml_tag', 'ml_review', $args);
	add_filter('taxonomy_template', 'ml_tags_taxonomy_template');
}

/**
 * callback from registering ml_review to generate meta boxes on an edit page
 */
function ml_review_boxes() {
	add_meta_box('ml_review_meta', __('Product', 'media-libraries'), 'ml_review_meta', 'ml_review', 'side', 'high');
	wp_enqueue_script('ml-review-script', plugins_url('/js/media.review.js', __FILE__));
	wp_enqueue_script('ml-metadata-script', plugins_url('/js/jquery.metadata.js', __FILE__));
	wp_enqueue_script('ml-rating-script', plugins_url('/js/jquery.rating.js', __FILE__));
}

/**
 * meta-box for review details/meta
 *
 * @param object WP_post
 * @todo push html to template file
 */
function ml_review_meta ($post) {
	$rating = get_post_meta($post->ID, 'ml_rating', true);
	$parent = isset($post->post_parent) ? $post->post_parent : 0;

	$old_args = array('numberposts' => -1, 'fields' => 'id=>parent', 'post_type' => 'ml_review', 'author' => $post->post_author, 'exclude' => array($post->ID));
	$old = get_posts($old_args);

	$args = array('post_type' => 'ml_product', 'depth' => 1, 'echo' => 0, 'selected' => $parent, 'name' => 'parent_id',  'sort_column'=> 'menu_order,post_title', 'exclude' => $old);
	$parents = wp_dropdown_pages($args);
	if ( !empty($parents) ) {
		echo '<p><strong>' . __('Product to Review', 'media-libraries') . '</strong></p>' .
		'<label class="screen-reader-text" for="parent_id">' . __('Product to Review', 'media-libraries') . '</label>' .
		$parents;
	}
	$image = ($parent) ? '<img src="' . get_post_meta($parent, 'ml_image', true) . '" />' : '';
	echo '<div id="ml_product-thumb">' . $image . '</div>';
	ml_review_stars($rating);

	// flag for official review
	if (current_user_can('edit_published_products', $post->ID)) {
		$official = get_post_meta($post->post_parent, 'ml_official_review');
		$official = is_array($official) ? $official : array();
		echo '<span id="ml_official-span"><input id="ml_official" name="ml_official" type="checkbox" value="official" ' . checked(in_array($post->ID, $official), true, false) . ' tabindex="4" /> <label for="ml_official" class="selectit">' . __('Mark as the official review.', 'media-libraries') . '</label><br /></span>';
	}
}

/**
 * callback to process posted metadata
 *
 * @param int post id
 */
function ml_review_meta_postback ($post_id) {
	$req = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : '';
	if (('ml_review' == $req) && current_user_can('edit_review', $post_id)) {
		$rating = (isset($_POST['ml_rating'])) ? floatval($_POST['ml_rating']) : null;
		update_post_meta($post_id, 'ml_rating', $rating);

		if (current_user_can('edit_published_products', $post_id)) {
			if (isset($_POST['ml_official'])) {
				$post = get_post($post_id);
				update_post_meta($post->post_parent, 'ml_official_review', $post_id);
			}
			else {
				$post = get_post($post_id);
				delete_post_meta($post->post_parent, 'ml_official_review', $post_id);
			}
		}
	}
}

/**
 * register additional columns for manage reviews page
 *
 * @param array columns
 * @return array columns (with additions)
 */
function ml_review_register_columns ($cols) {
	$cols['product'] = 'Product';
	$cols['rating'] = 'Rating';
	return $cols;
}

/**
 * display additional columns for manage reviews page
 *
 * @param string column name
 * @param int post id
 */
function ml_review_display_columns ($name, $post_id) {
	$post = get_post($post_id);
	switch ($name) {
		case 'product':
			$parent = isset($post->post_parent) ? $post->post_parent : 0;
			if ($parent) {
				$product = get_post($parent);
				$image = get_post_meta($parent, 'ml_image', true);
				$image = (empty($image)) ? '' : '<br /><img src="'.$image.'" class="image_preview" />';
				echo $product->post_title.'<div class="image">'.$image.'</div>';
			}
			break;
		case 'rating':
			$rating = get_post_meta($post_id, 'ml_rating', true);
			wp_enqueue_script('ml-metadata-script', plugins_url('/js/jquery.metadata.js', __FILE__));
			wp_enqueue_script('ml-rating-script', plugins_url('/js/jquery.rating.js', __FILE__));
			ml_review_stars($rating, true);

			$official = get_post_meta($post->post_parent, 'ml_official_review', true);
			if ($official) {
				echo '<br /><b>' . __('Official Review', 'media-libraries') . '</b>';
			}
			break;
	}
}

/**
 * display counts in the diashboard
 * @todo push html to template functions
 */
function ml_review_right_now() {
	$num_posts = wp_count_posts('ml_review');
	$num = number_format_i18n($num_posts->publish);
	$text = _n('Review', 'Reviews', intval($num_posts->publish), 'media-libraries');
	if (current_user_can('edit_reviews')) {
		$num = '<a href="/wp-admin/edit.php?post_type=ml_review">' . $num . '</a>';
		$text = '<a href="/wp-admin/edit.php?post_type=ml_review">' . $text . '</a>';
	}

	echo '<tr>';
	echo '<td class="first b b-tags">'.$num.'</td>';
	echo '<td class="t tags">' . $text . '</td>';
	echo '</tr>';
}

function ml_page_help() {
	$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
	if ('ml_review' == $post_type) {
		$screen = get_current_screen();
		add_contextual_help($screen, '<p>' .
			__('Pages are similar to Posts in that they have a title, body text, and associated metadata, but they are different in that they are not part of the chronological blog stream, kind of like permanent posts. Pages are not categorized or tagged, but can have a hierarchy. You can nest Pages under other Pages by making one the &#8220;Parent&#8221; of the other, creating a group of Pages.') . '</p>' .
			'<p>' . __('Creating a Page is very similar to creating a Post, and the screens can be customized in the same way using drag and drop, the Screen Options tab, and expanding/collapsing boxes as you choose. The Page editor mostly works the same Post editor, but there are some Page-specific features in the Page Attributes box:') . '</p>' .
			'<p>' . __('<strong>Parent</strong> - You can arrange your pages in hierarchies. For example, you could have an &#8220;About&#8221; page that has &#8220;Life Story&#8221; and &#8220;My Dog&#8221; pages under it. There are no limits to how many levels you can nest pages.') . '</p>' .
			'<p>' . __('<strong>Template</strong> - Some themes have custom templates you can use for certain pages that might have additional features or custom layouts. If so, you&#8217;ll see them in this dropdown menu.') . '</p>' .
			'<p>' . __('<strong>Order</strong> - Pages are usually ordered alphabetically, but you can choose your own order by entering a number (1 for first, etc.) in this field.') . '</p>' .
			'<p><strong>' . __('For more information:') . '</strong></p>' .
			'<p>' . __('<a href="http://codex.wordpress.org/Pages_Add_New_SubPanel" target="_blank">Documentation on Adding New Pages</a>') . '</p>' .
			'<p>' . __('<a href="http://codex.wordpress.org/Pages_Pages_SubPanel#Editing_Individual_Pages" target="_blank">Documentation on Editing Pages</a>') . '</p>' .
			'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
		);
	}
}

/**
 * initialise and register the actions for review post_type
 */
function ml_init_review() {
	require_once dirname(__FILE__) . '/review-template.php';

	ml_review_type();

	if (ml_get_option('use_tags')) {
		ml_tag_tax();
	}
	if (ml_get_option('use_categories')) {
		register_taxonomy_for_object_type('category', 'ml_review');
	}

	wp_enqueue_style('ml-rating-style', plugins_url('/css/jquery.rating.css', __FILE__));

	add_action('manage_ml_review_posts_custom_column', 'ml_review_display_columns', 10, 2);
	add_action('manage_edit-ml_review_columns', 'ml_review_register_columns');
	add_action('right_now_content_table_end', 'ml_review_right_now');
	add_action('save_post', 'ml_review_meta_postback');
// 	add_action('admin_head-edit.php', 'ml_page_help');
}

ml_init_review();
