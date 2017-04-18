$().ready(function() {
	$('#page_content .vAlign').vAlign();
	$('#news_list .new-item').hover(function() {
		if (!$(this).find('.new-intro').hasClass('no-image')) {
			if ($(this).hasClass('odd')) {
				$(this).find('.hover-border').animate({
					'width': '20px',
					'margin-left': '-20px'
				}, 200);
			}
			if ($(this).hasClass('even')) {
				$(this).find('.hover-border').animate({
					'width': '20px',
					'margin-right': '-20px'
				}, 200);
			}
			if ($(this).hasClass('first')) {
				$(this).find('.hover-border').animate({
					'width': '30px',
					'margin-left': '-30px'
				}, 200);
			}
		}
	}, function() {
		if (!$(this).find('.new-intro').hasClass('no-image')) {
			if ($(this).hasClass('odd')) {
				$(this).find('.hover-border').animate({
					'width': '0px',
					'margin-left': '0px'
				}, 200);
			}
			if ($(this).hasClass('even')) {
				$(this).find('.hover-border').animate({
					'width': '0px',
					'margin-right': '0px'
				}, 200);
			}
			if ($(this).hasClass('first')) {
				$(this).find('.hover-border').animate({
					'width': '0px',
					'margin-left': '0px'
				}, 200);
			}
		}
	});
	
	$('#new_gallery').sliderkit({
		mousewheel:false,
		keyboard:true,
		shownavitems:4,
		auto:false,
		panelfx: 'tabsfading'
	});
})