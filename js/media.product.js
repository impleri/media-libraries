jQuery(document).ready(function(jQuery) {
	jQuery("#ml_search_button").click( function() {
		var data = {
			action: 'ml_amazon_search',
			type: jQuery('#ml_search_type').val(),
			search: jQuery('#ml_search_string').val(),
		};

		jQuery.post(ajaxurl, data, function(data) {
			jQuery('.ml_search_box').append(data);
		});
	});

	jQuery("#ml_search_string").keydown( function(event) {
		if (event.which == '13') {
			jQuery("#ml_search_button").click();
		}
	});

	jQuery("#ml_search_reset").click( function() {
		jQuery('.ml-list-item').remove();
		jQuery('#ml_search_string').val('').trigger('blur');
	});

	jQuery("#ml_lookup_button").click( function() {
		var data = {
			action: 'ml_amazon_lookup',
			lookup: jQuery('#ml_lookup_string').val(),
		};

		jQuery.post(ajaxurl, data, function(data) {
			jQuery('#ml_amazon_lookup .inside').append(data);
		});
	});

	jQuery("#ml_lookup_reset").click( function() {
		jQuery('.ml-list-item').remove();
		jQuery('#ml_lookup_string').val('').trigger('blur');
	});

	jQuery(".ml-item").live('click', function() {
		var title = jQuery(this).parent().find('.ml-item-title').text();
		var people = jQuery(this).parent().find('.ml-item-people-names').text();
		var amzasin = jQuery(this).parent().find('.ml-item-asin-number').text();
		var link = jQuery(this).parent().find('.ml-item-link a').attr('href');
		var image = jQuery(this).parent().parent().find('.ml-item-image img').attr('src');
		var type = jQuery('#ml_search_type').val();

		jQuery('#title').val(title);
		jQuery('#new-tag-ml_person').val(people);
		jQuery('#ml_image').val(image);
		jQuery('#ml_asin').val(amzasin);
		jQuery('#ml_link').val(link);
		jQuery('#ml_type').val(type);

		if (image !== '') {
			jQuery('#ml_image_preview').html('<img src="' + image + '" />');
		}

		if ( jQuery('#new-tag-ml_person').val() !== '' ) {
			jQuery('#ml_person').find('input.tagadd').trigger('click');
		}

		if ( jQuery('#title').val() !== '' ) {
			jQuery('#title').trigger('focus');
			jQuery('#title').trigger('blur');
		}
	});

	jQuery(".ml-list-item").live('mouseover mouseout', function(event) {
		if (event.type == 'mouseover') {
			jQuery(this).addClass("ml-hover");
		} else {
			jQuery(this).removeClass("ml-hover");
		}
	});

	if ( jQuery('#ml_search_string').val() == '' )
		jQuery('#ml_search_string').siblings('#ml_search_string-prompt-text').css('visibility', '');
	jQuery('#ml_search_string-prompt-text').click(function(){
		jQuery(this).css('visibility', 'hidden').siblings('#ml_search_string').focus();
	});
	jQuery('#ml_search_string').blur(function(){
		if (this.value == '')
			jQuery(this).siblings('#ml_search_string-prompt-text').css('visibility', '');
	}).focus(function(){
		jQuery(this).siblings('#ml_search_string-prompt-text').css('visibility', 'hidden');
	}).keydown(function(e){
		jQuery(this).siblings('#ml_search_string-prompt-text').css('visibility', 'hidden');
		jQuery(this).unbind(e);
	});

	if ( jQuery('#ml_lookup_string').val() == '' )
		jQuery('#ml_lookup_string').siblings('#ml_lookup_string-prompt-text').css('visibility', '');
	jQuery('#ml_lookup_string-prompt-text').click(function(){
		jQuery(this).css('visibility', 'hidden').siblings('#ml_lookup_string').focus();
	});
	jQuery('#ml_lookup_string').blur(function(){
		if (this.value == '')
			jQuery(this).siblings('#ml_lookup_string-prompt-text').css('visibility', '');
	}).focus(function(){
		jQuery(this).siblings('#ml_lookup_string-prompt-text').css('visibility', 'hidden');
	}).keydown(function(e){
		jQuery(this).siblings('#ml_lookup_string-prompt-text').css('visibility', 'hidden');
		jQuery(this).unbind(e);
	});
});
