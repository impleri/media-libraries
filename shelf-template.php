<?php
/**
 * Template functions
 * @package amazon-library
 */

/**
 * Get all Term data from database by Term ID.
 *
 * The usage of the get_term function is to apply filters to a term object. It
 * is possible to get a term object from the database before applying the
 * filters.
 *
 * $shelf ID must be part of , to get from the database. Failure, might
 * be able to be captured by the hooks. Failure would be the same value as $wpdb
 * returns for the get_row method.
 *
 * There are two hooks, one is specifically for each term, named 'get_term', and
 * the second is for the taxonomy name, 'term_'. Both hooks gets the
 * term object, and the taxonomy name as parameters. Both hooks are expected to
 * return a Term object.
 *
 * 'get_term' hook - Takes two parameters the term Object and the taxonomy name.
 * Must return term object. Used in get_term() as a catch-all filter for every
 * $shelf.
 *
 * 'get_' hook - Takes two parameters the term Object and the taxonomy
 * name. Must return term object.  will be the taxonomy name, so for
 * example, if 'category', it would be 'get_category' as the filter name. Useful
 * for custom taxonomies or plugging into default taxonomies.
 *
 * @package WordPress
 * @subpackage Taxonomy
 * @since 2.3.0
 *
 * @uses $wpdb
 * @uses sanitize_term() Cleanses the term based on $filter context before returning.
 * @see sanitize_term_field() The $context param lists the available values for get_term_by() $filter param.
 *
 * @param int|object $shelf If integer, will get from database. If object will apply filters and return $shelf.
 * @param string  Taxonomy name that $shelf is part of.
 * @param string $output Constant OBJECT, ARRAY_A, or ARRAY_N
 * @param string $filter Optional, default is raw or no WordPress defined filter will applied.
 * @return mixed|null|WP_Error Term Row from database. Will return null if $shelf is empty. If taxonomy does not
 * exist then WP_Error will be returned.
 */
function &get_shelf($shelf, $output = OBJECT, $filter = 'raw') {
	global $wpdb;
	$null = null;

	if ( empty($shelf) ) {
		$error = new WP_Error('invalid_term', __('Empty Term'));
		return $error;
	}

	if ( is_object($shelf) && empty($shelf->filter) ) {
		wp_cache_add($shelf->shelf_id, $shelf);
		$_term = $shelf;
	} else {
		if ( is_object($shelf) )
			$shelf = $shelf->shelf_id;
		$shelf = (int) $shelf;
		if ( ! $_term = wp_cache_get($shelf) ) {
			$_term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.shelf_id = tt.shelf_id WHERE tt.taxonomy = %s AND t.shelf_id = %s LIMIT 1", $shelf) );
			if ( ! $_term )
				return $null;
			wp_cache_add($shelf, $_term);
		}
	}

	$_term = apply_filters('get_term', $_term);
	$_term = sanitize_term($_term, $filter);

	if ( $output == OBJECT ) {
		return $_term;
	} elseif ( $output == ARRAY_A ) {
		$__term = get_object_vars($_term);
		return $__term;
	} elseif ( $output == ARRAY_N ) {
		$__term = array_values(get_object_vars($_term));
		return $__term;
	} else {
		return $_term;
	}
}

/**
 * Retrieve a post's terms as a list with specified format.
 *
 * @since 2.5.0
 *
 * @param int $id Post ID.
 * @param string  Taxonomy name.
 * @param string $before Optional. Before list.
 * @param string $sep Optional. Separate items using this.
 * @param string $after Optional. After list.
 * @return string
 */
function get_the_shelf_list ($id=0, $before='', $sep='', $after='') {
	$shelves = get_the_shelves($id);

	if ( is_wp_error( $shelves ) )
		return $shelves;

	if ( empty( $shelves ) )
		return false;

	foreach ( $shelves as $shelf ) {
		$link = get_shelf_link( $shelf );
		if ( is_wp_error( $link ) )
			return $link;
		$shelf_links[] = '<a href="' . $link . '" rel="tag">' . $shelf->name . '</a>';
	}

	$shelf_links = apply_filters( "term_links-", $shelf_links );

	return $before . join( $sep, $shelf_links ) . $after;
}

/**
 * Retrieve the terms of the taxonomy that are attached to the post.
 *
 * This function can only be used within the loop.
 *
 * @since 2.5.0
 *
 * @param int $id Post ID. Is not optional.
 * @param string  Taxonomy name.
 * @return array|bool False on failure. Array of term objects on success.
 */
