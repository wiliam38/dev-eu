var mobile = {
    init_events: function() {
        console.log('events');
        $('#mobile_menu').click(function() {
            console.log('toggle menu');
            $('.top-menu a, .working-time, .user').toggle();
        });
    }
};

$(document).ready(function() {
	if (navigator.userAgent.toLowerCase().indexOf("chrome") >= 0) {
        var intervalId = 0;
        $(window).load(function() {
            intervalId = setInterval(function () { // << somehow  this does the trick!
                if ($('input:-webkit-autofill').length > 0) {
                    clearInterval(intervalId);
                    $('input:-webkit-autofill').each(function () {
                        var text = $(this).val();
                        var name = $(this).attr('name');
                        $(this).after(this.outerHTML).remove();
                        $('input[name=' + name + ']').val(text);
                    });
                }
            }, 1);
        });
    }
	
	contentResize();
	$(window).resize(function() { contentResize(); });
	$('select').combobox();
	$('#content').show();
	
	if (parseInt($('#cart_qty').text()) > 0) $('#cart_content_button').show();

    mobile.init_events();
});



function nextBlock() {
	$.fn.fullpage.moveSectionDown();
}
function prevBlock() {
	$.fn.fullpage.moveSectionUp();
}
function initBlocks() {
	if ($('#fullpage .section.active').is($('#fullpage .section:first'))) {
		$('#data_block_prev').hide();
	} else {
		$('#data_block_prev').show();
	}
	if ($('#fullpage .section.active').is($('#fullpage .section:last'))) {
		$('#data_block_next').hide();
	} else {
		$('#data_block_next').show();
	}
}
function nextBlock2() {
	$.fn.fullpage2.moveSectionDown();
}
function prevBlock2() {
	$.fn.fullpage2.moveSectionUp();
}
function initBlocks2() {
	if ($('#how_to_choose_fullpage .section.active').is($('#how_to_choose_fullpage .section:first'))) {
		$('#how_to_choose_prev').hide();
	} else {
		$('#how_to_choose_prev').show();
	}
	if ($('#how_to_choose_fullpage .section.active').is($('#how_to_choose_fullpage .section:last'))) {
		$('#how_to_choose_next').hide();
	} else {
		$('#how_to_choose_next').show();
	}
}

function submit(obj) {
	$(obj).closest('form').submit();
}
function initAjaxForm(form_obj, url, redirect_url) {
	$(form_obj).ajaxForm({
		dataType: 'json',
		url: url,
		beforeSubmit: function() {
			$('.error-text', form_obj).html('');
			$(':input.error', form_obj).removeClass('error');				
			$('input[type="submit"]', form_obj).showActivity();
		},
		beforeSerialize:function($Form, options){
	        for (instance in CKEDITOR.instances ) {
	        	CKEDITOR.instances[instance].updateElement();
	        }
	        return true; 
	    },
		success: function(data) {
			if (data.status == 1) {
				if (redirect_url == '') $(form_obj).replaceWith(data.response);
				else window.location = redirect_url;
			} else {
				$.each(data.error, function(input, o) {
					$(':input[name="'+input+'"]', form_obj).addClass('error');
				});
				
				$('.error-text', form_obj).html(data.error_text);
				$('input[type="submit"]', form_obj).hideActivity();
			}				
		}
	});

	$(':input', form_obj).focus(function() {
		$(this).removeClass('error');
	});
}

function popup(link, width, height) {		
	var left = screen.width?(screen.width-width)/2:0;
	var top = screen.height?(screen.height-height)/2:0; 
	window.open(link, '', 'width='+width+', height='+height+', left='+left+', top='+top+', scrollbars=no');
}

function showActivity(obj) {
	if ($(obj).next('.ajaxActivity').length == 0) {
		var wait = $(obj).clone();
		$(wait)
			.css('background','url(assets/templates/global/img/ajax-loader.gif) no-repeat center')
			.addClass('ajaxActivity')
			.html('')
			.val('')
			.removeAttr('href')
			.removeAttr('onclick')
			.unbind('click')
			.click(function(e) { e.preventDefault(); });
		if ($(wait).height() < 32) $(wait).height('32px');
		
		$(obj).hide();
		$(wait).insertAfter(obj);
	}
}
function hideActivity(obj) {
	$(obj).next('.ajaxActivity').remove();
	$(obj).show();
}

