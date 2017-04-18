$().ready(function() {
	$.ajaxSetup( { type: "post" } );
	$('.button').button();
	$('#filter_form select[name="category_name"]').combobox();
});

function type_view(obj) {
	var tr = $(obj).closest('tr');
	var id = $(tr).attr('data-id');
	
	if (id == 'new') {
		$(tr).remove();
	} else {
		$(tr).showActivity();

		$.post(base_url+'manager/types/view', {
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

function type_edit(obj) {
	var tr = $(obj).closest('tr');
	var id = $(tr).attr('data-id');
	
	if (id == 'new') {
		var tr = $('<tr data_id="new"><td colspan="'+$(tr).closest('table').find('thead tr:first th').attr('colspan')+'">'+loading+'</td></tr>');		
		$('#data_panel table.data_table tbody').append(tr);
	}
	
	$(tr).showActivity();
	$.post(base_url+'manager/types/edit', {
		id:					id,
		table_type_name:	$('#filter_form *[name="category_name"]').val()
	}, function(data) {
		if (data.status == 1) {
			var new_tr = $(data.response).replaceAll(tr);
			init_types(new_tr);
		} else {
			jError(data.error, 'Error');
			$(tr).hideActivity();
		}
	}, 'json');
}

function type_save(obj) {
	var tr = $(obj).closest('tr');
	var id = $(tr).attr('data-id');
		
	$(tr).showActivity();	
	$.post(base_url+'manager/types/save', 
		$(tr).postData(),
	function(data) {
		if (data.status == 1) {
			if (id == 'new') $(tr).attr('data-id', data.id);
			type_view(tr);
		} else {
			jError(data.error, 'Error');
			$(tr).hideActivity();
		}
	}, 'json');
}

function type_delete(obj) {
	var tr = $(obj).closest('tr');
	
	jConfirm('Are you sure to delete this type?', 'Are you sure?', function(r) {
		if (r) {
			$(tr).showActivity();
			
			$.post(base_url+'manager/types/delete', {
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

function init_types(obj) {
	$('.button', obj).button();
	
	$('.file_upload_input', obj).uploadify({
		'uploader'  : base_url+'assets/libs/jquery-plugins/uploadify/uploadify.swf',
		'cancelImg' : base_url+'assets/libs/jquery-plugins/uploadify/cancel.png',
	    'script'    : base_url+'manager/files/upload_tmp_files',
	    'scriptData': { 
	    	'dbsid': unescape(document.cookie).match(/iss_w_box_session_database=([^;]*)/)[1].match(/[a-z0-9-]*$/i)[0]
			//, 'resize': 'no'
	    },
	    
	    'wmode'     : 'transparent',
	    'fileExt'   : '*.jpg;*.gif;*.png',
		'fileDesc'	: 'Images',
		'multi'		: false,		
	    'auto'		: true,
	    'removeCompleted'	: true,
	    'height'	: 50,
	    'width'		: 120,
	    'buttonImg'	: base_url+'assets/libs/jquery-plugins/uploadify/browse.gif',
	    'buttonText': '',
	 
	    'onOpen'    : 	function(event,ID,fileObj) {
							page_loading('Uploading image...');
							rmFileTmp($(event.target).closest('td').find('div.image_icon input'));
	    				},
	    'onComplete': 	function(event, ID, fileObj, response, data) {
	    					var thumb = response.replace(/\/([^\/]*)$/i,'/thumb_$1');
							$(event.target).closest('tr').find('div.image_icon img').attr('src',thumb);
							$(event.target).closest('td').find('div.image_icon img').show();	    					
							$(event.target).closest('td').find('div.image_icon input').val(response);
	    				},
		'onAllComplete': 	function() {
	    					page_loading('');  
	    			}
	});	
}