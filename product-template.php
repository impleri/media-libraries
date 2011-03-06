<?php
/**
 * Template functions
 * @package amazon-library
 */

/**
 * Retrieve post title.
 *
 * If the post is protected and the visitor is not an admin, then "Protected"
 * will be displayed before the post title. If the post is private, then
 * "Private" will be located before the post title.
 *
 * @since 0.71
 *
 * @param int $id Optional. Post ID.
 * @return string
 */
function get_the_product_image ($id=0) {
	$post = &get_post($id);

	$title = isset($post->post_title) ? $post->post_title : '';
	$id = isset($post->ID) ? $post->ID : (int) $id;

	if ( !is_admin() ) {
		if ( !empty($post->post_password) ) {
			$protected_title_format = apply_filters('protected_title_format', __('Protected: %s'));
			$title = sprintf($protected_title_format, $title);
		} else if ( isset($post->post_status) && 'private' == $post->post_status ) {
			$private_title_format = apply_filters('private_title_format', __('Private: %s'));
			$title = sprintf($private_title_format, $title);
		}
	}
	return apply_filters( 'the_title', $title, $id );
}

/**
 * Display or retrieve the current post title with optional content.
 *
 * @since 0.71
 *
 * @param string $before Optional. Content to prepend to the title.
 * @param string $after Optional. Content to append to the title.
 * @param bool $echo Optional, default to true.Whether to display or return.
 * @return null|string Null on no title. String if $echo parameter is false.
 */
function the_product_image ($before = '', $after = '', $echo = true) {
	$title = get_the_product_image();

	if ( strlen($title) == 0 )
		return;

	$title = $before . $title . $after;

	if ( $echo )
		echo $title;
	else
		return $title;
}

/* Functions for product itself  */

/**
 * Wrapper to standard the_time()
 */
function the_product_time( $d = '' ) {
	return the_time($d);
}

/**
 * Wrapper to standard the_date
 */
function the_product_date( $d = '', $before = '', $after = '', $echo = true ) {
	return the_date($d, $before, $after, $echo);
}

/* Functions for product usage  */

/**
 * Retrieve the time at which the post was last modified.
 *
 * @since 2.0.0
 *
 * @param string $d Optional, default is 'U'. Either 'G', 'U', or php date format.
 * @param bool $gmt Optional, default is false. Whether to return the gmt time.
 * @param int|object $post Optional, default is global post object. A post_id or post object
 * @param bool $translate Optional, default is false. Whether to translate the result
 * @return string Returns timestamp
 */
function get_usage_custom_time ($d='U', $which='usage_added', $translate=true) {
	$post = get_post();
	$time = mysql2date($d, $post->$which, $translate);
	return apply_filters('get_post_modified_time', $time, $d);
}

/**
 * Retrieve the time at which the post was last modified.
 *
 * @since 2.0.0
 *
 * @param string $d Optional Either 'G', 'U', or php date format defaults to the value specified in the time_format option.
 * @return string
 */
function get_the_usage_custom_time ($d='', $which='usage_added') {
	if ( '' == $d )
		$the_time = get_usage_custom_time(get_option('time_format'), $which);
	else
		$the_time = get_usage_custom_time($d, $which);
	return apply_filters('get_the_modified_time', $the_time, $d);
}

/**
 * Display the time at which the post was last modified.
 *
 * @since 2.0.0
 *
 * @param string $d Optional Either 'G', 'U', or php date format defaults to the value specified in the time_format option.
 */
function the_usage_custom_time ($d='', $which='usage_added') {
	echo apply_filters('the_modified_time', get_the_modified_time($d, $which), $d);
}

/**
 * Retrieve the date on which the post was last modified.
 *
 * @since 2.1.0
 *
 * @param string $d Optional. PHP date format. Defaults to the "date_format" option
 * @return string
 */
function get_the_usage_custom_date ($d='', $which='usage_added') {
	if ( '' == $d )
		$the_time = get_the_usage_custom_time(get_option('date_format'), $which);
	else
		$the_time = get_the_usage_custom_time($d, $which);
	return apply_filters('get_the_modified_date', $the_time, $d);
}

/**
 * Display the date on which the post was last modified.
 *
 * @since 2.1.0
 *
 * @param string $d Optional. PHP date format defaults to the date_format option if not specified.
 * @param string $before Optional. Output before the date.
 * @param string $after Optional. Output after the date.
 * @param bool $echo Optional, default is display. Whether to echo the date or return it.
 * @return string|null Null if displaying, string if retrieving.
 */
function the_usage_custom_date ($d='', $which='usage_added', $before='', $after='', $echo=true) {
	$the_modified_date = $before . get_the_usage_custom_date($d, $which) . $after;
	$the_modified_date = apply_filters('the_modified_date', $the_modified_date, $d, $before, $after);

	if ( $echo )
		echo $the_modified_date;
	else
		return $the_modified_date;

}

/**
 * Prints the total number of books in the library.
 * @param string $status A comma-separated list of statuses to include in the count. If ommitted, all statuses will be counted.
 * @param bool $echo Whether or not to echo the results.
 * @param int $userID Counting only userID's books
 */
