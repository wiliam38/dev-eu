$().ready(function() {
	$("#resource_tabs").tabs();
	$('.button').button();
	
	$('#user_form').ajaxForm({
		dataType: 'json',
		beforeSubmit: function() {
			showActivity('Saving...');
		},
		success: function(data) {
			if (data.status == '1') {
				$('#return_form').submit();
			} else {
				hideActivity('Saving...');
				jError(data.error, 'Error');
			}
		}
	});
	
	$('button[action=delete_user]').click(function() {
		var panel = $('#data_panel');
		
		var dlg = $('<div><b>Are you sure to delete this User</b></div>').dialog({
			title: 'Are you sure?',
			modal: true,
			close: $(dlg).dialog('destroy'),
			buttons: {
				'Yes': function() {					
					$.post(base_url+'manager/usereditor/delete', {
						user_id:			$(panel).find('#general #user_id').val()
					}, function(data) {
						$(dlg).dialog('close');
						$('#return_form').submit();
					});
				},
				'No': function() {
					$(dlg).dialog('close');
				}
			}
		});
	});
	
	// STATUS
	$('select[name="status_id"]').combobox();
	
	// INIT ROLES
	init_roles_row($('body'));
	
	$('button.add_role_popup').click(function() {
		var dlg = $('<div id="add_role_popup">Loading...</div>').dialog({
			title: 'Add role',
			modal: true,
			width: 500,
			height: 180,
			buttons: {
				'Add': function() {			
					showActivity('Loading...');	
					
					$.post(base_url+'manager/usereditor/add_role', {
						role_id: $('#role_id_popup').val(),
						data_id: $('#data_id_popup').val()
					}, function(data) {
						var new_data = $('#user_roles_table tbody').append(data);
						init_roles_row(new_data);
						
						hideActivity();
					});
					
					$(dlg).remove();
				},
				'Cancel': function() {
					$(dlg).remove();
				}
			}
		});
		
		// LOAD ROLES
		load_roles();
	});
});

function load_roles() {
	var role_id = $('#role_id_popup').val();
	showActivity('Loading...', $('#add_role_popup'));
	
	if (!role_id) {
		$.ajax({
			url: base_url+'manager/usereditor/add_role_popup_get_role',
			method: 'post',
			async: false,
			data: {
				roles_list: $.map($('input[name="roles_role_id[]"]').serializeArray(), function(o) {
					var obj = $('input[name="roles_role_id[]"][value="'+o.value+'"]');
					if ($(obj).next('input[name="roles_data_id[]"]').val() == '' || $(obj).next('input[name="roles_data_id[]"]').val() == '0') return o.value;
				})
			},
			success: function(data) {
				role_id = data;
			}
		});
	}
	
	$.post(base_url+'manager/usereditor/add_role_popup', {
		user_id: $('#data_panel #general #user_id').val(),
		role_id: role_id,
		roles_list: $.map($('input[name="roles_role_id[]"]').serializeArray(), function(o) {
			var obj = $('input[name="roles_role_id[]"][value="'+o.value+'"]');
			if ($(obj).next('input[name="roles_data_id[]"]').val() == '' || $(obj).next('input[name="roles_data_id[]"]').val() == '0') return o.value;
		}),
		roles_data_list: $.map($('input[name="roles_role_id[]"][value="'+role_id+'"]').serializeArray(), function(o, i) {
			var obj = $('input[name="roles_role_id[]"][value="'+o.value+'"]:eq('+i+')');
			if ($(obj).next('input[name="roles_data_id[]"]').val() != '' && $(obj).next('input[name="roles_data_id[]"]').val() != '0') return $(obj).next('input[name="roles_data_id[]"]').val();
		})
	}, function(data) {
		$('#add_role_popup').html(data);
	});
}
function init_roles_row(obj) {
	// STATUS
	$('select[name="roles_status_id[]"]', obj).combobox();
	
	// BUTTON
	$('.button', obj).button();
}
