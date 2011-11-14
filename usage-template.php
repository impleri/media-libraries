<?php
/**
 * Template functions
 * @package media-libraries
 */

/**
 * Retrieve the time at which the specified event occured
 *
 * @param string $d Optional, default is 'U'. Either 'G', 'U', or php date format.
 * @param string $which Optional, default is added to shelf. The meta timestamp to get
 * @param bool $translate Optional, default is false. Whether to translate the result
 * @return string Returns timestamp
 *
function get_review_custom_time ($d='U', $which='ml_added', $translate=true) {
	$post = get_post();
	$which_time = get_post_meta($post->ID, $which, true);
	$time = mysql2date($d, $which_time, $translate);
	return apply_filters('get_post_modified_time', $time, $d);
}*/

/**
 * Retrieve the time at which the specified event occured
 *
 * @param string $d Optional Either 'G', 'U', or php date format defaults to the value specified in the time_format option.
 * @param string $which Optional, default is added to shelf. The meta timestamp to get
 * @return string
 *
function get_the_review_custom_time ($d='', $which='ml_added') {
	if ( '' == $d )
		$the_time = get_review_custom_time(get_option('time_format'), $which);
	else
		$the_time = get_review_custom_time($d, $which);
	return apply_filters('get_the_modified_time', $the_time, $d);
}*/

/**
 * Display the time at which the specified event occured
 *
 * @param string $d Optional Either 'G', 'U', or php date format defaults to the value specified in the time_format option.
 * @param string $which Optional, default is added to shelf. The meta timestamp to get
 *
function the_review_custom_time ($d='', $which='ml_added') {
	echo apply_filters('the_modified_time', get_the_review_custom_time($d, $which), $d);
}*/

/**
 * Retrieve the date on which the specified event occured
 *
 * @param string $d Optional. PHP date format. Defaults to the "date_format" option
 * @param string $which Optional, default is added to shelf. The meta timestamp to get
 * @return string
 *
function get_the_review_custom_date ($d='', $which='ml_added') {
	if ( '' == $d )
		$the_time = get_the_review_custom_time(get_option('date_format'), $which);
	else
		$the_time = get_the_review_custom_time($d, $which);
	return apply_filters('get_the_modified_date', $the_time, $d);
}*/

/**
 * Display the date on which the specified event occured
 *
 * @param string $d Optional. PHP date format defaults to the date_format option if not specified.
 * @param string $which Optional, default is added to shelf. The meta timestamp to get
 * @param string $before Optional. Output before the date.
 * @param string $after Optional. Output after the date.
 * @param bool $echo Optional, default is display. Whether to echo the date or return it.
 * @return string|null Null if displaying, string if retrieving.
 *
function the_review_custom_date ($d='', $which='ml_added', $before='', $after='', $echo=true) {
	$the_modified_date = $before . get_the_review_custom_date($d, $which) . $after;
	$the_modified_date = apply_filters('the_modified_date', $the_modified_date, $d, $before, $after);

	if ( $echo )
		echo $the_modified_date;
	else
		return $the_modified_date;
}*/

/**
 * Display post submit form fields.
 *
 * @since 2.7.0
 *
 * @param object $post
 *
function ml_pubdel_box($post) {
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
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Save') ?>" />
		<input name="save" type="submit" class="button-primary" id="publish" tabindex="5" accesskey="p" value="<?php esc_attr_e('Save') ?>" />
	</div>
	<div class="clear"></div>
	<?php
}*/

/**
 * Display post submit form fields.
 *
 * @param object $post
 *
function ml_status_box($post, $can_publish=true) {
	$stati = ml_get_usage_stati();
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
				<option<?php selected( $post->post_status, $name ); ?> value='<?php echo $name; ?>'><?php _e($args['label'], 'media-libraries') ?></option>
			<?php } ?>
			</select>
			<a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e('OK'); ?></a>
			<a href="#post_status" class="cancel-post-status hide-if-no-js"><?php _e('Cancel'); ?></a>
		</div>
	<?php } ?>
</div>
<?php
}*/

/**
 * Prints the date when the book was added to the database.
 * @param bool $echo Whether or not to echo the results.
 *
function usage_date ($echo=true) {
    global $book;
    $added = apply_filters('book_added', $book->added);
    if ( $echo )
        echo $added;
    return $added;
}*/

/**
 * Prints the date when the book's status was changed from unread to reading.
 * @param bool $echo Whether or not to echo the results.
 *
function usage_started ($echo=true) {
    global $book;
    if ( nr_empty_date($book->started) )
        $started = __('Not yet started.', NRTD);
    else
        $started = apply_filters('book_started', $book->started);
    if ( $echo )
        echo $started;
    return $started;

}*/

/**
 * Prints the date when the book's status was changed from reading to read.
 * @param bool $echo Whether or not to echo the results.
 *
function usage_held ($echo=true) {
    global $book;
    if ( nr_empty_date($book->finished) )
        $finished = __('Not yet finished.', NRTD);
    else
        $finished = apply_filters('book_finished', $book->finished);
    if ( $echo )
        echo $finished;
    return $finished;
}*/

/**
 * Prints the date when the book's status was changed from reading to read.
 * @param bool $echo Whether or not to echo the results.
 *
function usage_finished ($echo=true) {
    global $book;
    if ( nr_empty_date($book->finished) )
        $finished = __('Not yet finished.', NRTD);
    else
        $finished = apply_filters('book_finished', $book->finished);
    if ( $echo )
        echo $finished;
    return $finished;
}*/

/**
 * Prints the current book's status with optional overrides for messages.
 * @param bool $echo Whether or not to echo the results.
 *
function usage_status ( $echo = true, $unread = '', $reading = '', $read = '', $onhold = '' ) {
    global $book, $nr_statuses;

    if ( empty($unread) )
        $unread = $nr_statuses['unread'];
    if ( empty($reading) )
        $reading = $nr_statuses['reading'];
    if ( empty($read) )
        $read = $nr_statuses['read'];
    if ( empty($onhold) )
        $onhold = $nr_statuses['onhold'];

    switch ( $book->status ) {
        case 'unread':
            $text = $unread;
            break;
        case 'onhold':
            $text = $onhold;
            break;
        case 'reading':
            $text = $reading;
            break;
        case 'read':
            $text = $read;
            break;
        default:
            return;
    }

    if ( $echo )
        echo $text;
    return $text;
}*/


function the_product_usage () {}

