<?php
/**
 * amazon data source for media libraries
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/*
Plugin Name: Media Libraries: Amazon Provider
Version: 1.1
Plugin URI: http://impleri.net/development/media_libraries/
Description: Default media provider.
Author: Christopher Roussel
Author URI: http://impleri.net
*/

/**
 * AmazonECS wrapper
 *
 * acts as a simple middle layer between AML and AmazonECS.
 * @static
 */
class ml_amazon {

	/**
	 * @var Amazon domains that can be searched
	 */
	public static $domains = array(
		'US' => 'United States',
		'UK' => 'United Kingdom',
		'DE' => 'Germany',
		'JP' => 'Japan',
		'FR' => 'France',
		'CA' => 'Canada',
	);

	/**
	 * @var accepted Amazon categories
	 */
	public static $categories = array(
		'b' => 'Books',
		'v' => 'DVD',
		'm' => 'Music',
		'g' => 'VideoGames',
	);

	/**
	 * @var image sizes
	 */
	public static $image_sizes = array(
		'sm' => '._SL75_',
		'med' => '._SL110_',
		'lg' => '._SL160_',
		'or' => '',
	);

	/**
	 * @var image size texts
	 */
	public static $img_size_text = array(
		'sm' => 'Small',
		'med' => 'Medium',
		'lg' => 'Large',
		'or' => 'Original',
	);

	/**
	 * @var url to blank image if Amazon does not have one
	 */
	public static $blank_image = '';

	/**
	 * @var url prefix for Amazon images
	 */
	private static $image_base = 'http://ecx.images-amazon.com/images/I/';

	/**
	 * @var template for html error messages
	 */
	private static $error = '<div class="">%s</div>';

	/**
	 * AmazonECS instance
	 *
	 * gets and holds a single AmazonECS instance
	 * @return object AmazonECS object
	 */
	public static function &get() {
		static $amazon;

		if (empty($amazon)) {
			if (!class_exists('AmazonECS')) {
				require dirname(__FILE__) . '/AmazonECS.class.php';
			}
			$id = ml_get_option('ml_amazon_id');
			$key = ml_get_option('ml_secret_key');
			if (!empty($id) && !empty($key)) {
				$country = ml_get_option('ml_domain','US');
				$amazon = new AmazonECS($id, $key, $country, ml_get_option('ml_associate'));
			}
			else {
				$amazon = false;
			}
		}
		return $amazon;
	}

	/**
	 * Amazon item search
	 *
	 * searches Amazon for items matching the set descriptions
	 * @param string search terms
	 * @param string Amazon category/product type (see ml_amazon::$categories for accepted terms)
	 * @param int page of results to return
	 * @return string parsed html from ml_amazon::parse
	 */
	public static function search ($search, $type='b', $page=1) {
		$amazon = self::get();
		if (!$amazon) {
			return __('Error loading Amazon ECS library', 'media-libraries');
		}

		$ret = '';
		$type = (isset(self::$categories[$type])) ? self::$categories[$type] : 'Books';
		try {
			if ($page>1) {
				$response = $amazon->category($type)->responseGroup('Small,Images')->optionalParameters(array('ItemPage' => $page))->search($search);
			}
			else {
				$response = $amazon->category($type)->responseGroup('Small,Images')->search($search);
			}
		}
		catch(Exception $e) {
			$ret .= sprintf(self::$error, $e->getMessage());
		}

		if (is_object($response)) {
			if (intval($response->Items->TotalResults) > 0) {
				foreach ($response->Items->Item as $result) {
					$ret .= self::parse($result);
				}
			}
			else {
				$ret .= __('Nothing found for the search query', 'media-libraries');
			}
		}
		return $ret;
	}

	/**
	 * Amazon item lookup
	 *
	 * looks up an item on Amazon by asin/isbn
	 * @param string asin/isbn number
	 * @return string parsed html from ml_amazon::parse for echo
	 */
	public static function lookup ($asin) {
		$amazon = self::get();
		if (!$amazon) {
			return __('Error loading Amazon ECS library', 'media-libraries');
		}

		$ret = '';
		try {
			$response = $amazon->responseGroup('Small,Images')->lookup($asin);
		}
		catch(Exception $e) {
			$ret .= sprintf(self::$error, $e->getMessage());
		}

		if (is_object($response)) {
			if ('True' == $response->Items->Request->IsValid && isset($response->Items->Item)) {
				$ret = self::parse($response->Items->Item);
			}
		}
		return $ret;
	}

