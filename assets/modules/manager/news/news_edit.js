var change_alias = false;
var change_menu_title = false;

$().ready(function() {
	$('#add-gallery-items').uploadify({
		'uploader'  : base_url+'assets/libs/jquery-plugins/uploadify/uploadify.swf',
		'cancelImg' : base_url+'assets/libs/jquery-plugins/uploadify/cancel.png',
	    'script'    : base_url+'manager/files/upload_tmp_files',
	    'scriptData': { 
	    	'dbsid': unescape(document.cookie).match(/iss_w_box_session_database=([^;]*)/)[1].match(/[a-z0-9-]*$/i)[0]
	    },
	    
	    'wmode'     : 'transparent',
	    'fileExt'   : '*.jpg;*.gif;*.png',
		'fileDesc'	: 'Images',
		'multi'		: true,		
	    'auto'		: true,
	    'removeCompleted'	: true,
	    'height'	: 22,
	    'width'		: 60,
	    'buttonImg'	: base_url+'assets/libs/jquery-plugins/uploadify/browse.gif',
	    'buttonText': '',
	 
	    'onOpen'    : 	function(event,ID,fileObj) {
			page_loading('Uploading image...');
	    },
	    'onComplete': 	function(event, ID, fileObj, response, data) {
			$.post(base_url+'manager/news_news/load_gallery_item', {
				image_src: response
			}, function(tr) {			
				tr = $(tr).insertBefore($('#gallery_data_div .clear'));
				gallery_init(tr);

				if ($('#gallery_data_div input[name="new_main_image_chk"]:checked').length == 0) {
					$('#gallery_data_div input[name="new_main_image_chk"]:first').attr('checked',true);
					changeMainImage($('#gallery_data_div input[name="new_main_image_chk"]:first'));
				}					
			});
		},
		'onAllComplete': 	function() {
			page_loading('');  
		}
	});
});

function content_init(obj) {
	content_lang_buttons(obj);
	
	// EDIOTRS
	var config = { };
	$('textarea.editor', obj).each( function() {
	    CKEDITOR.replace( $(this).attr('id'),config );
	});
	
	config = {
		width: 521,
		height: 150,
		resize_enabled: false,	
		removePlugins: 'elementspath',
		//contentsCss: '',
		toolbar : [
			['Bold','Italic','Underline','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BulletedList','NumberedList','-','Source'],
			['Format','Font','FontSize','TextColor','BGColor','-','RemoveFormat']
        ]
	};
	$('textarea.editor_simple', obj).each( function() {
	    CKEDITOR.replace( $(this).attr('id'),config );
	});
	
	$('input.date[data="from"]', obj).each(function(i,o) {
		$(o).issCalendar({
			show_time: true,
			show_tbd: true,
			max_date: $(o).closest('td').find('input.date[data="to"]')
		});
	});
	$('input.date[data="to"]', obj).each(function(i,o) {
		$(o).issCalendar({
			show_time: true,
			show_tbd: true,
			min_date: $(o).closest('td').find('input.date[data="from"]')
		});
	});
	
	// BUTTONS
	$('.button', obj).button();
	$('select', obj).combobox();
	
	gallery_init($('body #data_panel table.gallery_table tbody'));
}

function init_answer(obj) {
	$('.button', obj).button();
	$('select[name="answer_type_id[]"]', obj).combobox();
	
	$('.file_upload_input', obj).uploadify({
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
}

function add_answer() {
	var tr = $('<tr><td colspan="100%"></td></tr>');
	var tr = $(tr).appendTo($('#answers_table tbody'));
	loading_tr(tr);
	
	$.post(base_url+'manager/news_news/add_answer', 
	function(data) {
		if (data.status == 1) {
			var new_tr = $(data.response).replaceAll(tr);
			init_answer(new_tr);
		} else {
			$(tr).remove();
		}
	}, 'json');
}
function delete_answer(obj) {
	$(obj).closest('tr').remove();
}

function gallery_init(obj) {
	$(obj).find('.vAlign').vAlign();
}
function removeGalleryItem(obj) {
	jConfirm('Are you sure to delete this Image?', 'Are you sure?', function(r) {
		if (r) {
			page_loading('Deleting image...');
					
			// DELETE TMP
			rmFileTmp($(obj).closest('div.gallery-item').find('input[name="new_image_src[]"]'));
			
			// REMOVE ROW
			$(obj).closest('div.gallery-item').remove();
			if ($('#gallery_data_div input[name="new_main_image_chk"]:checked').length == 0) {
				$('#gallery_data_div input[name="new_main_image_chk"]:first').attr('checked',true);
				changeMainImage($('#gallery_data_div input[name="new_main_image_chk"]:first'));
			}	
			
			page_loading('');
		}
	});	
}
function changeMainImage(obj) {
	$('input[name="new_main_image[]"]').val('0');
	$(obj).parent('div.gallery-main-image').find('input[name="new_main_image[]"]').val('1');
}
