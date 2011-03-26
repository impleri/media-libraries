<?php
/**
 * Various meta-data boxes and callbacks
 * @package amazon-library
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * Add auths to regular WP roles
 */
function aml_capabilities() {
	$admin = get_role('administrator');
	$admin->add_cap('read_products');
	$admin->add_cap('edit_products');
	$admin->add_cap('edit_published_products');
	$admin->add_cap('publish_products');
	$admin->add_cap('delete_products');
	$admin->add_cap('delete_published_products');

	$editor = get_role('editor');
	$editor->add_cap('read_products');
	$editor->add_cap('delete_products');
	$editor->add_cap('publish_products');
	$editor->add_cap('edit_products');
	$editor->add_cap('edit_published_products');

	$author = get_role('author');
	$author->add_cap('read_products');
	$author->add_cap('publish_products');
	$author->add_cap('edit_products');

	$contrib = get_role('contributor');
	$contrib->add_cap('read_products');
	$contrib->add_cap('edit_products');

	$sub = get_role('subscriber');
	$sub->add_cap('read_products');
}

/**
 * Map meta capabilities to primitive capabilities
 *
 * @param array Capabilities to check
 * @param string Capability
 * @param int User ID
 * @param array $args All arguments
 * @return array Capabilities to check (modified)
 */
function aml_meta_cap ($caps, $cap, $user_id, $args) {
	// only check capabilities we deal with
	$arr = array('edit_product', 'delete_product', 'read_product');
	if (in_array($cap, $arr)) {
		$caps = array();
	}
	$post = get_post($args[0]);
	$post_type = get_post_type_object($post->post_type);

	switch ($cap) {
		// products
		case 'edit_product':
			$caps[] = ('published' == $post->post_status) ? $post_type->cap->edit_published_posts : $post_type->cap->edit_posts;
			break;
		case 'delete_product':
			$caps[] = $post_type->cap->delete_posts;
			break;
		case 'read_product':
			$caps[] = 'read';
			break;

		case 'edit_shelf':
			$caps[] = ($user_id == $post->post_author) ? $post_type->cap->edit_posts : $post_type->cap->edit_others_posts;
			break;
		case 'delete_shelf':
			$caps[] = ($user_id == $post->post_author) ? $post_type->cap->delete_posts : $post_type->cap->delete_others_posts;
			break;
		case 'read_shelf':
			if ('private' != $post->post_status || $user_id == $post->post_author) {
				$caps[] = 'read';
			}
			else {
				$caps[] = $post_type->cap->read_private_posts;
			}
			break;
	}
	return $caps;
}
