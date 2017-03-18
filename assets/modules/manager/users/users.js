$().ready(function() {
	$('body').find('a[action=user_delete]').click(function() {
		var tr = $(this).closest('tr');
		
		var dlg = $('<div><b>Are you sure to delete this User?</b></div>').dialog({
			title: 'Are you sure?',
			modal: true,
			close: $(dlg).dialog('destroy'),
			buttons: {
				'Yes': function() {	
					page_loading('Deleting user...');
					// DELETE TMP
					$.post(base_url+'manager/usereditor/delete', {
						user_id:			$(tr).attr('id')
					}, function(data) {
						$(tr).remove();
						page_loading('');
					});
					
					$(dlg).dialog('close');
				},
				'No': function() {
					$(dlg).dialog('close');
				}
			}
		});	
	});
	
	$('#filter_form select').multiSelect();
});

function exportXML(obj) {
	$('#filter_form')
		.attr('action', base_url+'manager/users/excel')
		.submit();
	$('#filter_form').attr('action', base_url+'manager/users/load');
}
