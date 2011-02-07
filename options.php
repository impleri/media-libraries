<?php
/**
 * Adds our admin menus, and some stylesheets and JavaScript to the admin head.
 * @package amazon-library
 * $Rev$
 * $Date$
 */

/**
 * Default options
 */
function aml_default_options() {
	return array(
		'aml_amazon_id' => '',
		'aml_secret_key' => '',
		'aml_associate' => '',
		'aml_domain' => 'us',
		'aml_image_size' => 'med',
		'aml_per_page' => 20,
		'aml_slug_base' => 'library',
		'aml_slug_product' => 'book',
		'aml_slug_author' => 'author',
		'aml_slug_tag' => 'tag',
		'aml_slug_user' => 'user',
		'aml_version' => AML_VERSION,
	);
}

/**
 * Initialises options for Now Reading by inserting missing options and registering with WP Settings API
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
	add_settings_field('aml_slug_author', __('Author base', 'amazon-library'), 'aml_slug_author_field', 'aml_options', 'aml_options_display');
	add_settings_field('aml_slug_tag', __('Tag base', 'amazon-library'), 'aml_slug_tag_field', 'aml_options', 'aml_options_display');
}
add_action('admin_menu', 'aml_options_init');

/**
 * Creates the options admin page and manages the updating of options.
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
 * Validates posted options for Now Reading
 */
function aml_options_validate ($aml_post) {
	$options = get_option('aml_options');
	$defaults = aml_default_options();
	$valid = array();
	//TODO: Better validation

	// Amazon fields
	$valid['aml_amazon_id'] = ($aml_post['aml_amazon_id']) ? preg_replace('/[^[:alnum:]]/', '', $aml_post['aml_amazon_id']) : null;
	$valid['aml_secret_key'] = ($aml_post['aml_secret_key']) ? preg_replace('/[^[\+[:alnum:]]]/', '', $aml_post['aml_secret_key']) : null;
	$valid['aml_associate'] = ($aml_post['aml_associate']) ? preg_replace('/[^[\-[:alnum:]]]/', '', $aml_post['aml_associate']) : null;
	$valid['aml_domain'] = ($aml_post['aml_domain']) ? preg_replace('/[^[:alpha:]]/', '', $aml_post['aml_domain']) : null;
	$valid['aml_image_size'] = in_array($aml_post['aml_image_size'], array('sm', 'med', 'lg')) ? $aml_post['aml_image_size'] : null;

	// Display fields
	$valid['aml_per_page'] = ($aml_post['aml_per_page']) ? intval($aml_post['aml_per_page']) : null;
	$valid['aml_slug_base'] = ($aml_post['aml_slug_base']) ? str_replace('#', '', $aml_post['aml_slug_base']) : null;
	$valid['aml_slug_product'] = ($aml_post['aml_slug_product']) ? str_replace('#', '', $aml_post['aml_slug_product']) : null;
	$valid['aml_slug_author'] = ($aml_post['aml_slug_author']) ? str_replace('#', '', $aml_post['aml_slug_author']) : null;
	$valid['aml_slug_tag'] = ($aml_post['aml_slug_tag']) ? str_replace('#', '', $aml_post['aml_slug_tag']) : null;
	$valid['aml_slug_user'] = (!empty($aml_post['aml_slug_user'])) ? str_replace('#', '', $aml_post['aml_slug_user']) : null;

	// Throw an error if no AWS info
	$valid = array_merge($defaults, $options, $valid);
	if (empty($valid['aml_amazon_id'])) {
		add_settings_error('aml_options', 'amazon-library', __('Amazon ID option is required for Now Reading to function properly!', 'amazon-library'));
	}
	if (empty($valid['aml_secret_key'])) {
		add_settings_error('aml_options', 'amazon-library', __('Amazon secret key is required for Now Reading to function properly!', 'amazon-library'));
	}
	return $valid;
}

// Section header texts
function aml_options_amazon() {
?>
<p><?php _e('The following settings determine what Now Reading will retrieve from Amazon.', 'amazon-library'); ?></p>
<?php }

function aml_options_display() {
?>
<p><?php _e('These settings determine how libraries will be displayed.', 'amazon-library'); ?></p>
<?php }

// Amazon field boxes
function aml_amazon_id_field() {
	$options = get_option('aml_options');
?>
<input type="text" size="50" id="aml_amazon_id" name="aml_options[aml_amazon_id]" value="<?php echo htmlentities($options['aml_amazon_id'], ENT_QUOTES, "UTF-8"); ?>" />
<p><?php echo sprintf(__('Required to add books from Amazon.  It is free to sign up. Register <a href="%s">here</a>.', 'amazon-library'), 'https://aws-portal.amazon.com/gp/aws/developer/registration/index.html'); ?></p>
<?php }

