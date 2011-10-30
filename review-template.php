<?php
/**
 * template functions for review pages
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * wrapper to template hack for archive-ml_review
 *
 * @param string found template
 * @return string path to template
 */
function ml_review_archive_template ($template) {
	return ml_insert_template ($template, 'ml_review', 'archive');
}

/**
 * wrapper to template hack for single-ml_review
 *
 * @param string found template
 * @return string path to template
 */
function ml_review_single_template ($template) {
	return ml_insert_template ($template, 'ml_review', 'single');
}

/**
 * wrapper to template hack for taxonomy-ml_tag
 *
 * @param string found template
 * @return string path to template
 */
function ml_tags_taxonomy_template ($template) {
	return ml_insert_template ($template, 'ml_tag', 'taxonomy');
}

/**
 * display star ratings
 *
 * @param string stored rating
 * @param bool read only (if true) or editable (if false)
 * @param bool use half ratings (if true) or only whole ratings (if false)
 * @param bool echo (if true) or return (if false)
 * @return string html if echo is false
 * @todo make $split a plugin option
 */
function ml_review_stars ($check='', $readonly=false, $split=true, $echo=true) {
	$values = ($split) ? array('0.5', '1', '1.5', '2', '2.5', '3', '3.5', '4', '4.5', '5') : array('1', '2', '3', '4', '5');
	$disabled = disabled($readonly, true, false);
	$split = ($split) ? ' {split:2}' : '';

	$return = '<div id="ml_rating">';
	foreach ($values as $val) {
		$checked = checked($check, $val, false);
		$return .= '<input name="ml_rating" type="radio" value="' . $val . '"' . $checked . $disabled . ' class="star' . $split . '" />';
	}
	$return .=  '</div><br /><br />';

	if ($echo) {
		echo $return;
	}
	else {
		return $return;
	}
}

/**
 * Retrieve add review link for product.
 *
 * @param int $id Optional. Product ID.
 * @param string $context Optional, default to display. How to write the '&', defaults to '&amp;'.
 * @return string
 */
function add_review_link ($id=0, $context='display') {
	if (!$product = get_post($id)) {
		return;
	}

	if ('display' == $context) {
		$action = '&amp;action=edit&amp;post_parent=';
	}
	else {
		$action = '&action=edit&post_parent=';
	}

	$post_type_object = get_post_type_object('ml_review');
	if (!$post_type_object) {
		return;
	}

	if (!current_user_can($post_type_object->cap->edit_posts)) {
		return;
	}

	$args = array('post_type' => 'ml_review', 'post_parent' => $product->ID, 'post_author' => wp_get_current_user());
	$reviews = get_posts($args);

	if (!empty($reviews)) {
		return;
	}

	return apply_filters('get_add_review_link', admin_url('post.php?post_type=ml_review' . $action . $product->ID), $context);
}

/**
 * Retrieve the author of the current review.
 *
 * If the review has an empty review_author field, then 'Anonymous' person is
 * assumed.
 *
 * @param int $review_ID The ID of the review for which to retrieve the author. Optional.
 * @return string The review author
 */
function get_review_author ($review_ID=0) {
	$review = get_post($review_ID);
	$author = get_userdata($review->post_author);

	return apply_filters('the_author', is_object($author) ? $author->display_name : null);
}

/**
 * Displays the author of the current review.
 *
 * @param int $review_ID The ID of the review for which to print the author. Optional.
 */
function review_author ($review_ID=0) {
	$author = apply_filters('review_author', get_review_author($review_ID));
	echo $author;
}

/**
 * Returns the classes for the review div as an array
 *
 * @param string|array $class One or more classes to add to the class list
 * @param int $review_id An optional review ID
 * @param int $product_id An optional product ID
 * @return array Array of classes
 */
