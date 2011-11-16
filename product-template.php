<?php
/**
 * template functions for products
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * wrapper to template hack for archive-ml_product
 *
 * @param string found template
 * @return string path to template
 */
function ml_product_archive_template ($template) {
	return ml_insert_template ($template, 'ml_product', 'archive');
}

/**
 * wrapper to template hack for single-ml_product
 *
 * @param string found template
 * @return string path to template
 */
function ml_product_single_template ($template) {
	return ml_insert_template ($template, 'ml_product', 'single');
}

/**
 * wrapper to template hack for taxonomy-ml_person
 *
 * @param string found template
 * @return string path to template
 */
function ml_person_taxonomy_template ($template) {
	return ml_insert_template ($template, 'ml_person', 'taxonomy');
}

/**
 * Retrieve product image
 *
 * Get the product image URL if it exists
 *
 * @param int $id Optional. Post ID.
 * @return string
 */
function get_the_product_image ($id=0) {
	$post = get_post($id);

	$id = ($post->post_parent > 0) ? $post->post_parent : $post->ID;
	$image = get_post_meta($id, 'ml_image', true);

	return apply_filters('the_image', $image, $id);
}

/**
 * Display or retrieve the current product image with optional content.
 *
 * @param string $before Optional. Content to prepend to the image URL.
 * @param string $after Optional. Content to append to the image URL.
 * @param bool $echo Optional, default to true. Whether to display or return.
 * @return null|string Null on no image. String if $echo parameter is false.
 */
function the_product_image ($before='', $after='', $echo=true) {
	$image = get_the_product_image();

	if (strlen($image)==0) {
		return;
	}

	$image = $before . $image . $after;

	if ($echo) {
		echo $image;
	}
	else {
		return $image;
	}
}

/**
 * Retrieve the people for a post.
 *
 * @uses apply_filters() Calls 'get_the_people' filter on the list of people tags.
 *
 * @param int $id Post ID.
 * @return array
 */
function get_the_people ($id=0) {
	$post = get_post($id);
	$id = ($post->post_parent > 0) ? $post->post_parent : $post->ID;
	return apply_filters( 'get_the_people', get_the_terms($id, 'ml_person'));
}

/**
 * Retrieve the people for a product formatted as a string.
 *
 * @uses apply_filters() Calls 'the_people' filter on string list of people.
 *
 * @param string $before Optional. Before people.
 * @param string $sep Optional. Between people.
 * @param string $after Optional. After people.
 * @return string
 */
function get_the_people_list ($id=0, $before='', $sep='', $after='') {
	$people = get_the_people($id);

	if (is_wp_error($people)) {
		return $people;
	}

	if (empty($people)) {
		return false;
	}

	foreach ( $people as $term ) {
		$link = get_term_link($term, 'ml_person');
		if (is_wp_error($link)) {
			return $link;
		}
		$people_links[] = '<a href="' . $link . '" rel="tag">' . $term->name . '</a>';
	}

	$people_links = apply_filters('term_links-ml-person', $people_links);

	return $before . join($sep, $people_links) . $after;
}

/**
 * Retrieve the people for a product.
 *
 * @param int $id Post ID.
 * @param string $before Optional. Before list.
 * @param string $sep Optional. Separate items using this.
 * @param string $after Optional. After list.
 * @return string
 */
function the_people ($id=0, $before=null, $sep=', ',$after = '') {
		$people = get_the_people_list($id, $before, $sep, $after);

	if (is_wp_error($people)) {
		return false;
	}

	echo apply_filters('the_people', $people, $before, $sep, $after);
}

/**
 * Retrieve product type
 *
 * Get the product type
 *
 * @param int $id Optional. Post ID.
 * @return string
 */
function get_the_product_type ($id=0) {
	$post = get_post($id);
	$id = ($post->post_parent > 0) ? $post->post_parent : $post->ID;
	$type = get_post_meta($id, 'ml_type', true);
	$type = isset($type) ? $type : 'b';
	$types = ml_product_categories();

	return apply_filters('the_product_type', $types[$type]);
}

