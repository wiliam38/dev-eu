$().ready(function() {
	init_plugins($('body'));
});

function init_plugins(data) {
	$(data).find('a[action=plugins_edit]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		plugins_edit(id,tr);
	});
	$(data).find('a[action=plugins_save]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		loading_tr(tr);
		$.post(base_url+'manager/plugins/save', {
			id:				id,			
			name:			$(tr).find('#name').val(),
			model:			$(tr).find('#model').val(),
			template:		$(tr).find('#template').val(),
			parameters:		$(tr).find('#parameters').val(),
			type_id:		$(tr).find('#type_id').val()
		}, function(data) {
			if (id == 'new') id = data;
			plugins_view(id,tr);
		});
	});
	$(data).find('a[action=plugins_view]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		if (id == 'new') {
			$(tr).remove();
		} else {
			plugins_view(id,tr);
		}		
	});
	$(data).find('a[action=plugins_add]').click(function() {
		var tr = $('<tr data_id="new"><td colspan="7">Loading...</td></tr>');		
		var id = 'new';
		
		if ($(this).closest('table').find('tbody>tr:last').length > 0) {
			$(this).closest('table').find('tbody>tr:last').after(tr);
		} else {
			$(this).closest('table').find('tbody').html(tr);
		}
		
		plugins_edit(id,tr);
	});
	$(data).find('a[action=plugins_delete]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		var dlg = $('<div><b>Are you sure to delete this plugin?</b></div>').dialog({
			title: 'Are you sure?',
			modal: true,
			close: $(dlg).dialog('destroy'),
			buttons: {
				'Yes': function() {
					loading_tr(tr);
					
					$.post(base_url+'manager/plugins/delete', {
						id:			id
					}, function(data) {
						if (data == 'deleted') $(tr).remove();
						else plugins_view(id,tr);
						
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

function plugins_edit(id,tr) {
	loading_tr(tr);
	
	$.post(base_url+'manager/plugins/edit', {
		id:			id
	}, function(data) {
		data = $(data).replaceAll(tr);
		$('.button').button();
		init_plugins(data);
	});
}

function plugins_view(id,tr) {
	loading_tr(tr);
	
	$.post(base_url+'manager/plugins/view', {
		id:			id
	}, function(data) {
		data = $(data).replaceAll(tr);
		$('.button').button();
		init_plugins(data);
	});
}

function plugins_generate_files() {
	page_loading('Generating...');
	
	$.post(base_url+'manager/plugins/generate_files',
	function(data) {
		page_loading('');
	});
}