<?php
/**
 * admin menus and some plugin options
 * @package amazon-library
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * default options
 */
function aml_default_options() {
	return array(
		'aml_amazon_id' => '',
		'aml_secret_key' => '',
		'aml_associate' => '',
		'aml_domain' => 'us',
		'aml_image_size' => 'med',
		'aml_per_page' => 20,
		'aml_use_tags' => 1,
		'aml_use_categories' => 0,
		'aml_use_shelves' => 1,
		'aml_slug_base' => 'library',
		'aml_slug_product' => 'product',
		'aml_slug_person' => 'person',
		'aml_slug_tag' => 'tag',
		'aml_slug_user' => 'user',
		'aml_slug_shelf' => 'shelf',
		'aml_version' => AML_VERSION,
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
function aml_update_meta ($field, $post, $new=null) {
	$old = get_post_meta($post, $field, true);
	if(empty($new)) {
		$ret = delete_post_meta($post, $field, $old);
	}
	elseif (empty($old)) {
		$ret = add_post_meta($post, $field, $new);
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
 * shortcut for getting option from the aml_options array
 *
 * @param string name of option
 * @param mixed default value override (if null, will give from aml_default_options)
 * @return mixed option value
 */
function aml_get_option ($key='', $def=null) {
	static $options;
	static $defaults;

	if (!is_array($defaults)) {
		$defaults = aml_default_options();
	}

	if (!is_array($options)) {
		$options = get_option('aml_options', $defaults);
	}

	if (false === strpos($key, 'aml_')) {
		$key = 'aml_' . $key;
	}

	$def = (is_null($def) && isset($defaults[$key])) ? $defaults[$key] : $def;

	return (isset($options[$key])) ? $options[$key] : $def;
}

/**
 * hack to use our templates
 *
 * @param string found template (passed from the filter)
 * @param string type of taxonomy to check
 * @param string type of page (archive, single, or taxonomy)
 * @return string path to template
 */
function aml_insert_template ($template, $check, $page='archive') {
	if ($page == 'taxonomy') {
		$term = get_queried_object();
		$type = $term->taxonomy;
	}
	else {
		$type = get_query_var('post_type');
	}

	// not ours to worry about!
	if ($check != $type) {
		return $template;
	}

	$file = $page.'-'.$check.'.php';

	// template not found in theme folder, so insert our default
	if ($file != basename($template)) {
		$path = dirname(__FILE__) . '/templates/' . $file;
		if ( file_exists($path)) {
			return $path;
		}
	}

	return $template;
}

/**
 * options page display
 */
function aml_options_page() {
?>
	<div class="wrap">
	<form method="post" action="options.php">
		<h2>Amazon Media Libraries</h2>
		 <?php settings_fields('amazon_library'); ?>
		<?php do_settings_sections('aml_options'); ?>

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
function aml_options_validate ($aml_post) {
	$options = get_option('aml_options');
	$defaults = aml_default_options();
	$valid = array();
	//TODO: more validation!

	// Amazon fields
	$valid['aml_amazon_id'] = ($aml_post['aml_amazon_id']) ? sanitize_text_field($aml_post['aml_amazon_id']) : null;
	$valid['aml_secret_key'] = ($aml_post['aml_secret_key']) ? sanitize_text_field($aml_post['aml_secret_key']) : null;
	$valid['aml_associate'] = ($aml_post['aml_associate']) ? sanitize_text_field($aml_post['aml_associate']) : null;
	$valid['aml_domain'] = ($aml_post['aml_domain']) ? sanitize_text_field($aml_post['aml_domain']) : null;
	$valid['aml_image_size'] = in_array($aml_post['aml_image_size'], array('sm', 'med', 'lg')) ? $aml_post['aml_image_size'] : null;

	// Display fields
	$valid['aml_per_page'] = ($aml_post['aml_per_page']) ? intval($aml_post['aml_per_page']) : null;
	$valid['aml_slug_base'] = ($aml_post['aml_slug_base']) ? sanitize_text_field($aml_post['aml_slug_base']) : null;
	$valid['aml_slug_product'] = ($aml_post['aml_slug_product']) ? sanitize_text_field($aml_post['aml_slug_product']) : null;
	$valid['aml_slug_person'] = ($aml_post['aml_slug_person']) ? sanitize_text_field($aml_post['aml_slug_person']) : null;
	$valid['aml_slug_tag'] = ($aml_post['aml_slug_tag']) ? sanitize_text_field($aml_post['aml_slug_tag']) : null;
	$valid['aml_slug_user'] = ($aml_post['aml_slug_user']) ? sanitize_text_field($aml_post['aml_slug_user']) : null;

	// merge (defaults, current, and new values) into one array
	$valid = array_merge($defaults, $options, $valid);
	// Throw an error if no AWS info
	if (empty($valid['aml_amazon_id'])) {
		add_settings_error('aml_options', 'amazon-library', __('Amazon ID option is required for Now Reading to function properly!', 'amazon-library'));
	}
	if (empty($valid['aml_secret_key'])) {
		add_settings_error('aml_options', 'amazon-library', __('Amazon secret key is required for Now Reading to function properly!', 'amazon-library'));
	}
	return $valid;
}

/**
 * Amazon options header display
 */
function aml_options_amazon() {
?>
<p><?php _e('The following settings determine what Now Reading will retrieve from Amazon.', 'amazon-library'); ?></p>
<?php }

/**
 * display options header display
 */
function aml_options_display() {
?>
<p><?php _e('These settings determine how libraries will be displayed.', 'amazon-library'); ?></p>
<?php }

/**
 * Amazon AWS id field
 */
function aml_amazon_id_field() {
?>
<input type="text" size="50" id="aml_amazon_id" name="aml_options[aml_amazon_id]" value="<?php echo htmlentities(aml_get_option('aml_amazon_id'), ENT_QUOTES, "UTF-8"); ?>" />
<p><?php echo sprintf(__('Required to add books from Amazon.  It is free to sign up. Register <a href="%s">here</a>.', 'amazon-library'), 'https://aws-portal.amazon.com/gp/aws/developer/registration/index.html'); ?></p>
<?php }

/**
 * Amazon AWS secret field
 */
function aml_secret_key_field() {
?>
<input type="text" size="50" id="aml_secret_key" name="aml_options[aml_secret_key]" value="<?php echo htmlentities(aml_get_option('aml_secret_key'), ENT_QUOTES, "UTF-8"); ?>" />
<p><?php echo sprintf(__('Required to add books from Amazon.  Found at the same site as above. Register <a href="%s">here</a>.', 'amazon-library'), 'https://aws-portal.amazon.com/gp/aws/developer/registration/index.html'); ?></p>
<?php }

/**
 * Amazon associate id field
 */
function aml_associate_field() {
?>
<input type="text" size="50" id="aml_associate" name="aml_options[aml_associate]" value="<?php echo htmlentities(aml_get_option('aml_associate'), ENT_QUOTES, "UTF-8"); ?>" />
<p><?php _e('If you choose to link to a product page on Amazon using the url meta - as the default template does - then you can earn commission if your visitors then purchase products.'); ?></p>
<p><?php echo sprintf(__('If you do not have an Amazon Associates ID, you can either <a href="%s">get one</a>.', 'amazon-library'), 'http://associates.amazon.com/'); ?></p>
<?php }

/**
 * Amazon domain field
 */
function aml_domain_field() {
	$option = aml_get_option('aml_domain');
	$aml_domains = aml_amazon::$domains;
?>
<select id="aml_domain" name="aml_options[aml_domain]">
<?php foreach ($aml_domains as $domain => $country) { ?>
<option value="<?php echo $domain; ?>"<?php selected($domain, $option); ?>><?php echo $country; ?></option>
<?php } ?>
</select>
<p><?php _e('Country-specific Amazon site to use for searching and product links', 'amazon-library'); ?></p>
<p><?php _e('NB: If you have country-specific books in your catalogue and then change your domain setting, some old links might stop working.', 'amazon-library'); ?></p>
<?php }

/**
 * Amazon image size field
 */
function aml_image_size_field() {
	$option = aml_get_option('aml_image_size');
	$sizes = aml_amazon::$img_size_text;
?>
<select id="aml_image_size" name="aml_options[aml_image_size]">
<?php foreach ($sizes as $size => $name) { ?>
<option value="<?php echo $size; ?>"<?php selected($size, $option); ?>><?php _e($name); ?></option>
<?php } ?>
</select>
<p><?php _e('NB: This change will only be applied to books you add from this point onwards.'); ?></p>
<?php }

/**
 * products per page field display
 */
function aml_per_page_field() {
?>
<input type="text" size="2" id="aml_per_page" name="aml_options[aml_per_page]" value="<?php echo intval(aml_get_option('aml_per_page')); ?>" />
<?php }

/**
 * template for slug field display
 *
 * @param string key
 * @param string description
 */
function aml_slug_field ($option, $text) {
?>
<input type="text" size="50" id="<?php echo $option; ?>" name="aml_options[<?php echo $option; ?>]" value="<?php echo aml_get_option($option); ?>" />
<p><?php _e($text, 'amazon-library'); ?></p>
<p><?php _e('NB: Only Alpha-numerics and dashes are allowed.', 'amazon-library'); ?></p>
<?php }

/**
 * base slug field
 */
function aml_slug_base_field() {
	aml_slug_field('aml_slug_base', 'Base tag for all Amazon Media Libraries pages. Default is library.');
}

/**
 * product slug field display
 */
function aml_slug_product_field() {
	aml_slug_field('aml_slug_product', 'Tag prepended for product URLs. Default is book.');
}

/**
 * person slug field display
 */
function aml_slug_person_field() {
	aml_slug_field('aml_slug_person', 'Tag prepended for person URLs (e.g. authors, editors, actors, directors, etc). Default is person.');
}

/**
 * tag slug field display
 */
function aml_slug_tag_field() {
	aml_slug_field('aml_slug_tag', 'Tag prepended for tag URLs. Default is tag.');
}

/**
 * user slug field display
 */
function aml_slug_user_field() {
	aml_slug_field('aml_slug_user', 'Tag prepended for user URLs. Default is user.');
	$options = get_option('aml_options');
}

/**
 * shelf slug field display
 */
function aml_slug_shelf_field() {
	aml_slug_field('aml_slug_shelf', 'Tag prepended for shelf URLs. Default is shelf.');
}

/**
 * initialises options by inserting missing options and registering with WP settings api
 * @todo check once more
 */
function aml_options_init() {
	$default_options = aml_default_options();
	$options = get_option('aml_options', aml_default_options());
	$options = (false === $options) ? array() : $options;
	$options = array_merge($default_options, $options);
	update_option('aml_options', $options);

	register_setting('amazon_library', 'aml_options', 'aml_options_validate');

	add_options_page(__('Amazon Media Libraries', 'amazon-library'), __('Amazon Media Libraries', 'amazon-library'), 'manage_options', 'aml_options', 'aml_options_page');

	add_settings_section('aml_options_amazon', __('Amazon Settings', 'amazon-library'), 'aml_options_amazon', 'aml_options');
	add_settings_section('aml_options_display', __('Display Settings', 'amazon-library'), 'aml_options_display', 'aml_options');

	// Amazon field definitions
	add_settings_field('aml_amazon_id', __('Amazon Web Services Access Key ID', 'amazon-library'), 'aml_amazon_id_field', 'aml_options', 'aml_options_amazon');
	add_settings_field('aml_secret_key', __('Amazon Web Services Secret Access Key', 'amazon-library'), 'aml_secret_key_field', 'aml_options', 'aml_options_amazon');
	add_settings_field('aml_associate', __('Your Amazon Associates ID', 'amazon-library'), 'aml_associate_field', 'aml_options', 'aml_options_amazon');
	add_settings_field('aml_domain', __('Amazon domain to use', 'amazon-library'), 'aml_domain_field', 'aml_options', 'aml_options_amazon');
	add_settings_field('aml_image_size', __('Image size to use', 'amazon-library'), 'aml_image_size_field', 'aml_options', 'aml_options_display');

	// Display field definitions
	add_settings_field('aml_per_page', __('Books per page', 'amazon-library'), 'aml_per_page_field', 'aml_options', 'aml_options_display');
	add_settings_field('aml_slug_base', __('Permalink base', 'amazon-library'), 'aml_slug_base_field', 'aml_options', 'aml_options_display');
	add_settings_field('aml_slug_product', __('Product base', 'amazon-library'), 'aml_slug_product_field', 'aml_options', 'aml_options_display');
	add_settings_field('aml_slug_person', __('Person base', 'amazon-library'), 'aml_slug_person_field', 'aml_options', 'aml_options_display');
	add_settings_field('aml_slug_tag', __('Tag base', 'amazon-library'), 'aml_slug_tag_field', 'aml_options', 'aml_options_display');
	add_settings_field('aml_slug_user', __('User base', 'amazon-library'), 'aml_slug_user_field', 'aml_options', 'aml_options_display');
	add_settings_field('aml_slug_shelf', __('Shelf base', 'amazon-library'), 'aml_slug_shelf_field', 'aml_options', 'aml_options_display');
}

add_action('admin_menu', 'aml_options_init');
