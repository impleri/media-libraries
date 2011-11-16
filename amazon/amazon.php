<?php
/**
 * amazon data source for media libraries
 * @package media-libraries
 * @subpackage amazon
 * @author Christopher Roussel <christopher@impleri.net>
 */

/*
Plugin Name: Media Libraries: Amazon Provider
Version: 1.3.2
Plugin URI: http://impleri.net/development/media_libraries/
Description: Provides access to Amazon for adding products to Media Libraries.
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
		'com' => 'United States',
		'co.uk' => 'United Kingdom',
		'de' => 'Germany',
		'co.jp' => 'Japan',
		'fr' => 'France',
		'ca' => 'Canada',
		'it' => 'Italy',
		'cn' => 'China',
		'es' => 'Spain',
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
	public static function search ($ret, $search, $type='b', $page=1) {
		$amazon = self::get();
		if (!$amazon) {
			return __('Error loading Amazon ECS library', 'media-libraries');
		}

		$type = (isset(self::$categories[$type])) ? self::$categories[$type] : 'Books';
		try {
// 			$opt = ($page>1) ? array('ItemPage' => $page) : null;
			$response = $amazon->category($type)->responseGroup('Small,Images')->search($search);
		}
		catch(Exception $e) {
			$ret .= sprintf(self::$error, $e->getMessage());
		}

		if (is_object($response)) {
			if (isset($response->Items) && intval($response->Items->TotalResults) > 0) {
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

		$image = (isset($item->MediumImage)) ? $item->MediumImage->URL : ((isset($item->SmallImage)) ? $item->SmallImage->URL : ((isset($item->LargeImage)) ? $item->LargeImage->URL : ml_blank_image()));
		if (!empty($image)) {
			$ret .= '<div class="ml-item-image"><img src="' . $image . '" /><span class="ml-image" style="display:none;">' . self::strip_image($image) . '</div>';
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
		$image = '';
		if ($url != ml_blank_image()) {
			$arr = array_merge(array_values(self::$image_sizes), array(self::$image_base, '.jpg'));
			$image = str_replace($arr, '', $url);
		}

		return $image;
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
 * Amazon options header display
 */
function ml_options_amazon() {
	echo '<p>' . __('The following settings determine what Media Libraries will retrieve from Amazon.', 'media-libraries') . '</p>';
}

/**
 * Amazon AWS key field
 */
function ml_amz_key_field() {
	ml_text_field('ml_amz_key', sprintf(__('Required to add products from Amazon.  It is free to sign up. Register <a href="%s">here</a>.', 'media-libraries'), 'https://aws-portal.amazon.com/gp/aws/developer/registration/index.html') );
}

/**
 * Amazon AWS secret field
 */
function ml_amz_secret_field() {
	ml_text_field('ml_amz_secret', sprintf(__('Required to add products from Amazon.  Found at the same site as above. Register <a href="%s">here</a>.', 'media-libraries'), 'https://aws-portal.amazon.com/gp/aws/developer/registration/index.html') );
}

/**
 * Amazon associate id field
 */
function ml_amz_assoc_field() {
	ml_text_field('ml_amz_assoc', sprintf(__('Required to add products from Amazon. If your visitors purchase products, you will get commission on their purchases. Register <a href="%s">here</a>.', 'media-libraries'), 'http://associates.amazon.com/') );
}

/**
 * Amazon domain field
 */
function ml_amz_domain_field() {
	ml_select_field('ml_amz_domain', ml_amazon::$domains, __('Country-specific Amazon site to use for searching and product links', 'media-libraries'));
	echo '<p>' . __('NB: If you have country-specific books in your catalogue and then change your domain setting, some old links might stop working.', 'media-libraries') . '</p>';
}

/**
 * Amazon image size field
 * @todo remove NB when image auto-size working
 */
function ml_amz_image_field() {
	ml_select_field('ml_amz_image', ml_amazon::$img_size_text, __('NB: This change will only be applied to products you add from this point onwards.'));
}

