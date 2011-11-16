<?php
/**
 * product custom_type and person taxonomy
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * @todo
 * Posting boxes: clean up search & results boxes
 * BE quick edit box: link to image, link to product source
 * FE template: metaDetails, link to add/edit review, link to add reading (or edit if one is onhold or in process)
 * FE widgets: random product, most popular products (by number of reviews or usages)
 */

/**
 * custom product post_type
 */
function ml_product_type() {
	$slug_base = ml_get_option('ml_slug_base');
	$slug_product = ml_get_option('ml_slug_product');

	$labels = array(
		'name' => _x('Products', 'post type general name', 'media-libraries'),
		'singular_name' =>  _x('Product', 'post type singular name', 'media-libraries'),
		'add_new_item' => __('Add New Product', 'media-libraries'),
		'edit_item' => __('Edit Product', 'media-libraries'),
		'new_item' => __('New Product', 'media-libraries'),
		'view_item' => __('View Product', 'media-libraries'),
		'search_items' => __('Search Products', 'media-libraries'),
		'not_found' => __('No products found', 'media-libraries'),
		'not_found_in_trash' => __('No products found in Trash', 'media-libraries'),
	);

	$args = array(
		'description' => __('Product information and picture. Reviews are made by individual users and show in the product page.'),
		'rewrite' => array('slug' => "$slug_base/$slug_product", 'pages' => false, 'feeds' => false, 'with_front' => false),
		'register_meta_box_cb' => 'ml_product_boxes',
		'capability_type' => 'product',
		'supports' => array('title'),
		'has_archive' => $slug_base,
		'map_meta_cap' => true,
		'hierarchical' => true,
		'show_in_menu' => 'edit.php?post_type=ml_product',
		'menu_position' => 10,
		'labels' => $labels,
		'query_var' => true,
		'public' => true,
	);
	register_post_type('ml_product', $args);
	add_filter('archive_template', 'ml_product_archive_template');
	add_filter('single_template', 'ml_product_single_template');
}

/**
 * people taxonomy for products
 */
function ml_people_tax() {
	$slug_base = ml_get_option('ml_slug_base');
	$slug_person = ml_get_option('ml_slug_person');

	$labels = array(
		'name' => _x('People', 'taxonomy general name', 'media-libraries'),
		'singular_name' => _x('Person', 'taxonomy singular name', 'media-libraries'),
		'search_items' =>  __('Search People', 'media-libraries'),
		'popular_items' =>  __('Popular People', 'media-libraries'),
		'all_items' => __('All People', 'media-libraries'),
		'edit_item' => __('Edit Person', 'media-libraries'),
		'update_item' => __('Update Person', 'media-libraries'),
		'add_new_item' => __('Add New Person', 'media-libraries'),
		'new_item_name' => __('New Person', 'media-libraries'),
		'add_or_remove_items' => __('Add or remove people'),
		'choose_from_most_used' => __('Choose from the most used people'),
		'separate_items_with_commas' => __('Separate people\'s names with commas', 'media-libraries'),
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
		'query_var' => 'ml_person',
		'hierarchical' => false,
		'labels' => $labels,
	);
	register_taxonomy('ml_person','ml_product', $args);
	add_filter('taxonomy_template', 'ml_person_taxonomy_template');
}

/**
 * callback from registering ml_product to generate meta boxes on an edit page
 */
function ml_product_boxes() {
	 add_meta_box('ml_product_search', __('Search Online', 'media-libraries'), 'ml_product_mb_search', 'ml_product', 'normal', 'high');
	 add_meta_box('ml_product_meta', __('Product Metadata', 'media-libraries'), 'ml_product_mb_meta', 'ml_product', 'side', 'high');
	 wp_enqueue_script( 'ml-product-script', plugins_url('/js/media.product.js', __FILE__) );
	 wp_enqueue_style( 'ml-product-style', plugins_url('/css/media.product.css', __FILE__) );
}

/**
 * meta-box for online search
 * @todo push html to template functions
 */
