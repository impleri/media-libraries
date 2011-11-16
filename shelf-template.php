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
 */
function ml_shelf_page ($shelf, $edit=false, $echo=true) {
	$meta = get_post_meta($shelf, 'ml_usage');
	$stati = ml_get_usage_stati();

	$return = (($edit) ? '<div class="products-liquid">' : '') . '<div id="products-shelves">';
	foreach ($stati as $id => $labels) {
		$return .= '<div class="products-holder-wrap">';
		$return .= '<div class="shelf-name">';
		if ($edit) {
			$return .= '<div class="shelf-name-arrow"><br /></div>';
		}
		$return .= '<h3>' . $labels['label'];
		$return .= ($edit) ? '<span><img src="' . esc_url(admin_url('images/wpspin_dark.gif')) . '" class="ajax-feedback" title="" alt="" /></span>' : '';
		$return .= '</h3></div>';
		$return .= '<div id="shelf-' . $shelf . '-' . $id . '" class="products-droppable">';
		if ($meta) {
			$args = array('post_type' => 'ml_usage', 'post_status' => $id, 'include' => $meta);
			$usages = get_posts($args);
			if (!empty($usages)) {
				foreach ($usages as $use) {
					$product = get_post($use->post_parent);
					$return .= ml_product_thumbnail($product, $use, $edit, false);
				}
			}
		}
		elseif (!$edit) {
			$return .= '<p>' . __('There are no products listed on this shelf.', 'media-libraries') . '</p>';
		}
		$return .= '<br class="clear" />';
		$return .= '</div>';
		$return .= '</div>';
		$return .= '<br class="clear" />';
	}
	$return .= '</div></div>';
	$return .= '<br class="clear" />';

	if (!$echo) {
		return $return;
	}

	echo $return;
}

function ml_product_thumbnail ($prod, $use=false, $edit=false, $echo=true) {
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

	$return = '<div id="' . $id_string . '" class="product">';

	$return .=  '<div class="product-top">';
	if ($edit) {
		$return .= '<div class="product-title-action">';
		$return .= '<a class="product-action hide-if-no-js" href="#available-products"></a>';
		$return .= '<a class="product-control-edit hide-if-js" href="' . esc_url(add_query_arg($query_arg)) . '"><span class="edit">' . __('Edit') . '</span><span class="add">' . __('Add') . '</span></a>';
		$return .= '</div>';
	}
	$return .= '<div class="product-title"><h4>' . $prod->post_title . '</h4></div>';
	$return .= '</div>';

	$return .= '<div class="product-inside">';
	$return .= '<div class="product-content">';
	if ($edit) {
		$return .= '<form action="" method="post">';
		$return .= '<label for="' . $id_string . '-status">' . __('Status:') . '</label>';
		$return .= '<select name="' . $id_arr_string . '[status]" id="' . $id_string . '-status" tabindex="4">';
		foreach ($stati as $name => $args) {
			$return .= '<option' . selected($status, $name) . ' value="' . $name . '">' . __($args['label'], 'media-libraries') . '</option>';
		}
		$return .= '</select>';
		$return .= ml_time_box($use, $id_string, $id_arr_string, true, false);
		$return .= '<input type="hidden" name="product-id" class="product-id" value="product-' . esc_attr($prod->ID) . '-' . esc_attr($use_id) . '" />';
		$return .= '<div class="product-control-actions">';
		$return .= '<div class="alignleft">';
		$return .= '<a class="product-control-remove" href="#remove">' . __('Delete') . '</a> |';
		$return .= '<a class="product-control-close" href="#close">' . __('Close') . '</a>';
		$return .= '</div>';
		$return .= '<div class="alignright">';
		$return .= '<img src="' . esc_url(admin_url('images/wpspin_light.gif')) . '" class="ajax-feedback" title="" alt="" />';
		$return .= submit_button(__( 'Save' ), 'button-primary product-control-save', 'saveproduct', false, array('id' => 'product-' . esc_attr($prod->ID) . '-saveproduct'));
		$return .= '</div>';
		$return .= '<br class="clear" />';
		$return .= '</div>';
		$return .= '</form>';
	}
	else {
		ml_time_box($use, $id_string, $id_arr_string, false, false);
	}
	$return .= '</div>';
	$return .= '</div>';

	$return .= '<div class="product-description">';
	$return .= '<img src="' . esc_url($image) . '" /><br />';
	$return .= $people . $add_html;
	$return .= '</div>';

	$return .= '</div>';

	if (!$echo) {
		return $return;
	}

	echo $return;
}

// testing

function ml_time_box ($usage, $id_string, $id_array, $edit=false, $echo=true) {
	$added = (!$usage) ? 0 : get_post_meta($usage->ID, 'ml_added', true);
	$start = (!$usage) ? 0 : get_post_meta($usage->ID, 'ml_started', true);
	$ended = (!$usage) ? 0 : get_post_meta($usage->ID, 'ml_finished', true);

	$function = ($edit) ? 'ml_show_date' : 'ml_show_times';

	$return = $function($added, 'added', $id_string, $id_array, $echo);
	$return .= $function($start, 'started', $id_string, $id_array, $echo);
	$return .= $function($ended, 'finished', $id_string, $id_array, $echo);

	if (!$echo) {
		return $return;
	}

	echo $return;
}

function ml_show_date ($time=0, $which='added', $id_string='', $id_array='', $echo=true) {
	global $post, $action;

	$time = ($time == 0) ? current_time('mysql') : $time;
	$datef = __('M j, Y @ G:i');
	$stamp = __('<b>%1$s</b>');
	$date = date_i18n($datef, strtotime($time));
	$stati = ml_get_usage_stati();
	$string = (isset($stati[$which]['single'])) ? $stati[$which]['single'] : '';

	$return = '<div id="' . $id_string . '-' . $which . '" class="curtime">';
	$return .= __($string, 'media-libraries') . ': <span id="' . $id_string . '-timestamp-' . $which . '">' . sprintf($stamp, $date) . '?></span>';
	$return .= '<a href="#edit_timestamp-' . $which . '" class="edit-timestamp hide-if-no-js" tabindex="5">' . __('Edit') . '</a>';
	$return .= '<div id="' . $id_string . '-timestampdiv-' . $which . '" class="hide-if-js">' . ml_touch_time($time, $which, $id_string, $id_array, $echo) . '</div>';
	$return .= '</div>';

	if (!$echo) {
		return $return;
	}

	echo $return;
}

function ml_show_times ($time=0, $which='added', $id_string='', $id_array='', $echo=true) {
	global $post, $action;

	$time = ($time == 0) ? current_time('mysql') : $time;
	$datef = __('M j, Y @ G:i');
	$stamp = __('<b>%1$s</b>');
	$date = date_i18n($datef, strtotime($time));
	$stati = ml_get_usage_stati();
	$string = (isset($stati[$which]['single'])) ? $stati[$which]['single'] : '';

	$return = '<div id="' . $id_string . '-' . $which . '" class="curtime">';
	$return .= $string . ': <span id="' . $id_string . '-timestamp-' . $which . '">' . sprintf($stamp, $date) . '</span>';
	$return .= '</div>';

	if (!$echo) {
		return $return;
	}

	echo $return;
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
