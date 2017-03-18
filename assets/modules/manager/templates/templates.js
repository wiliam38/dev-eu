$().ready(function() {
	init_templates($('body'));
});

function init_templates(data) {
	$(data).find('a[action=templates_edit]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		templates_edit(id,tr);
	});
	$(data).find('a[action=templates_save]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		loading_tr(tr);
		$.post(base_url+'manager/templates/save', 
			$(tr).postData()
		, function(data) {
			if (id == 'new') id = data;
			templates_view(id,tr);
		});
	});
	$(data).find('a[action=templates_view]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		if (id == 'new') {
			$(tr).remove();
		} else {
			templates_view(id,tr);
		}		
	});
	$(data).find('a[action=templates_add]').click(function() {
		var tr = $('<tr data_id="new"><td colspan="10">Loading...</td></tr>');		
		var id = 'new';
		
		if ($(this).closest('table').find('tbody>tr:last').length > 0) {
			$(this).closest('table').find('tbody>tr:last').after(tr);
		} else {
			$(this).closest('table').find('tbody').html(tr);
		}
		
		templates_edit(id,tr);
	});
	$(data).find('a[action=templates_delete]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		var dlg = $('<div><b>Are you sure to delete this template?</b></div>').dialog({
			title: 'Are you sure?',
			modal: true,
			close: $(dlg).dialog('destroy'),
			buttons: {
				'Yes': function() {
					loading_tr(tr);
					
					$.post(base_url+'manager/templates/delete', {
						id:			id
					}, function(data) {
						if (data == 'deleted') $(tr).remove();
						else templates_view(id,tr);
						
						$(dlg).dialog('close');
					});
				},
				'No': function() {
					$(dlg).dialog('close');
				}
			}
		});
	});
	
	$(data).find('#type_id').combobox();
}

function templates_edit(id,tr) {
	loading_tr(tr);
	
	$.post(base_url+'manager/templates/edit', {
		id:			id
	}, function(data) {
		data = $(data).replaceAll(tr);
		$('.button').button();
		init_templates(data);
	});
}

function templates_view(id,tr) {
	loading_tr(tr);
	
	$.post(base_url+'manager/templates/view', {
		id:			id
	}, function(data) {
		data = $(data).replaceAll(tr);
		$('.button').button();
		init_templates(data);
	});
}