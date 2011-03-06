function setSuggest(id) {
	jQuery('#' + id).suggest("<?php echo get_bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=aml_product_search");
}

jQuery(document).ready(function(jQuery) {
	jQuery("#aml_search_button").click( function() {
		var data = {
			action: 'aml_shelf_search',
			search: jQuery('#aml_search_string').val(),
		};

		jQuery.post(ajaxurl, data, function(data) {
			jQuery('.aml_search_box').append(data);
		});
	});

	jQuery("#aml_search_string").keydown( function(event) {
		if (event.which == '13') {
			jQuery("#aml_search_button").click();
		}
	});

	jQuery("#aml_search_reset").click( function() {
		jQuery('.aml-list-item').remove();
		jQuery('#aml_search_string').val('').trigger('blur');
	});

	jQuery(".aml-item").live('click', function() {
		var data = {
			action: 'aml_shelf_add_product',
			shelf: jQuery('#aml_shelf_id').val(),
			product: jQuery(this).parent().find('.aml-item-id').val(),
		};

		jQuery.post(ajaxurl, data, function(data) { return; });
	});

	jQuery(".aml-list-item").live('mouseover mouseout', function(event) {
		if (event.type == 'mouseover') {
			jQuery(this).addClass("aml-hover");
		} else {
			jQuery(this).removeClass("aml-hover");
		}
	});

	if ( jQuery('#aml_search_string').val() == '' )
		jQuery('#aml_search_string').siblings('#aml_search_string-prompt-text').css('visibility', '');
	jQuery('#aml_search_string-prompt-text').click(function(){
		jQuery(this).css('visibility', 'hidden').siblings('#aml_search_string').focus();
	});
	jQuery('#aml_search_string').blur(function(){
		if (this.value == '')
			jQuery(this).siblings('#aml_search_string-prompt-text').css('visibility', '');
	}).focus(function(){
		jQuery(this).siblings('#aml_search_string-prompt-text').css('visibility', 'hidden');
	}).keydown(function(e){
		jQuery(this).siblings('#aml_search_string-prompt-text').css('visibility', 'hidden');
		jQuery(this).unbind(e);
	});
});
