var xhr = null;

function initOrders() {
	$('#configure-list .vAlign').vAlign();
	$('#payment_form input[type="radio"], #payment_form2 input[type="radio"]').checkbox({
		class_name: 'input-checkbox',
		onChange: function(obj) {
			$(obj).closest('table').find('.shipping-item').removeClass('active');
			$(obj).closest('tr').addClass('active');
			eval($(obj).attr('onchange'));
		}
	});
	$('#payment_form .wb-checkbox, #payment_form2 .wb-checkbox').each(function(i,o) {
		$(o).closest('tr').find('td:eq(1)').css('cursor', 'pointer');
		$(o).closest('tr').find('td:eq(2)').css('cursor', 'pointer');
		$(o).closest('tr').find('td:eq(3)').css('cursor', 'pointer');
		
		$(o).closest('tr').find('td:eq(1)').click(function() {
			$(o).click();
		});
		$(o).closest('tr').find('td:eq(2)').click(function() {
			$(o).click();
		});
		$(o).closest('tr').find('td:eq(3)').click(function() {
			$(o).click();
		});
	});
	
	$('#payment_form').ajaxForm({
		dataType: 'json',
		url: base_url+'plugins/orders/payment',
		beforeSubmit: function() {
			$('#payment_form #error').html('');
			showActivity($('#payment_form button[type="submit"]').parent());
		},
		success: function(data) {
			if (data.status == '1') {
				$('#cart_content')
					.mCustomScrollbar('destroy')
					.html(data.response)
					.mCustomScrollbar({ scrollbarPosition: 'outside', mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) } });
				initOrders();
			} else if (data.status == 3) {
				order_configure($('#payment_form input[name="order_id"]').val(), null);
			} else {
				hideActivity($('#payment_form button[type="submit"]').parent());
				$('#payment_form #error').html(data.error);
			}
		}
	});
	$('#payment_form select').combobox();
	
	$('#payment_form2').ajaxForm({
		dataType: 'json',
		url: base_url+'plugins/orders/payment2',
		beforeSubmit: function() {
			$('#payment_form2 #error').html('');
			showActivity($('#payment_form2 button[type="submit"]').parent());
		},
		success: function(data) {
			if (data.status == '1') {
				// SHOW CONFIRM
				$('#cart_content')
					.mCustomScrollbar('destroy')
					.html(data.response)
					.mCustomScrollbar({ scrollbarPosition: 'outside', mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) } });
				initOrders();
			} else if (data.status == '2') {
				// RELOAD - PAY BY BANK
				$('#cart_qty').text('0');
				if (parseInt($('#cart_qty').text()) == 1) {
					$('#cart_qty').parent().find('.one').show();
					$('#cart_qty').parent().find('.more').hide();
				} else {
					$('#cart_qty').parent().find('.one').hide();
					$('#cart_qty').parent().find('.more').show();
				}
				$('#cart_content')
					.mCustomScrollbar('destroy')
					.html(data.response)
					.mCustomScrollbar({ scrollbarPosition: 'outside', mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) } });
				initOrders();
			} else if (data.status == 3) {
				order_configure($('#payment_form2 input[name="order_id"]').val(), null);
			} else {
				hideActivity($('#payment_form2 button[type="submit"]').parent());
				$('#payment_form2 #error').html(data.error);
			}
		}
	});
	$('select#tmp_owner_user_id').combobox({
		source: base_url+'plugins/orders/search_user',
		minLength: 3,
		onChange: function() {
			$.post(base_url+'plugins/orders/get_user', {
				user_id: $('#tmp_owner_user_id').val()
			}, function(data) {
				if (data.status == 1) {
					$('#payment_form2 input[name="contact_name"]').val(data.response.contact_name);
					$('#payment_form2 input[name="company"]').val(data.response.company);
					$('#payment_form2 input[name="reg_nr"]').val(data.response.reg_nr);
					$('#payment_form2 input[name="vat_nr"]').val(data.response.vat_nr);
					$('#payment_form2 input[name="email"]').val(data.response.email);
					$('#payment_form2 input[name="phone"]').val(data.response.phone);
					$('#payment_form2 input[name="address"]').val(data.response.address);
				}
			}, 'json');
		}
	});
	
	$('#confirm_form').ajaxForm({
		dataType: 'json',
		url: base_url+'plugins/orders/confirm',
		beforeSubmit: function() {
			$('#confirm_form #error').html('');
			showActivity($('#confirm_form button[type="submit"]').parent());
		},
		success: function(data) {
			if (data.status == '1') {
				$('#cart_qty').text('0');
				if (parseInt($('#cart_qty').text()) == 1) {
					$('#cart_qty').parent().find('.one').show();
					$('#cart_qty').parent().find('.more').hide();
				} else {
					$('#cart_qty').parent().find('.one').hide();
					$('#cart_qty').parent().find('.more').show();
				}
				$('#cart_content')
					.mCustomScrollbar('destroy')
					.html(data.response)
					.mCustomScrollbar({ scrollbarPosition: 'outside', mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) } });
				initOrders();
			} else if (data.status == 3) {
				order_configure($('#confirm_form input[name="order_id"]').val(), null);
			} else {
				hideActivity($('#confirm_form button[type="submit"]').parent());
				$('#confirm_form #error').html(data.error);
			}
		}
	});
	$('#confirm_form select').combobox();
}

