$().ready(function() {		
	$('a[action="product_delete"]').click(function() {
		var button = this;
		
		jConfirm(product_delete_msg, product_delete_title, function(r) {
			if (r) {
				page_loading('Deleting...');
				$.post(base_url+'manager/'+product_link+'/delete', {
					product_id:	$(button).closest('tr').attr('data-id')
				}, function(data) {
					if (data.status == '1') {
						$(button).closest('tr').remove();
						page_loading('');
					} else {
						page_loading('');
						alert(data.error);
					}
				}, 'json');
			}
		});
	});
	
	$('select[name="filter_category_id"]').multiSelect();
	$('select[name="filter_status_id"]').multiSelect();
});

function toggleProductDetails(obj) {
	var id = $(obj).closest('tr').attr('data-id');
	$(obj).closest('tr').nextAll('tr[data-level="'+id+'"]').remove();
	
	if ($(obj).hasClass('ui-icon-plus')) {
		// SHOW
		$(obj).removeClass('ui-icon-plus').addClass('ui-icon-minus');
		var new_tr = $('<tr data-level="'+id+'"><td style="background-color: #D5D5D5;"><td style="padding: 0px 0px 10px 0px; background-color: #D5D5D5;" colspan="7"></td></tr>').insertAfter($(obj).closest('tr'));
		$(new_tr).showActivity();
		
		$.post(base_url+'manager/products_products/details', {
			product_id: id
		}, function(data) {
			$(new_tr).find('td:last').html(data);
			$('.button', new_tr).button();
		});
	} else {
		// HIDE
		$(obj).addClass('ui-icon-plus').removeClass('ui-icon-minus');		
	}
}
function balanceUpdate(obj) {
	var id = $(obj).closest('tr').attr('data-id');
	var balance = $(obj).closest('tr').find('.balance').text();
	jPrompt('Ievadiet jauno atlikumu:', balance, 'Atlikuma labošana!', function(r) {
		if(r) {
			$(obj).closest('tr').showActivity();
			$.post(base_url+'manager/products_products/balance_update', {
				product_reference_id: id,
				balance: r
			}, function(data) {
				$(obj).closest('tr').find('.balance').html(data.balance);
				$(obj).closest('div.order-details').closest('tr').prev('tr').find('.balance').html(data.product_balance);
				$(obj).closest('tr').hideActivity();
			}, 'json');
		}
	});
}
function balanceAdd(obj) {
	var id = $(obj).closest('tr').attr('data-id');
	var balance = $(obj).closest('tr').find('.balance').text();
	jPrompt('Ievadiet skaitu pa cik palielināt atlikumu:', '0', 'Atlikuma labošana!', function(r) {
		if(r) {
			$(obj).closest('tr').showActivity();
			$.post(base_url+'manager/products_products/balance_add', {
				product_reference_id: id,
				add_qty: r
			}, function(data) {
				$(obj).closest('tr').find('.balance').html(data.balance);
				$(obj).closest('div.order-details').closest('tr').prev('tr').find('.balance').html(data.product_balance);
				$(obj).closest('tr').hideActivity();
			}, 'json');
		}
	});
}