/**
 * Display or retrieve the current product type with optional content.
 *
 * @param string $before Optional. Content to prepend to the type.
 * @param string $after Optional. Content to append to the type.
 * @param bool $echo Optional, default to true. Whether to display or return.
 * @return null|string Null on no type. String if $echo parameter is false.
 */
function the_product_type ($before='', $after='', $echo=true) {
	$type = get_the_product_type();

	if (strlen($type)==0) {
		return;
	}

	$type = $before . $type . $after;

	if ($echo) {
		echo $type;
	}
	else {
		return $type;
	}
}

/**
 * Retrieve product type
 *
 * Get the product type
 *
 * @param int $id Optional. Post ID.
 * @return string
 */
function get_the_product_title ($id=0) {
	$post = get_post($id);
	if ($post->post_parent > 0) {
		$parent = get_post($post->post_parent);
		$title = $parent->post_title;
	}
	else {
		$title = $post->post_title;
	}

	return apply_filters('the_title', $title);
}

/**
 * Display or retrieve the current product type with optional content.
 *
 * @param string $before Optional. Content to prepend to the type.
 * @param string $after Optional. Content to append to the type.
 * @param bool $echo Optional, default to true. Whether to display or return.
 * @return null|string Null on no type. String if $echo parameter is false.
 */
function the_product_title ($before='', $after='', $echo=true) {
	$type = get_the_product_title();

	if (strlen($type)==0) {
		return;
	}

	$type = $before . $type . $after;

	if ($echo) {
		echo $type;
	}
	else {
		return $type;
	}
}

/**
 * Retrieve product link
 *
 * Get the product source URL if it exists
 *
 * @param int $id Optional. Post ID.
 * @return string
 */
function get_the_product_link ($id=0) {
	$post = get_post($id);

	$id = ($post->post_parent > 0) ? $post->post_parent : $post->ID;
	$link = get_post_meta($id, 'ml_link', true);

	return apply_filters('the_product_link', $link, $id);
}

/**
 * Display or retrieve the current product link with optional content.
 *
 * @param string $before Optional. Content to prepend to the source URL.
 * @param string $after Optional. Content to append to the source URL.
 * @param bool $echo Optional, default to true. Whether to display or return.
 * @return null|string Null on no image. String if $echo parameter is false.
 */
function the_product_link ($before='', $after='', $echo=true) {
	$link = get_the_product_link();

	if (strlen($link)==0) {
		return;
	}

	$link = $before . $link . $after;

	if ($echo) {
		echo $link;
	}
	else {
		return $link;
	}
}

/**
 * Retrieve the amount of reviews a post has.
 *
 * @param int $product_id The Product ID
 * @return int The number of reviews a post has
 */
function get_reviews_number ($product_id=0, $type='all') {
	$product_id = absint($product_id);

	if (!$product_id) {
		$id = get_the_ID();
	}

	$post = get_post($id);
	$product_id = ($post->post_parent > 0) ? $post->post_parent : $post->ID;

	$args = array('post_type' => 'ml_review', 'post_parent' => $product_id);

	if ($type != 'all') {
		$official = get_post_meta($product_id, 'ml_official_review');

		if ($type == 'official') {
			return count($official);
		}

		$args['exclude'] = $official;
	}

	$reviews = get_posts($args);
	$count = count($reviews);

	return apply_filters('get_reviews_number', $count, $product_id);
}

/**
 * Display the language string for the number of reviews the current post has.
 *
 * @param string $zero Text for no reviews
 * @param string $one Text for one review
 * @param string $more Text for more than one review
 */