function aml_secret_key_field() {
	$options = get_option('aml_options');
?>
<input type="text" size="50" id="aml_secret_key" name="aml_options[aml_secret_key]" value="<?php echo htmlentities($options['aml_secret_key'], ENT_QUOTES, "UTF-8"); ?>" />
<p><?php echo sprintf(__('Required to add books from Amazon.  Found at the same site as above. Register <a href="%s">here</a>.', 'amazon-library'), 'https://aws-portal.amazon.com/gp/aws/developer/registration/index.html'); ?></p>
<?php }

function aml_associate_field() {
	$options = get_option('aml_options');
?>
<input type="text" size="50" id="aml_associate" name="aml_options[aml_associate]" value="<?php echo htmlentities($options['aml_associate'], ENT_QUOTES, "UTF-8"); ?>" />
<p><?php _e('If you choose to link to a product page on Amazon.com using the <code>product_url()</code> template tag - as the default template does - then you can earn commission if your visitors then purchase products.'); ?></p>
<p><?php echo sprintf(__('If you do not have an Amazon Associates ID, you can either <a href="%s">get one</a>.', 'amazon-library'), 'http://associates.amazon.com/'); ?></p>
<?php }

function aml_domain_field() {
	$options = get_option('aml_options');
	$aml_domains = aml_amazon::$domains;
?>
<select id="aml_domain" name="aml_options[aml_domain]">
<?php foreach ($aml_domains as $domain => $country) { ?>
<option value="<?php echo $domain; ?>"<?php selected($domain, $options['aml_domain']); ?>><?php echo $country; ?></option>
<?php } ?>
</select>
<p><?php echo sprintf(__('If you choose to link to a product page on Amazon.com using the <code>product_url()</code> template tag, you can specify which country-specific Amazon site to link to. Now Reading will also use this domain when searching.', 'amazon-library'), "https://aws-portal.amazon.com/gp/aws/developer/registration/index.html"); ?></p>
<p><?php _e('NB: If you have country-specific books in your catalogue and then change your domain setting, some old links might stop working.', 'amazon-library'); ?></p>
<?php }

function aml_image_size_field() {
	$options = get_option('aml_options');
	$sizes = array('sm' => 'Small', 'med' => 'Medium', 'lg' => 'Large');
?>
<select id="aml_image_size" name="aml_options[aml_image_size]">
<?php foreach ($sizes as $size => $name) { ?>
<option value="<?php echo $size; ?>"<?php selected($size, $options['aml_image_size']); ?>><?php _e($name); ?></option>
<?php } ?>
</select>
<p><?php _e('NB: This change will only be applied to books you add from this point onwards.'); ?></p>
<?php }

// Display
function aml_per_page_field() {
	$options = get_option('aml_options');
?>
<input type="text" size="2" id="aml_per_page" name="aml_options[aml_per_page]" value="<?php echo intval($options['aml_per_page']); ?>" />
<?php }

function aml_slug_base_field() {
	$options = get_option('aml_options');
?>
<input type="text" size="50" id="aml_slug_base" name="aml_options[aml_slug_base]" value="<?php echo $options['aml_slug_base']; ?>" />
<p><?php _e('Base tag for all Now Reading pages. Default is library', 'amazon-library'); ?></p>
<p><?php _e('NB: Only Alpha-numerics and dashes are allowed.', 'amazon-library'); ?></p>
<?php }

function aml_slug_product_field() {
	$options = get_option('aml_options');
?>
<input type="text" size="50"id="aml_slug_product" name="aml_options[aml_slug_product]" value="<?php echo $options['aml_slug_product']; ?>" />
<p><?php _e('Tag prepended for product URLs. Default is book.', 'amazon-library'); ?></p>
<p><?php _e('NB: Only Alpha-numerics and dashes are allowed.', 'amazon-library'); ?></p>
<?php }

function aml_slug_author_field() {
	$options = get_option('aml_options');
?>
<input type="text" size="50" id="aml_slug_author" name="aml_options[aml_slug_author]" value="<?php echo $options['aml_slug_author']; ?>" />
<p><?php _e('Tag prepended for author URLs. Default is author.', 'amazon-library'); ?></p>
<p><?php _e('NB: Only Alpha-numerics and dashes are allowed.', 'amazon-library'); ?></p>
<?php }

function aml_slug_tag_field() {
	$options = get_option('aml_options');
?>
<input type="text" size="50" id="aml_slug_tag" name="aml_options[aml_slug_tag]" value="<?php echo $options['aml_slug_tag']; ?>" />
<p><?php _e('Tag prepended for Now Reading tag URLs. Default is tag.', 'amazon-library'); ?></p>
<p><?php _e('NB: Only Alpha-numerics and dashes are allowed.', 'amazon-library'); ?></p>
<?php }
