<?php
/**
 * product reviews and related taxonomies
 * @package amazon-library
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * review post_type
 */
function aml_review_type() {
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
		'description' => __('A single review/use of a product (e.g. reading a book, watching a DVD, listening to music, etc)'),
		'rewrite' => array('slug' => "$slug_base/$slug_review", 'pages' => false, 'feeds' => false, 'with_front' => false),
		'supports' => array('title', 'author', 'editor', 'revisions'),
		'show_in_menu' => 'edit.php?post_type=aml_product',
		'register_meta_box_cb' => 'aml_review_boxes',
		'capability_type' => 'review',
		'map_meta_cap' => true,
		'hierarchical' => false,
		'query_var' => true,
		'labels' => $labels,
		'show_ui' => true,
		'public' => true,
	);
	register_post_type('aml_review', $args);
	add_filter('archive_template', 'aml_review_archive_template');
	add_filter('single_template', 'aml_review_single_template');
}

/**
 * review stati
 *
 * @todo restrict these stati to aml_review type
 */
function aml_review_stati() {
	$stati = aml_get_review_stati();
	foreach ($stati as $name => $args) {
		register_post_status( $name, array(
			'label'			=> _x($args['label'], 'post', 'amazon-library'),
			'label_count'	=> _n_noop($args['single'] . ' <span class="count">(%s)</span>', $args['plural'] . ' <span class="count">(%s)</span>' ),
			'public'		=> true,
		) );
	}
}

function aml_get_review_stati() {
	return array(
	'added'		=> array('label' => 'Yet to use', 'single' => 'Added', 'plural' => 'Added'),
	'onhold'	=> array('label' => 'On Hold', 'single' => 'Held', 'plural' => 'Held'),
	'using'		=> array('label' => 'Currently using', 'single' => 'Using', 'plural' => 'Using'),
	'finished'	=> array('label' => 'Finished', 'single' => 'Finished', 'plural' => 'Finished'),
	);
}

/**
 * generic taxonomy for reviews (all of this just to rename 'post tags' to simply 'tags'!)
 */
function aml_tag_tax() {
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
 * callback from registering aml_review to generate meta boxes on an edit page
 */
function aml_review_boxes() {
	remove_meta_box('submitdiv', 'aml_review', 'side');
	add_meta_box('aml_review_submit', __('Publish'), 'aml_submit_box', 'aml_review', 'side', 'high');
	add_meta_box('aml_review_meta', __('Product', 'amazon-library'), 'aml_review_meta', 'aml_review', 'side', 'high');
}

/**
 * Hacked post submit form (falls to WP default if not a review)
 *
 * @param object $post
 */
function aml_submit_box ($post) {
	$post_type_object = get_post_type_object($post->post_type);
	$can_publish = current_user_can($post_type_object->cap->publish_posts);
	$added = get_post_meta($post->ID, 'aml_added', true);
	$started = get_post_meta($post->ID, 'aml_started', true);
	$finish = get_post_meta($post->ID, 'aml_finished', true);
	$stati = aml_get_review_stati();
	$stati_names = array_keys($stati);
	$post->post_status = (in_array($post->post_status, $stati_names)) ? $post->post_status : $stati_names[0];
?>
<div class="submitbox" id="submitpost">
	<div id="minor-publishing">
		<div id="misc-publishing-actions">
		<?php
			aml_status_box($post, $can_publish);
			aml_show_date($added, 'added', $can_publish);
			aml_show_date($started, 'started', $can_publish);
			aml_show_date($finish, 'finished', $can_publish);
			do_action('post_submitbox_misc_actions');
		?>
		<div id="timestampdiv" class="hide-if-js"><?php touch_time(); ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<?php aml_pubdel_box($post); ?>
	</div>
</div>
<?php
}

/**
 * meta-box for review details/meta
 *
 * @param object WP_post
 * @todo push html to template file
 */
function aml_review_meta ($post) {
	$rating = get_post_meta($post->ID, 'aml_rating', true);
	// todo: need to add post_status!!

	$post_type_object = get_post_type_object($post->post_type);
	$can_publish = current_user_can($post_type_object->cap->publish_posts);
	$parent = isset($post->post_parent) ? $post->post_parent : 0;

	$args = array('post_type' => 'aml_product', 'depth' => 1, 'echo' => 0, 'selected' => $parent, 'name' => 'parent_id',  'sort_column'=> 'menu_order,post_title');
	$parents = wp_dropdown_pages($args);
	if ( !empty($parents) ) {
		echo '<p><strong>' . __('Parent') . '</strong></p>' .
		'<label class="screen-reader-text" for="parent_id">' . __('Review', 'amazon-library') . '</label>' .
		$parents;
	}
	$image = ($parent) ? '<img src="' . get_post_meta($parent, 'aml_image', true) . '" />' : '';
	echo '<div id="aml_product-thumb">' . $image . '</div>';
	review_stars();
}

/**
 * callback to process posted metadata
 *
 * @param int post id
 */
function aml_review_meta_postback ($post_id) {
	$req = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : '';
	if (('aml_review' == $req) && current_user_can('edit_review', $post_id)) {
		$rating = (isset($_POST['aml_rating'])) ? floatval($_POST['aml_rating']) : null;
		aml_update_meta('aml_rating', $post_id, $rating);


		$times = array('added', 'started', 'finished');
		foreach ($times as $time) {
			$jj = (isset($_POST['jj-'.$time])) ? intval($_POST['jj-'.$time]) : 0;
			$mm = (isset($_POST['mm-'.$time])) ? intval($_POST['mm-'.$time]) : 0;
			$aa = (isset($_POST['aa-'.$time])) ? intval($_POST['aa-'.$time]) : 0;
			$hh = (isset($_POST['hh-'.$time])) ? intval($_POST['hh-'.$time]) : 0;
			$mn = (isset($_POST['mn-'.$time])) ? intval($_POST['mn-'.$time]) : 0;
			$ss = (isset($_POST['ss-'.$time])) ? intval($_POST['ss-'.$time]) : 0;
			$jj = ($jj > 31) ? 31 : $jj;
			$jj = ($jj <= 0) ? date('j') : $jj;
			$mm = ($mm <= 0) ? date('n') : $mm;
			$aa = ($aa <= 0) ? date('Y') : $aa;
			$hh = ($hh > 23) ? $hh-24 : $hh;
			$mn = ($mn > 59) ? $mn-60 : $mn;
			$ss = ($ss > 59) ? $ss-60 : $ss;
			$set = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $aa, $mm, $jj, $hh, $mn, $ss);
			$gmt = get_gmt_from_date($set);
			aml_update_meta('aml_'.$time, $post_id, $set);
			aml_update_meta('aml_'.$time.'_gmt', $post_id, $gmt);
		}
	}
}

