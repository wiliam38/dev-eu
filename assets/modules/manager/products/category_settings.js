function setting_view(obj) {
	var tr = $(obj).closest('tr');
	var id = $(tr).attr('data-id');
	
	if (id == 'new') {
		$(tr).remove();
	} else {
		tr.showActivity();		
		$.post(base_url+'manager/products_categories/settings_view', {
			id: id
		}, function(data) {
			if (data.status == 1) {
				tr = $(data.response).replaceAll(tr);
				$('.button', tr).button();
			} else {
				$(tr).hideActivity();
				jError(data.error, 'Error!');
			}
		}, 'json');	
	}
}

function setting_edit(obj) {
	var tr = $(obj).closest('tr');
	var id = $(tr).attr('data-id');
	
	if (id == 'new') tr = $('<tr data-id="new"><td colspan="'+($(obj).closest('table').find('thead th').length)+'"></td></tr>').appendTo($(obj).closest('table').find('tbody:first'));
	tr.showActivity();
	
	$.post(base_url+'manager/products_categories/settings_edit', {
		id: 			id,
		category_id:	$('#category_id').val()
	}, function(data) {
		if (data.status == 1) {
			tr = $(data.response).replaceAll(tr);
			$('.button', tr).button();
			$('select', tr).combobox();
			
			$(tr).find('.file_upload_input').uploadify({
				'uploader'  : base_url+'assets/libs/jquery-plugins/uploadify/uploadify.swf',
				'cancelImg' : base_url+'assets/libs/jquery-plugins/uploadify/cancel.png',
			    'script'    : base_url+'manager/files/upload_tmp_files',
			    'scriptData': { 
			    	'dbsid': unescape(document.cookie).match(/iss_w_box_session_database=([^;]*)/)[1].match(/[a-z0-9-]*$/i)[0]
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
									rmFileTmp($(event.target).closest('tr').find('div.image_icon input'));
			    				},
			    'onComplete': 	function(event, ID, fileObj, response, data) {
			    					var thumb = response.replace(/\/([^\/]*)$/i,'/thumb_$1');
									$(event.target).closest('tr').find('div.image_icon img').attr('src',thumb);
									$(event.target).closest('tr').find('div.image_icon img').show();	    					
									$(event.target).closest('tr').find('div.image_icon input').val(response);
			    				},
				'onAllComplete': 	function() {
			    					page_loading('');  
			    			}
			});	
		} else {
			if (id == 'new') $(tr).remove();
			else $(tr).hideActivity();
			jError(data.error, 'Error!');
		}
	}, 'json');	
}

function setting_save(obj) {
	var tr = $(obj).closest('tr');
	var id = $(tr).attr('data-id');
	
	tr.showActivity();	
	$.post(base_url+'manager/products_categories/settings_save', 
		$(tr).postData(),
	function(data) {
		if (data.status == 1) {
			tr = $(data.response).replaceAll(tr);
			$('.button', tr).button();
			$('select', tr).combobox();			

			if (id == 'new') {
				sub_data = $(data.sub_data).insertAfter(tr);
				$('.button', sub_data).button();
			}
		} else {
			$(tr).hideActivity();
			jError(data.error, 'Error!');
		}
	}, 'json');	
}

function setting_delete(obj) {
	jConfirm(settings_delete_msg, settings_delete_title, function(r) {
		if (r) {
			page_loading('Deleting...');
			$.post(base_url+'manager/products_categories/settings_delete', {
				setting_id:	$(obj).closest('tr').attr('data-id')
			}, function(data) {
				if (data.status == '1') {
					$(obj).closest('tr').next('tr').remove();
					$(obj).closest('tr').remove();					
					page_loading('');
				} else {
					page_loading('');
					jError(data.error, 'Error!');
				}
			}, 'json');
		}
	});
}







function setting_sub_view(obj) {
	var tr = $(obj).closest('tr');
	var id = $(tr).attr('data-id');
	
	if (id == 'new') {
		$(tr).remove();
	} else {
		tr.showActivity();		
		$.post(base_url+'manager/products_categories/settings_sub_view', {
			id: id
		}, function(data) {
			if (data.status == 1) {
				tr = $(data.response).replaceAll(tr);
				$('.button', tr).button();
			} else {
				$(tr).hideActivity();
				jError(data.error, 'Error!');
			}
		}, 'json');	
	}
}

function setting_sub_edit(obj) {
	var tr = $(obj).closest('tr');
	var id = $(tr).attr('data-id');
	
	if (id == 'new') tr = $('<tr data-id="new"><td colspan="'+($(obj).closest('table').find('thead th').length)+'"></td></tr>').appendTo($(obj).closest('table').find('tbody:first'));
	tr.showActivity();
	
	$.post(base_url+'manager/products_categories/settings_sub_edit', {
		id: 			id,
		category_setting_id:	$(obj).closest('table').closest('tr').prev('tr').attr('data-id')
	}, function(data) {
		if (data.status == 1) {
			tr = $(data.response).replaceAll(tr);
			$('.button', tr).button();
			$('select', tr).combobox();
			
			$(tr).find('.file_upload_input').uploadify({
				'uploader'  : base_url+'assets/libs/jquery-plugins/uploadify/uploadify.swf',
				'cancelImg' : base_url+'assets/libs/jquery-plugins/uploadify/cancel.png',
			    'script'    : base_url+'manager/files/upload_tmp_files',
			    'scriptData': { 
			    	'dbsid': unescape(document.cookie).match(/iss_w_box_session_database=([^;]*)/)[1].match(/[a-z0-9-]*$/i)[0]
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
									rmFileTmp($(event.target).closest('tr').find('div.image_icon input'));
			    				},
			    'onComplete': 	function(event, ID, fileObj, response, data) {
			    					var thumb = response.replace(/\/([^\/]*)$/i,'/thumb_$1');
									$(event.target).closest('tr').find('div.image_icon img').attr('src',thumb);
									$(event.target).closest('tr').find('div.image_icon img').show();	    					
									$(event.target).closest('tr').find('div.image_icon input').val(response);
			    				},
				'onAllComplete': 	function() {
			    					page_loading('');  
			    			}
			});	
		} else {
			if (id == 'new') $(tr).remove();
			else $(tr).hideActivity();
			jError(data.error, 'Error!');
		}
	}, 'json');	
}

function setting_sub_save(obj) {
	var tr = $(obj).closest('tr');
	var id = $(tr).attr('data-id');
	
	tr.showActivity();	
	$.post(base_url+'manager/products_categories/settings_sub_save', 
		$(tr).postData(),
	function(data) {
		if (data.status == 1) {
			tr = $(data.response).replaceAll(tr);
			$('.button', tr).button();
			$('select', tr).combobox();
		} else {
			$(tr).hideActivity();
			jError(data.error, 'Error!');
		}
	}, 'json');	
}

function setting_sub_delete(obj) {
	jConfirm(settings_sub_delete_msg, settings_sub_delete_title, function(r) {
		if (r) {
			page_loading('Deleting...');
			$.post(base_url+'manager/products_categories/settings_sub_delete', {
				setting_value_id:	$(obj).closest('tr').attr('data-id')
			}, function(data) {
				if (data.status == '1') {
					$(obj).closest('tr').remove();
					page_loading('');
				} else {
					page_loading('');
					jError(data.error, 'Error!');
				}
			}, 'json');
		}
	});
}