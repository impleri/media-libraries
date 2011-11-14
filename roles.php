<?php
/**
 * roles and capabilities
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * add auths to regular WP roles
 *
 * @todo make own roles and leave WP's alone
 */
function ml_init_roles() {
	$admin = get_role('administrator');
	$admin->add_cap('read_products');
	$admin->add_cap('edit_products');
	$admin->add_cap('edit_published_products');
	$admin->add_cap('publish_products');
	$admin->add_cap('delete_products');
	$admin->add_cap('delete_published_products');

	$admin->add_cap('read_reviews');
	$admin->add_cap('edit_reviews');
	$admin->add_cap('edit_published_reviews');
	$admin->add_cap('publish_reviews');
	$admin->add_cap('delete_reviews');
	$admin->add_cap('delete_published_reviews');

	$admin->add_cap('read_shelves');
	$admin->add_cap('edit_shelves');
	$admin->add_cap('edit_published_shelves');
	$admin->add_cap('publish_shelves');
	$admin->add_cap('delete_shelves');
	$admin->add_cap('delete_published_shelves');

	$admin->add_cap('read_usages');
	$admin->add_cap('edit_usages');
	$admin->add_cap('edit_published_usages');
	$admin->add_cap('publish_usages');
	$admin->add_cap('delete_usages');
	$admin->add_cap('delete_published_usages');

	$editor = get_role('editor');
	$editor->add_cap('read_products');
	$editor->add_cap('delete_products');
	$editor->add_cap('publish_products');
	$editor->add_cap('edit_products');
	$editor->add_cap('edit_published_products');

	$editor->add_cap('read_reviews');
	$editor->add_cap('edit_reviews');
	$editor->add_cap('edit_published_reviews');
	$editor->add_cap('publish_reviews');
	$editor->add_cap('delete_reviews');

	$editor->add_cap('read_shelves');
	$editor->add_cap('edit_shelves');
	$editor->add_cap('edit_published_shelves');
	$editor->add_cap('publish_shelves');
	$editor->add_cap('delete_shelves');

	$editor->add_cap('read_usages');
	$editor->add_cap('edit_usages');
	$editor->add_cap('edit_published_usages');
	$editor->add_cap('publish_usages');
	$editor->add_cap('delete_usages');

	$author = get_role('author');
	$author->add_cap('read_products');
	$author->add_cap('publish_products');
	$author->add_cap('edit_products');

	$author->add_cap('read_reviews');
	$author->add_cap('edit_reviews');
	$author->add_cap('publish_reviews');

	$author->add_cap('read_shelves');
	$author->add_cap('edit_shelves');
	$author->add_cap('publish_shelves');

	$author->add_cap('read_usages');
	$author->add_cap('edit_usages');
	$author->add_cap('publish_usages');

	$contrib = get_role('contributor');
	$contrib->add_cap('read_products');
	$contrib->add_cap('edit_products');

	$contrib->add_cap('read_reviews');
	$contrib->add_cap('edit_reviews');

	$contrib->add_cap('read_shelves');
	$contrib->add_cap('edit_shelves');

	$contrib->add_cap('read_usages');
	$contrib->add_cap('edit_usages');

	$sub = get_role('subscriber');
	$sub->add_cap('read_products');
	$sub->add_cap('read_reviews');
	$sub->add_cap('read_shelves');
	$sub->add_cap('read_usages');

	add_filter('map_meta_cap', 'ml_meta_cap', 10, 4);
}

/**
 * Map meta capabilities to primitive capabilities
 *
 * @param array capabilities to check
 * @param string capability
 * @param int user id
 * @param array $args all arguments
 * @return array capabilities to check (modified)
 * @todo make this work
 */
function ml_meta_cap ($caps, $cap, $user_id, $args) {
	// only check capabilities we deal with
	$arr = array('edit_product', 'delete_product', 'read_product', 'edit_review', 'delete_review', 'read_review', 'edit_shelf', 'delete_shelf', 'read_shelf', 'edit_usage', 'delete_usage', 'read_usage');
	if (!in_array($cap, $arr)) {
		return $caps;
	}
	$post = get_post($args[0]);
	$post_type = ($post) ? get_post_type_object($post->post_type) : $_REQUEST['post_type'];

	switch ($cap) {
		// products
		case 'edit_product':
		case 'edit_review':
		case 'edit_shelf':
		case 'edit_usage':
			$caps[] = ('published' == $post->post_status) ? $post_type->cap->edit_published_posts : $post_type->cap->edit_posts;
			break;
		case 'delete_product':
		case 'delete_review':
		case 'delete_shelf':
		case 'delete_usage':
			$caps[] = $post_type->cap->delete_posts;
			break;
		case 'read_product':
		case 'read_review':
		case 'read_shelf':
		case 'read_usage':
			$caps[] = 'read';
			break;
	}
	return $caps;
}

ml_init_roles();
