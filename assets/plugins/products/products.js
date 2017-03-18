$().ready(function() {
	$('#fullpage').fullpage({
		resize : false,
		scrollingSpeed: 300,
		css3: true,
		verticalCentered: true,
		afterLoad: function() {
			initBlocks();
		},
		/*onLeave: function(anchorLink, index, slideIndex, direction) {
			$('#fullpage .section').addClass('inactive');
			$('#fullpage .section:eq('+(index-1)+')').removeClass('inactive');
		},*/
		afterResize: function(){
			$('#fullpage div.vAlign').css('margin-top', '0px');
			$('#fullpage div.vAlign').vAlign();
		}
	});
	$('#product_filter').mCustomScrollbar({
		scrollbarPosition: 'outside', 
		mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) }
	});
	$('#content').children('.content').css('overflow', 'hidden');
	
	initBlocks();
	$('#fullpage .vAlign').vAlign();
	
	$('#page_content .products-list .products-coffee-data div.item').hover(function() {
		$(this).find('.coffee-info').show();
		$('#content > .content').css('overflow', 'visible');
		$('#fullpage .section.active').css('overflow', 'visible');
		$('#page_content').css('z-index', '4000');
	}, function() {
		$(this).find('.coffee-info').hide();
		$('#content > .content').css('overflow', 'hidden');
		$('#fullpage .section.active').css('overflow', 'hidden');
		$('#page_content').css('z-index', '');
	});
});

function productReference(obj) {
	var product_id = $(obj).attr('data-product_id');
	var product_reference_id = $(obj).attr('data-color');
	var product_reference_order_btn = $(obj).attr('data-order-btn');
	
	$('#product_color_images_'+product_id+' .image-wrapper').removeClass('active');
	$('#product_color_images_'+product_id+' .image-wrapper[data-color="'+product_reference_id+'"]').addClass('active');
	
	$(obj).closest('.item').find('.add-to-chart').val(product_reference_order_btn);
}
function productOpen(obj) {
	$(obj).attr('href', $(obj).attr('href')+'-k'+$(obj).closest('.item').find('.main-image .image-wrapper.active').attr('data-color'));
}

function showHowToChoose() {
	if ($('#how_to_choose_content').hasClass('active')) {
		$('#how_to_choose_content')
			.removeClass('active')
			.animate({
				'top': '100%'
			}, 500, function() {
				$.fn.fullpage2.moveTo(1);
			});
	} else {
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
		
		$('#how_to_choose_content .vAlign').vAlign();
		$('#how_to_choose_content')
			.addClass('active')
			.animate({
				'top': '0px'
			}, 500);
	}
}
function add_to_order(product_reference_id, qty, obj) {
	if (typeof(product_reference_id) == 'object') product_reference_id = $(product_reference_id).attr('data-color');
	if (typeof(qty) == 'object') qty = $(qty).val();
	
	showActivity(obj);
	
	$.post(base_url+'plugins/orders/add_to_order', {
		product_reference_id: product_reference_id,
		qty: qty
	}, function(data) {
		if (data.status == '1') {
			// ADDED
			
			// UPDATE CART
			$('#cart_qty').html(parseFloat(data.item_qty).toFixed(0));
			if (parseInt($('#cart_qty').text()) == 1) {
				$('#cart_qty').parent().find('.one').show();
				$('#cart_qty').parent().find('.more').hide();
			} else {
				$('#cart_qty').parent().find('.one').hide();
				$('#cart_qty').parent().find('.more').show();
			}
			if (parseInt($('#cart_qty').text()) > 0) $('#cart_content_button').fadeIn();
			
			//$('#cart_price').html(parseFloat(data.price).toFixed(2)+' '+data.currency);
			
			// SET LABEL FOR BOOK				
			hideActivity(obj);
			//$(obj).replaceWith(data.button);
			
			//window.location = base_url+data.response;
		} else {
			hideActivity(obj);
			if (data.status == '2') {
				// REDIRECT TO LOGIN
				//window.location = base_url+data.response;
				$('#cart_button').click();
			}
		}
	}, 'json');
}

function filter_reset() {
	$('#product_filter_form input[type="hidden"]').each(function() {
		if ($(this).attr('name') != 'filter_post') $(this).remove();
	});
	$('#product_filter_form').submit();
}

function coffee_qty_change(obj) {
	$(obj).parent().find('input.add-to-chart').val($(obj).parent().find('select[name="qty_combobox_select"] option[value="'+$(obj).val()+'"]').attr('data-btn'));
	
	if (parseFloat($(obj).parent().find('select[name="qty_combobox_select"]').attr('data-balance')) >= parseFloat($(obj).val())) $(obj).parent().find('select[name="qty_combobox_select"]').closest('.price').find('.price-order-info').hide();
	else $(obj).parent().find('select[name="qty_combobox_select"]').closest('.price').find('.price-order-info').show();
}