	/**
	 * item parser
	 *
	 * parse an result item object into an html listing
	 * @param object SimpleXML item node from AmazonECS response
	 * @return string formatted html
	 * @todo port out formatting to template
	 */
	public static function parse ($item) {
		$ret = '';

		$ret .= '<div class="ml-item-details">';
		$ret .= '<div class="ml-item-title">' . $item->ItemAttributes->Title . '</div>';

		$people = array();
		if (isset($item->ItemAttributes->Author)) {
			$people = (array)$item->ItemAttributes->Author;
		}
		if (isset($item->ItemAttributes->Creator)) {
			if (is_array($item->ItemAttributes->Creator)) {
				foreach ($item->ItemAttributes->Creator as $extra_name) {
					$people[] = $extra_name->_;
				}
			}
			else {
				$people[] = $item->ItemAttributes->Creator->_;
			}
		}

		if (!empty($people)) {
			array_walk($people, array('self', 'clean_name'));
			$ret .= '<div class="ml-item-people">' . __('People', 'media-libraries') . ': <span class="ml-item-people-names">' . implode(', ', $people) . '</span></div>';
			}

		$ret .= '<div class="ml-item-asin">ASIN: <span class="ml-item-asin-number">' . $item->ASIN . '</span></div>';
		$ret .= '<div class="ml-item-link"><a href="' . $item->DetailPageURL . '">Details</a></div>';
		$ret .= '<div id="' . $item->ASIN . '" class="ml-item">' . __('Use this item', 'media-libraries') . '</div>';
		$ret .= '</div>';

		$image = (isset($item->MediumImage)) ? $item->MediumImage->URL : ((isset($item->SmallImage)) ? $item->SmallImage->URL : ((isset($item->LargeImage)) ? $item->LargeImage->URL : self::$blank_image));
		if (!empty($image)) {
			$ret .= '<div class="ml-item-image"><img src="' . $image . '" /></div>';
		}

		return '<div id="ml-'.$item->ASIN.'" class="ml-list-item">' . $ret . '</div>' . "\n";
	}

	/**
	 * Amazon image url destructor
	 *
	 * reduces a url for an Amazon image to the name hash (for dynamic sizing of images)
	 * @param string url
	 * @return string image name hash
	 * @todo implement in ml_amazon::parse
	 */
	public function strip_image ($url) {
		$arr = array_merge(array_values(self::$image_sizes), array(self::$image_base, '.jpg'));
		return str_replace($arr, '', $url);
	}

	/**
	 * Amazon image url constructor
	 *
	 * creates a url for an Amazon image from the stored hash, sized at the currently configured size
	 * @param string image name hash
	 * @param string image size
	 * @return string url to image
	 */
	public function build_image ($image, $size='med') {
		if (0 === strpos('http', $image)) {
			return $image;
		}

		return self::$image_base . $image . self::$image_sizes[$size] . '.jpg';
	}

	/**
	 * name cleaner
	 *
	 * strips commas from name for compatibility with taxonomy entry
	 * @param string (value) name to clean
	 * @param string (key) unused
	 * @return string cleaned name
	 */
	private static function clean_name (&$name, $key='') {
		str_replace(array(',', '  '), array('', ' '), $name);
	}
}

/**
 * default amazon options
 */
function ml_amazon_defaults ($options) {
	$options['ml_amazon_id'] = '';
	$options['ml_secret_key'] = '';
	$options['ml_associate'] = '';
	$options['ml_domain'] = 'us';
	$options['ml_image_size'] = 'med';

	return $options;
}

/**
 * validates posted options for amazon
 *
 * @param array $_POST data passed from WP via ml_options_validate()
 * @return array validated options
 */
function ml_amazon_validate ($valid, $ml_post) {
	$options = get_option('ml_options');

	// Amazon fields
	$valid['ml_amazon_id'] = ($ml_post['ml_amazon_id']) ? sanitize_text_field($ml_post['ml_amazon_id']) : null;
	$valid['ml_secret_key'] = ($ml_post['ml_secret_key']) ? sanitize_text_field($ml_post['ml_secret_key']) : null;
	$valid['ml_associate'] = ($ml_post['ml_associate']) ? sanitize_text_field($ml_post['ml_associate']) : null;
	$valid['ml_domain'] = ($ml_post['ml_domain']) ? sanitize_text_field($ml_post['ml_domain']) : null;
	$valid['ml_image_size'] = in_array($ml_post['ml_image_size'], array('sm', 'med', 'lg')) ? $ml_post['ml_image_size'] : null;

	// Throw an error if no AWS info
	if (empty($valid['ml_amazon_id'])) {
		add_settings_error('ml_options', 'media-libraries', __('Amazon ID option is required for Media Libraries to function properly!', 'media-libraries'));
	}
	if (empty($valid['ml_secret_key'])) {
		add_settings_error('ml_options', 'media-libraries', __('Amazon secret key is required for Media Libraries to function properly!', 'media-libraries'));
	}
	return $valid;
}

/**
 * Amazon options header display
 */
function ml_options_amazon() {
?>
<p><?php _e('The following settings determine what Media Libraries will retrieve from Amazon.', 'media-libraries'); ?></p>
<?php }

