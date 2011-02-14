<?php
/**
 * Wrapper for Amazon ECS library
 * @package amazon-library
 */

require_once dirname(__FILE__) . '/lib/AmazonECS.class.php';

class aml_amazon {

	public static $domains = array(
		'US' => 'United States',
		'UK' => 'United Kingdom',
		'DE' => 'Germany',
		'JP' => 'Japan',
		'FR' => 'France',
		'CA' => 'Canada',
	);

	public static $categories = array(
		'Books',
		'DVD',
		'Music',
		'VideoGames',
	);

	public static $error = '<div class="">%s</div>';

	public static $blank_image = '';

	public static $image_base = 'http://ecx.images-amazon.com/images/I/';

	public static function get() {
		static $amazon;

		if (empty($amazon)) {
			$options = get_option('aml_options');
			if (!empty($options['aml_amazon_id']) && !empty($options['aml_secret_key'])) {
				$country = (empty($options['aml_domain'])) ? 'US' : strtoupper($options['aml_domain']);
				$amazon = new AmazonECS($options['aml_amazon_id'], $options['aml_secret_key'], $country, $options['aml_associate']);
			}
			else {
				$amazon = false;
			}
		}
		return $amazon;
	}

	public static function search ($search, $type='Books', $page=1) {
		$amazon = self::get();
		if (!$amazon) {
			return __('Error loading Amazon ECS library', 'amazon-library');
		}

		$ret = '';
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
				$ret .= __('Nothing found for the search query', 'amazon-library');
			}
		}
		return $ret;
	}

	public static function lookup ($asin) {
		$amazon = self::get();
		if (!$amazon) {
			return __('Error loading Amazon ECS library', 'amazon-library');
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

	public static function parse ($item) {
		$ret = '';

		$ret .= '<div class="aml-item-details">';
		$ret .= '<div class="aml-item-title">' . $item->ItemAttributes->Title . '</div>';

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
			array_walk($people, 'aml_clean_name');
			$ret .= '<div class="aml-item-people">' . __('People', 'amazon-library') . ': <span class="aml-item-people-names">' . implode(', ', $people) . '</span></div>';
			}

		$ret .= '<div class="aml-item-asin">ASIN: <span class="aml-item-asin-number">' . $item->ASIN . '</span></div>';
		$ret .= '<div class="aml-item-link"><a href="' . $item->DetailPageURL . '">Details</a></div>';
		$ret .= '<div id="' . $item->ASIN . '" class="aml-item">' . __('Use this item', 'amazon-library') . '</div>';
		$ret .= '</div>';

		$image = (isset($item->MediumImage)) ? $item->MediumImage->URL : ((isset($item->SmallImage)) ? $item->SmallImage->URL : ((isset($item->LargeImage)) ? $item->LargeImage->URL : self::$blank_image));
		if (!empty($image)) {
			$ret .= '<div class="aml-item-image"><img src="' . $image . '" /></div>';
		}

		return '<div id="aml-'.$item->ASIN.'" class="aml-list-item">' . $ret . '</div>' . "\n";
	}

	function strip_image ($url) {
		return str_replace(array(self::$image_base, '.jpg', '._SL75_', '._SL160'), '', $image);
	}

	function build_image ($image, $size='med') {
		if (0 == strpos('http', $image)) {
			return $image;
		}

		switch ($size) {
			case 'lg':
				$post = '160';
				break;
			case 'sm':
				$post = '75';
				break;
			case 'med':
			default:
				$post = '110';
				break;
		}
		return self::$image_base . $image . '._SL' . $post . '_.jpg';
	}
}

// strips commas from name
function aml_clean_name (&$item, $key='') {
	str_replace(array(',', '  '), array('', ' '), $item);
}

// handle js callbacks
function aml_ajax_callback() {
	// validate posted data
	$search = (isset($_POST['search'])) ? $_POST['search'] : '';
	$type = (isset($_POST['type'])) ? $_POST['type'] : '';
	$lookup = (isset($_POST['lookup'])) ? $_POST['lookup'] : '';

	// run amazon query
	$ret = (!empty($lookup)) ? aml_amazon::lookup($lookup) : aml_amazon::search($search, $type);

	//return results
	echo $ret;
	die;
}

add_action('wp_ajax_aml_amazon_search', 'aml_ajax_callback');
//add_action('wp_ajax_aml_amazon_lookup', 'aml_ajax_callback')
