$().ready(function() {
	init_languages($('body'));
});

function init_languages(data) {
	$(data).find('a[action=languages_edit]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		languages_edit(id,tr);
	});
	$(data).find('a[action=languages_save]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		loading_tr(tr);
		$.post(base_url+'manager/languages/save', {
			id:				id,
			name:			$(tr).find('#name').val(),
			ticker:			$(tr).find('#ticker').val(),
			tag:			$(tr).find('#tag').val(),
			order_index:	$(tr).find('#order_index').val(),
			status_id:		$(tr).find('#status_id').val(),
			img_src:		$(tr).find('#img_src').val()
		}, function(data) {
			if (id == 'new') id = data;
			languages_view(id,tr);
		});
	});
	$(data).find('a[action=languages_view]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		if (id == 'new') {
			$(tr).remove();
		} else {
			languages_view(id,tr);
		}		
	});
	$(data).find('a[action=languages_add]').click(function() {
		var tr = $('<tr data_id="new"><td colspan="7">Loading...</td></tr>');		
		var id = 'new';
		
		if ($(this).closest('table').find('tbody>tr:last').length > 0) {
			$(this).closest('table').find('tbody>tr:last').after(tr);
		} else {
			$(this).closest('table').find('tbody').html(tr);
		}
		
		languages_edit(id,tr);
	});
	$(data).find('a[action=languages_delete]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		var dlg = $('<div><b>Are you sure to delete this language?</b><br><br>If you delete this language, all documents in this languge will be deleted!</b></div>').dialog({
			title: 'Are you sure?',
			modal: true,
			close: $(dlg).dialog('destroy'),
			buttons: {
				'Yes': function() {
					loading_tr(tr);
					
					$.post(base_url+'manager/languages/delete', {
						id:			id
					}, function(data) {
						if (data == 'deleted') $(tr).remove();
						else languages_view(id,tr);
						
						$(dlg).dialog('close');
					});
				},
				'No': function() {
					$(dlg).dialog('close');
				}
			}
		});
	});
	$(data).find('a[action=languages_default]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		loading_tr(tr);
		$.post(base_url+'manager/languages/default', {
			id:				id
		}, function(data) {
			// RELOAD THIS 
			languages_view(id,tr);
			
			// RELOAD PREVIOUS DEFAULT
			var p_tr = $(tr).closest('tbody').find('tr[data_id='+data+']');
			var p_id = $(p_tr).attr('data_id');
			
			languages_view(p_id,p_tr);
		});
	});
	
	$(data).find('#status_id').combobox();
}

function languages_edit(id,tr) {
	loading_tr(tr);
	
	$.post(base_url+'manager/languages/edit', {
		id:			id
	}, function(data) {
		data = $(data).replaceAll(tr);
		$('.button').button();
		init_languages(data);
	});
}

function languages_view(id,tr) {
	loading_tr(tr);
	
	$.post(base_url+'manager/languages/view', {
		id:			id
	}, function(data) {
		data = $(data).replaceAll(tr);
		$('.button').button();
		init_languages(data);
	});
}