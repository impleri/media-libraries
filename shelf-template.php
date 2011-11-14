<?php
/**
 * template functions for shelves
 * @package media-libraries
 */

/**
 * Wrapper to template hack for archive-ml_shelf
 *
 * @param string found template
 * @return string path to template
 */
function ml_shelf_archive_template ($template) {
	return ml_insert_template ($template, 'ml_shelf', 'archive');
}

/**
 * Wrapper to template hack for single-ml_shelf
 *
 * @param string found template
 * @return string path to template
 */
function ml_shelf_single_template ($template) {
	return ml_insert_template ($template, 'ml_shelf', 'single');
}

/**
 * Display a thumbnail page of products
 *
function ml_shelf_page ($products, $format='t', $page=1, $max_pages=1) {
	$function = ($format == 'l') ? 'product_shelf_row' : 'product_shelf_image';
	$paginate = '<div class="aml-paginate">';
	$paginate .= ($page < 1) ? '' : '<div class="aml-paginate-prev">' . __('Previous page', 'media-libraries') . '</div>';
	$paginate .= ($page >= $max_pages) ? '' : '<div class="aml-paginate-next">' . __('Next page', 'media-libraries') . '</div>';
	$paginate .= '</div>';

	$html = '';
	if (is_array($products)) {
		foreach ($products as $prod) {
			$html .= $function($prod, '<li class="ml_product">', '</li>');
		}
	}
	return (empty($html)) ? __('No products found on the self.', 'media-libraries') : '<ul>' . $html . $paginate . '</ul>';
}*/

function ml_product_thumbnail ($prod, $use=false) {
	static $i = 0;

	$i++;
	$image = get_post_meta($prod->ID, 'ml_image', true);
	$link = get_post_meta($prod->ID, 'ml_link', true);
	$stati = ml_get_usage_stati();
	$query_arg = array('editproduct' => $prod->ID, 'addnew' => 1);
	$people = get_the_term_list($prod->ID, 'ml_person', '<span class="ml_product-people">', ', ', '</span></br>');
	$add_html = '';
	$add_html = apply_filters('ml-product-thumbnail', $add_html, $prod, $use);
	$use_id = (!$use) ? '__i__' : $use->ID;
	$id_string = 'product-'.esc_attr($prod->ID).'-'.esc_attr($use_id);
	$id_arr_string = 'product-'.esc_attr($prod->ID).'['.esc_attr($use_id).']';
	$status = (!$use) ? false : $use->post_status;
?>
<div id="<?php echo $id_string; ?>" class="product">
	<div class="product-top">
		<div class="product-title-action">
			<a class="product-action hide-if-no-js" href="#available-products"></a>
			<a class="product-control-edit hide-if-js" href="<?php echo esc_url(add_query_arg($query_arg)); ?>"><span class="edit"><?php _e('Edit'); ?></span><span class="add"><?php _e('Add'); ?></span></a>
		</div>
		<div class="product-title"><h4><?php echo $prod->post_title; ?></h4></div>
	</div>

	<div class="product-inside">
		<form action="" method="post">
		<div class="product-content">
			<label for="<?php echo $id_string; ?>-status"><?php _e('Status:'); ?></label>
			<select name="<?php echo $id_arr_string; ?>[status]" id="<?php echo $id_string; ?>-status" tabindex="4">
			<?php foreach ($stati as $name => $args) { ?>
				<option<?php selected($status, $name ); ?> value="<?php echo $name; ?>"><?  _e($args['label'], 'media-libraries'); ?></option>
			<? } ?>
			</select>
			<?php ml_time_box($use, $id_string, $id_arr_string); ?>
			<input type="hidden" name="product-id" class="product-id" value="product-<?php echo esc_attr($prod->ID); ?>-<?php echo esc_attr($use_id); ?>" />
			<div class="product-control-actions">
				<div class="alignleft">
					<a class="product-control-remove" href="#remove"><?php _e('Delete'); ?></a> |
					<a class="product-control-close" href="#close"><?php _e('Close'); ?></a>
				</div>
				<div class="alignright">
					<img src="<?php echo esc_url(admin_url('images/wpspin_light.gif')); ?>" class="ajax-feedback" title="" alt="" />
					<?php submit_button(__( 'Save' ), 'button-primary product-control-save', 'saveproduct', false, array('id' => 'product-' . esc_attr($prod->ID) . '-saveproduct')); ?>
				</div>
				<br class="clear" />
			</div>
		</div>
		</form>
	</div>

	<div class="product-description">
		<img src="<?php echo esc_url($image); ?>" /><br />
		<?php echo $people . $add_html; ?>
	</div>
</div>
	<?php
}

