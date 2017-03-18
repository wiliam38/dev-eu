var hover_timeout = [];

$().ready(function() {	
	$('#product_colors a').hover(function() {
		product_id = $(this).attr('data-product_id');
		if (typeof(hover_timeout[product_id]) == 'undefined') hover_timeout[product_id] = null;
		
		clearTimeout(hover_timeout[product_id]);
		$('#product_color_images .image-wrapper').finish();
		if ($(this).attr('data-color') != $('#product_color_images_'+product_id+' .image-wrapper:visible').attr('data-color')) {
			$('#product_color_images_'+product_id+' .image-wrapper:visible').fadeOut();
			$('#product_color_images_'+product_id+' .image-wrapper[data-color="'+$(this).attr('data-color')+'"]').fadeIn();
			
			$(this).closest('.data-block').find('.add-to-chart').val($(this).attr('data-order-btn'));
			if (parseFloat($(this).attr('data-balance')) > 0) $(this).closest('.data-block').find('.price-order-info').hide();
			else $(this).closest('.data-block').find('.price-order-info').show();
		}
	}, function() {
		hover_timeout[product_id] = setTimeout(function() {								
			$('#product_color_images_'+product_id+' .image-wrapper').finish();
			if ($('#product_color_images_'+product_id+' .image-wrapper.active').attr('data-color') != $('#product_color_images_'+product_id+' .image-wrapper:visible').attr('data-color')) {
				$('#product_color_images_'+product_id+' .image-wrapper:visible').fadeOut();
				$('#product_color_images_'+product_id+' .image-wrapper.active').fadeIn();
				
				$('#product_color_images_'+product_id).closest('.data-block').find('.add-to-chart').val($('#product_color_images_'+product_id+' .image-wrapper.active').attr('data-order-btn'));
				if (parseFloat($('#product_color_images_'+product_id+' .image-wrapper.active').attr('data-balance')) > 0) $('#product_color_images_'+product_id+' .image-wrapper.active').closest('.data-block').find('.price-order-info').hide();
				else $('#product_color_images_'+product_id+' .image-wrapper.active').closest('.data-block').find('.price-order-info').show();
			}
		}, 200);
	});
	
	$('#product_gallery a').click(function(e) {
		e.preventDefault();
		$('#product_gallery_view .image-wrapper').finish();
		if ($('#product_gallery_view .image-wrapper:visible').attr('data-id') != $(this).attr('data-id')) {
			$('#product_gallery_view .image-wrapper:visible').fadeOut();
			$('#product_gallery_view .image-wrapper[data-id="'+$(this).attr('data-id')+'"]').fadeIn();
		}
	});
	
	$('.vAlign').vAlign();
});