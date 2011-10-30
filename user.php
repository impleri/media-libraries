<?php
/**
 * frontend and multi-user functionality
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * @todo
 * NO BACKEND
 * FE template: shelves with unique products (sorted by status of latest usage), reviews
 */

// More WP rewrites (not covered in taxonomies above)
function ml_user_rewrite() {
	global $wp_rewrite;
	$options = get_option('ml_options', ml_default_options());
	$slug_base = (empty($options['ml_slug_base'])) ? 'library' : $options['ml_slug_base'];
	$slug_user = (empty($options['ml_slug_user'])) ? 'user' : $options['ml_slug_user'];

	$wp_rewrite->add_rule("$wp_rewrite->root/$slug_user/([^/]+)/", $wp_rewrite->index.'?ml_user=$matches[1]');
}
add_action('admin_init', 'ml_user_rewrite');

function get_user_library_url ($user_id) {
	$user = get_userdata($user_id);
	return $user->display_name;
}