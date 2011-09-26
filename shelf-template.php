<?php
/**
 * template functions for shelves
 * @package media-libraries
 */

/**
 * Wrapper to template hack for archive-ml_shelf
 *
 * @param string found template
 * @return string path to template
 */
function ml_shelf_archive_template ($template) {
	return ml_insert_template ($template, 'ml_shelf', 'archive');
}

/**
 * Wrapper to template hack for single-ml_shelf
 *
 * @param string found template
 * @return string path to template
 */
function ml_shelf_single_template ($template) {
	return ml_insert_template ($template, 'ml_shelf', 'single');
}

/**
 * Display a thumbnail page of products
 */
function ml_shelf_page ($products, $format='t', $page=1, $max_pages=1) {
	$function = ($format == 'l') ? 'product_shelf_row' : 'product_shelf_image';
	$paginate = '<div class="aml-paginate">';
	$paginate .= ($page < 1) ? '' : '<div class="aml-paginate-prev">' . __('Previous page', 'media-libraries') . '</div>';
	$paginate .= ($page >= $max_pages) ? '' : '<div class="aml-paginate-next">' . __('Next page', 'media-libraries') . '</div>';
	$paginate .= '</div>';

	$html = '';
	if (is_array($products)) {
		foreach ($products as $prod) {
			$html .= $function($prod, '<li class="ml_product">', '</li>');
		}
	}
	return (empty($html)) ? __('No products found on the self.', 'media-libraries') : '<ul>' . $html . $paginate . '</ul>';
}