/**
 * Amazon AWS id field
 */
function ml_amazon_id_field() {
?>
<input type="text" size="50" id="ml_amazon_id" name="ml_options[ml_amazon_id]" value="<?php echo htmlentities(ml_get_option('ml_amazon_id'), ENT_QUOTES, "UTF-8"); ?>" />
<p><?php echo sprintf(__('Required to add products from Amazon.  It is free to sign up. Register <a href="%s">here</a>.', 'media-libraries'), 'https://aws-portal.amazon.com/gp/aws/developer/registration/index.html'); ?></p>
<?php }

/**
 * Amazon AWS secret field
 */
function ml_secret_key_field() {
?>
<input type="text" size="50" id="ml_secret_key" name="ml_options[ml_secret_key]" value="<?php echo htmlentities(ml_get_option('ml_secret_key'), ENT_QUOTES, "UTF-8"); ?>" />
<p><?php echo sprintf(__('Required to add products from Amazon.  Found at the same site as above. Register <a href="%s">here</a>.', 'media-libraries'), 'https://aws-portal.amazon.com/gp/aws/developer/registration/index.html'); ?></p>
<?php }

/**
 * Amazon associate id field
 */
function ml_associate_field() {
?>
<input type="text" size="50" id="ml_associate" name="ml_options[ml_associate]" value="<?php echo htmlentities(ml_get_option('ml_associate'), ENT_QUOTES, "UTF-8"); ?>" />
<p><?php _e('If you choose to link to a product page on Amazon using the url meta - as the default template does - then you can earn commission if your visitors then purchase products.'); ?></p>
<p><?php echo sprintf(__('If you do not have an Amazon Associates ID, you can either <a href="%s">get one</a>.', 'media-libraries'), 'http://associates.amazon.com/'); ?></p>
<?php }

/**
 * Amazon domain field
 */
function ml_domain_field() {
	$option = ml_get_option('ml_domain');
	$ml_domains = ml_amazon::$domains;
?>
<select id="ml_domain" name="ml_options[ml_domain]">
<?php foreach ($ml_domains as $domain => $country) { ?>
<option value="<?php echo $domain; ?>"<?php selected($domain, $option); ?>><?php echo $country; ?></option>
<?php } ?>
</select>
<p><?php _e('Country-specific Amazon site to use for searching and product links', 'media-libraries'); ?></p>
<p><?php _e('NB: If you have country-specific books in your catalogue and then change your domain setting, some old links might stop working.', 'media-libraries'); ?></p>
<?php }

/**
 * Amazon image size field
 * @todo remove NB when image auto-size working
 */
function ml_image_size_field() {
	$option = ml_get_option('ml_image_size');
	$sizes = ml_amazon::$img_size_text;
?>
<select id="ml_image_size" name="ml_options[ml_image_size]">
<?php foreach ($sizes as $size => $name) { ?>
<option value="<?php echo $size; ?>"<?php selected($size, $option); ?>><?php _e($name); ?></option>
<?php } ?>
</select>
<p><?php _e('NB: This change will only be applied to products you add from this point onwards.'); ?></p>
<?php }

/**
 * register amazon options with WP settings api
 */
function ml_amazon_init_options() {
	add_settings_section('ml_options_amazon', __('Amazon Settings', 'media-libraries'), 'ml_options_amazon', 'ml_options');

	// Amazon field definitions
	add_settings_field('ml_amazon_id', __('Amazon Web Services Access Key ID', 'media-libraries'), 'ml_amazon_id_field', 'ml_options', 'ml_options_amazon');
	add_settings_field('ml_secret_key', __('Amazon Web Services Secret Access Key', 'media-libraries'), 'ml_secret_key_field', 'ml_options', 'ml_options_amazon');
	add_settings_field('ml_associate', __('Your Amazon Associates ID', 'media-libraries'), 'ml_associate_field', 'ml_options', 'ml_options_amazon');
	add_settings_field('ml_domain', __('Amazon domain to use', 'media-libraries'), 'ml_domain_field', 'ml_options', 'ml_options_amazon');
	add_settings_field('ml_image_size', __('Image size to use', 'media-libraries'), 'ml_image_size_field', 'ml_options', 'ml_options_display');
}

/**
 * checks if required options (aws keys) need to be set
 *
 * @return bool true if necessary options are valid
 */
function ml_amazon_check ($pass) {
	if ($pass == true) {
		$aws_key = ml_get_option('ml_amazon_id');
		$aws_secret = ml_get_option('ml_secret_key');

		$pass = (!empty($aws_key) && !empty($aws_secret));
	}
	return $pass;
}

add_action('ml-options-init', 'ml_amazon_init_options');
add_filter('ml-check-init', 'ml_amazon_check');
add_filter('ml-default-options', 'ml_amazon_defaults');
add_filter('ml-options-validate', 'ml_amazon_validate', 10, 2);
