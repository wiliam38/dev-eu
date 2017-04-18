jQuery.fn.issSlider = function(params) {
	// PARAMS
	class_name = typeof(params)!='undefined'&&typeof(params.class_name)!='undefined'?params.class_name:'iss-slider';
	
	// INIT OBJ
	var obj = $(this);
	obj.addClass('iss-slider').addClass(class_name).show();
	
	// ADD CLASS TO ALL ITEMS
	$(obj).children('*').addClass('item');
	$(obj).append('<div class="iss-slider-list"></div>');
	$('div.iss-slider-list',obj).wrap('<div class="iss-slider-wrapper"></div>')
	$(obj).children('.item').appendTo($('div.iss-slider-list',obj));
	$('div.iss-slider-list',obj).append('<div style="clear: both;"></div>');
	
	// GET TOTAL WIDTH
	var totalWidth = 0;
	$('div.iss-slider-list',obj).children('.item').each(function() { totalWidth = totalWidth + $(this).outerWidth(true); });
	$('div.iss-slider-list',obj).width(totalWidth);
	
	// BUTTONS
	$('<a href="#next" class="iss-slider-next"></a>')
		.insertAfter($('div.iss-slider-wrapper',obj))
		.click(function(e) {
			e.preventDefault();			
			$('div.iss-slider-wrapper',obj).animate({scrollLeft: '+='+$('div.iss-slider-wrapper',obj).width()}, 750);
		});
	$('<a href="#next" class="iss-slider-prev"></a>')
	.insertAfter($('div.iss-slider-wrapper',obj))
	.click(function(e) {
		e.preventDefault();		
		$('div.iss-slider-wrapper',obj).animate({scrollLeft: '-='+$('div.iss-slider-wrapper',obj).width()}, 750);
	});
	
}