function get_review_class ($class='', $review_id=null, $product_id=null) {
	global $review_alt;

	$review = get_post($review_id);
	$product_id = (empty($product_id)) ? $product_id : $review->post_parent;
	$product = get_post($product_id);
	$classes = array('review');

	if (empty($review_alt)) {
		$review_alt = 0;
	}

	if ($review_alt % 2) {
		$classes[] = 'odd';
		$classes[] = 'alt';
	}
	else {
		$classes[] = 'even';
	}

	if (in_array($review->ID, get_post_meta($product->ID, 'ml_official_review'))) {
		$classes[] = 'official';
	}

	if ($user = get_userdata($review->post_author)) {
		$classes[] = 'byuser';
		$classes[] = 'review-author-' . sanitize_html_class($user->user_nicename, $review->post_author);
	}

	if (!empty($class)) {
		if (!is_array($class)) {
			$class = preg_split('#\s+#', $class);
		}
		$classes = array_merge($classes, $class);
	}

	$classes = array_map('esc_attr', $classes);
	$review_alt++;

	return apply_filters('review_class', $classes, $class, $review_id, $product_id);
}

/**
 * Generates semantic classes for each review element
 *
 * @param string|array $class One or more classes to add to the class list
 * @param int $review_id An optional review ID
 * @param int $product_id An optional product ID
 * @param bool $echo Whether review_class should echo or return
 */
function review_class ($class='', $review_id=null, $product_id=null, $echo=true) {
	// Separates classes with a single space, collates classes for review DIV
	$class = ' class="' . join(' ', get_review_class($class, $review_id, $product_id)) . '"';
	if ($echo) {
		echo $class;
	}
	else {
		return $class;
	}
}

/**
 * Retrieve the review date of the current review.
 *
 * @param string $d The format of the date (defaults to user's config)
 * @param int $review_ID The ID of the review for which to get the date. Optional.
 * @return string The review's date
 */
function get_review_date ($d='', $review_ID=0) {
	$review = get_post($review_ID);
	if ('' == $d) {
		$date = mysql2date(get_option('date_format'), $review->post_date);
	}
	else {
		$date = mysql2date($d, $review->post_date);
	}

	return apply_filters('get_the_date', $date, $d);
}

/**
 * Display the review date of the current review.
 *
 * @param string $d The format of the date (defaults to user's config)
 * @param int $review_ID The ID of the review for which to print the date.  Optional.
 */
function review_date ($d='', $review_ID=0) {
	echo get_review_date($d, $review_ID);
}

/**
 * Retrieve the link to a given review.
 *
 * @param object|string|int $review Review to retrieve.
 * @param array $args Optional args.
 * @return string The permalink to the given review.
 */
function get_review_link ($review=null) {
	$review = get_post($review);
	$link = get_permalink($review->ID);

	return apply_filters('get_review_link', $link, $review);
}

/**
 * Retrieve the text of the current review.
 *
 * @param int $review_ID The ID of the review for which to get the text. Optional.
 * @return string The review content
 */
function get_review_text ($review_ID=0) {
	$review =& get_post($review_ID);
	return apply_filters('get_review_text', $review->post_content, $review);
}

/**
 * Displays the text of the current review.
 *
 * @param int $review_ID The ID of the review for which to print the text. Optional.
 */
function review_text ($review_ID=0) {
	$content = get_review_text($review_ID);
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	echo $content;
}

/**
 * Display the review time of the current review.
 *
 * @param string $d Optional. The format of the time (defaults to user's config)
 */
function review_time ($d='', $review_ID=0) {
	echo get_the_time($d, $review_ID);
}

/**
 * Retrieve the review type of the current review.
 *
 * @param int $review_ID The ID of the review for which to get the type. Optional.
 * @return string The review type
 */
function get_review_type ($review_ID=0) {
	$review = get_post($review_ID);
	$review->review_type = (in_array($review->ID, get_post_meta($review->post_parent, 'ml_official_review'))) ? 'official' : 'review';

	return apply_filters('get_review_type', $review->review_type);
}