function ml_product_mb_search() {
	$ml_categories = ml_product_categories();
	echo '<div class="ml_search_box">' . "\n";
	echo '<select id="ml_search_type" name="ml_search_type">' . "\n";
	foreach ($ml_categories as $id => $cat) {
		echo '<option value="' . $id . '">' . $cat . '</option>' . "\n";
	}
	echo '</select>' . "\n";
	echo '<div id="ml_search_stringwrap">' . "\n";
	echo '<label class="hide-if-no-js" style="" id="ml_search_string-prompt-text" for="ml_search_string">' . __('Search for...', 'media-libraries') . '</label>' . "\n";
	echo '<input type="text" size="50" id="ml_search_string" name="ml_search_string" value="" autocomplete="off" />' . "\n";
	echo '</div>' . "\n";
	echo '<div id="ml_search_button" class="button-primary">' . __('Search', 'media-libraries') . '</div>' . "\n";
	echo '<div id="ml_search_reset" class="button-primary">' . __('Reset Search', 'media-libraries') . '</div>' . "\n";
	echo '<br style="clear:both" />' . "\n";
	echo '</div>' . "\n";
}

/**
 * meta-box for additional meta-data (asin, link, image)
 * @todo push html to template functions
 */
function ml_product_mb_meta() {
	global $post;
	$type = get_post_meta($post->ID, 'ml_type', true);
	$asin = get_post_meta($post->ID, 'ml_asin', true);
	$link = get_post_meta($post->ID, 'ml_link', true);
	$image = get_post_meta($post->ID, 'ml_image', true);
	$image_preview = (empty($image)) ? '' : '<img src="' . $image . '" alt="preview" />';
	$ml_categories = ml_product_categories();

	echo '<div id="ml_image_preview">' . $image_preview . '</div>' . "\n";
	echo '<div id="ml_imagewrap">' . "\n";
	echo '<label id="ml_image-prompt-text" for="ml_image">' . __('Link to image', 'media-libraries') . '</label>' . "\n";
	echo '<input type="text" size="50" id="ml_image" name="ml_image" value="' . $image . '" autocomplete="off" />' . "\n";
	echo '</div>' . "\n";

	echo '<label for="ml_type">' . __('Product type', 'media-libraries') . '</label>' . "\n";
	echo '<select id="ml_type" name="ml_type">' . "\n";
	foreach ($ml_categories as $cat => $name) {
		echo '<option value="' . $cat . '"' . selected($cat, $type, false) . '>' . __($name, 'media-libraries') . '</option>' . "\n";
	}
	echo '</select>' . "\n";

	echo '<div id="ml_asinwrap">' . "\n";
	echo '<label id="ml_asin-prompt-text" for="ml_asin">' . __('ASIN Number', 'media-libraries') . '</label>' . "\n";
	echo '<input type="text" size="50" id="ml_asin" name="ml_asin" value="' . $asin . '" autocomplete="off" />' . "\n";
	echo '</div>' . "\n";

	echo '<div id="ml_linkwrap">' . "\n";
	echo '<label id="ml_link-prompt-text" for="ml_link">' . __('Product link', 'media-libraries') . '</label>' . "\n";
	echo '<input type="text" size="50" id="ml_link" name="ml_link" value="' . $link . '" autocomplete="off" />' . "\n";
	echo '</div>' . "\n";
}

/**
 * callback to process posted metadata
 *
 * @param int post id
 */
function ml_product_postback ($post_id) {
	$req = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : '';
	if ( ('ml_product' != $req) || !current_user_can( 'edit_product', $post_id ) ) {
		return $post_id;
	}

	$image = (isset($_REQUEST['ml_image'])) ? $_REQUEST['ml_image'] : null;
	$asin = (isset($_REQUEST['ml_asin'])) ? $_REQUEST['ml_asin'] : null;
	$type = (isset($_REQUEST['ml_type'])) ? $_REQUEST['ml_type'] : null;
	$link = (isset($_REQUEST['ml_link'])) ? $_REQUEST['ml_link'] : null;

	update_post_meta($post_id, 'ml_asin', $asin);
	update_post_meta($post_id, 'ml_type', $type);
	update_post_meta($post_id, 'ml_link', $link);
	update_post_meta($post_id, 'ml_image', $image);
}

/**
 * register additional columns for manage products page
 *
 * @param array columns
 * @return array columns (with additions)
 */