function get_the_shelves ($id=0) {
	global $post;

 	$id = (int) $id;

	if ( !$id ) {
		if ( !$post->ID )
			return false;
		else
			$id = (int) $post->ID;
	}

	$shelves = get_object_term_cache( $id );
	if ( false === $shelves ) {
		$shelves = wp_get_object_terms( $id );
		wp_cache_add($id, $shelves,  '_relationships');
	}

	$shelves = apply_filters( 'get_the_terms', $shelves, $id );

	if ( empty( $shelves ) )
		return false;

	return $shelves;
}

/**
 * Generates a permalink for a taxonomy term archive.
 *
 * @since 2.5.0
 *
 * @uses apply_filters() Calls 'term_link' with term link and term object, and taxonomy parameters.
 * @uses apply_filters() For the post_tag Taxonomy, Calls 'tag_link' with tag link and tag ID as parameters.
 * @uses apply_filters() For the category Taxonomy, Calls 'category_link' filter on category link and category ID.
 *
 * @param object|int|string $shelf
 * @param string  (optional if $shelf is object)
 * @return string|WP_Error HTML link to taxonomy term archive on success, WP_Error if term does not exist.
 */
function get_shelf_link ($shelf) {
	global $wp_rewrite;

	if ( !is_object($shelf) ) {
		if ( is_int($shelf) ) {
			$shelf = &get_shelf($shelf);
		} else {
			$shelf = &get_shelf_by('slug', $shelf);
		}
	}

	if ( !is_object($shelf) )
		$shelf = new WP_Error('invalid_term', __('Empty Term'));

	if ( is_wp_error( $shelf ) )
		return $shelf;

	$shelflink = $wp_rewrite->get_extra_permastruct();

	$slug = $shelf->slug;

	if ( empty($shelflink) ) {
		$shelflink = "?&aml_shelf=$slug";
		$shelflink = home_url($shelflink);
	} else {
		$shelflink = str_replace("%aml_shelf%", $slug, $shelflink);
		$shelflink = home_url( user_trailingslashit($shelflink, 'category') );
	}
	return apply_filters('term_link', $shelflink, $shelf);
}


/**
 * Get all Term data from database by Term field and data.
 *
 * Warning: $value is not escaped for 'name' $field. You must do it yourself, if
 * required.
 *
 * The default $field is 'id', therefore it is possible to also use null for
 * field, but not recommended that you do so.
 *
 * If $value does not exist, the return value will be false. If $taxonomy exists
 * and $field and $value combinations exist, the Term will be returned.
 *
 * @package WordPress
 * @subpackage Taxonomy
 * @since 2.3.0
 *
 * @uses $wpdb
 * @uses sanitize_term() Cleanses the term based on $filter context before returning.
 * @see sanitize_term_field() The $context param lists the available values for get_term_by() $filter param.
 *
 * @param string $field Either 'slug', 'name', or 'id'
 * @param string|int $value Search for this term value
 * @param string $taxonomy Taxonomy Name
 * @param string $output Constant OBJECT, ARRAY_A, or ARRAY_N
 * @param string $filter Optional, default is raw or no WordPress defined filter will applied.
 * @return mixed Term Row from database. Will return false if $taxonomy does not exist or $shelf was not found.
 */
function get_shelf_by($field, $value, $output = OBJECT, $filter = 'raw') {
	global $wpdb;

	if ( 'slug' == $field ) {
		$field = 't.slug';
		$value = sanitize_title_for_query($value);
		if ( empty($value) )
			return false;
	} else if ( 'name' == $field ) {
		// Assume already escaped
		$value = stripslashes($value);
		$field = 't.name';
	} else {
		return get_shelf( (int) $value, $output, $filter);
	}

	$shelf = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.shelf_id = tt.shelf_id WHERE tt.taxonomy = %s AND $field = %s LIMIT 1", $value) );
	if ( !$shelf )
		return false;

	wp_cache_add($shelf->shelf_id, $shelf);

	$shelf = apply_filters('get_term', $shelf);
	$shelf = sanitize_term($shelf, $filter);

	if ( $output == OBJECT ) {
		return $shelf;
	} elseif ( $output == ARRAY_A ) {
		return get_object_vars($shelf);
	} elseif ( $output == ARRAY_N ) {
		return array_values(get_object_vars($shelf));
	} else {
		return $shelf;
	}
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