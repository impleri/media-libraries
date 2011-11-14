var mlProducts;
(function($) {
	mlProducts = {
		init : function() {
			var rem, shelves = $('div.products-droppable'), isRTL = !! ( 'undefined' != typeof isRtl && isRtl ),
			margin = ( isRtl ? 'marginRight' : 'marginLeft' );

			$('#products-shelves').children('.products-holder-wrap').children('.shelf-name').click(function() {
				$(this).siblings('.products-droppable').parent().toggleClass('closed');
			});

			shelves.each(function(){
				var h = 50, H = $(this).children('.product').length;
				h = h + parseInt(H * 48, 10);
				$(this).css( 'minHeight', h + 'px' );
			});

			$('a.product-action').live('click', function(){
				var css = {}, product = $(this).closest('div.product'), inside = product.children('.product-inside'), w = parseInt( product.find('input.product-width').val(), 10 );

				if ( inside.is(':hidden') ) {
					if ( w > 250 && inside.closest('div.products-droppable').length ) {
						css['width'] = w + 30 + 'px';
						if ( inside.closest('#ml_shelf_add > div.inside').length )
							css[margin] = 235 - w + 'px';
						product.css(css);
					}
					mlProducts.fixLabels(product);
					inside.slideDown('fast');
				} else {
					inside.slideUp('fast', function() {
						product.css({'width':'', margin:''});
					});
				}
				return false;
			});

			$('input.product-control-save').live('click', function(){
				mlProducts.save( $(this).closest('div.product'), 0, 1, 0 );
				return false;
			});

			$('a.product-control-remove').live('click', function(){
				mlProducts.save( $(this).closest('div.product'), 1, 1, 0 );
				return false;
			});

			$('a.product-control-close').live('click', function(){
				mlProducts.close( $(this).closest('div.product') );
				return false;
			});

			shelves.children('.product').each(function() {
				if ( $('p.product-error', this).length )
					$('a.product-action', this).click();
			});

			mlProducts.makeDrag($('.product'));

			shelves.droppable({
				tolerance: 'pointer',
				accept: function(o){
					return $(o).parent().attr('id') != $(this).attr('id');
				},
				hoverClass: 'shelf-hover',

				// create/update usage
				drop: function(e,ui) {
					var id = ui.draggable.attr('id'),
						sb = $(this).attr('id');

					ui.draggable.css({margin:'', 'width':''});
					mlProducts.fixWebkit();
					$(this).find('#droppable').remove();

					// do AJAX method to easy update product
					var product = ($(ui.draggable).parent().attr('id') == 'product-list') ? $(ui.draggable).clone() : $(ui.draggable);
					mlProducts.move(product, sb);
				},
			});

			$('#available-products').droppable({
				tolerance: 'pointer',
				accept: function(o){
					return $(o).parent().attr('id') != 'product-list';
				},
				hoverClass: 'shelf-hover',

				// delete usage if dragged back to sort list
				drop: function(e,ui) {
					ui.draggable.addClass('deleting');
					$('#removing-product').hide().children('span').html('');
					mlProducts.save(ui.draggable, 1, 0, 1);
					ui.draggable.remove();
				},

				// emphasise that dropping here will delete the usages
				over: function(e,ui) {
					ui.draggable.addClass('deleting');
				},

				// remove messages of deletion once away from here
				out: function(e,ui) {
					ui.draggable.removeClass('deleting');
					$('#removing-product').hide().children('span').html('');
				}
			});
		},

		makeDrag: function(product) {
			$(product).draggable({
				connectToSortable: 'div.products-droppable',
				handle: '> .product-top > .product-title',
				distance: 2,
				helper: 'clone',
				zIndex: 5,
				containment: 'document',
				start: function(e,ui) {
					mlProducts.fixWebkit(1);
				},
				stop: function(e,ui) {
					mlProducts.fixWebkit();
				}
			});
		},

		// action for moving a product anywhere
		move : function(prod, sb) {
			var shelf = $('#' + sb),
				id = prod.attr('id'),
				a = {
					action: 'ml_usage_save',
					nonce: $('#_mlnonce_products').val(),
					product: id,
					shelf: sb
				};

			if (sb)
				shelf.closest('div.products-holder-wrap').find('img.ajax-feedback').css('visibility', 'visible');

			$.post(ajaxurl, a, function(d) {
				// first change usage time
				var data = $.parseJSON(d),
				p = $('#' + id, shelf);

				// switch out __i__ with the ml_usage ID
				if (data.id) {
					p.html(p.html().replace(/<[^<>]+>/g, function(m){ return m.replace(/__i__|%i%/g, data.id); }));
					p.attr('id', p.attr('id').replace(/__i__|%i%/g, data.id));
				}
					$('img.ajax-feedback').css('visibility', 'hidden');
			});

			this.resize();
			prod.insertBefore($('br.clear', shelf));
			mlProducts.makeDrag(prod);
		},

		// TODO: handle time and status
		save : function(product, del, animate, order) {
			var sb = product.closest('div.products-droppable').attr('id'), data = product.find('form').serialize(), a;
			$('.ajax-feedback', product).css('visibility', 'visible');

			a = {
				action: 'ml_usage_save',
				nonce: $('#_mlnonce_products').val(),
				product: product.attr('id'),
				shelf: sb
			};

			if ( del )
				a['delete'] = 1;

			data += '&' + $.param(a);

			$.post( ajaxurl, data, function(r){
				var id;

				if ( del ) {
					if ( !$('input.product_number', product).val() ) {
						id = $('input.product-id', product).val();
						$('#available-products').find('input.product-id').each(function(){
							if ( $(this).val() == id )
								$(this).closest('div.product').show();
						});
					}

					if ( animate ) {
						order = 0;
						product.slideUp('fast', function(){
							$(this).remove();
							mlProducts.saveOrder();
						});
					} else {
						product.remove();
						mlProducts.resize();
					}
				} else {
					$('.ajax-feedback').css('visibility', 'hidden');
					if ( r && r.length > 2 ) {
						$('div.product-content', product).html(r);
						mlProducts.fixLabels(product);
					}
				}
				if ( order )
					mlProducts.saveOrder();
			});
		},

		resize : function() {
			$('div.products-droppable').each(function(){
				var h = 50, H = $(this).children('.product').length;
				h = h + parseInt(H * 48, 10);
				$(this).css( 'minHeight', h + 'px' );
			});
		},

		fixWebkit : function(n) {
			n = n ? 'none' : '';
			$('body').css({
				WebkitUserSelect: n,
		KhtmlUserSelect: n
			});
		},

		fixLabels : function(product) {
			product.children('.product-inside').find('label').each(function(){
				var f = $(this).attr('for');
				if ( f && f == $('input', this).attr('id') )
					$(this).removeAttr('for');
			});
		},

		close : function(product) {
			product.children('.product-inside').slideUp('fast', function(){
				product.css({'width':'', margin:''});
			});
		}
};

$(document).ready(function($){ mlProducts.init(); });

})(jQuery);