function reviews_number ($zero=false, $one=false, $more=false) {
	$number = get_reviews_number();

	if ($number > 1) {
		$output = str_replace('%', number_format_i18n($number), ( false === $more ) ? __('% Reviews', 'media-libraries') : $more);
	}
	elseif ($number == 0) {
		$output = ( false === $zero ) ? __('No Reviews', 'media-libraries') : $zero;
	}
	else {
		$output = ( false === $one ) ? __('1 Review', 'media-libraries') : $one;
	}

	echo apply_filters('reviews_number', $output, $number);
}

/**
 * Quickly tell if a product has reviews
 *
 * @param int product ID (defaults to get_the_ID())
 * @return bool true if reviews exist for the product
 */
function has_reviews ($product_ID=0, $type='all') {
	return (get_reviews_number($product_ID, $type) > 0);
}

/**
 * Retrieve page numbers links.
 *
 * @param int $pagenum Optional. Page number.
 * @return string
 */
function get_reviews_pagenum_link ($pagenum=1, $max_page=0) {
	global $post, $wp_rewrite;

	$pagenum = (int) $pagenum;

	$result = get_permalink($post->ID);

	if ('newest' == get_option('default_comments_page')) {
		if ($pagenum != $max_page) {
			if ($wp_rewrite->using_permalinks()) {
				$result = user_trailingslashit( trailingslashit($result) . 'review-page-' . $pagenum, 'reviewpaged');
			}
			else {
				$result = add_query_arg('cpage', $pagenum, $result);
			}
		}
	}
	elseif ($pagenum > 1) {
		if ($wp_rewrite->using_permalinks()) {
			$result = user_trailingslashit( trailingslashit($result) . 'review-page-' . $pagenum, 'reviewpaged');
		}
		else {
			$result = add_query_arg( 'cpage', $pagenum, $result );
		}
	}

	$result .= '#reviews';
	$result = apply_filters('get_reviews_pagenum_link', $result);
	return $result;
}

/**
 * Return the link to next reviews pages.
 *
 * @param string $label Optional. Label for link text.
 * @param int $max_page Optional. Max page.
 * @return string|null
 */
function get_next_reviews_link ($label='', $max_page=0) {
	global $wp_query;

	if (!is_singular()) {
		return;
	}

	$page = get_query_var('cpage');
	$nextpage = intval($page) + 1;

	if (empty($max_page)) {
		$max_page = get_review_pages_count();
	}

	if ($nextpage >= $max_page) {
		return;
	}

	if (empty($label)) {
		$label = __('Newer Reviews &raquo;');
	}

	return '<a href="' . esc_url(get_reviews_pagenum_link($nextpage, $max_page)) . '" ' . apply_filters( 'next_reviews_link_attributes', '' ) . '>'. preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label) .'</a>';
}

/**
 * Display the link to next reviews pages.
 *
 * @since 2.7.0
 *
 * @param string $label Optional. Label for link text.
 * @param int $max_page Optional. Max page.
 */
function next_reviews_link( $label = '', $max_page = 0 ) {
	echo get_next_reviews_link( $label, $max_page );
}

/**
 * Return the previous reviews page link.
 *
 * @since 2.7.1
 *
 * @param string $label Optional. Label for reviews link text.
 * @return string|null
 */
function get_previous_reviews_link( $label = '' ) {
	if ( !is_singular() || !get_option('page_reviews') )
		return;

	$page = get_query_var('cpage');

	if ( intval($page) <= 1 )
		return;

	$prevpage = intval($page) - 1;

	if ( empty($label) )
		$label = __('&laquo; Older Comments');

	return '<a href="' . esc_url( get_reviews_pagenum_link( $prevpage ) ) . '" ' . apply_filters( 'previous_reviews_link_attributes', '' ) . '>' . preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label) .'</a>';
}

/**
 * Display the previous reviews page link.
 *
 * @since 2.7.0
 *
 * @param string $label Optional. Label for reviews link text.
 */
function previous_reviews_link( $label = '' ) {
	echo get_previous_reviews_link( $label );
}

// everything below is unstable


function product_shelf_listing() {

}

function the_product_usage () {}