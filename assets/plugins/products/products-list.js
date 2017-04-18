var hover_timeout = [];

$().ready(function() {
	$('.product-color-change a').hover(function() {
		product_id = $(this).attr('data-product_id');
		if (typeof(hover_timeout[product_id]) == 'undefined') hover_timeout[product_id] = null;
		
		clearTimeout(hover_timeout[product_id]);
		$('#product_color_images_'+product_id+' .image-wrapper').finish();
		if ($(this).attr('data-color') != $('#product_color_images_'+product_id+' .image-wrapper:visible').attr('data-color')) {
			$('#product_color_images_'+product_id+' .image-wrapper:visible').fadeOut();
			$('#product_color_images_'+product_id+' .image-wrapper[data-color="'+$(this).attr('data-color')+'"]').fadeIn();
			
			$(this).closest('.item').find('.add-to-chart').val($(this).attr('data-order-btn'));
		}
	}, function() {
		product_id = $(this).attr('data-product_id');
		if (typeof(hover_timeout[product_id]) == 'undefined') hover_timeout[product_id] = null;
		
		hover_timeout[product_id] = setTimeout(function() {								
			$('#product_color_images_'+product_id+' .image-wrapper').finish();
			if ($('#product_color_images_'+product_id+' .image-wrapper.active').attr('data-color') != $('#product_color_images_'+product_id+' .image-wrapper:visible').attr('data-color')) {
				$('#product_color_images_'+product_id+' .image-wrapper:visible').fadeOut();
				$('#product_color_images_'+product_id+' .image-wrapper.active').fadeIn();
				
				$('#product_color_images_'+product_id).closest('.item').find('.add-to-chart').val($('#product_color_images_'+product_id+' .image-wrapper.active').attr('data-order-btn'));
			}
		}, 200);
	});
	

	$('#how_to_choose_fullpage').fullpage2({
		resize : false,
		scrollingSpeed: 300,
		css3: true,
		verticalCentered: true,
		afterLoad: function() {
			initBlocks2();
		},
		/*onLeave: function(anchorLink, index, slideIndex, direction) {
			$('#fullpage .section').addClass('inactive');
			$('#fullpage .section:eq('+(index-1)+')').removeClass('inactive');
		},*/
		afterResize: function(){
			$('#how_to_choose_fullpage div.vAlign').css('margin-top', '0px');
			$('#how_to_choose_fullpage div.vAlign').vAlign();
		}
	});
	initBlocks2();
	$('#how_to_choose_fullpage .vAlign').vAlign();
	
	$('.product-coffee-gift').hover(function() {
		$('.coffee-gift-info', this).stop().fadeIn(200);
		$('#page_content .products-list').css('z-index', '999999');
		$('#data_block_prev').css('z-index', '999998');
	}, function() {
		$('.coffee-gift-info', this).stop().fadeOut(200, function() {
			$('#page_content .products-list').css('z-index', 'auto');
			$('#data_block_prev').css('z-index', 'auto');
		});		
	});
});