function orderIncrese(obj) {
	multi = 1;
	if ($(obj).parent().attr('data-category_id') == 1) multi = 10;
	if ($(obj).parent().attr('data-pro_sub_category_id') == 19) multi = 50;
	
	$(obj).parent().find('.product_qty').html((parseInt($(obj).parent().find('.product_qty').html())+(1*multi)));
	if (xhr) xhr.abort();
	
	xhr = $.post(base_url+'plugins/orders/update_qty', {
		qty: parseInt($(obj).parent().find('.product_qty').html()),
		order_detail_id: $(obj).closest('.item').find('input[name="order_detail_id"]').val()
	}, function(data) {
		if (data.status == 1) {
			if (data.qty <= 0) $(obj).closest('div.item').remove();
			else $(obj).parent().find('.product_qty').html(parseFloat(data.qty).toFixed(0));
			$(obj).closest('.price-data').find('.error').html(data.error);
			$('#checkout_total').html(parseFloat(data.total.price).toFixed(2)+' '+data.total.curr_symbol);
			$('#cart_qty').html(parseFloat(data.total.item_qty).toFixed(0));
			
			if (parseInt($('#cart_qty').text()) == 1) {
				$('#cart_qty').parent().find('.one').show();
				$('#cart_qty').parent().find('.more').hide();
			} else {
				$('#cart_qty').parent().find('.one').hide();
				$('#cart_qty').parent().find('.more').show();
			}			
			
			//$('#cart_price').html(parseFloat(data.total.price).toFixed(2)+' '+data.total.curr_symbol);
			$('#list_total').replaceWith(data.total_table);
			
			$('#configure_error').html(data.total_error);
			$('.checkout-text').html(data.button);
		} else { 
			$(obj).parent().find('.product_qty').html(parseInt($(obj).parent().find('.product_qty').html())-(1*multi));
		}
	}, 'json');
}
function orderDecrese(obj) {
	multi = 1;
	if ($(obj).parent().attr('data-category_id') == 1) multi = 10;
	if ($(obj).parent().attr('data-pro_sub_category_id') == 19) multi = 50;
	
	if (parseInt($(obj).parent().find('.product_qty').html()) > 0) {
		$(obj).parent().find('.product_qty').html(parseInt($(obj).parent().find('.product_qty').html())-(1*multi));
		if (xhr) xhr.abort();
		
		xhr = $.post(base_url+'plugins/orders/update_qty', {
			qty: parseInt($(obj).parent().find('.product_qty').html()),
			order_detail_id: $(obj).closest('.item').find('input[name="order_detail_id"]').val()
		}, function(data) {
			if (data.status == 1) {
				if (data.qty <= 0) $(obj).closest('div.item').remove();
				else $(obj).parent().find('.product_qty').html(parseFloat(data.qty).toFixed(0));
				$(obj).closest('.price-data').find('.error').html(data.error);
				$('#checkout_total').html(parseFloat(data.total.price).toFixed(2)+' '+data.total.curr_symbol);
				$('#cart_qty').html(parseFloat(data.total.item_qty).toFixed(0));
				if (parseInt($('#cart_qty').text()) == 1) {
					$('#cart_qty').parent().find('.one').show();
					$('#cart_qty').parent().find('.more').hide();
				} else {
					$('#cart_qty').parent().find('.one').hide();
					$('#cart_qty').parent().find('.more').show();
				}
				//$('#cart_price').html(parseFloat(data.total.price).toFixed(2)+' '+data.total.curr_symbol);
				$('#list_total').replaceWith(data.total_table);
				
				$('#configure_error').html(data.total_error);
				$('.checkout-text').html(data.button);
			} else { 
				$(obj).parent().find('.product_qty').html(parseInt($(obj).parent().find('.product_qty').html())+(1*multi));
			}
		}, 'json');
	}
}
function orderRemove(obj) {
	var old_qty = $(obj).parent().find('.product_qty').html();
	if (xhr) xhr.abort();
		
	xhr = $.post(base_url+'plugins/orders/update_qty', {
		qty: 0,
		order_detail_id: $(obj).closest('.item').find('input[name="order_detail_id"]').val()
	}, function(data) {
		if (data.status == 1) {
			$(obj).closest('div.item').remove();
			$('#checkout_total').html(parseFloat(data.total.price).toFixed(2)+' '+data.total.curr_symbol);
			$('#cart_qty').html(parseFloat(data.total.item_qty).toFixed(0));
			if (parseInt($('#cart_qty').text()) == 1) {
				$('#cart_qty').parent().find('.one').show();
				$('#cart_qty').parent().find('.more').hide();
			} else {
				$('#cart_qty').parent().find('.one').hide();
				$('#cart_qty').parent().find('.more').show();
			}
			//$('#cart_price').html(parseFloat(data.total.price).toFixed(2)+' '+data.total.curr_symbol);
			$('#list_total').replaceWith(data.total_table);
		} else { 
			$(obj).parent().find('.product_qty').html(old_qty);
		}
	}, 'json');
}

