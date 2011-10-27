<?php
/**
 * admin menus and some plugin options
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * default options
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
		'ml_version' => ML_VERSION,
	));
}

function ml_product_categories() {
	return array(
		'b' => __('Books', 'media-libraries'),
		'v' => __('Video', 'media-libraries'),
		'm' => __('Music', 'media-libraries'),
		'g' => __('Video Games', 'media-libraries'),
	);
}

/**
 * shortcut for handling meta updates
 *
 * @param string name of meta field
 * @param int post id for meta
 * @param mixed new value (default is null)
 * @return bool true on success
 */
function ml_update_meta ($field, $post, $new=null, $single=true) {
	$old = get_post_meta($post, $field, $single);
	if(empty($new)) {
		$ret = delete_post_meta($post, $field, $old);
	}
	elseif (empty($old)) {
		$ret = add_post_meta($post, $field, $new, $single);
	}
	elseif ($new != $old) {
		$ret = update_post_meta($post, $field, $new, $old);
	}
	else {
		$ret = false;
	}

	return $ret;
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
 * hack to use our templates
 *
 * @param string found template (passed from the filter)
 * @param string type of custom post/taxonomy to check
 * @param string type of page (archive, single, or taxonomy)
 * @return string path to template
 */
function ml_insert_template ($template, $type, $page='archive') {
	if ($page == 'taxonomy') {
		$term = get_queried_object();
		$check = $term->taxonomy;
	}
	else {
		$check = get_query_var('post_type');
	}

	// one of ours to worry about!
	if ($check == $type) {
		$file = $page.'-'.$type.'.php';

		// template not found in theme folder, so replace it with our default
		if ($file != basename($template)) {
			$path = dirname(__FILE__) . '/templates/' . $file;
			if ( file_exists($path)) {
				$template = $path;
			}
		}
	}

	return $template;
}

/**
 * options page display
 */
function ml_options_page() {
?>
	<div class="wrap">
	<form method="post" action="options.php">
		<h2>Media Libraries</h2>
		 <?php settings_fields('media_libraries'); ?>
		<?php do_settings_sections('ml_options'); ?>

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
	</div>
<?php
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
 * display options header display
 */
function ml_options_display() {
?>
<p><?php _e('These settings determine how libraries will be displayed.', 'media-libraries'); ?></p>
<?php }

/**
 * products per page field display
 */
function ml_per_page_field() {
?>
<input type="text" size="2" id="ml_per_page" name="ml_options[ml_per_page]" value="<?php echo intval(ml_get_option('ml_per_page')); ?>" />
<?php }

/**
 * template for slug field display
 *
 * @param string key
 * @param string description
 */
function ml_slug_field ($option, $text) {
?>
<input type="text" size="50" id="<?php echo $option; ?>" name="ml_options[<?php echo $option; ?>]" value="<?php echo ml_get_option($option); ?>" />
<p><?php _e($text, 'media-libraries'); ?></p>
<p><?php _e('NB: Only Alpha-numerics and dashes are allowed.', 'media-libraries'); ?></p>
<?php }

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
	add_menu_page( __('Media Libraries', 'media-libraries'), __('Libraries', 'media-libraries'), 'edit_posts', 'edit.php?post_type=ml_product', '', '', 15);
	$default_options = ml_default_options();
	$options = get_option('ml_options', ml_default_options());
	$options = (false === $options) ? array() : $options;
	$options = array_merge($default_options, $options);
	update_option('ml_options', $options);

	register_setting('media_libraries', 'ml_options', 'ml_options_validate');

	add_options_page(__('Media Libraries', 'media-libraries'), __('Media Libraries', 'media-libraries'), 'manage_options', 'ml_options', 'ml_options_page');

	add_settings_section('ml_options_display', __('Display Settings', 'media-libraries'), 'ml_options_display', 'ml_options');

	// Display field definitions
	add_settings_field('ml_per_page', __('Products per page', 'media-libraries'), 'ml_per_page_field', 'ml_options', 'ml_options_display');
	add_settings_field('ml_slug_base', __('Permalink base', 'media-libraries'), 'ml_slug_base_field', 'ml_options', 'ml_options_display');
	add_settings_field('ml_slug_product', __('Product base', 'media-libraries'), 'ml_slug_product_field', 'ml_options', 'ml_options_display');
	add_settings_field('ml_slug_person', __('Person base', 'media-libraries'), 'ml_slug_person_field', 'ml_options', 'ml_options_display');
	add_settings_field('ml_slug_tag', __('Tag base', 'media-libraries'), 'ml_slug_tag_field', 'ml_options', 'ml_options_display');
	add_settings_field('ml_slug_user', __('User base', 'media-libraries'), 'ml_slug_user_field', 'ml_options', 'ml_options_display');
	add_settings_field('ml_slug_shelf', __('Shelf base', 'media-libraries'), 'ml_slug_shelf_field', 'ml_options', 'ml_options_display');

	do_action('ml-options-init');
}
