jQuery(document).ready( function($) {
	$('#parent_id').live('change', function() {
		var data = {
			action: 'ml_review_product',
			product: jQuery('#parent_id').val(),
		};

		jQuery.post(ajaxurl, data, function(image) {
			if (image) {
				jQuery('#ml_product-thumb').html('<img src="' + image + '" />');
			}
			else {
				jQuery('#ml_product-thumb').html('');
			}
		});
	});
});