/**
 * Display the review type of the current review.
 *
 * @param string $reviewtxt The string to display for review type
 * @param string $officialtxt The string to display for official review type
 */
function review_type($reviewtxt=false, $officialtxt=false) {
	$reviewtxt = (false === $reviewtxt) ? _x('Review', 'noun', 'media-libraries') : $reviewtxt;
	$trackbacktxt = (false === $officialtxt) ? __('Official Review', 'media-libraries') : $officialtxt;
	$type = get_review_type();
	switch( $type ) {
		case 'official':
			echo $officialtxt;
			break;
		default :
			echo $reviewtxt;
	}
}

/**
 * Calculate the total number of review pages.
 *
 * @param array $reviews Optional array of review objects.
 * @param int $per_page Optional reviews per page.
 * @return int Number of review pages.
 */
function get_review_pages_count ($reviews=null, $per_page=null) {
	if (!$reviews || !is_array($reviews)) {
		$args = array('post_type' => 'ml_review', 'post_parent' => get_the_ID(), 'numberposts' => -1, 'exclude' => get_post_meta(get_the_ID(), 'ml_official_review'));
		$reviews = get_posts($args);
	}

	if (empty($reviews)) {
		return 0;
	}

	if (!isset($per_page) || 0 === $per_page) {
		$per_page = (int) get_option('comments_per_page');
	}

	if (0 === $per_page) {
		return 1;
	}

	$count = ceil(count($reviews)/$per_page);
	return $count;
}

/**
 * HTML review list class.
 */
class Walker_Review extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @var string
	 */
	public $tree_type = 'review';

	/**
	 * @see Walker::$db_fields
	 * @var array
	 */
	public $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

	/**
	 * @see Walker::start_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param array $args Uses 'style' argument for type of HTML list.
	 */
	function start_lvl(&$output, $depth=0, $args=array()) {
		switch ( $args['style'] ) {
			case 'div':
				break;
			case 'ol':
				echo "<ol class='children'>\n";
				break;
			default:
			case 'ul':
				echo "<ul class='children'>\n";
				break;
		}
	}

	/**
	 * @see Walker::end_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param array $args Will only append content if style argument value is 'ol' or 'ul'.
	 */
	function end_lvl(&$output, $depth=0, $args=array()) {
		switch ( $args['style'] ) {
			case 'div':
				break;
			case 'ol':
				echo "</ol>\n";
				break;
			default:
			case 'ul':
				echo "</ul>\n";
				break;
		}
	}

	/**
	 * @see Walker::start_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $review Review data object.
	 * @param array $args
	 */
	function start_el(&$output, $review, $depth=0, $args=array()) {
		if ( !empty($args['callback']) ) {
			call_user_func($args['callback'], $review, $args);
			return;
		}

		$GLOBALS['review'] = $review;
		extract($args, EXTR_SKIP);

		if ( 'div' == $args['style'] ) {
			$tag = 'div';
			$add_below = 'review';
		} else {
			$tag = 'li';
			$add_below = 'div-review';
		}

		echo '<' . $tag  . review_class('', $review->ID, $review->post_parent, false) . ' id="review-' . $review->ID . '">' . "\n";
		if ('div' != $args['style']) {
			echo '<div id="div-review-' . $review->ID . '" class="review-body">' . "\n";
		}
		echo '<div class="review-author vcard">' . "\n";
		if ($args['avatar_size'] != 0) {
			echo get_avatar($review->post_author, $args['avatar_size']) . "\n";
		}
		printf(__('<cite class="fn">%s</cite> <span class="says">writes:</span>'), get_user_library_url($review->post_author));
		echo "\n" . '</div>' . "\n";

		echo '<div class="review-meta reviewmetadata"><a href="' . htmlspecialchars(get_review_link($review->ID)) . '">' . "\n";
		/* translators: 1: date, 2: time */
		printf(__('%1$s at %2$s'), get_review_date(), get_the_time('', $review->ID));
		echo '</a>';
		edit_post_link(__('(Edit)'),'&nbsp;&nbsp;','', $review->ID);
		echo '</div>' . "\n";

		review_text($review->ID);

		if ('div' != $args['style']) {
		echo '</div>' . "\n";
		}
	}

	/**
	 * @see Walker::end_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $review
	 * @param array $args
	 */
	function end_el(&$output, $review, $depth=0, $args=array()) {
		if ( !empty($args['end-callback']) ) {
			call_user_func($args['end-callback'], $review, $args);
			return;
		}
		if ( 'div' == $args['style'] )
			echo "</div>\n";
		else
			echo "</li>\n";
	}
}