function order_list(order_id, obj) {
	$(obj).parent().showActivity();
	$('#checkout-list-information').hide();
	$('#checkout-list').show();
}
 
function order_configure(order_id, obj) {
	showActivity($(obj).parent());
	$.post(base_url+'plugins/orders/configure', {
		order_id: order_id
	}, function(data) {
		if (data.status == '1') {
			$('#cart .close-button').show();
			$('#cart').removeClass('small-list');
			$('#cart').animate({ 
				'width': '100%',
				'right': '0px' 
			});
			$('#cart_content')
				.mCustomScrollbar('destroy')
				.html(data.response)
				.mCustomScrollbar({ scrollbarPosition: 'outside', mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) } });
			initOrders();
		} else if (data.status == 3) {
			order_configure($('#payment_form input[name="order_id"]').val(), null);
		} else {
			hideActivity($(obj).parent());
			alert(data.error);
		}
	}, 'json');
}

function order_checkout(order_id, obj) {
	showActivity($(obj).parent());
	$('#configure_error').html('');
	$.post(base_url+'plugins/orders/checkout', {
		order_id: order_id
	}, function(data) {
		if (data.status == '1') {
			//window.location = window.location.href.replace(/(-step[0-9])*$/i,'-step2');
			$('#cart_content')
				.mCustomScrollbar('destroy')
				.html(data.response)
				.mCustomScrollbar({ scrollbarPosition: 'outside', mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) } });
			initOrders();
		} else if (data.status == 3) {
			order_configure($('#payment_form input[name="order_id"]').val(), null);
		} else {
			hideActivity($(obj).parent());
			$('#configure_error').html(data.error);
			if (typeof(data.configure_data) != 'undefined') $('#configure-list').html(data.configure_data); 
		}
	}, 'json');
}

/* BACK */
function order_back(order_id, step, obj) {
	showActivity($(obj).parent());
	$('#configure_error').html('');
	$.post(base_url+'plugins/orders/back', {
		order_id: order_id,
		step: step
	}, function(data) {
		if (data.status == '1') {
			if (step == 'list') {
				$('#cart .close-button').hide();
				$('#cart').addClass('small-list');
				$('#cart').animate({ 
					'width': '370px',
					'right': '0px' 
				});
			}
			
			$('#cart_content')
				.mCustomScrollbar('destroy')
				.html(data.response)
				.mCustomScrollbar({ scrollbarPosition: 'outside', mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) } });
			initOrders();
		} else {
			hideActivity($(obj).parent());
			$('#error').html(data.error);
		}
	}, 'json');
}