/*
 * SHOW / HIDE ACTIVITY
 */
$.fn.showActivity = function() {
	return this.each(function(i){
		if ($(this).next('.ajaxActivity').length == 0) {
			var wait = $(this).clone();
			$(wait)
				.css('background','url(assets/templates/global/img/ajax-loader.gif) no-repeat center')
				.addClass('ajaxActivity')
				.html('')
				.val('')
				.removeAttr('href')
				.removeAttr('onclick')
				.unbind('click')
				.click(function(e) { e.preventDefault(); });
			if ($(wait).height() < 32) $(wait).height('32px');
			
			$(this).hide();
			$(wait).insertAfter(this);
		}
	});
};
$.fn.hideActivity = function() {
	return this.each(function(i){
		$(this).next('.ajaxActivity').remove();
		$(this).show();
	});
};

/*
 * GET POST DATA
 */
$.fn.postData = function(post_data) {
	if (typeof(post_data) === 'undefined') var post_data = {};
	
	if ($(this).is(':input')) {
		var obj = $(this);
	} else {
		var obj = $(':input', this);
	}
	
	$(obj).each(function(i,o) {
		if (typeof($(o).attr('name')) != 'undefined') {
			if ($(o).attr('name').substring($(o).attr('name').length-2,$(o).attr('name').length) != '[]') {
				if ($(o).is(':checkbox') || $(o).is(':radio')) {
					if ($(o).is(':checked')) post_data[$(o).attr('name')] = $(o).val();
				} else {
					post_data[$(o).attr('name')] = $(o).val();
				}
			} else {
				// ARRAY
				if (typeof(post_data[$(o).attr('name').substring(0,$(o).attr('name').length-2)]) == 'undefined') {
					post_data[$(o).attr('name').substring(0,$(o).attr('name').length-2)] = [];
					var i = 0;
				} else {
					var i = post_data[$(o).attr('name').substring(0,$(o).attr('name').length-2)].length;
				}	
				if ($(o).is(':checkbox') || $(o).is(':radio')) {
					if ($(o).is(':checked')) post_data[$(o).attr('name').substring(0,$(o).attr('name').length-2)][i] = $(o).val();
				} else {
					post_data[$(o).attr('name').substring(0,$(o).attr('name').length-2)][i] = $(o).val();
				}
			}
		}
	});
	
	return post_data;
};

/*
 * VERTICALLY ALIGN
 */
(function ($) {
// VERTICALLY ALIGN FUNCTION
$.fn.vAlign = function() {
	return this.each(function(i){		
		if ($(this).is('img') && $(this).css('position') != 'absolute') {
			$(this).prevAll('span.vAlign-obj').remove();
			
			$('<span class="vAlign-obj"></span>').css({
					'display': 'inline-block',
					'height': '100%',
					'width': '1px',
					'vertical-align': ($(this).hasClass('bottom')?'bottom':'middle') })
				.insertBefore($(this));
			$(this).css({
				'vertical-align': ($(this).hasClass('bottom')?'bottom':'middle'),
				'display': 'inline',
				'margin-left': '-1px'
			});
		} else {
			$(this).css('margin-top', '0px');			
			var ah = $(this).height();
			if ($(this).attr('data-height')) ah = $(this).attr('data-height'); 
			var ph = $(this).parent().height();
			var mh = Math.ceil((ph-ah) / 2);
			if (mh < 0) mh = 0;
			if ($(this).hasClass('bottom')) mh = mh * 2;
			$(this).css('margin-top', mh);
		}
	});
};
})(jQuery);

