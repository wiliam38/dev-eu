var change_alias = false;
var change_menu_title = false;


function content_init(obj) {
	content_lang_buttons(obj)
	
	$('.vAlign', obj).vAlign();
	
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
	$('#linked_product_id').combobox({
		source: base_url+'manager/reciepes_reciepes/linked_products'
	});
	$('.button', obj).button();
	$('select', obj).combobox();
	
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
							$(event.target).closest('tr').find('div.image_icon img').vAlign();
	    				},
		'onAllComplete': 	function() {
	    					page_loading('');  
	    			}
	});	
}

function gallery_init(obj) {
	$(obj).find('.vAlign').vAlign();
}
function removeGalleryItem(obj) {
	jConfirm('Are you sure to delete this Image?', 'Are you sure?', function(r) {
		if (r) {
			page_loading('Deleting image...');
					
			// DELETE TMP
			rmFileTmp($(obj).closest('div.gallery-item').find('input[name="reciepe_image_src[]"]'));
			
			// REMOVE ROW
			$(obj).closest('div.gallery-item').remove();
			if ($('#gallery_data_div input[name="reciepe_main_image_chk"]:checked').length == 0) {
				$('#gallery_data_div input[name="reciepe_main_image_chk"]:first').attr('checked',true);
				changeMainImage($('#gallery_data_div input[name="reciepe_main_image_chk"]:first'));
			}	
			
			page_loading('');
		}
	});	
}
function changeMainImage(obj) {
	$('input[name="reciepe_main_image[]"]').val('0');
	$(obj).parent('div.gallery-main-image').find('input[name="reciepe_main_image[]"]').val('1');
}

function addLinkedProduct(obj) {
	if ($('#linked_product_id').attr('data_id') != '') {
		page_loading('Loading...');
		
		$.post(base_url+'manager/reciepes_reciepes/load_linked_item', {
			id: $('#linked_product_id').val()
		}, function(tr) {
			if ($(obj).closest('table').find('tbody').find('tr:first').length == 0) {
				$(obj).closest('table').find('tbody').html(tr);
			} else {
				$(tr).insertAfter($(obj).closest('table').find('tbody').find('tr:last'));
			}			
			tr = $(obj).closest('table').find('tbody').find('tr:last');	
			$(tr).find('.button').button();
				
			page_loading('');
		});
	}	
}
function removeLinkedProduct(obj) {
	jConfirm('Are you sure to remove this material?', 'Are you sure?', function(r) {
		if (r) $(obj).closest('tr').remove();
	});	
}