function recalculate_shipping(value,vat) {
	if ($('#payment_form input[name="shipping_id"]:checked').val() == 5) {
		// STATOIL
		$('#payment_form .shipping-statoil').slideDown();
	} else {
		// OTHER
		$('#payment_form .shipping-statoil').slideUp();
		$('#payment_form select[name="shipping_statoil_id_combobox_select"]').combobox('setValue', '');
	}
	
	if ($('#payment_form input[name="shipping_id"]:checked').val() == 9) {
		// STATOIL
		$('#payment_form .shipping-office').slideDown();
	} else {
		// OTHER
		$('#payment_form .shipping-office').slideUp();
		$('#payment_form input[name="shipping_pickup_time"]').val('');
	}
	
	$('#shipping_display').html(parseFloat((value * (1 + vat / 100))).toFixed(2));
	$('#vat_display').html((parseFloat($('#vat_value').val()) + (parseFloat(value) * parseFloat(vat) / 100)).toFixed(2));
	$('#total_display').html((parseFloat($('#total_value').val()) + parseFloat(value) + (parseFloat(value) * parseFloat(vat) / 100)).toFixed(2));
	$('#checkout_total').html($('#total_display').closest('td').text());
}
function check_discount(obj) {
	showActivity(obj);
	
	$('#discount_row').hide();
	$('#discount_percents_display').html('0');
	$('#discount_code').val('');
	$('#discount_display').html('0.00');
	$('#discount_code_info').html('');
	
	$.post(base_url+'plugins/orders/discount', {
		code: $('#discount_code_chk').val()
	}, function(data) {
		if (data.status == 1) {
			$('#discount_percents_display').html(data.discount);
			$('#discount_code').val(data.code);
			$('#discount_display').html('-'+parseFloat(data.discaount_value).toFixed(2));
			$('#discount_row').show();
		}
		$('#vat_value').val(parseFloat(data.vat).toFixed(2));
		$('#total_value').val(parseFloat(data.total_vat).toFixed(2));
		$('#discount_code_info').html(data.info);
		if ($('input[name="shipping_id"]:checked').length > 0) {
			eval($('input[name="shipping_id"]:checked').attr('onchange'));
		} else {
			$('#vat_display').html(parseFloat(data.vat).toFixed(2));
			$('#total_display').html(parseFloat(data.total_vat).toFixed(2));
			$('#total_display_eur').html((parseFloat(data.total_vat).toFixed(2) * 0.702804).toFixed(2));
			$('#checkout_total').html($('#total_display').closest('td').text());
		}
		
		hideActivity(obj);
	}, 'json');
}

function check_share_discount(obj) {
	showActivity(obj);
	
	$('#discount_share_row').hide();
	$('#discount_share_percents_display').html('0');
	$('#discount_share_display').html('0.00');
	$('#discount_share_code_info').html('');
	
	$.post(base_url+'plugins/orders/discount_share', 
	function(data) {
		if (data.status == 1) {
			$('#discount_share_percents_display').html(data.discount);
			$('#discount_share_display').html('-'+parseFloat(data.discaount_value).toFixed(2));
			$('#discount_share_row').show();
		}
		$('#vat_value').val(parseFloat(data.vat).toFixed(2));
		$('#total_value').val(parseFloat(data.total_vat).toFixed(2));
		$('#discount_share_code_info').html(data.info);
		if ($('input[name="shipping_id"]:checked').length > 0) {
			eval($('input[name="shipping_id"]:checked').attr('onchange'));
		} else {
			$('#vat_display').html(parseFloat(data.vat).toFixed(2));
			$('#total_display').html(parseFloat(data.total_vat).toFixed(2));
			$('#total_display_eur').html((parseFloat(data.total_vat).toFixed(2) * 0.702804).toFixed(2));
			$('#checkout_total').html($('#total_display').closest('td').text());
		}
		
		hideActivity(obj);
	}, 'json');
}


function fd_pay_ok(order_id) {
	showActivity($('#checkout_total').closest('.button-green'));
	$.post(base_url+'plugins/orders/paid', {
		order_id: order_id
	}, function(data) {
		if (data.status == '1') {
			$('#cart_content')
				.mCustomScrollbar('destroy')
				.html(data.response)
				.mCustomScrollbar({ scrollbarPosition: 'outside', mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) } });
			initOrders();
		} else {
			hideActivity($('#checkout_total').closest('.button-green'));
			alert(data.error);
		}
	}, 'json');
}
function fd_pay_error() {
	$('#payment_form #error').html('Error!');
}