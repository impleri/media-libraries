<?php
/**
 * Adds multi-user options to admin areas.
 * @package amazon-library
 * $Rev$
 * $Date$
 */

 /**
 * Creates the settings fields for the options page
 */
function aml_multi_init() {
	add_settings_field('aml_slug_user', __('User base', 'amazon-library'), 'aml_slug_user_field', 'aml_options', 'aml_options_display');
	nrm_extra_rewrite();
}
add_action('admin_init', 'aml_multi_init');

function aml_slug_user_field() {
	$options = get_option('aml_options');
?>
<input type="text" size="50" name="aml_options[aml_slug_user]" value="<?php htmlentities($options['aml_slug_user']); ?>" />
<p><?php _e('Tag prepended for user URLs. Default is user.', 'amazon-library'); ?></p>
<p><?php _e('NB: Only Alpha-numerics and dashes are allowed.', 'amazon-library'); ?></p>
<?php }