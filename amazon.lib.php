<?php
/**
 * Amazon wrapper
 * @package now-reading
 * $Rev$
 * $Date$
 */

require_once dirname(__FILE__) . '/lib/AmazonECS.class.php';

class aml_amazon {

	var $domains = array(
			'us' => 'United States',
			'uk' => 'United Kingdom',
		);

	var $error = '<div class="">%s</div>';

	var $blank_image = '';

	var $image_base = 'http://ecx.images-amazon.com/images/I/';

	function get() {
		static $amazon;

		if (empty($amazon)) {
			$options = get_option('now_reading_options');
			if (!empty($options['aml_amazon_id']) && !empty($options['aml_secret_key'])) {
				$country = (empty($options['aml_domain'])) ? 'US' : $options['aml_domain'];
				$amazon = new AmazonECS($options['aml_amazon_id'], $options['aml_secret_key'], $country, $options['aml_associate']);
			}
			else {
				$amazon = false;
			}
		}
		return $amazon;
	}

	function search ($search, $type='Books') {
		$amazon = self::get();
		$ret = '';
		try {
			$response = $amazon->category($type)->responseGroup('Small,Images')->search($search);
		}
		catch(Exception $e) {
			$ret = sprintf(self::$error, $e->getMessage());
		}

		if (is_object($response)) {
			if ('True' == $response->Items->Request->IsValid &&  intval($response->Items->TotalResults) > 0) {
				foreach ($response->Items->Item as $result) {
					$ret .= self::parse($result);
				}
			}
		}
		return $ret;
	}

	function lookup ($asin) {
		$amazon = self::get();
		$ret = '';
		try {
			$response = $amazon->responseGroup('Small,Images')->lookup($asin);
		}
		catch(Exception $e) {
			$ret = sprintf(self::$error, $e->getMessage());
		}

		if (is_object($response)) {
			if ('True' == $response->Items->Request->IsValid && isset($response->Items->Item)) {
				$ret = self::parse($response->Items->Item);
			}
		}
		return $ret;
	}

	function parse ($item) {
		$ret = '';

		$image = (isset($item->MediumImage)) ? $item->MediumImage->URL : ((isset($item->SmallImage)) ? $item->SmallImage->URL : ((isset($item->LargeImage)) ? $item->LargeImage->URL : self::$blank_image));
		if (isset($image)) {
			$ret .= '<div class="amz-item-title"><img src="' . $image . '" /></div>';
			//echo '<p>The base image file is ' . str_replace(array(self::$image_base, '.jpg', '._SL75_', '._SL160'), '', $image) . '</p>';
		}

		$ret .= '<div class="amz-item-details"><div class="amz-item-title">' . $item->ItemAttributes->Title . '</div>';
		$author = (is_array($item->ItemAttributes->Author)) ? implode('; ', $item->ItemAttributes->Author) : $item->ItemAttributes->Author;
		if (!empty($author)) {
			$ret .= '<div class="amz-item-author">Author: ' . $author . '</div>';
		}

		if (isset($item->ItemAttributes->Creator)) {
			if (is_array($item->ItemAttributes->Creator)) {
				foreach ($item->ItemAttributes->Creator as $extra_name) {
					$ret .= '<div class="amz-item-author">' . $extra_name->Role . ': ' . $extra_name->_ . '</p>';
					${$extra_name->Role} = $extra_name->_;
				}
			}
			else {
				$ret .= '<div class="amz-item-author">' . $item->ItemAttributes->Creator->Role . ': ' . $item->ItemAttributes->Creator->_ . '</p>';
				${$item->ItemAttributes->Creator->Role} = $item->ItemAttributes->Creator->_;
			}
		}

		$ret .= '<div class="amz-item-asin">ASIN: ' . $item->ASIN . '</div>';
		$ret .= '<div class="amz-item-link"><a href="' . $item->DetailPageURL . '">Details</a></div>';

		return '<div class="amz-list-item">' . $ret . '</div>';
	}
}
