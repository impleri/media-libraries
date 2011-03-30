<?php
/**
 * wrapper for AmazonECS library
 * @package amazon-library
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * AML Amazon Wrapper
 *
 * Wrapper class to the AmazonECS library. Acts as a simple middle layer between AML and AmazonECS.
 * @static
 */
class aml_amazon {

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
		'Books',
		'DVD',
		'Music',
		'VideoGames',
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
	 * Gets and holds a single AmazonECS instance
	 * @return object AmazonECS object
	 */
	public static function &get() {
		static $amazon;

		if (empty($amazon)) {
			if (!class_exists('AmazonECS')) {
				require dirname(__FILE__) . '/lib/AmazonECS.class.php';
			}
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

	/**
	 * Amazon item search
	 *
	 * searches Amazon for items matching the set descriptions
	 * @param string search terms
	 * @param string Amazon category/product type (see aml_amazon::$categories for accepted terms)
	 * @param int page of results to return
	 * @return string parsed html from aml_amazon::parse
	 */
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

	/**
	 * Amazon item lookup
	 *
	 * looks up an item on Amazon by asin/isbn
	 * @param string asin/isbn number
	 * @return string parsed html from aml_amazon::parse for echo
	 */
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

	/**
	 * Item parser
	 *
	 * parse an result item object into an html listing
	 * @param object SimpleXML item node from AmazonECS response
	 * @return string formatted html
	 * @todo port out formatting to template
	 */
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
			array_walk($people, array(self, 'clean_name'));
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

	/**
	 * Amazon image url destructor
	 *
	 * reduces a url for an Amazon image to the name hash (for dynamic sizing of images)
	 * @param string url
	 * @return string image name hash
	 * @todo implement in aml_amazon::parse
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
	 * Name cleaner
	 *
	 * Strips commas from name for compatibility with taxonomy entry
	 * @param string (value) name to clean
	 * @param string (key) unused
	 * @return string cleaned name
	 */
	private static function clean_name (&$name, $key='') {
		str_replace(array(',', '  '), array('', ' '), $name);
	}
}
