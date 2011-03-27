<?php
/**
 * Template functions for shelves
 * @package amazon-library
 */

/**
 * Wrapper to template hack for archive-aml_shelf
 *
 * @param string found template
 * @return string path to template
 */
function aml_shelf_archive_template ($template) {
	return aml_insert_type_template ($template, 'aml_shelf', 'archive');
}

/**
 * Wrapper to template hack for single-aml_shelf
 *
 * @param string found template
 * @return string path to template
 */
function aml_shelf_single_template ($template) {
	return aml_insert_type_template ($template, 'aml_shelf', 'single');
}

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
