$().ready(function() {
	$('body').find('.button').button();
	
	// CATEGORY NAME
	$('#filter_form select[name="category_name"]').combobox();
});

function init_lexicon(data) {
	$(data).find('.button').button();
}

function lexicon_view(obj,id) {	
	var tr = $(obj).closest('tr');

	if (id == 'new') {
		$(tr).remove();
	} else {
		loading_tr(tr);
	
		$.post(base_url+'manager/lexicon/view', {
			id:			id
		}, function(data) {
			data = $(data).replaceAll(tr);
			$('button').button();
			init_lexicon(data);
		});
	}
}
function lexicon_edit(obj,id) {
	var tr = $(obj).closest('tr');
	
	loading_tr(tr);
	
	$.post(base_url+'manager/lexicon/edit', {
		id:			id
	}, function(data) {
		data = $(data).replaceAll(tr);
		$('button').button();
		init_lexicon(data);
	});
}
function lexicon_save(obj,id) {	
	var tr = $(obj).closest('tr');
	
	loading_tr(tr);
	$.post(base_url+'manager/lexicon/save', 
		$(tr).find('input'), 
	function(data) {
		if (id == 'new') id = data;
		lexicon_view($(tr).find('td:last'),id);
	});
}
function lexicon_add(obj) {
		var tr = $('<tr data_id="new"><td colspan="10">'+loading+'</td></tr>');		
		var id = 'new';

		if ($('#data_panel').find('table.data_table tbody>tr:last').length > 0) {
			$('#data_panel').find('table.data_table tbody>tr:last').after(tr);
		} else {
			$('#data_panel').find('table.data_table tbody').html(tr);
		}
		
		lexicon_edit($(tr).find('td:last'),id);
}
function lexicon_delete(obj,id) {
	var tr = $(obj).closest('tr');
	
	var dlg = $('<div><b>Are you sure to delete this lexicon?</b></div>').dialog({
		title: 'Are you sure?',
		modal: true,
		close: $(dlg).dialog('destroy'),
		buttons: {
			'Yes': function() {
				loading_tr(tr);
				$.post(base_url+'manager/lexicon/delete', {
					id:			id
				}, function(data) {
					if (data == 'deleted') $(tr).remove();
					else lexicon_view($(tr).find('td:last'),id);

					$(dlg).dialog('close');
				});
			},
			'No': function() {
				$(dlg).dialog('close');
			}
		}
	});
}
function lexicon_generate_files() {
	page_loading('Generating...');
	
	$.post(base_url+'manager/lexicon/generate_files',
	function(data) {
		page_loading('');
	});
}

function lexicon_rich_text(obj) {	
	var dlg = $('<div id="popup_dialog_div"></div>')
		.html('<textarea style="height: 235px; width: 560px;" id="lexicon_ck">'+$(obj).prev('input').val()+'</textarea>')
		.dialog({
			title: $(obj).closest('tr').find('input[name="system_name"]').length!=0?$(obj).closest('tr').find('input[name="system_name"]').val():$(obj).closest('tr').find('td:first').text(),
			modal: true,
			resizable: false,
			width: '600',
			height: '405',
			close: function() { 
				if (CKEDITOR.instances.lexicon_ck) CKEDITOR.instances.lexicon_ck.destroy(true);
				$(dlg).remove(); 
			},
			buttons: {
				'Update': function() {	
					var val = CKEDITOR.instances.lexicon_ck.getData();
					var input = $(obj).prev('input');					
					var div = $('<div id="value" style="display: none;">'+val+'</div>').insertAfter(input);
					
					if ($('p, ul, ol, div', div).length == 1) {
						$(div).html($.trim($('p', div).html()));
					}
					
					$(input).val($(div).html());
					$(div).remove();					
					$(dlg).dialog('close');
					$('div.ui-widget-overlay').remove();
				},
				'Cancel': function() {
					$(dlg).dialog('close');
					$('div.ui-widget-overlay').remove();
				}
			},
			open: function(event, ui) { 
				var config = {
					width: 570,
					height: 218,
					resize_enabled: false,	
					removePlugins: 'elementspath',
					toolbar : [
						['Bold','Italic','Underline','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BulletedList','NumberedList','-','Link','Unlink','-','Source'],
						['Format','Font','FontSize','TextColor','BGColor','-','RemoveFormat']
			        ]
				};
				CKEDITOR.replace('lexicon_ck', config);
			}
		});	
}