/**
 * default amazon options
 */
function ml_amazon_defaults ($options) {
	$options['ml_amz_key'] = '';
	$options['ml_amz_secret'] = '';
	$options['ml_amz_assoc'] = '';
	$options['ml_amz_domain'] = 'com';
	$options['ml_amz_image'] = 'med';

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
	$valid['ml_amz_key'] = ($ml_post['ml_amz_key']) ? sanitize_text_field($ml_post['ml_amz_key']) : null;
	$valid['ml_amz_secret'] = ($ml_post['ml_amz_secret']) ? sanitize_text_field($ml_post['ml_amz_secret']) : null;
	$valid['ml_amz_assoc'] = ($ml_post['ml_amz_assoc']) ? sanitize_text_field($ml_post['ml_amz_assoc']) : null;
	$valid['ml_amz_domain'] = ($ml_post['ml_amz_domain']) ? sanitize_text_field($ml_post['ml_amz_domain']) : null;
	$valid['ml_amz_image'] = in_array($ml_post['ml_amz_image'], array('sm', 'med', 'lg')) ? $ml_post['ml_amz_image'] : null;

	// Throw an error if no AWS info
	if (empty($valid['ml_amz_key'])) {
		add_settings_error('ml_options', 'media-libraries', __('Amazon WDSL requires an <b>Amazon AWS key</b>!', 'media-libraries'));
	}
	if (empty($valid['ml_amz_secret'])) {
		add_settings_error('ml_options', 'media-libraries', __('Amazon WDSL requires the <b>Amazon AWS secret</b> attached to an AWS key!', 'media-libraries'));
	}
	if (empty($valid['ml_amz_assoc'])) {
		add_settings_error('ml_options', 'media-libraries', __('Amazon WDSL requires an <b>Amazon Associates ID</b>!', 'media-libraries'));
	}
	return $valid;
}

/**
 * checks if required options (aws keys) need to be set
 *
 * @return bool true if necessary options are valid
 */
function ml_amazon_check ($pass) {
	if ($pass == true) {
		$aws_key = ml_get_option('ml_amz_key');
		$aws_secret = ml_get_option('ml_amz_secret');
		$aws_associate = ml_get_option('ml_amz_assoc');

		$pass = (!(empty($aws_key) || empty($aws_secret) || empty($aws_associate)));
	}
	return $pass;
}

/**
 * register amazon options with WP settings api
 */
function ml_amazon_init_options() {
	add_settings_section('ml_options_amazon', __('Amazon Settings', 'media-libraries'), 'ml_options_amazon', 'ml_options');

	// Amazon field definitions
	add_settings_field('ml_amz_key', __('Amazon Web Services Access Key ID', 'media-libraries'), 'ml_amz_key_field', 'ml_options', 'ml_options_amazon');
	add_settings_field('ml_amz_secret', __('Amazon Web Services Secret Access Key', 'media-libraries'), 'ml_amz_secret_field', 'ml_options', 'ml_options_amazon');
	add_settings_field('ml_amz_assoc', __('Your Amazon Associates ID', 'media-libraries'), 'ml_amz_assoc_field', 'ml_options', 'ml_options_amazon');
	add_settings_field('ml_amz_domain', __('Amazon domain to use', 'media-libraries'), 'ml_amz_domain_field', 'ml_options', 'ml_options_amazon');
	add_settings_field('ml_amz_image', __('Image size to use', 'media-libraries'), 'ml_amz_image_field', 'ml_options', 'ml_options_display');
}

function ml_amazon_init() {
	add_filter('ml-check-init', 'ml_amazon_check');
	add_filter('ml-default-options', 'ml_amazon_defaults');
	add_filter('ml-options-validate', 'ml_amazon_validate', 10, 2);
	add_filter('ml-do-search', array('ml_amazon', 'search)', 10, 3));
}

add_action('ml-options-init', 'ml_amazon_init_options');
add_action('init', 'ml_amazon_init');