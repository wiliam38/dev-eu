$().ready(function() {
	$.ajaxSetup( { type: "post" } );
	$('.button').button();
	$('#filter_form select[name="category_name"]').combobox();
});

function status_view(obj) {
	var tr = $(obj).closest('tr');
	var id = $(tr).attr('data-id');
	
	if (id == 'new') {
		$(tr).remove();
	} else {
		$(tr).showActivity();

		$.post(base_url+'manager/status/view', {
			id:			id
		}, function(data) {
			if (data.status == 1) {
				var new_tr = $(data.response).replaceAll(tr);
				$('.button', new_tr).button();
			} else {
				jError(data.error, 'Error');
				$(tr).hideActivity();
			}
		}, 'json');
	}	
}

function status_edit(obj) {
	var tr = $(obj).closest('tr');
	var id = $(tr).attr('data-id');
	
	if (id == 'new') {
		var tr = $('<tr data_id="new"><td colspan="'+$(tr).closest('table').find('thead tr:first th').attr('colspan')+'">'+loading+'</td></tr>');		
		$('#data_panel table.data_table tbody').append(tr);
	}
	
	$(tr).showActivity();
	$.post(base_url+'manager/status/edit', {
		id:					id,
		table_status_name:	$('#filter_form *[name="category_name"]').val()
	}, function(data) {
		if (data.status == 1) {
			var new_tr = $(data.response).replaceAll(tr);
			$('.button', new_tr).button();
		} else {
			jError(data.error, 'Error');
			$(tr).hideActivity();
		}
	}, 'json');
}

function status_save(obj) {
	var tr = $(obj).closest('tr');
	var id = $(tr).attr('data-id');
		
	$(tr).showActivity();	
	$.post(base_url+'manager/status/save', 
		$(tr).postData(),
	function(data) {
		if (data.status == 1) {
			if (id == 'new') $(tr).attr('data-id', data.id);
			status_view(tr);
		} else {
			jError(data.error, 'Error');
			$(tr).hideActivity();
		}
	}, 'json');
}

function status_delete(obj) {
	var tr = $(obj).closest('tr');
	
	jConfirm('Are you sure to delete this status?', 'Are you sure?', function(r) {
		if (r) {
			$(tr).showActivity();
			
			$.post(base_url+'manager/status/delete', {
				id: $(tr).attr('data-id')
			}, function(data) {
				if (data.status == '1') {
					$(tr).remove();
				}
				else {
					$(tr).hideActivity();
					jError(data.error, 'Error!');
				}
			}, 'json');
		}
	});
}