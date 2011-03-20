<?php
/**
 * Template functions for shelves
 * @package amazon-library
 */

/**
 * Display a thumbnail page of products
 */
function aml_shelf_page ($products, $format='t', $page=1, $max_pages=1) {
	$function = ($format == 'l') ? 'product_shelf_row' : 'product_shelf_image';
	$paginate = '<div class="aml-paginate">';
	$paginate .= ($page < 1) ? '' : '<div class="aml-paginate-prev">' . __('Previous page', 'amazon-library') . '</div>';
	$paginate .= ($page >= $max_pages) ? '' : '<div class="aml-paginate-next">' . __('Next page', 'amazon-library') . '</div>';
	$paginate .= '</div>';

	$html = '';
	if (is_array($products)) {
		foreach ($products as $prod) {
			$html .= $function($prod, '<li class="aml_product">', '</li>');
		}
	}
	return (empty($html)) ? __('No products found on the self.', 'amazon-library') : '<ul>' . $html . $paginate . '</ul>';
}