$.fn.__tabs = $.fn.tabs;
$.fn.tabs = function (a, b, c, d, e, f) {
    var base = location.href.replace(/#.*$/, '');
    $('ul>li>a[href^="#"]', this).each(function () {
        var href = $(this).attr('href');
        $(this).attr('href', base + href);
    });
    $(this).__tabs(a, b, c, d, e, f);
};

function product_filter(input_name, value, obj) {
	$(obj).toggleClass('active');
	$('#product_filter_form input[name="'+input_name+'[]"][value="'+value+'"]').remove();
	if ($(obj).hasClass('active')) $('#product_filter_form').append('<input type="hidden" name="'+input_name+'[]" value="'+value+'"/>');	
	$('#product_filter_form').submit();
}

function contentResize() {
	$('#content').height($('body').height()-61);
	
	$('#how_to_choose_content .how_to_choose_content_text')
		.width($('#fullpage').width()-30)
		.height($('#fullpage').height()-30);		
	$('#how_to_choose_content .how_to_choose_content_text').each(function() {
		var h_add = 0;
		if ($('#how_to_choose_content').hasClass('coffee-popup')) h_add = h_add + 75;
		$(this)
			.width($('.main-image', this).width())
			.height($('.main-image', this).height() + h_add);
	});

	$('#content .vAlign').vAlign();
	
	// LEFT MENU
	menu_top = (($(window).height() - 160 - $('#page .left-menu .category-menu').height()) / 2) + 80;
	if (menu_top < 115) menu_top = 115; 
	if (menu_top > 170) menu_top = 170; 
	$('#page .left-menu .category-menu').css('top', menu_top);
	
	// INFO PAGE IMAGE
	$('.page .content-wrapper .content .text-content .image').css({
		width: (($(window).width() / 2) - 110),
		height: ($(window).height() - 59)
	});
}

function menu_login(obj) {
	$(obj).toggleClass('active');
	
	if ($(obj).hasClass('active')) {
		// SHOW		
		$('#overlay').show().animate({ 'opacity': '0.75' });
		$('#cart').animate({ 'right': '-100%' });
		$('#cart_button').removeClass('active');
		$('#cart_content_button').fadeOut();
		
		$('#profile_content')
			.mCustomScrollbar('destroy')
			.html('')
			.css('height', '100%')
			.showActivity();
		
		$.post(base_url+'plugins/userlogin/profile',
		function(data) {
			$('#profile_content')
				.hideActivity()
				.html(data.response)
				.mCustomScrollbar({ scrollbarPosition: 'outside', mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) } })
				.hide()
				.fadeIn();
		}, 'json');
		
		$('#profile').animate({ 'right': '0px' });
	} else {
		// HIDE
		if (parseInt($('#cart_qty').text()) > 0) $('#cart_content_button').fadeIn();
		$('#overlay').animate({ 'opacity': '0' }, function() { $('#overlay').hide(); });
		$('#profile_content')
			.mCustomScrollbar('destroy')
			.html('');
		$('#profile').animate({ 'right': '-750px' });
	}
}

function menu_cart(obj) {
	$(obj).toggleClass('active');
	
	if ($(obj).hasClass('active')) {
		// SHOW		
		$('#overlay').show().animate({ 'opacity': '0.75' });
		$('#profile').animate({ 'right': '-750px' });
		$('#login_button').removeClass('active');
		$('#cart_content_button').fadeOut();
		
		$('#cart_content')
			.mCustomScrollbar('destroy')
			.html('')
			.css('height', '100%')
			.showActivity();
		
		$.post(base_url+'plugins/orders/current_order',
		function(data) {
			$('#cart_content')
				.hideActivity()
				.html(data.response)
				.mCustomScrollbar({ scrollbarPosition: 'outside', mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) } })
				.hide()
				.fadeIn();
			initOrders();
		}, 'json');
		
		$('#cart').innerWidth('370px');
		$('#cart').addClass('small-list');
		$('#cart').animate({ 'right': '0px' });
	} else {
		// HIDE
		if (parseInt($('#cart_qty').text()) > 0) $('#cart_content_button').fadeIn();
		$('#overlay').animate({ 'opacity': '0' }, function() { $('#overlay').hide(); });
		$('#cart_content')
			.mCustomScrollbar('destroy')
			.html('');
		$('#cart').animate({ 'right': '-100%' });
	}
}