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

	$id = isset($post->ID) ? $post->ID : (int) $id;
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
	$type = get_post_meta($post->ID, 'ml_type', true);
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
 * Retrieve product link
 *
 * Get the product source URL if it exists
 *
 * @param int $id Optional. Post ID.
 * @return string
 */
function get_the_product_link ($id=0) {
	$post = get_post($id);

	$id = isset($post->ID) ? $post->ID : (int) $id;
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


// everything below is unstable

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
	$image = get_post_meta($prod->ID, 'ml_image', true);
	$link = get_post_meta($prod->ID, 'ml_link', true);
	$asin = get_post_meta($prod->ID, 'ml_asin', true);
	$edit_link = '';
	$people = get_the_term_list($prod->ID, 'ml_person', '<span class="ml_product-people">', ', ', '</span></br>');

	$html = $before;
	$html .= '<a href="'.$edit_link.'">';
	$html .= '<img src="'.$image.'" /><br />';
	$html .= '<span class="ml_product-title">' . $prod->title . '</span></a><br />';
	$html .= $people;
	if (!empty($asin)) {
		$html .= '<span class="ml_product-link">';
		if (!empty($link)) {
		$asin = '<a href="'.$link.'">'.$asin.'</a>';
		}
		$html .= $asin . '</a>';
	}
	$html .= $after . "\n";
	return $html;
}
