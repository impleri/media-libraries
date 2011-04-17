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
function get_review_custom_time ($d='U', $which='aml_added', $translate=true) {
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
function get_the_review_custom_time ($d='', $which='aml_added') {
	if ( '' == $d )
		$the_time = get_review_custom_time(get_option('time_format'), $which);
	else
		$the_time = get_review_custom_time($d, $which);
	return apply_filters('get_the_modified_time', $the_time, $d);
}

/**
 * Display the time at which the specified event occured
 *
 * @param string $d Optional Either 'G', 'U', or php date format defaults to the value specified in the time_format option.
 * @param string $which Optional, default is added to shelf. The meta timestamp to get
 */
function the_review_custom_time ($d='', $which='aml_added') {
	echo apply_filters('the_modified_time', get_the_review_custom_time($d, $which), $d);
}

/**
 * Retrieve the date on which the specified event occured
 *
 * @param string $d Optional. PHP date format. Defaults to the "date_format" option
 * @param string $which Optional, default is added to shelf. The meta timestamp to get
 * @return string
 */
function get_the_review_custom_date ($d='', $which='aml_added') {
	if ( '' == $d )
		$the_time = get_the_review_custom_time(get_option('date_format'), $which);
	else
		$the_time = get_the_review_custom_time($d, $which);
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
function the_review_custom_date ($d='', $which='aml_added', $before='', $after='', $echo=true) {
	$the_modified_date = $before . get_the_review_custom_date($d, $which) . $after;
	$the_modified_date = apply_filters('the_modified_date', $the_modified_date, $d, $before, $after);

	if ( $echo )
		echo $the_modified_date;
	else
		return $the_modified_date;
}

/**
 * Display post submit form fields.
 *
 * @since 2.7.0
 *
 * @param object $post
 */
function aml_pubdel_box($post) {
	?>
<div id="major-publishing-actions">
<?php do_action('post_submitbox_start'); ?>
	<div id="delete-action">
	<?php if ( current_user_can( "delete_post", $post->ID ) ) {
			if ( !EMPTY_TRASH_DAYS ) {
				$delete_text = __('Delete Permanently');
			}
			else {
				$delete_text = __('Move to Trash');
			} ?>
		<a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a>
	<?php } ?>
	</div>

	<div id="publishing-action">
		<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading" alt="" />
		<?php if (0 == $post->ID) { ?>
			<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
		<?php submit_button( __( 'Publish' ), 'primary', 'publish', false, array( 'tabindex' => '5', 'accesskey' => 'p' ) );
		} else { ?>
			<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
			<input name="save" type="submit" class="button-primary" id="publish" tabindex="5" accesskey="p" value="<?php esc_attr_e('Update') ?>" />
		<?php } ?>
	</div>
	<div class="clear"></div>
	<?php
}

/**
 * Display post submit form fields.
 *
 * @since 2.7.0
 *
 * @param object $post
 */
function aml_status_box($post, $can_publish) {
	$stati = aml_get_review_stati();
?>
<div class="misc-pub-section<?php if ( !$can_publish ) { echo ' misc-pub-section-last'; } ?>">
	<label for="post_status"><?php _e('Status:') ?></label>
	<span id="post-status-display">
	<?php _e($stati[$post->post_status]['label']); ?>
	</span>
	<?php if ($can_publish) { ?>
		<a href="#post_status" class="edit-post-status hide-if-no-js" tabindex='4'><?php _e('Edit') ?></a>
		<div id="post-status-select" class="hide-if-js">
			<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr($post->post_status); ?>" />
			<select name='post_status' id='post_status' tabindex='4'>
			<?php foreach ($stati as $name => $args) { ?>
				<option<?php selected( $post->post_status, $name ); ?> value='<?php echo $name; ?>'><?php _e($args['label'], 'amazon-library') ?></option>
			<?php } ?>
			</select>
			<a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e('OK'); ?></a>
			<a href="#post_status" class="cancel-post-status hide-if-no-js"><?php _e('Cancel'); ?></a>
		</div>
	<?php } ?>
</div>
<?php
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
	$stamp = __('<b>%1$s</b>');
	$date = date_i18n($datef, strtotime($time));
?>
<div id="<?php echo $which; ?>" class="curtime">
	<?php echo $string; ?>: <span id="timestamp-<?php echo $which; ?>"><?php printf($stamp, $date); ?></span>
	<a href="#edit_timestamp-<?php echo $which; ?>" class="edit-timestamp hide-if-no-js" tabindex='5'><?php _e('Edit') ?></a>
	<div id="timestampdiv-<?php echo $which; ?>" class="hide-if-js"><?php aml_touch_time($time, $which); ?></div>
</div>
<?php
}

/**
 * Generate a timestamp field in html
 * Adapted from WP's touch_time()
 *
 * @param string $which which timestamp to produce
 * @param bool $edit true for edit mode (default)
 * @param int $tab_index tab index for input fields (default is none)
 * @param bool $echo false to return a string, true to echo (default is true)
 * @return mixed html string if $echo is false, null otherwise
 */
function aml_touch_time($time, $which, $echo=true ) {
	global $wp_locale, $post, $comment;

	$time_adj = current_time('timestamp');

	$jj = mysql2date( 'd', $time, false );
	$mm = mysql2date( 'm', $time, false );
	$aa = mysql2date( 'Y', $time, false );
	$hh = mysql2date( 'H', $time, false );
	$mn = mysql2date( 'i', $time, false );
	$ss = mysql2date( 's', $time, false );

	$cur_jj = gmdate( 'd', $time_adj );
	$cur_mm = gmdate( 'm', $time_adj );
	$cur_aa = gmdate( 'Y', $time_adj );
	$cur_hh = gmdate( 'H', $time_adj );
	$cur_mn = gmdate( 'i', $time_adj );

	$month = '<select id="mm-' . $which . '" name="mm-' . $which . "\">\n";
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$month .= "\t\t\t" . '<option value="' . zeroise($i, 2) . '"';
		if ( $i == $mm )
			$month .= ' selected="selected"';
		$month .= '>' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
	}
	$month .= '</select>';

	$day = '<input type="text" id="jj-' . $which . '" name="jj-' . $which . '" value="' . $jj . '" size="2" maxlength="2" autocomplete="off" />';
	$year = '<input type="text" id="aa-' . $which . '" name="aa-' . $which . '" value="' . $aa . '" size="4" maxlength="4" autocomplete="off" />';
	$hour = '<input type="text" id="hh-' . $which . '" name="hh-' . $which . '" value="' . $hh . '" size="2" maxlength="2" autocomplete="off" />';
	$minute = '<input type="text" id="mn-' . $which . '" name="mn-' . $which . '" value="' . $mn . '" size="2" maxlength="2" autocomplete="off" />';

	$ret = '<div class="timestamp-wrap">';
	$ret .= sprintf(__('%1$s%2$s, %3$s @ %4$s : %5$s'), $month, $day, $year, $hour, $minute);
	$ret .= '</div><input type="hidden" id="ss-' . $which . '" name="ss-' . $which . '" value="' . $ss . '" />';

	$ret .= "\n\n";
	foreach ( array('mm', 'jj', 'aa', 'hh', 'mn') as $timeunit ) {
		$ret .= '<input type="hidden" id="hidden_' . $timeunit . '-' . $which . '" name="hidden_' . $timeunit . '" value="' . $$timeunit . '" />' . "\n";
		$cur_timeunit = 'cur_' . $timeunit;
		$ret .= '<input type="hidden" id="'. $cur_timeunit . '-' . $which . '" name="'. $cur_timeunit . '" value="' . $$cur_timeunit . '" />' . "\n";
	}

	$ret .= '<p>';
	$ret .= '<a href="#edit_timestamp-' . $which . '" class="save-timestamp hide-if-no-js button">' . __('OK') .  '</a>';
	$ret .= '<a href="#edit_timestamp-' . $which . '" class="cancel-timestamp hide-if-no-js">' . __('Cancel') .  '</a>';
	$ret .= '</p>';

	if (!$echo) {
		return $ret;
	}
	echo $ret;
}

function review_stars ($check='', $readonly=false, $split=true) {
	$values = ($split) ? array('0.5', '1', '1.5', '2', '2.5', '3', '3.5', '4', '4.5', '5') : array('1', '2', '3', '4', '5');
	$disabled = disabled($readonly, true, false);
	$split = ($split) ? ' {split:2}' : '';

	echo '<div id="aml_rating">';
	foreach ($values as $val) {
		$checked = checked($check, $val, false);
		echo '<input name="aml_rating" type="radio" value="' . $val . '"' . $checked . $disabled . ' class="star' . $split . '" />';
	}
	echo '</div><br /><br />';
}

?>