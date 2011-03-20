<?php
/**
 * Template functions for products
 * @package amazon-library
 */

// data

/**
 * Retrieve post title.
 *
 * If the post is protected and the visitor is not an admin, then "Protected"
 * will be displayed before the post title. If the post is private, then
 * "Private" will be located before the post title.
 *
 * @param int $id Optional. Post ID.
 * @return string
 */
function get_the_product_image ($id=0) {
	$post = &get_post($id);

	$image = isset($post->post_title) ? $post->post_title : '';
	$id = isset($post->ID) ? $post->ID : (int) $id;

	return apply_filters( 'the_title', $image, $id );
}

/**
 * Display or retrieve the current post title with optional content.
 *
 * @param string $before Optional. Content to prepend to the title.
 * @param string $after Optional. Content to append to the title.
 * @param bool $echo Optional, default to true.Whether to display or return.
 * @return null|string Null on no title. String if $echo parameter is false.
 */
function the_product_image ($before = '', $after = '', $echo = true) {
	$image = get_the_product_image();

	if ( strlen($image) == 0 )
		return;

	$image = $before . $image . $after;

	if ( $echo )
		echo $image;
	else
		return $image;
}

/**
 * Retrieve post title.
 *
 * If the post is protected and the visitor is not an admin, then "Protected"
 * will be displayed before the post title. If the post is private, then
 * "Private" will be located before the post title.
 *
 * @param int $id Optional. Post ID.
 * @return string
 */
function get_the_product_title ($id=0) {
	$post = &get_post($id);

	$image = isset($post->post_title) ? $post->post_title : '';
	$id = isset($post->ID) ? $post->ID : (int) $id;

	return apply_filters( 'the_title', $image, $id );
}

/**
 * Display or retrieve the current post title with optional content.
 *
 * @param string $before Optional. Content to prepend to the title.
 * @param string $after Optional. Content to append to the title.
 * @param bool $echo Optional, default to true.Whether to display or return.
 * @return null|string Null on no title. String if $echo parameter is false.
 */
function the_product_title ($before = '', $after = '', $echo = true) {
	$image = get_the_title();

	if ( strlen($image) == 0 )
		return;

	$image = $before . $image . $after;

	if ( $echo )
		echo $image;
	else
		return $image;
}

// related posts

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

// edit url

/**
 * If the user has the correct permissions, prints a URL to the review-writing screen for the current book.
 * @param bool $echo Whether or not to echo the results.
 */
function product_edit_url ($echo=true) {
    global $book, $nr_url;
    if ( can_now_reading_admin() )
        echo apply_filters('book_edit_url', $nr_url->urls['manage'] . '&amp;action=editsingle&amp;id=' . $book->id);
}

// counts

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
 * Prints the number of books started and finished within a given time period.
 * @param string $interval The time interval, eg  "1 year", "3 month"
 * @param bool $echo Whether or not to echo the results.
 */
function products_used_since ($interval, $echo=true) {
    global $wpdb;

    $interval = $wpdb->escape($interval);
    $num = $wpdb->get_var("
	SELECT
		COUNT(*) AS count
	FROM
        {$wpdb->prefix}now_reading
	WHERE
		DATE_SUB(CURDATE(), INTERVAL $interval) <= b_finished
        ");

    if ( $echo )
        echo "$num book".($num != 1 ? 's' : '');
    return $num;
}

// display

function product_shelf_listing() {

}


function product_shelf_image ($prod, $before='', $after='') {
	$image = get_post_meta($prod->ID, 'aml_image', true);
	$link = get_post_meta($prod->ID, 'aml_link', true);
	$asin = get_post_meta($prod->ID, 'aml_asin', true);
	$edit_link = '';
	$people = get_the_term_list($prod->ID, 'aml_person', '<span class="aml_product-people">', ', ', '</span></br>');

	$html = $before;
	$html .= '<a href="'.$edit_link.'">';
	$html .= '<img src="'.$image.'" /><br />';
	$html .= '<span class="aml_product-title">' . $prod->title . '</span></a><br />';
	$html .= $people;
	if (!empty($asin)) {
		$html .= '<span class="aml_product-link">';
		if (!empty($link)) {
		$asin = '<a href="'.$link.'">'.$asin.'</a>';
		}
		$html .= $asin . '</a>';
	}
	$html .= $after . "\n";
	return $html;
}
