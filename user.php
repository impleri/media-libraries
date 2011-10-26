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

 /**
 * Creates the settings fields for the options page
 */
function ml_multi_init() {
	add_settings_field('ml_slug_user', __('User base', 'media-libraries'), 'ml_slug_user_field', 'ml_options', 'ml_options_display');
	nrm_extra_rewrite();
}
add_action('admin_init', 'ml_multi_init');

function ml_slug_user_field() {
	$options = get_option('ml_options');
?>
<input type="text" size="50" name="ml_options[ml_slug_user]" value="<?php htmlentities($options['ml_slug_user']); ?>" />
<p><?php _e('Tag prepended for user URLs. Default is user.', 'media-libraries'); ?></p>
<p><?php _e('NB: Only Alpha-numerics and dashes are allowed.', 'media-libraries'); ?></p>
<?php }

// More WP rewrites (not covered in taxonomies above)
function nrm_extra_rewrite() {
	global $wp_rewrite;
	$options = get_option('ml_options', ml_default_options());
	$slug_base = (empty($options['ml_slug_base'])) ? 'library' : $options['ml_slug_base'];
	$slug_user = (empty($options['ml_slug_user'])) ? 'user' : $options['ml_slug_user'];

	$wp_rewrite->add_rule("$wp_rewrite->root/$slug_user/([^/]+)/", $wp_rewrite->index.'?ml_user=$matches[1]');
}