/**
 * List reviews
 *
 * Used in the ml_product.php template to list reviews for a particular product
 *
 * @param string|array $args Formatting options
 * @param array $reviews Optional array of review objects.  Defaults to $wp_query->reviews
 */
function ml_list_reviews ($args=array(), $reviews=null) {
	global $wp_query, $review_alt, $overridden_cpage;

	$review_alt = 0;

	$defaults = array('walker' => null, 'max_depth' => '', 'style' => 'ul', 'callback' => null, 'end-callback' => null, 'type' => 'all', 'page' => '', 'per_page' => '', 'avatar_size' => 32, 'reverse_top_level' => null);

	$r = wp_parse_args($args, $defaults);

	// Figure out what reviews we'll be looping through ($_reviews)
	if ( null !== $reviews ) {
		$reviews = (array) $reviews;
		if (empty($reviews)) {
			return;
		}
	}
	else {
		$r_args = array('post_type' => 'ml_review', 'post_parent' => get_the_ID());
		$reviews = get_posts($r_args);
	}

	if ('all' != $r['type']) {
		$reviews_by_type = &separate_reviews($reviews);
		if (empty($reviews_by_type[$r['type']])) {
			return;
		}
		$_reviews = $reviews_by_type[$r['type']];
	} else {
		$_reviews = $reviews;
	}

	if ('' === $r['per_page'] && get_option('page_reviews')) {
		$r['per_page'] = get_query_var('reviews_per_page');
	}

	if (empty($r['per_page'])) {
		$r['per_page'] = 0;
		$r['page'] = 0;
	}

	if ('' === $r['page']) {
		if (empty($overridden_cpage)) {
			$r['page'] = get_query_var('cpage');
		}
		else {
			$r['page'] = ('newest' == get_option('default_comments_page')) ? get_comment_pages_count($_reviews, $r['per_page'], false) : 1;
			set_query_var('cpage', $r['page']);
		}
	}

	// Validation check
	$r['page'] = intval($r['page']);
	if (0 == $r['page'] && 0 != $r['per_page']) {
		$r['page'] = 1;
	}

	if (null === $r['reverse_top_level']) {
		$r['reverse_top_level'] = ('desc' == get_option('review_order'));
	}

	extract($r, EXTR_SKIP);

	if (empty($walker)) {
		$walker = new Walker_Review;
	}

	$walker->paged_walk($_reviews, -1, $r['page'], $r['per_page'], $r);
	$wp_query->max_num_review_pages = $walker->max_pages;
}

/**
 * Separates an array of reviews into an array keyed by review_type.
 *
 * @param array $reviews Array of reviews
 * @return array Array of reviews keyed by review_type.
 */
function &separate_reviews (&$reviews) {
	$reviews_by_type = array('review' => array(), 'official' => array());
	$count = count($reviews);
	for ($i = 0; $i < $count; $i++) {
		$type = get_review_type($reviews[$i]->ID);
		if (empty($type)) {
			$type = 'review';
		}
		$reviews_by_type[$type][] = &$reviews[$i];
	}

	return $reviews_by_type;
}