function total_products ($status='', $echo=true , $userID=0) {
    global $wpdb;

    get_currentuserinfo();

    if ( $status ) {
        if ( strpos($status, ',') === false ) {
            $status = 'WHERE b_status = "' . $wpdb->escape($status) . '"';
        } else {
            $statuses = explode(',', $status);

            $status = 'WHERE 1=0';
            foreach ( (array) $statuses as $st ) {
                $status .= ' OR b_status = "' . $wpdb->escape(trim($st)) . '" ';
            }
        }
        //counting only current user's books
        if ($userID) { //there's no user whose ID is 0
            $status .= ' AND b_reader = '.$userID;
        }
    } else {
        if ($userID) {
            $status = 'WHERE b_reader = '.$userID;
        } else {
            $status = '';
        }
    }


    $num = $wpdb->get_var("
	SELECT
		COUNT(*) AS count
	FROM
        {$wpdb->prefix}now_reading
        $status
        ");

    if ( $echo )
        echo "$num book".($num != 1 ? 's' : '');
    return $num;
}

/**
 * Prints the average number of books read in the given time limit.
 * @param string $time_period The period to measure, eg "year", "month"
 * @param bool $echo Whether or not to echo the results.
 */
function average_products ($time_period='week', $echo=true) {
    global $wpdb;

    $books_per_day = $wpdb->get_var("
	SELECT
		( COUNT(*) / ( TO_DAYS(CURDATE()) - TO_DAYS(MIN(b_finished)) ) ) AS books_per_day
	FROM
        {$wpdb->prefix}now_reading
	WHERE
		b_status = 'read'
	AND b_finished > 0
        ");

    $average = 0;
    switch ( $time_period ) {
        case 'year':
            $average = round($books_per_day * 365);
            break;
        case 'month':
            $average = round($books_per_day * 31);
            break;
        case 'week':
            $average = round($books_per_day * 7);
        case 'day':
            break;
        default:
            return 0;
    }

    if( $echo )
        printf(__("an average of %s book%s each %s", NRTD), $average, ($average != 1 ? 's' : ''), $time_period);
    return $average;
}

/**
 * Prints a URL to the book's Amazon detail page. If the book is a custom one, it will print a URL to the book's permalink page.
 * @param bool $echo Whether or not to echo the results.
 * @param string $domain The Amazon domain to link to. If ommitted, the default domain will be used.
 * @see book_permalink()
 * @see is_custom_book()
 */
function product_url ($echo=true, $domain=null) {
    global $book;
    $options = get_option('nowReadingOptions');

    if ( empty($domain) )
        $domain = $options['domain'];

    if ( is_custom_book() )
        return book_permalink($echo);
    else {
        $url = apply_filters('book_url', "http://www.amazon{$domain}/exec/obidos/ASIN/{$book->asin}/ref=nosim/{$options['associate']}");
        if ( $echo )
            echo $url;
        return $url;
    }
}

/**
 * Returns true if the current book is linked to a post, false if it isn't.
 */
function product_has_post() {
    global $book;

    return ( $book->post > 0 );
}

/**
 * Returns or prints the permalink of the post linked to the current book.
 * @param bool $echo Whether or not to echo the results.
 */
function product_post_url ($echo=true) {
    global $book;

    if ( !book_has_post() )
        return;

    $permalink = get_permalink($book->post);

    if ( $echo )
        echo $permalink;
    return $permalink;
}

/**
 * Returns or prints the title of the post linked to the current book.
 * @param bool $echo Whether or not to echo the results.
 */
function product_post_title ($echo=true) {
    global $book;

    if ( !book_has_post() )
        return;

    $post = get_post($book->post);

    if ( $echo )
        echo $post->post_title;
    return $post->post_title;
}

/**
 * If the current book is linked to a post, prints an HTML link to said post.
 * @param bool $echo Whether or not to echo the results.
 */
function product_post_link ($echo=true) {
    global $book;

    if ( !book_has_post() )
        return;

    $link = '<a href="' . book_post_url(0) . '">' . book_post_title(0) . '</a>';

    if ( $echo )
        echo $link;
    return $link;
}

/**
 * If the user has the correct permissions, prints a URL to the Manage -> Now Reading page of the WP admin.
 * @param bool $echo Whether or not to echo the results.
 */
function manage_shelf_url ($echo=true) {
    global $nr_url;
    if ( can_now_reading_admin() )
        echo apply_filters('book_manage_url', $nr_url->urls['manage']);
}

/**
 * If the user has the correct permissions, prints a URL to the review-writing screen for the current book.
 * @param bool $echo Whether or not to echo the results.
 */
function product_edit_url ($echo=true) {
    global $book, $nr_url;
    if ( can_now_reading_admin() )
        echo apply_filters('book_edit_url', $nr_url->urls['manage'] . '&amp;action=editsingle&amp;id=' . $book->id);
}

/**
 * Prints the book's rating or "Unrated" if the book is unrated.
 * @param bool $echo Whether or not to echo the results.
 */
function usage_rating ($echo=true) {
    global $book;
    if ( $book->rating )
        $rate = apply_filters('book_rating', $book->rating);
    else
        $rate = apply_filters('book_rating', __('Unrated', NRTD));

    if ( $echo )
        echo $rate;
    return $rate;
}

function product_shelf_image() {

}

function product_shelf_listing() {

}

?>