function ml_product_register_columns ($cols) {
	$cols['type'] = 'Category';
	$cols['image'] = 'Image';
	$cols['people'] = 'People';
	$cols['reviews'] = 'Reviews';
	$cols['usage'] = 'Usage';
	return $cols;
}

/**
 * display additional columns for manage products page
 *
 * @param string column name
 * @param int post id
 * @todo implement review, usage
 */
function ml_product_display_columns ($name, $post_id) {
	$post = get_post($post_id);

	switch ($name) {
		case 'type':
			$types = ml_product_categories();
			$type = get_post_meta($post_id, 'ml_type', true);
			if (!empty($types[$type])) {
				_e($types[$type], 'media-libraries');
			}
			break;

		case 'image':
			$link = get_post_meta($post_id, 'ml_link', true);
			$image = get_post_meta($post_id, 'ml_image', true);
			$asin = get_post_meta($post_id, 'ml_asin', true);
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
			$terms = get_the_term_list($post_id, 'ml_person', '', ', ');
			echo $terms;
			break;

		case 'reviews':
			$args = array(
				'post_type' => 'ml_review',
				'post_parent' => $post_id,
				'numberposts' => -1
			);
			$reviews = get_posts($args);
			$count = count($reviews);
			echo sprintf(__('%s Reviews', 'media-libraries'), $count);

			if ($count > 0) {
				$args['numberposts'] = 1;
				$o_args = $args;
				$o_args['meta_key'] = 'ml_official_review';
				$o_args['meta_value'] = true;
				$official = get_posts($o_args);
				if (count($official) == 1) {
					$official = $official[0];
					echo "\n" . '<a href="' . get_post_permalink($official->ID) . '">' . __('Link to official review', 'media-libraries') . '</a>';
				}

				$user = wp_get_current_user();
				$args['author'] = $user->ID;
				$own = get_posts($args);
				if (count($own) == 1) {
					$own = $own[0];
					echo "\n" . '<a href="' . get_edit_post_link($own->ID) . '">' . __('Go to your review', 'media-libraries') . '</a>';
				}
				else {
					echo "\n" . '<a href="' . admin_url('post-new.php?post_type=ml_review&post_parent=' . $post_id) . '">' . __('Write your own review', 'media-libraries') . '</a>';
				}
			}
			break;

		case 'usage':
			$args = array(
				'post_type' => 'ml_usage',
				'post_parent' => $post_id,
				'numberposts' => -1
			);
			$uses = get_posts($args);
			$count = count($uses);
			echo sprintf(__('%s Uses', 'media-libraries'), $count);

			if ($count > 0) {
				$user = wp_get_current_user();
				$args['author'] = $user->ID;
				$own = get_posts($args);
				$count = count($own);
				echo "\n" . sprintf(__('%s Personal Uses', 'media-libraries'), $count);
				echo "\n" . '<a href="' . admin_url('post-new.php?post_type=ml_usage&post_parent=' . $post_id) . '">' . __('Add a usage', 'media-libraries') . '</a>';
			}
			break;
	}
}

/**
 * display counts in the diashboard
 * @todo push html to template functions
 */
function ml_product_right_now() {
	$num_posts = wp_count_posts('ml_product');
	$num = number_format_i18n($num_posts->publish);
	$text = _n('Product', 'Products', intval($num_posts->publish), 'media-libraries');
	if (current_user_can('edit_products')) {
		$num = '<a href="/wp-admin/edit.php?post_type=ml_product">' . $num . '</a>';
		$text = '<a href="/wp-admin/edit.php?post_type=ml_product">' . $text . '</a>';
	}

	echo '<tr>';
	echo '<td class="first b b-tags">'.$num.'</td>';
	echo '<td class="t tags">' . $text . '</td>';
	echo '</tr>';
}

/**
 * initialise and register the actions for product post_type
 */
function ml_init_product() {
	require_once dirname(__FILE__) . '/product-template.php';

	ml_product_type();
	ml_people_tax();

	add_action('manage_ml_product_posts_custom_column', 'ml_product_display_columns', 10, 2);
	add_action('manage_edit-ml_product_columns', 'ml_product_register_columns');
	add_action('right_now_content_table_end', 'ml_product_right_now');
	add_action('save_post', 'ml_product_postback');
}

ml_init_product();