function ml_time_box ($usage, $id_string, $id_array) {
	$added = (!$usage) ? 0 : get_post_meta($usage->ID, 'ml_added', true);
	$start = (!$usage) ? 0 : get_post_meta($usage->ID, 'ml_started', true);
	$ended = (!$usage) ? 0 : get_post_meta($usage->ID, 'ml_finished', true);

	ml_show_date($added, 'added', $id_string, $id_array);
	ml_show_date($start, 'started', $id_string, $id_array);
	ml_show_date($ended, 'finished', $id_string, $id_array);
}

function ml_show_date ($time=0, $which='added', $id_string='', $id_array='') {
	global $post, $action;

	$time = ($time == 0) ? current_time('mysql') : $time;
	$datef = __('M j, Y @ G:i');
	$stamp = __('<b>%1$s</b>');
	$date = date_i18n($datef, strtotime($time));
	switch ($which) {
		case 'added':
			$string = 'Added to Shelf';
			break;
		case 'started':
			$string = 'Began Usage';
			break;
		case 'finished':
			$string = 'Usage Finished';
			break;
		default:
			$string = '';
			break;
	}
?>
<div id="<?php echo $id_string; ?>-<?php echo $which; ?>" class="curtime">
	<?php echo $string; ?>: <span id="<?php echo $id_string; ?>-timestamp-<?php echo $which; ?>"><?php printf($stamp, $date); ?></span>
	<a href="#edit_timestamp-<?php echo $which; ?>" class="edit-timestamp hide-if-no-js" tabindex='5'><?php _e('Edit') ?></a>
	<div id="<?php echo $id_string; ?>-timestampdiv-<?php echo $which; ?>" class="hide-if-js"><?php ml_touch_time($time, $which, $id_string, $id_array); ?></div>
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
function ml_touch_time($time, $which, $id_string, $id_array, $echo=true ) {
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
	$cur_ss = gmdate( 's', $time_adj );

	$month = '<select id="<?php echo $id_string; ?>-mm-' . $which . '" name="<?php echo $id_string; ?>-mm-' . $which . "\">\n";
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$month .= "\t\t\t" . '<option value="' . zeroise($i, 2) . '"' . selected($i, $mm, false) .'>' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
	}
	$month .= '</select>';

	$day = '<input type="text" id="<?php echo $id_string; ?>-jj-' . $which . '" name="<?php echo $id_array; ?>[jj-' . $which . ']" value="' . $jj . '" size="2" maxlength="2" autocomplete="off" />';
	$year = '<input type="text" id="<?php echo $id_string; ?>-aa-' . $which . '" name="<?php echo $id_array; ?>[aa-' . $which . ']" value="' . $aa . '" size="4" maxlength="4" autocomplete="off" />';
	$hour = '<input type="text" id="<?php echo $id_string; ?>-hh-' . $which . '" name="<?php echo $id_array; ?>[hh-' . $which . ']" value="' . $hh . '" size="2" maxlength="2" autocomplete="off" />';
	$minute = '<input type="text" id="<?php echo $id_string; ?>-mn-' . $which . '" name="<?php echo $id_array; ?>[mn-' . $which . ']" value="' . $mn . '" size="2" maxlength="2" autocomplete="off" />';

	$ret = '<div class="timestamp-wrap">';
	$ret .= sprintf(__('%1$s%2$s, %3$s @ %4$s : %5$s'), $month, $day, $year, $hour, $minute);
	$ret .= '</div><input type="hidden" id="<?php echo $id_string; ?>-ss-' . $which . '" name="<?php echo $id_array; ?>[ss-' . $which . ']" value="' . $ss . '" />';

	$ret .= "\n\n";
	foreach ( array('mm', 'jj', 'aa', 'hh', 'mn', 'ss') as $timeunit ) {
		$ret .= '<input type="hidden" id="<?php echo $id_string; ?>-hidden_' . $timeunit . '-' . $which . '" name="<?php echo $id_string; ?>-hidden_' . $timeunit . '" value="' . $$timeunit . '" />' . "\n";
		$cur_timeunit = 'cur_' . $timeunit;
		$ret .= '<input type="hidden" id="<?php echo $id_string; ?>-'. $cur_timeunit . '-' . $which . '" name="<?php echo $id_string; ?>-'. $cur_timeunit . '" value="' . $$cur_timeunit . '" />' . "\n";
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
