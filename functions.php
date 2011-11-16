<?php
/**
 * aml connective functions
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * @todo
 * product: one-to-many review (one per user), one-to-many reading
 * reading: many-to-one product, many-to-one review, many-to-many shelf
 * review: many-to-one product (one per user), one-to-many reading
 * shelf: many-to-many reading
 *
 * product-review: product is post_parent of review
 * product-reading: product is post_parent of reading
 * product-shelf: no direct relation (indirect through reading:shelf)
 * reading-review: no direct relation (indirect through product)
 * reading-shelf: reading is metadata for shelf
 * review-shelf: no direct relation (indirect through shelf:reading--reading:product relation)
 */

/**
 * hack to use our templates
 *
 * @param string found template (passed from the filter)
 * @param string type of custom post/taxonomy to check
 * @param string type of page (archive, single, or taxonomy)
 * @return string path to template
 */
function ml_insert_template ($template, $type, $page='archive') {
	if ($page == 'taxonomy') {
		$term = get_queried_object();
		$check = $term->taxonomy;
	}
	else {
		$check = get_query_var('post_type');
	}

	// one of ours to worry about!
	if ($check == $type) {
		$file = $page.'-'.$type.'.php';

		// template not found in theme folder, so replace it with our default
		if ($file != basename($template)) {
			$path = dirname(__FILE__) . '/templates/' . $file;
			if ( file_exists($path)) {
				$template = $path;
			}
		}
	}

	return $template;
}

/**
 * insert a page into WP and link it to our template
 *
 * @param string found template (passed from the filter)
 * @param string type of custom post/taxonomy to check
 * @param string type of page (archive, single, or taxonomy)
 * @return string path to template
 */
function ml_add_library_page ($slug, $type='base') {
	$templates = ml_library_templates();

	if (isset($templates[$slug])) {
		$args = array('comment_status' => 'closed', 'ping_status' => 'closed', 'post_title' => $templates[$slug]['title'], 'post_name' => $templates[$slug]['slug'], 'post_status' => 'publish', 'post_type' => 'page');

		if ($type != 'base') {
			$library = ml_get_option('ml_page_base');
			$args['post_parent'] = $library;
		}

		$id = wp_insert_post($args);
		if ($id) {
			update_post_meta($id, '_wp_page_template', $templates[$slug]['file']);
			ml_set_option('page_' . $type, $id);
		}
	}
}

/**
 * Wrapper to template hack for archive-ml_shelf
 *
 * @param string found template
 * @return string path to template
 */
function ml_page_template ($template) {
	$post = get_post();
	$ml_pages = array();
	$templates = ml_library_templates();

	// patch only for connected templates
	foreach ($templates as $key => $val) {
		$check = ml_get_option('ml_page_'.$key, true);
		if ($check && is_numeric($check)) {
			$ml_pages[$check] = $val['file'];
		}
	}

	// use our page template where possible (allowing for themes and styles to override)
	if ($post->post_type == 'page' && in_array($post->ID, array_keys($ml_pages))) {
		$file = $ml_pages[$post->ID];
		if ($file != basename($template)) {
			$path = dirname(__FILE__) . '/templates/' . $file;
			if ( file_exists($path)) {
				$template = $path;
			}
		}
	}

	return $template;
}

/**
 * available product categories
 */
function ml_product_categories() {
	return array(
		'b' => __('Books', 'media-libraries'),
		'v' => __('Video', 'media-libraries'),
		'm' => __('Music', 'media-libraries'),
		'g' => __('Video Games', 'media-libraries'),
	);
}

/**
 * default page templates
 */
function ml_library_templates() {
	return array(
		'base' => array('title' => __('Library', 'media-libraries'), 'file' => 'page-library.php', 'slug' => ml_get_option('ml_slug_base')),
		'product' => array('title' => __('Products', 'media-libraries'), 'file' => 'page-product.php', 'slug' => ml_get_option('ml_slug_product')),
		'person' => array('title' => __('People', 'media-libraries'), 'file' => 'page-person.php', 'slug' => ml_get_option('ml_slug_person')),
		'tag' => array('title' => __('Tags', 'media-libraries'), 'file' => 'page-tag.php', 'slug' => ml_get_option('ml_slug_tag')),
		'review' => array('title' => __('Reviews', 'media-libraries'), 'file' => 'page-review.php', 'slug' => ml_get_option('ml_slug_review')),
		'shelf' => array('title' => __('Shelves', 'media-libraries'), 'file' => 'page-shelf.php', 'slug' => ml_get_option('ml_slug_shelf')),
		'user' => array('title' => __('Users', 'media-libraries'), 'file' => 'page-user.php', 'slug' => ml_get_option('ml_slug_user')),
	);
}

function ml_blank_image() {
	return plugins_url('no-image.png', __FILE__);
}

// unstable from here

function the_library_header() {}

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


// Pure PHP files should not have a closing PHP tag!!
