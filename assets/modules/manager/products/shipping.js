function shipping_view(obj) {
	if ($(obj).closest('tr').attr('data-id') == 'new') {
		$(obj).closest('tr').remove();	
	} else {
		var tr = $(obj).closest('tr');
		var id = $(obj).closest('tr').attr('data-id');	
	
		$(tr).showActivity();
		$.post(base_url+'manager/products_shipping/view/'+id, 
		function(data) {
			if (data.status == '1') {
				var new_tr = $(data.response).replaceAll(tr);
				shipping_init_view(new_tr)
			} 
			$(tr).hideActivity();
		}, 'json');
	}
}
function shipping_init_view(obj) {
	$('.button', obj).button();
}

function shipping_edit(obj) {
	if ($(obj).closest('tr').attr('data-id') == 'new') {
		var tr = $('<tr><td colspan="'+$(obj).closest('table').find('thead tr:first th').length+'"></td></tr>').appendTo($(obj).closest('table').find('tbody'));
		var id = 'new';		
	} else {
		var tr = $(obj).closest('tr');
		var id = $(obj).closest('tr').attr('data-id');
	}
	
	$(tr).showActivity();
	$.post(base_url+'manager/products_shipping/edit/'+id, 
	function(data) {
		if (data.status == '1') {
			var new_tr = $(data.response).replaceAll(tr);
			shipping_init_edit(new_tr)
		} 
		$(tr).hideActivity();
	}, 'json');
}
function shipping_init_edit(obj) {
	$('.button', obj).button();
	$('select').combobox();
}

function shipping_save(obj) {
	var tr = $(obj).closest('tr');
	var id = $(obj).closest('tr').attr('data-id');
	
	$(tr).showActivity();
	$.post(base_url+'manager/products_shipping/save/'+id, 
		$(tr).postData(),
	function(data) {
		if (data.status == '1') {
			var new_tr = $(data.response).replaceAll(tr);
			shipping_init_view(new_tr)
		} 
		$(tr).hideActivity();
	}, 'json');
}

function shipping_delete(obj) {
	jConfirm('Vai dzēst šo piegādes variantu?', 'Apstiprinājums', function(r) {
		if (r) {
			var tr = $(obj).closest('tr');
			var id = $(obj).closest('tr').attr('data-id');
			
			$(tr).showActivity();
			$.post(base_url+'manager/products_shipping/delete/'+id, 
			function(data) {
				if (data.status == '1') {
					$(tr).remove();
				} 
				$(tr).hideActivity();
			}, 'json');
		}
	});
}