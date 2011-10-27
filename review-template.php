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
 */
function review_stars ($check='', $readonly=false, $split=true, $echo=true) {
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


// incomplete and subject to change

/**
 * Retrieve the author of the current review.
 *
 * If the review has an empty review_author field, then 'Anonymous' person is
 * assumed.
 *
 * @uses apply_filters() Calls 'get_review_author' hook on the review author
 *
 * @param int $review_ID The ID of the review for which to retrieve the author. Optional.
 * @return string The review author
 */
function get_review_author( $review_ID = 0 ) {
	$review = get_post( $review_ID );
	if ( empty($review->review_author) ) {
		if (!empty($review->user_id)){
			$user=get_userdata($review->user_id);
			$author=$user->user_login;
		} else {
			$author = __('Anonymous');
		}
	} else {
		$author = $review->review_author;
	}
	return apply_filters('get_review_author', $author);
}

/**
 * Displays the author of the current review.
 *
 * @uses apply_filters() Calls 'review_author' on review author before displaying
 *
 * @param int $review_ID The ID of the review for which to print the author. Optional.
 */
function review_author( $review_ID = 0 ) {
	$author = apply_filters('review_author', get_review_author( $review_ID ) );
	echo $author;
}

/**
 * Retrieve the html link to the url of the author of the current review.
 *
 * @since 1.5.0
 * @uses apply_filters() Calls 'get_review_author_link' hook on the complete link HTML or author
 *
 * @param int $review_ID The ID of the review for which to get the author's link.  Optional.
 * @return string Review Author name or HTML link for author's URL
 */
function get_review_author_link( $review_ID = 0 ) {
	/** @todo Only call these functions when they are needed. Include in if... else blocks */
	$url    = get_review_author_url( $review_ID );
	$author = get_review_author( $review_ID );

	if ( empty( $url ) || 'http://' == $url )
		$return = $author;
	else
		$return = "<a href='$url' rel='external nofollow' class='url'>$author</a>";
	return apply_filters('get_review_author_link', $return);
}

/**
 * Display the html link to the url of the author of the current review.
 *
 * @since 0.71
 * @see get_review_author_link() Echoes result
 *
 * @param int $review_ID The ID of the review for which to print the author's link. Optional.
 */
function review_author_link( $review_ID = 0 ) {
	echo get_review_author_link( $review_ID );
}

/**
 * Retrieve the url of the author of the current review.
 *
 * @since 1.5.0
 * @uses apply_filters() Calls 'get_review_author_url' hook on the review author's URL
 *
 * @param int $review_ID The ID of the review for which to get the author's URL.  Optional.
 * @return string
 */
function get_review_author_url( $review_ID = 0 ) {
	$review = get_review( $review_ID );
	$url = ('http://' == $review->review_author_url) ? '' : $review->review_author_url;
	$url = esc_url( $url, array('http', 'https') );
	return apply_filters('get_review_author_url', $url);
}

/**
 * Display the url of the author of the current review.
 *
 * @since 0.71
 * @uses apply_filters()
 * @uses get_review_author_url() Retrieves the review author's URL
 *
 * @param int $review_ID The ID of the review for which to print the author's URL. Optional.
 */
function review_author_url( $review_ID = 0 ) {
	echo apply_filters('review_url', get_review_author_url( $review_ID ));
}

/**
 * Retrieves the HTML link of the url of the author of the current review.
 *
 * $linktext parameter is only used if the URL does not exist for the review
 * author. If the URL does exist then the URL will be used and the $linktext
 * will be ignored.
 *
 * Encapsulate the HTML link between the $before and $after. So it will appear
 * in the order of $before, link, and finally $after.
 *
 * @since 1.5.0
 * @uses apply_filters() Calls the 'get_review_author_url_link' on the complete HTML before returning.
 *
 * @param string $linktext The text to display instead of the review author's email address
 * @param string $before The text or HTML to display before the email link.
 * @param string $after The text or HTML to display after the email link.
 * @return string The HTML link between the $before and $after parameters
 */
function get_review_author_url_link( $linktext = '', $before = '', $after = '' ) {
	$url = get_review_author_url();
	$display = ($linktext != '') ? $linktext : $url;
	$display = str_replace( 'http://www.', '', $display );
	$display = str_replace( 'http://', '', $display );
	if ( '/' == substr($display, -1) )
		$display = substr($display, 0, -1);
	$return = "$before<a href='$url' rel='external'>$display</a>$after";
	return apply_filters('get_review_author_url_link', $return);
}

/**
 * Displays the HTML link of the url of the author of the current review.
 *
 * @since 0.71
 * @see get_review_author_url_link() Echoes result
 *
 * @param string $linktext The text to display instead of the review author's email address
 * @param string $before The text or HTML to display before the email link.
 * @param string $after The text or HTML to display after the email link.
 */
function review_author_url_link( $linktext = '', $before = '', $after = '' ) {
	echo get_review_author_url_link( $linktext, $before, $after );
}

/**
 * Generates semantic classes for each review element
 *
 * @since 2.7.0
 *
 * @param string|array $class One or more classes to add to the class list
 * @param int $review_id An optional review ID
 * @param int $post_id An optional post ID
 * @param bool $echo Whether review_class should echo or return
 */
function review_class( $class = '', $review_id = null, $post_id = null, $echo = true ) {
	// Separates classes with a single space, collates classes for review DIV
	$class = 'class="' . join( ' ', get_review_class( $class, $review_id, $post_id ) ) . '"';
	if ( $echo)
		echo $class;
	else
		return $class;
}

/**
 * Returns the classes for the review div as an array
 *
 * @since 2.7.0
 *
 * @param string|array $class One or more classes to add to the class list
 * @param int $review_id An optional review ID
 * @param int $post_id An optional post ID
 * @return array Array of classes
 */
function get_review_class( $class = '', $review_id = null, $post_id = null ) {
	global $review_alt, $review_depth, $review_thread_alt;

	$review = get_review($review_id);

	$classes = array();

	// Get the review type (review, trackback),
	$classes[] = ( empty( $review->review_type ) ) ? 'review' : $review->review_type;

	// If the review author has an id (registered), then print the log in name
	if ( $review->user_id > 0 && $user = get_userdata($review->user_id) ) {
		// For all registered users, 'byuser'
		$classes[] = 'byuser';
		$classes[] = 'review-author-' . sanitize_html_class($user->user_nicename, $review->user_id);
		// For review authors who are the author of the post
		if ( $post = get_post($post_id) ) {
			if ( $review->user_id === $post->post_author )
				$classes[] = 'bypostauthor';
		}
	}

	if ( empty($review_alt) )
		$review_alt = 0;
	if ( empty($review_depth) )
		$review_depth = 1;
	if ( empty($review_thread_alt) )
		$review_thread_alt = 0;

	if ( $review_alt % 2 ) {
		$classes[] = 'odd';
		$classes[] = 'alt';
	} else {
		$classes[] = 'even';
	}

	$review_alt++;

	// Alt for top-level reviews
	if ( 1 == $review_depth ) {
		if ( $review_thread_alt % 2 ) {
			$classes[] = 'thread-odd';
			$classes[] = 'thread-alt';
		} else {
			$classes[] = 'thread-even';
		}
		$review_thread_alt++;
	}

	$classes[] = "depth-$review_depth";

	if ( !empty($class) ) {
		if ( !is_array( $class ) )
			$class = preg_split('#\s+#', $class);
		$classes = array_merge($classes, $class);
	}

	$classes = array_map('esc_attr', $classes);

	return apply_filters('review_class', $classes, $class, $review_id, $post_id);
}

/**
 * Retrieve the review date of the current review.
 *
 * @since 1.5.0
 * @uses apply_filters() Calls 'get_review_date' hook with the formated date and the $d parameter respectively
 * @uses $review
 *
 * @param string $d The format of the date (defaults to user's config)
 * @param int $review_ID The ID of the review for which to get the date. Optional.
 * @return string The review's date
 */
function get_review_date( $d = '', $review_ID = 0 ) {
	$review = get_post( $review_ID );
	if ( '' == $d )
		$date = mysql2date(get_option('date_format'), $review->review_date);
	else
		$date = mysql2date($d, $review->review_date);
	return apply_filters('get_review_date', $date, $d);
}

/**
 * Display the review date of the current review.
 *
 * @since 0.71
 *
 * @param string $d The format of the date (defaults to user's config)
 * @param int $review_ID The ID of the review for which to print the date.  Optional.
 */
function review_date( $d = '', $review_ID = 0 ) {
	echo get_review_date( $d, $review_ID );
}

/**
 * Retrieve the excerpt of the current review.
 *
 * Will cut each word and only output the first 20 words with '...' at the end.
 * If the word count is less than 20, then no truncating is done and no '...'
 * will appear.
 *
 * @since 1.5.0
 * @uses $review
 * @uses apply_filters() Calls 'get_review_excerpt' on truncated review
 *
 * @param int $review_ID The ID of the review for which to get the excerpt. Optional.
 * @return string The maybe truncated review with 20 words or less
 */
function get_review_excerpt( $review_ID = 0 ) {
	$review = get_post( $review_ID );
	$review_text = strip_tags($review->review_content);
	$blah = explode(' ', $review_text);
	if (count($blah) > 20) {
		$k = 20;
		$use_dotdotdot = 1;
	} else {
		$k = count($blah);
		$use_dotdotdot = 0;
	}
	$excerpt = '';
	for ($i=0; $i<$k; $i++) {
		$excerpt .= $blah[$i] . ' ';
	}
	$excerpt .= ($use_dotdotdot) ? '...' : '';
	return apply_filters('get_review_excerpt', $excerpt);
}

/**
 * Display the excerpt of the current review.
 *
 * @since 1.2.0
 * @uses apply_filters() Calls 'review_excerpt' hook before displaying excerpt
 *
 * @param int $review_ID The ID of the review for which to print the excerpt. Optional.
 */
function review_excerpt( $review_ID = 0 ) {
	echo apply_filters('review_excerpt', get_review_excerpt($review_ID) );
}

/**
 * Retrieve the review id of the current review.
 *
 * @since 1.5.0
 * @uses $review
 * @uses apply_filters() Calls the 'get_review_ID' hook for the review ID
 *
 * @return int The review ID
 */
function get_review_ID() {
	global $review;
	return apply_filters('get_review_ID', $review->review_ID);
}

/**
 * Displays the review id of the current review.
 *
 * @since 0.71
 * @see get_review_ID() Echoes Result
 */
function review_ID() {
	echo get_review_ID();
}

/**
 * Retrieve the link to a given review.
 *
 * @since 1.5.0
 * @uses $review
 *
 * @param object|string|int $review Review to retrieve.
 * @param array $args Optional args.
 * @return string The permalink to the given review.
 */
function get_review_link( $review = null, $args = array() ) {
	global $wp_rewrite, $in_review_loop;

	$review = get_post($review);

	// Backwards compat
	if ( !is_array($args) ) {
		$page = $args;
		$args = array();
		$args['page'] = $page;
	}

	$defaults = array( 'type' => 'all', 'page' => '', 'per_page' => '', 'max_depth' => '' );
	$args = wp_parse_args( $args, $defaults );

	if ( '' === $args['per_page'] && get_option('page_reviews') )
		$args['per_page'] = get_option('reviews_per_page');

	if ( empty($args['per_page']) ) {
		$args['per_page'] = 0;
		$args['page'] = 0;
	}

	if ( $args['per_page'] ) {
		if ( '' == $args['page'] )
			$args['page'] = ( !empty($in_review_loop) ) ? get_query_var('cpage') : get_page_of_review( $review->review_ID, $args );

		if ( $wp_rewrite->using_permalinks() )
			$link = user_trailingslashit( trailingslashit( get_permalink( $review->review_post_ID ) ) . 'review-page-' . $args['page'], 'review' );
		else
			$link = add_query_arg( 'cpage', $args['page'], get_permalink( $review->review_post_ID ) );
	} else {
		$link = get_permalink( $review->review_post_ID );
	}

	return apply_filters( 'get_review_link', $link . '#review-' . $review->review_ID, $review, $args );
}

/**
 * Retrieves the link to the current post reviews.
 *
 * @since 1.5.0
 *
 * @param int $post_id Optional post id
 * @return string The link to the reviews
 */
function get_reviews_link($post_id = 0) {
	return get_permalink($post_id) . '#reviews';
}

/**
 * Displays the link to the current post reviews.
 *
 * @since 0.71
 *
 * @param string $deprecated Not Used
 * @param bool $deprecated_2 Not Used
 */
function reviews_link( $deprecated = '', $deprecated_2 = '' ) {
	if ( !empty( $deprecated ) )
		_deprecated_argument( __FUNCTION__, '0.72' );
	if ( !empty( $deprecated_2 ) )
		_deprecated_argument( __FUNCTION__, '1.3' );
	echo get_reviews_link();
}

/**
 * Retrieve the amount of reviews a post has.
 *
 * @since 1.5.0
 * @uses apply_filters() Calls the 'get_reviews_number' hook on the number of reviews
 *
 * @param int $post_id The Post ID
 * @return int The number of reviews a post has
 */
function get_reviews_number( $post_id = 0 ) {
	$post_id = absint( $post_id );

	if ( !$post_id )
		$post_id = get_the_ID();

	$post = get_post($post_id);
	if ( ! isset($post->review_count) )
		$count = 0;
	else
		$count = $post->review_count;

	return apply_filters('get_reviews_number', $count, $post_id);
}

/**
 * Display the language string for the number of reviews the current post has.
 *
 * @since 0.71
 * @uses apply_filters() Calls the 'reviews_number' hook on the output and number of reviews respectively.
 *
 * @param string $zero Text for no reviews
 * @param string $one Text for one review
 * @param string $more Text for more than one review
 * @param string $deprecated Not used.
 */
function reviews_number( $zero = false, $one = false, $more = false, $deprecated = '' ) {
	if ( !empty( $deprecated ) )
		_deprecated_argument( __FUNCTION__, '1.3' );

	$number = get_reviews_number();

	if ( $number > 1 )
		$output = str_replace('%', number_format_i18n($number), ( false === $more ) ? __('% Reviews') : $more);
	elseif ( $number == 0 )
		$output = ( false === $zero ) ? __('No Reviews') : $zero;
	else // must be one
		$output = ( false === $one ) ? __('1 Review') : $one;

	echo apply_filters('reviews_number', $output, $number);
}

/**
 * Retrieve the text of the current review.
 *
 * @since 1.5.0
 * @uses $review
 *
 * @param int $review_ID The ID of the review for which to get the text. Optional.
 * @return string The review content
 */
function get_review_text( $review_ID = 0 ) {
	$review = get_post( $review_ID );
	return apply_filters( 'get_review_text', $review->review_content, $review );
}

/**
 * Displays the text of the current review.
 *
 * @since 0.71
 * @uses apply_filters() Passes the review content through the 'review_text' hook before display
 * @uses get_review_text() Gets the review content
 *
 * @param int $review_ID The ID of the review for which to print the text. Optional.
 */
function review_text( $review_ID = 0 ) {
	$review = get_post( $review_ID );
	echo apply_filters( 'review_text', get_review_text( $review_ID ), $review );
}

/**
 * Retrieve the review time of the current review.
 *
 * @since 1.5.0
 * @uses $review
 * @uses apply_filter() Calls 'get_review_time' hook with the formatted time, the $d parameter, and $gmt parameter passed.
 *
 * @param string $d Optional. The format of the time (defaults to user's config)
 * @param bool $gmt Whether to use the GMT date
 * @param bool $translate Whether to translate the time (for use in feeds)
 * @return string The formatted time
 */
function get_review_time( $d = '', $gmt = false, $translate = true ) {
	global $review;
	$review_date = $gmt ? $review->review_date_gmt : $review->review_date;
	if ( '' == $d )
		$date = mysql2date(get_option('time_format'), $review_date, $translate);
	else
		$date = mysql2date($d, $review_date, $translate);
	return apply_filters('get_review_time', $date, $d, $gmt, $translate);
}

/**
 * Display the review time of the current review.
 *
 * @since 0.71
 *
 * @param string $d Optional. The format of the time (defaults to user's config)
 */
function review_time( $d = '' ) {
	echo get_review_time($d);
}

/**
 * Retrieve the review type of the current review.
 *
 * @since 1.5.0
 * @uses $review
 * @uses apply_filters() Calls the 'get_review_type' hook on the review type
 *
 * @param int $review_ID The ID of the review for which to get the type. Optional.
 * @return string The review type
 */
function get_review_type( $review_ID = 0 ) {
	$review = get_post( $review_ID );
	if ( '' == $review->review_type )
		$review->review_type = 'review';

	return apply_filters('get_review_type', $review->review_type);
}

/**
 * Display the review type of the current review.
 *
 * @since 0.71
 *
 * @param string $reviewtxt The string to display for review type
 * @param string $trackbacktxt The string to display for trackback type
 * @param string $pingbacktxt The string to display for pingback type
 */
function review_type($reviewtxt = false, $trackbacktxt = false, $pingbacktxt = false) {
	if ( false === $reviewtxt ) $reviewtxt = _x( 'Review', 'noun' );
	if ( false === $trackbacktxt ) $trackbacktxt = __( 'Trackback' );
	if ( false === $pingbacktxt ) $pingbacktxt = __( 'Pingback' );
	$type = get_review_type();
	switch( $type ) {
		case 'trackback' :
			echo $trackbacktxt;
			break;
		case 'pingback' :
			echo $pingbacktxt;
			break;
		default :
			echo $reviewtxt;
	}
}

/**
 * Retrieve HTML content for reply to review link.
 *
 * The default arguments that can be override are 'add_below', 'respond_id',
 * 'reply_text', 'login_text', and 'depth'. The 'login_text' argument will be
 * used, if the user must log in or register first before posting a review. The
 * 'reply_text' will be used, if they can post a reply. The 'add_below' and
 * 'respond_id' arguments are for the JavaScript moveAddReviewForm() function
 * parameters.
 *
 * @since 2.7.0
 *
 * @param array $args Optional. Override default options.
 * @param int $review Optional. Review being replied to.
 * @param int $post Optional. Post that the review is going to be displayed on.
 * @return string|bool|null Link to show review form, if successful. False, if reviews are closed.
 */
function get_review_reply_link($args = array(), $review = null, $post = null) {
	global $user_ID;

	$defaults = array('add_below' => 'review', 'respond_id' => 'respond', 'reply_text' => __('Reply'),
		'login_text' => __('Log in to Reply'), 'depth' => 0, 'before' => '', 'after' => '');

	$args = wp_parse_args($args, $defaults);

	if ( 0 == $args['depth'] || $args['max_depth'] <= $args['depth'] )
		return;

	extract($args, EXTR_SKIP);

	$review = get_review($review);
	if ( empty($post) )
		$post = $review->review_post_ID;
	$post = get_post($post);

	if ( !reviews_open($post->ID) )
		return false;

	$link = '';

	if ( get_option('review_registration') && !$user_ID )
		$link = '<a rel="nofollow" class="review-reply-login" href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . $login_text . '</a>';
	else
		$link = "<a class='review-reply-link' href='" . esc_url( add_query_arg( 'replytocom', $review->review_ID ) ) . "#" . $respond_id . "' onclick='return addReview.moveForm(\"$add_below-$review->review_ID\", \"$review->review_ID\", \"$respond_id\", \"$post->ID\")'>$reply_text</a>";
	return apply_filters('review_reply_link', $before . $link . $after, $args, $review, $post);
}

/**
 * Displays the HTML content for reply to review link.
 *
 * @since 2.7.0
 * @see get_review_reply_link() Echoes result
 *
 * @param array $args Optional. Override default options.
 * @param int $review Optional. Review being replied to.
 * @param int $post Optional. Post that the review is going to be displayed on.
 * @return string|bool|null Link to show review form, if successful. False, if reviews are closed.
 */
function review_reply_link($args = array(), $review = null, $post = null) {
	echo get_review_reply_link($args, $review, $post);
}

/**
 * Retrieve HTML content for reply to post link.
 *
 * The default arguments that can be override are 'add_below', 'respond_id',
 * 'reply_text', 'login_text', and 'depth'. The 'login_text' argument will be
 * used, if the user must log in or register first before posting a review. The
 * 'reply_text' will be used, if they can post a reply. The 'add_below' and
 * 'respond_id' arguments are for the JavaScript moveAddReviewForm() function
 * parameters.
 *
 * @since 2.7.0
 *
 * @param array $args Optional. Override default options.
 * @param int|object $post Optional. Post that the review is going to be displayed on.  Defaults to current post.
 * @return string|bool|null Link to show review form, if successful. False, if reviews are closed.
 */
function get_post_reply_link($args = array(), $post = null) {
	global $user_ID;

	$defaults = array('add_below' => 'post', 'respond_id' => 'respond', 'reply_text' => __('Leave a Review'),
		'login_text' => __('Log in to leave a Review'), 'before' => '', 'after' => '');

	$args = wp_parse_args($args, $defaults);
	extract($args, EXTR_SKIP);
	$post = get_post($post);

	if ( !reviews_open($post->ID) )
		return false;

	if ( get_option('review_registration') && !$user_ID ) {
		$link = '<a rel="nofollow" href="' . wp_login_url( get_permalink() ) . '">' . $login_text . '</a>';
	} else {
		$link = "<a rel='nofollow' class='review-reply-link' href='" . get_permalink($post->ID) . "#$respond_id' onclick='return addReview.moveForm(\"$add_below-$post->ID\", \"0\", \"$respond_id\", \"$post->ID\")'>$reply_text</a>";
	}
	return apply_filters('post_reviews_link', $before . $link . $after, $post);
}

/**
 * HTML review list class.
 *
 * @package WordPress
 * @uses Walker
 * @since 2.7.0
 */
class Walker_Review extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 2.7.0
	 * @var string
	 */
	var $tree_type = 'review';

	/**
	 * @see Walker::$db_fields
	 * @since 2.7.0
	 * @var array
	 */
	var $db_fields = array ('parent' => 'review_parent', 'id' => 'review_ID');

	/**
	 * @see Walker::start_lvl()
	 * @since 2.7.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of review.
	 * @param array $args Uses 'style' argument for type of HTML list.
	 */
	function start_lvl(&$output, $depth, $args) {
		$GLOBALS['review_depth'] = $depth + 1;

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
	 * @since 2.7.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of review.
	 * @param array $args Will only append content if style argument value is 'ol' or 'ul'.
	 */
	function end_lvl(&$output, $depth, $args) {
		$GLOBALS['review_depth'] = $depth + 1;

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
	 * @since 2.7.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $review Review data object.
	 * @param int $depth Depth of review in reference to parents.
	 * @param array $args
	 */
	function start_el(&$output, $review, $depth, $args) {
		$depth++;
		$GLOBALS['review_depth'] = $depth;

		if ( !empty($args['callback']) ) {
			call_user_func($args['callback'], $review, $args, $depth);
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
?>
		<<?php echo $tag ?> <?php review_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="review-<?php review_ID() ?>">
		<?php if ( 'div' != $args['style'] ) : ?>
		<div id="div-review-<?php review_ID() ?>" class="review-body">
		<?php endif; ?>
		<div class="review-author vcard">
		<?php if ($args['avatar_size'] != 0) echo get_avatar( $review, $args['avatar_size'] ); ?>
		<?php printf(__('<cite class="fn">%s</cite> <span class="says">says:</span>'), get_review_author_link()) ?>
		</div>
<?php if ($review->review_approved == '0') : ?>
		<em class="review-awaiting-moderation"><?php _e('Your review is awaiting moderation.') ?></em>
		<br />
<?php endif; ?>

		<div class="review-meta reviewmetadata"><a href="<?php echo htmlspecialchars( get_review_link( $review->review_ID ) ) ?>">
			<?php
				/* translators: 1: date, 2: time */
				printf( __('%1$s at %2$s'), get_review_date(),  get_review_time()) ?></a><?php edit_review_link(__('(Edit)'),'&nbsp;&nbsp;','' );
			?>
		</div>

		<?php review_text() ?>

		<div class="reply">
		<?php review_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
		</div>
		<?php if ( 'div' != $args['style'] ) : ?>
		</div>
		<?php endif; ?>
<?php
	}

	/**
	 * @see Walker::end_el()
	 * @since 2.7.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $review
	 * @param int $depth Depth of review.
	 * @param array $args
	 */
	function end_el(&$output, $review, $depth, $args) {
		if ( !empty($args['end-callback']) ) {
			call_user_func($args['end-callback'], $review, $args, $depth);
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
 * Used in the reviews.php template to list reviews for a particular post
 *
 * @since 2.7.0
 * @uses Walker_Review
 *
 * @param string|array $args Formatting options
 * @param array $reviews Optional array of review objects.  Defaults to $wp_query->reviews
 */
function ml_list_reviews($args = array(), $reviews = null ) {
	global $wp_query, $review_alt, $review_depth, $review_thread_alt, $overridden_cpage, $in_review_loop;

	$in_review_loop = true;

	$review_alt = $review_thread_alt = 0;
	$review_depth = 1;

	$defaults = array('walker' => null, 'max_depth' => '', 'style' => 'ul', 'callback' => null, 'end-callback' => null, 'type' => 'all',
		'page' => '', 'per_page' => '', 'avatar_size' => 32, 'reverse_top_level' => null, 'reverse_children' => '');

	$r = wp_parse_args( $args, $defaults );

	// Figure out what reviews we'll be looping through ($_reviews)
	if ( null !== $reviews ) {
		$reviews = (array) $reviews;
		if ( empty($reviews) )
			return;
		if ( 'all' != $r['type'] ) {
			$reviews_by_type = &separate_reviews($reviews);
			if ( empty($reviews_by_type[$r['type']]) )
				return;
			$_reviews = $reviews_by_type[$r['type']];
		} else {
			$_reviews = $reviews;
		}
	} else {
		if ( empty($wp_query->reviews) )
			return;
		if ( 'all' != $r['type'] ) {
			if ( empty($wp_query->reviews_by_type) )
				$wp_query->reviews_by_type = &separate_reviews($wp_query->reviews);
			if ( empty($wp_query->reviews_by_type[$r['type']]) )
				return;
			$_reviews = $wp_query->reviews_by_type[$r['type']];
		} else {
			$_reviews = $wp_query->reviews;
		}
	}

	if ( '' === $r['per_page'] && get_option('page_reviews') )
		$r['per_page'] = get_query_var('reviews_per_page');

	if ( empty($r['per_page']) ) {
		$r['per_page'] = 0;
		$r['page'] = 0;
	}

	if ( '' === $r['max_depth'] ) {
		if ( get_option('thread_reviews') )
			$r['max_depth'] = get_option('thread_reviews_depth');
		else
			$r['max_depth'] = -1;
	}

	if ( '' === $r['page'] ) {
		if ( empty($overridden_cpage) ) {
			$r['page'] = get_query_var('cpage');
		} else {
			$threaded = ( -1 != $r['max_depth'] );
			$r['page'] = ( 'newest' == get_option('default_reviews_page') ) ? get_review_pages_count($_reviews, $r['per_page'], $threaded) : 1;
			set_query_var( 'cpage', $r['page'] );
		}
	}
	// Validation check
	$r['page'] = intval($r['page']);
	if ( 0 == $r['page'] && 0 != $r['per_page'] )
		$r['page'] = 1;

	if ( null === $r['reverse_top_level'] )
		$r['reverse_top_level'] = ( 'desc' == get_option('review_order') );

	extract( $r, EXTR_SKIP );

	if ( empty($walker) )
		$walker = new Walker_Review;

	$walker->paged_walk($_reviews, $max_depth, $page, $per_page, $r);
	$wp_query->max_num_review_pages = $walker->max_pages;

	$in_review_loop = false;
}

/**
 * Separates an array of reviews into an array keyed by review_type.
 *
 * @since 2.7.0
 *
 * @param array $reviews Array of reviews
 * @return array Array of reviews keyed by review_type.
 */
function &separate_reviews(&$reviews) {
	$reviews_by_type = array('review' => array(), 'trackback' => array(), 'pingback' => array(), 'pings' => array());
	$count = count($reviews);
	for ( $i = 0; $i < $count; $i++ ) {
		$type = $reviews[$i]->review_type;
		if ( empty($type) )
			$type = 'review';
		$reviews_by_type[$type][] = &$reviews[$i];
		if ( 'trackback' == $type || 'pingback' == $type )
			$reviews_by_type['pings'][] = &$reviews[$i];
	}

	return $reviews_by_type;
}

?>