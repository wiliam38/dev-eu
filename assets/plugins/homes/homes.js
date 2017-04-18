var banner_index = 0;
var banner_timer = null;
var banner_sec = 8000;

$().ready(function() {		
	windowHeight();
	$(window).resize(function() {
		windowHeight();
	});
	
	banner_timer = setInterval(function() {
		banner_index = banner_index + 1;
		bannerGoto(banner_index);
	}, banner_sec);
	
	$('#banner > div.item:first .text').css({
		'margin-bottom': '-30px',
		'opacity': '0'
	});
	$('#banner > div.item:first .text .title').css({ 'margin-bottom': '100px'});
	setTimeout(function() {
		$('#banner > div.item:first .text').animate({
			'margin-bottom': '0px',
			'opacity': '1'
		}, 500);
		$('#banner > div.item:first .text .title').animate({ 'margin-bottom': '30px'}, 500);
	}, 100);	
});

function windowHeight() {
	var block_height = ($('body').height()-61);	
	$('#content').height(block_height);
	
	$('#content .home-banners img.banner-image').each(function() {		
		bannerPossition(this);
	});
}

function bannerSizes(obj) {
	$(obj).data('height', obj.height);
	$(obj).data('width', obj.width);
	bannerPossition(obj);
}
function bannerPossition(obj) {
	var img_w = $(obj).data('width');
	var img_h = $(obj).data('height');
	var win_w = $('#content').width();
	var win_h = $('#content').height();
	var coef = 1;
	
	if (win_w/img_w > coef) coef = win_w/img_w;
	if (win_h/img_h > coef) coef = win_h/img_h;
	
	var new_img_w = Math.ceil(img_w * coef);
	var new_img_h = Math.ceil(img_h * coef);
	
	$(obj).css('height', new_img_h);
	$(obj).css('width', new_img_w);
	$(obj).css('top', (win_h-new_img_h)/2);
	$(obj).css('left', (win_w-new_img_w)/2);
}
function bannerGoto(page) {
	clearInterval(banner_timer);
	banner_timer = setInterval(function() {
		banner_index = banner_index + 1;
		bannerGoto(banner_index);
	}, banner_sec);
	
	if (page >= $('#banner > div.item').length) page = 0;
	banner_index = page;
	
	if (!$('#banner > div.item').eq(page).is(':visible')) {		
		$('#banner > div.item:visible').fadeOut(200);
		$('#banner > div.item').eq(page).find('.text').css({
			'margin-bottom': '-30px',
			'opacity': '0'
		});
		$('#banner > div.item').eq(page).find('.text .title').css({ 'margin-bottom': '100px'});
		$('#banner > div.item').eq(page).fadeIn(200);
		setTimeout(function() {
			$('#banner > div.item').eq(page).find('.text')
				.animate({
					'margin-bottom': '0px',
					'opacity': '1'
				}, 500);
			$('#banner > div.item').eq(page).find('.text .title').animate({ 'margin-bottom': '30px'}, 500);
		}, 100);
		
		//$('#paginate a').removeClass('active');
		//$('#paginate a').eq(page).addClass('active');
	}
}