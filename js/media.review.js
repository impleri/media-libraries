jQuery(document).ready( function($) {
	$('#parent_id').live('change', function() {
		var data = {
			action: 'ml_product_image',
			product: jQuery('#parent_id').val(),
		};

		jQuery.post(ajaxurl, data, function(i) {
			if (i) {
				jQuery('#ml_product-thumb').html('<img src="' + i + '" />');
			}
			else {
				jQuery('#ml_product-thumb').html('');
			}
		});
	});
});