/**
 * register additional columns for manage reviews page
 *
 * @param array columns
 * @return array columns (with additions)
 */
function aml_review_register_columns ($cols) {
	$cols['product'] = 'Product';
	$cols['status'] = 'Status';
	$cols['connect'] = 'Connections';
	unset($cols['date']);
	return $cols;
}

/**
 * display additional columns for manage reviews page
 *
 * @param string column name
 * @param int post id
 */
function aml_review_display_columns ($name, $post_id) {
	$post = get_post($post_id);
	switch ($name) {
		case 'product':
			$parent = isset($post->post_parent) ? $post->post_parent : 0;
			if ($parent) {
				$product = get_post($parent);
				$image = get_post_meta($parent, 'aml_image', true);
				$image = (empty($image)) ? '' : '<br /><img src="'.$image.'" class="image_preview" />';
				echo $product->post_title.'<div class="image">'.$image.'</div>';
			}
			break;
		case 'status':
			$times = array(
				'added' => 'Added to Shelf',
				'started' => 'Began Review',
				'finished' => 'Review Finished',
			);
			$stati = aml_get_review_stati();
			echo (isset($stati[$post->post_status])) ? $stati[$post->post_status]['label'] : '';
			foreach ($times as $label => $string) {
				$time = get_post_meta($post_id, 'aml_'.$label, true);
				$datef = __('M j, Y @ G:i');
				$stamp = __('<b>%1$s</b>');
				$date = date_i18n($datef, strtotime($time));
				echo '<br />' . __($string, 'amazon-library') . ': <span id="timestamp-' . $label . '">' . sprintf($stamp, $date) . '</span>';
			}
			break;
		case 'connect':
			break;
	}
}

/**
 * display counts in the diashboard
 * @todo push html to template functions
 */
function aml_review_right_now() {
	$num_posts = wp_count_posts('aml_review');
	$num = number_format_i18n($num_posts->publish);
	$text = _n('Review', 'Reviews', intval($num_posts->publish), 'amazon-library');
	if (current_user_can('edit_reviews')) {
		$num = '<a href="/wp-admin/edit.php?post_type=aml_review">' . $num . '</a>';
		$text = '<a href="/wp-admin/edit.php?post_type=aml_review">' . $text . '</a>';
	}

	echo '<tr>';
	echo '<td class="first b b-tags">'.$num.'</td>';
	echo '<td class="t tags">' . $text . '</td>';
	echo '</tr>';
}

function aml_page_help() {
	$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
	if ('aml_review' == $post_type) {
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
function aml_init_review() {
	aml_review_type();
	aml_review_stati();
	if (aml_get_option('use_tags')) {
		aml_tag_tax();
	}
	if (aml_get_option('use_categories')) {
		register_taxonomy_for_object_type('category', 'aml_review');
	}
	add_action('manage_aml_review_posts_custom_column', 'aml_review_display_columns', 10, 2);
	add_action('manage_edit-aml_review_columns', 'aml_review_register_columns');
	add_action('right_now_content_table_end', 'aml_review_right_now');
	add_action('save_post', 'aml_review_meta_postback');
	add_action('admin_head-edit.php', 'aml_page_help');
	wp_enqueue_script('aml-review-script', plugins_url('/amazon-media-libraries/js/amazon.review.js'));
	wp_enqueue_script('aml-metadata-script', plugins_url('/amazon-media-libraries/js/jquery.metadata.js'));
	wp_enqueue_script('aml-rating-script', plugins_url('/amazon-media-libraries/js/jquery.rating.js'));
	wp_enqueue_style('aml-rating-style', plugins_url('/amazon-media-libraries/css/jquery.rating.css'));
}
