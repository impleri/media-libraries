<?php
/**
 * admin menus and some plugin options
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * default options
 *
 * @todo verify options, add page mapping
 */
function ml_default_options() {
	return apply_filters('ml-default-options', array(
		'ml_per_page' => 20,
		'ml_use_tags' => 1,
		'ml_use_categories' => 0,
		'ml_use_shelves' => 1,
		'ml_slug_base' => 'library',
		'ml_slug_product' => 'product',
		'ml_slug_person' => 'person',
		'ml_slug_tag' => 'tag',
		'ml_slug_review' => 'review',
		'ml_slug_shelf' => 'shelf',
		'ml_slug_user' => 'user',
		'ml_version' => ML_VERSION,
	));
}

/**
 * shortcut for getting option from the ml_options array
 *
 * @param string name of option
 * @param mixed default value override (if null, will give from ml_default_options)
 * @return mixed option value
 */
function ml_get_option ($key='', $def=null) {
	static $options;
	static $defaults;

	if (!is_array($defaults)) {
		$defaults = ml_default_options();
	}

	if (!is_array($options)) {
		$options = get_option('ml_options', $defaults);
	}

	if (false === strpos($key, 'ml_')) {
		$key = 'ml_' . $key;
	}

	$def = (is_null($def) && isset($defaults[$key])) ? $defaults[$key] : $def;

	return (isset($options[$key])) ? $options[$key] : $def;
}

/**
 * shortcut for setting option
 *
 * @param string name of option
 * @param mixed option value (if null, will use default)
 * @return boolean true on success
 */
function ml_set_option($key='', $val=null) {
	static $options;
	static $defaults;

	if (!is_array($defaults)) {
		$defaults = ml_default_options();
	}

	if (!is_array($options)) {
		$options = get_option('ml_options', $defaults);
	}

	if (!empty($key)) {
		if (false === strpos($key, 'ml_')) {
			$key = 'ml_' . $key;
		}
		$val = (is_null($val) && isset($defaults[$key])) ? $defaults[$key] : $val;

		$options[$key] = $val;
	}

	$options = array_merge($defaults, $options);
	update_option('ml_options', $options);
}

/**
 * options page display
 */
function ml_options_page() {
	echo '<div class="wrap"><form method="post" action="options.php">';
	echo '<h2>' . __('Media Libraries', 'media-libraries') . '</h2>';
	settings_fields('media_libraries');
	do_settings_sections('ml_options');
	echo '<p class="submit"><input type="submit" class="button-primary" value="' . __('Save Changes') . '" /></p>';
	echo '</form></div>';
}

/**
 * validates posted options
 *
 * @param array $_POST data passed from WP
 * @return array validated options
 */
function ml_options_validate ($ml_post) {
	$options = get_option('ml_options');
	$defaults = ml_default_options();
	$valid = array();
	$valid = apply_filters('ml-options-validate', $valid, $ml_post);
	//TODO: more validation!

	// Display fields
	$valid['ml_per_page'] = ($ml_post['ml_per_page']) ? intval($ml_post['ml_per_page']) : null;
	$valid['ml_slug_base'] = ($ml_post['ml_slug_base']) ? sanitize_text_field($ml_post['ml_slug_base']) : null;
	$valid['ml_slug_product'] = ($ml_post['ml_slug_product']) ? sanitize_text_field($ml_post['ml_slug_product']) : null;
	$valid['ml_slug_person'] = ($ml_post['ml_slug_person']) ? sanitize_text_field($ml_post['ml_slug_person']) : null;
	$valid['ml_slug_tag'] = ($ml_post['ml_slug_tag']) ? sanitize_text_field($ml_post['ml_slug_tag']) : null;
	$valid['ml_slug_user'] = ($ml_post['ml_slug_user']) ? sanitize_text_field($ml_post['ml_slug_user']) : null;

	// merge (defaults, current, and new values) into one array
	$valid = array_merge($defaults, $options, $valid);
	return $valid;
}

/**
 * template for a text field display
 *
 * @param string key
 * @param string value
 * @param string description
 * @param int size of input field (default is 50)
 */
function ml_text_field ($key, $text='', $size=50) {
	$value = ml_get_option($key);
	echo '<input type="text" size="' . $size . '" id="' . $key . '" name="ml_options[' . $key. ']" value="' . $value . '" />';
	if (!empty($text)) {
		echo '<p>' . $text . '</p>';
	}
}

