<?php
/**
 * template functions for review pages
 * @package amazon-library
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * wrapper to template hack for archive-aml_review
 *
 * @param string found template
 * @return string path to template
 */
function aml_review_archive_template ($template) {
	return aml_insert_template ($template, 'aml_review', 'archive');
}

/**
 * wrapper to template hack for single-aml_review
 *
 * @param string found template
 * @return string path to template
 */
function aml_review_single_template ($template) {
	return aml_insert_template ($template, 'aml_review', 'single');
}

/**
 * wrapper to template hack for taxonomy-aml_tag
 *
 * @param string found template
 * @return string path to template
 */
function aml_tags_taxonomy_template ($template) {
	return aml_insert_template ($template, 'aml_tag', 'taxonomy');
}

// Also not finished and subject to change

/**
 * Retrieve the time at which the specified event occured
 *
 * @param string $d Optional, default is 'U'. Either 'G', 'U', or php date format.
 * @param string $which Optional, default is added to shelf. The meta timestamp to get
 * @param bool $translate Optional, default is false. Whether to translate the result
 * @return string Returns timestamp
 */
function get_review_custom_time ($d='U', $which='usage_added', $translate=true) {
	$post = get_post();
	$which_time = get_post_meta($post->ID, $which, true);
	$time = mysql2date($d, $which_time, $translate);
	return apply_filters('get_post_modified_time', $time, $d);
}

/**
 * Retrieve the time at which the specified event occured
 *
 * @param string $d Optional Either 'G', 'U', or php date format defaults to the value specified in the time_format option.
 * @param string $which Optional, default is added to shelf. The meta timestamp to get
 * @return string
 */
function get_the_review_custom_time ($d='', $which='usage_added') {
	if ( '' == $d )
		$the_time = get_usage_custom_time(get_option('time_format'), $which);
	else
		$the_time = get_usage_custom_time($d, $which);
	return apply_filters('get_the_modified_time', $the_time, $d);
}

/**
 * Display the time at which the specified event occured
 *
 * @param string $d Optional Either 'G', 'U', or php date format defaults to the value specified in the time_format option.
 * @param string $which Optional, default is added to shelf. The meta timestamp to get
 */
function the_review_custom_time ($d='', $which='usage_added') {
	echo apply_filters('the_modified_time', get_the_modified_time($d, $which), $d);
}

/**
 * Retrieve the date on which the specified event occured
 *
 * @param string $d Optional. PHP date format. Defaults to the "date_format" option
 * @param string $which Optional, default is added to shelf. The meta timestamp to get
 * @return string
 */
function get_the_review_custom_date ($d='', $which='usage_added') {
	if ( '' == $d )
		$the_time = get_the_usage_custom_time(get_option('date_format'), $which);
	else
		$the_time = get_the_usage_custom_time($d, $which);
	return apply_filters('get_the_modified_date', $the_time, $d);
}

/**
 * Display the date on which the specified event occured
 *
 * @param string $d Optional. PHP date format defaults to the date_format option if not specified.
 * @param string $which Optional, default is added to shelf. The meta timestamp to get
 * @param string $before Optional. Output before the date.
 * @param string $after Optional. Output after the date.
 * @param bool $echo Optional, default is display. Whether to echo the date or return it.
 * @return string|null Null if displaying, string if retrieving.
 */
function the_review_custom_date ($d='', $which='usage_added', $before='', $after='', $echo=true) {
	$the_modified_date = $before . get_the_usage_custom_date($d, $which) . $after;
	$the_modified_date = apply_filters('the_modified_date', $the_modified_date, $d, $before, $after);

	if ( $echo )
		echo $the_modified_date;
	else
		return $the_modified_date;
}

function aml_show_date ($time=0, $which='added', $can_publish=true) {
	global $post, $action;

	if ( !$can_publish ) {
		return;
	}

	$time = ($time == 0) ? current_time('mysql') : $time;
	switch ($which) {
		case 'added':
			$string = 'Added to Shelf';
			break;
		case 'started':
			$string = 'Began Review';
			break;
		case 'finished':
			$string = 'Review Finished';
			break;
		default:
			$string = '';
			break;
	}
	$datef = __('M j, Y @ G:i');
	$stamp = __($string . ': <b>%1$s</b>');
	$date = date_i18n($datef, strtotime($time));
?>
<div class="curtime">
	<span id="timestamp-<?php echo $which; ?>">
	<?php printf($stamp, $date); ?></span>
	<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js" tabindex='4'><?php _e('Edit') ?></a>
	<div id="timestampdiv-<?php echo $which; ?>" class="hide-if-js"><?php touch_time(($action == 'edit'),1,4); ?></div>
</div>
<?php
}

function review_stars ($checked='', $readonly=false) {
?>
<div id="aml_rating">
	<input name="aml_rating" type="radio" value="0"<?php checked($checked, '0'); disabled($readonly); ?> class="star {split:2}" />
	<input name="aml_rating" type="radio" value="0.5"<?php checked($checked, '0.5'); disabled($readonly); ?> class="star {split:2}" />
	<input name="aml_rating" type="radio" value="1"<?php checked($checked, '1'); disabled($readonly); ?> class="star {split:2}" />
	<input name="aml_rating" type="radio" value="1.5"<?php checked($checked, '1.5'); disabled($readonly); ?> class="star {split:2}" />
	<input name="aml_rating" type="radio" value="2"<?php checked($checked, '2'); disabled($readonly); ?> class="star {split:2}" />
	<input name="aml_rating" type="radio" value="2.5"<?php checked($checked, '2.5'); disabled($readonly); ?> class="star {split:2}" />
	<input name="aml_rating" type="radio" value="3"<?php checked($checked, '3'); disabled($readonly); ?> class="star {split:2}" />
	<input name="aml_rating" type="radio" value="3.5"<?php checked($checked, '3.5'); disabled($readonly); ?> class="star {split:2}" />
	<input name="aml_rating" type="radio" value="4"<?php checked($checked, '4'); disabled($readonly); ?> class="star {split:2}" />
	<input name="aml_rating" type="radio" value="4.5"<?php checked($checked, '4.5'); disabled($readonly); ?> class="star {split:2}" />
	<input name="aml_rating" type="radio" value="5"<?php checked($checked, '5'); disabled($readonly); ?> class="star {split:2}" />
</div>
<?php
}
