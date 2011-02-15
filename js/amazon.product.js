jQuery(document).ready(function(jQuery) {
	jQuery("#aml_search_button").click( function() {
		var data = {
			action: 'aml_amazon_search',
			type: jQuery('#aml_search_type').val(),
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

	jQuery("#aml_lookup_button").click( function() {
		var data = {
			action: 'aml_amazon_lookup',
			lookup: jQuery('#aml_lookup_string').val(),
		};

		jQuery.post(ajaxurl, data, function(data) {
			jQuery('#aml_amazon_lookup .inside').append(data);
		});
	});

	jQuery("#aml_lookup_reset").click( function() {
		jQuery('.aml-list-item').remove();
		jQuery('#aml_lookup_string').val('').trigger('blur');
	});

	jQuery(".aml-item").live('click', function() {
		var title = jQuery(this).parent().find('.aml-item-title').text();
		var people = jQuery(this).parent().find('.aml-item-people-names').text();
		var amzasin = jQuery(this).parent().find('.aml-item-asin-number').text();
		var link = jQuery(this).parent().find('.aml-item-link a').attr('href');
		var image = jQuery(this).parent().parent().find('.aml-item-image img').attr('src');
		var type = jQuery('#aml_search_type').val();

		jQuery('#title').val(title);
		jQuery('#new-tag-aml_person').val(people);
		jQuery('#aml_image').val(image);
		jQuery('#aml_asin').val(amzasin);
		jQuery('#aml_link').val(link);
		jQuery('#aml_type').val(type);

		if (image !== '') {
			jQuery('#aml_image_preview').html('<img src="' + image + '" />');
		}

		if ( jQuery('#new-tag-aml_person').val() !== '' ) {
			jQuery('#aml_person').find('input.tagadd').trigger('click');
		}

		if ( jQuery('#title').val() !== '' ) {
			jQuery('#title').trigger('focus');
			jQuery('#title').trigger('blur');
		}
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

	if ( jQuery('#aml_lookup_string').val() == '' )
		jQuery('#aml_lookup_string').siblings('#aml_lookup_string-prompt-text').css('visibility', '');
	jQuery('#aml_lookup_string-prompt-text').click(function(){
		jQuery(this).css('visibility', 'hidden').siblings('#aml_lookup_string').focus();
	});
	jQuery('#aml_lookup_string').blur(function(){
		if (this.value == '')
			jQuery(this).siblings('#aml_lookup_string-prompt-text').css('visibility', '');
	}).focus(function(){
		jQuery(this).siblings('#aml_lookup_string-prompt-text').css('visibility', 'hidden');
	}).keydown(function(e){
		jQuery(this).siblings('#aml_lookup_string-prompt-text').css('visibility', 'hidden');
		jQuery(this).unbind(e);
	});
});