/**
 * template for slug field display
 *
 * @param string key
 * @param string description
 */
function ml_slug_field ($option, $text) {
	ml_text_field($option, __($text, 'media-libraries'));
	echo '<p>' . __('NB: Only Alpha-numerics and dashes are allowed.', 'media-libraries') . '</p>';
}

/**
 * template for a select field display
 *
 * @param string key
 * @param string description
 * @param int size of input field (default is 50)
 */
function ml_select_field ($key, $options, $text='') {
	$value = ml_get_option($key);
	echo '<select id="' . $key . '" name="ml_options[' . $key . ']">';
	foreach ($options as $val => $name) {
		echo '<option value="' . $val . '"' . selected($value, $val, false) . '>' . __($name, 'media-libraries') . '</option>';
	}
	echo '</select>';

	if (!empty($text)) {
		echo '<p>' . $text . '</p>';
	}
}

/**
 * display options header display
 */
function ml_options_display() {
	echo '<p>' . __('These settings determine how libraries will be displayed.', 'media-libraries') . '</p>';
}

/**
 * products per page field display
 */
function ml_per_page_field() {
	ml_text_field('ml_per_page', '', 2);
}

/**
 * base slug field
 */
function ml_slug_base_field() {
	ml_slug_field('ml_slug_base', 'Base tag for all Media Libraries pages. Default is library.');
}

/**
 * product slug field display
 */
function ml_slug_product_field() {
	ml_slug_field('ml_slug_product', 'Tag prepended for product URLs. Default is book.');
}

/**
 * person slug field display
 */
function ml_slug_person_field() {
	ml_slug_field('ml_slug_person', 'Tag prepended for person URLs (e.g. authors, editors, actors, directors, etc). Default is person.');
}

/**
 * tag slug field display
 */
function ml_slug_tag_field() {
	ml_slug_field('ml_slug_tag', 'Tag prepended for tag URLs. Default is tag.');
}

/**
 * user slug field display
 */
function ml_slug_user_field() {
	ml_slug_field('ml_slug_user', 'Tag prepended for user URLs. Default is user.');
}

/**
 * shelf slug field display
 */
function ml_slug_shelf_field() {
	ml_slug_field('ml_slug_shelf', 'Tag prepended for shelf URLs. Default is shelf.');
}

/**
 * initialises options by inserting missing options and registering with WP settings api
 * @todo check once more
 */
function ml_options_init() {
	ml_set_option();

	add_menu_page(__('Media Libraries', 'media-libraries'), __('Libraries', 'media-libraries'), 'edit_posts', 'edit.php?post_type=ml_product', '', '', 15);

	register_setting('media_libraries', 'ml_options', 'ml_options_validate');
	add_options_page(__('Media Libraries', 'media-libraries'), __('Media Libraries', 'media-libraries'), 'manage_options', 'ml_options', 'ml_options_page');

	// Display field definitions
	add_settings_section('ml_options_display', __('Display Settings', 'media-libraries'), 'ml_options_display', 'ml_options');
	add_settings_field('ml_per_page', __('Products per page', 'media-libraries'), 'ml_per_page_field', 'ml_options', 'ml_options_display');
	add_settings_field('ml_slug_base', __('Permalink base', 'media-libraries'), 'ml_slug_base_field', 'ml_options', 'ml_options_display');
	add_settings_field('ml_slug_product', __('Product base', 'media-libraries'), 'ml_slug_product_field', 'ml_options', 'ml_options_display');
	add_settings_field('ml_slug_person', __('Person base', 'media-libraries'), 'ml_slug_person_field', 'ml_options', 'ml_options_display');
	add_settings_field('ml_slug_tag', __('Tag base', 'media-libraries'), 'ml_slug_tag_field', 'ml_options', 'ml_options_display');
	add_settings_field('ml_slug_user', __('User base', 'media-libraries'), 'ml_slug_user_field', 'ml_options', 'ml_options_display');
	add_settings_field('ml_slug_shelf', __('Shelf base', 'media-libraries'), 'ml_slug_shelf_field', 'ml_options', 'ml_options_display');

	do_action('ml-options-init');
}

add_action('admin_menu', 'ml_options_init', 9);
