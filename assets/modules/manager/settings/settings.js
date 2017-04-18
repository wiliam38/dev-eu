$().ready(function() {
	init_filter($('body'));
	init_settings($('body'));
});

function init_filter(data) {
	$.ajaxSetup( { type: "post" } );
	$(data).find('.button').button();
	
	$(data).find('a[action=settings_show]').click(function() {
		page_loading('Loading...');	
		$('#data_div').html('');
		$.post(base_url+'manager/settings/show', {
			category_name:	$('#category_name').val()
		}, function(req_data) {
			var obj = $('#data_div').html(req_data);						
			init_settings(obj);
			page_loading('');	
		});
	});
	
	// ORGANIZERS
	$(data).find('#category_name').combobox({
		source: base_url+'manager/settings/categories'
	});
}

function init_settings(data) {
	$(data).find('.button').button();
	
	$(data).find('a[action=settings_edit]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		settings_edit(id,tr);
	});
	$(data).find('a[action=settings_save]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		loading_tr(tr);
		
		// get lang data
		var data = [];
		var i = 0;
		$(tr).find('td').not(':last').each(function(index, obj) {
			if (index > 2) {
				if ($(obj).find('#lang_id').val() != undefined) {
					data[i] = [	$(obj).find('#lang_id').val(), 
								$(obj).find('#lang_setting_id').val(), 
								$(obj).find('#value').val()];
					i++;
				}
			}
		});
		
		$.post(base_url+'manager/settings/save', {
			id:					id,
			def_name:			$(tr).find('#def_name').val(),
			def_description:	$(tr).find('#def_description').val(),
			def_value:			$(tr).find('#def_value').val(),
			lang_data:			data
		}, function(data) {
			if (id == 'new') id = data;
			settings_view(id,tr);
		});
	});
	$(data).find('a[action=settings_view]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		if (id == 'new') {
			$(tr).remove();
		} else {
			settings_view(id,tr);
		}		
	});
	$(data).find('a[action=settings_add]').click(function() {
		var tr = $('<tr data_id="new"><td colspan="6">'+loading+'</td></tr>');		
		var id = 'new';
		
		if ($('#data_panel').find('table.data_table tbody>tr:last').length > 0) {
			$('#data_panel').find('table.data_table tbody>tr:last').after(tr);
		} else {
			$('#data_panel').find('table.data_table tbody').html(tr);
		}
		
		settings_edit(id,tr);
	});
	$(data).find('a[action=settings_delete]').click(function() {
		var tr = $(this).closest('tr');
		var id = $(tr).attr('data_id');
		
		var dlg = $('<div><b>Are you sure to delete this setting?</b></div>').dialog({
			title: 'Are you sure?',
			modal: true,
			close: $(dlg).dialog('destroy'),
			buttons: {
				'Yes': function() {
					loading_tr(tr);
					$.post(base_url+'manager/settings/delete', {
						id:			id
					}, function(data) {
						if (data == 'deleted') $(tr).remove();
						else settings_view(id,tr);

						$(dlg).dialog('close');
					});
				},
				'No': function() {
					$(dlg).dialog('close');
				}
			}
		});
	});
}

function settings_edit(id,tr) {	
	loading_tr(tr);

	$.post(base_url+'manager/settings/edit', {
		id:			id
	}, function(data) {
		data = $(data).replaceAll(tr);
		$('button').button();
		init_settings(data);
	});
}

function settings_view(id,tr) {	
	loading_tr(tr);

	$.post(base_url+'manager/settings/view', {
		id:			id
	}, function(data) {
		data = $(data).replaceAll(tr);
		$('button').button();
		init_settings(data);
	});
}

function settings_generate_files() {
	page_loading('Generating...');
	
	$.post(base_url+'manager/settings/generate_files',
	function(data) {
		page_loading('');
	});
}