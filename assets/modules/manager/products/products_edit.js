var change_alias = false;
var change_menu_title = false;

$().ready(function() {	
	$("#resource_tabs, #resource_lang_tabs").tabs();	
	$('#resource_tabs select').combobox();
	
	$('#product_form').ajaxForm({
		dataType: 'json',
		beforeSerialize:function($Form, options) {
			for (instance in CKEDITOR.instances) CKEDITOR.instances[instance].updateElement();
			return true; 
		},
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
	
	$('#product_delete').click(function() {
		if ($('#product_id').val() != 'new') {
			
			jConfirm(product_delete_msg, product_delete_title, function(r) {
				if (r) {
					page_loading('Deleting...');
					$.post(base_url+'manager/'+product_link+'/delete', {
						product_id:	$('#product_id').val()
					}, function(data) {
						if (data.status == '1') $('#return_form').submit();
						else {
							page_loading('');
							jAlert(data.error);
						}
					}, 'json');
				}
			});
		}
	});
	
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
			$.post(base_url+'manager/'+product_link+'/load_gallery_item', {
				image_src: response
			}, function(tr) {			
				tr = $(tr).insertBefore($('#gallery_data_div .clear'));
				gallery_init(tr);

				if ($('#gallery_data_div input[name="product_main_image_chk"]:checked').length == 0) {
					$('#gallery_data_div input[name="product_main_image_chk"]:first').attr('checked',true);
					changeMainImage($('#gallery_data_div input[name="product_main_image_chk"]:first'));
				}					
			});
		},
		'onAllComplete': 	function() {
			page_loading('');  
		}
	});

	content_init($('body #data_panel'));
	gallery_init($('#gallery'));
	price_init($('body #data_panel table.prices_table tbody'));
	toggle_discount();
});


function content_init(obj) {
	content_lang_buttons(obj);
	
	$('.vAlign', obj).vAlign();
	
	// BUTTONS
	$('a.button', obj).button();
	$('select', obj).combobox();
	
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
	
	$(obj).find('.manual_upload_input').uploadify({
		'uploader'  : base_url+'assets/libs/jquery-plugins/uploadify/uploadify.swf',
		'cancelImg' : base_url+'assets/libs/jquery-plugins/uploadify/cancel.png',
	    'script'    : base_url+'manager/files/upload_tmp_files',
	    'scriptData': { 
	    	'dbsid': unescape(document.cookie).match(/iss_w_box_session_database=([^;]*)/)[1].match(/[a-z0-9-]*$/i)[0]
	    },
	    
	    'wmode'     : 'transparent',
		'fileDesc'	: 'Documents',
		'multi'		: false,		
	    'auto'		: true,
	    'removeCompleted'	: true,
	    'height'	: 50,
	    'width'		: 120,
	    'buttonImg'	: base_url+'assets/libs/jquery-plugins/uploadify/browse.gif',
	    'buttonText': '',
	 
	    'onOpen'    : 	function(event,ID,fileObj) {
							page_loading('Uploading image...');
							rmFileTmp($(event.target).closest('td').find('input.file-name-input'));
	    				},
	    'onComplete': 	function(event, ID, fileObj, response, data) { 					
	    					$(event.target).closest('td').find('input.file-name-input').val(response);
	    					$(event.target).closest('td').find('input.file-name').val(response);
	    				},
		'onAllComplete': 	function() {
	    					page_loading('');  
	    			}
	});
	
	category_settings();
	init_video_type(obj);
	recipe_change($(obj).find('input[name="0_recipe_id[]"]'));
}

function gallery_init(obj) {
	$(obj).find('.vAlign').vAlign();
}

function removeGalleryItem(obj) {
	jConfirm(product_image_delete_msg, product_image_delete_title, function(r) {
		if (r) {
			page_loading('Deleting image...');
					
			// DELETE TMP
			rmFileTmp($(obj).closest('div.gallery-item').find('input[name="product_image_src[]"]'));
			
			// REMOVE ROW
			$(obj).closest('div.gallery-item').remove();
			if ($('#gallery_data_div input[name="product_main_image_chk"]:checked').length == 0) {
				$('#gallery_data_div input[name="product_main_image_chk"]:first').attr('checked',true);
				changeMainImage($('#gallery_data_div input[name="product_main_image_chk"]:first'));
			}	
			
			page_loading('');
		}
	});	
}
function changeMainImage(obj) {
	$('input[name="product_main_image[]"]').val('0');
	$(obj).parent('div.gallery-main-image').find('input[name="product_main_image[]"]').val('1');
}

/* PRICES */
function price_init(obj) {
	$(obj).find('.button').button();
	
	$(obj).find('.color_upload_input').uploadify({
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
function addPrice(obj) {
	page_loading('Loading...');
	
	$.post(base_url+'manager/'+product_link+'/load_price_row', {		
	}, function(data) {						
		tr = $(data).appendTo($(obj).closest('table').find('tbody'));	
		price_init(tr);	
		
		page_loading('');			
	});
}
function removePrice(obj) {
	jConfirm(product_price_delete_msg, product_price_delete_title, function(r) {
		if (r) {
			// REMOVE ROW
			$(obj).closest('tr').remove();
		}
	});	
}


function category_settings() {
	var category_id = $('#data_panel #general input[name="category_id"]').val();
	
	$('#categories .category-settings').hide();
	$('#categories #category_settings_'+category_id).show();
	
	if (category_id == 2) {
		// APARATI
		$('#gallery_tab').show();
		$('#resource_lang_tabs .cat-2-image').show();
		$('#resource_lang_tabs .cat-all-image').hide();		
		$('#coffee_gift_tr').show();
	} else {
		$('#gallery_tab, #gallery').hide();
		$('#resource_lang_tabs .cat-2-image').hide();
		$('#resource_lang_tabs .cat-all-image').show();
		$('#coffee_gift_tr').hide();
	}
	
	if (category_id == 1) {
		// KAPSULAS
		$('#resource_lang_tabs .flavor').show();
	} else {
		$('#resource_lang_tabs .flavor').hide();
	}
	
	if (category_id == 8) $('#resource_lang_tabs .cat-title').each(function(i,o) { $(o).text($(o).attr('data-title-8')); });
	else $('#resource_lang_tabs .cat-title').each(function(i,o) { $(o).text($(o).attr('data-title')); });
	
	// CONTENT
	$('#resource_lang_tabs tr.cat').removeClass('active');
	$('#resource_lang_tabs tr.cat.cat-all').addClass('active');
	$('#resource_lang_tabs tr.cat.cat-'+category_id).addClass('active');
	$('#resource_lang_tabs tr.active .category-title input[type="checkbox"]').each(function(i,o) {
		content_data_toggle(o);
	});
}

function content_data_toggle(obj) {
	if ($(obj).is(':checked')) {
		$(obj).closest('table').find('tr.'+$(obj).attr('data-group')).removeAttr('style');
		$(obj).closest('table').find('tr.no-data.'+$(obj).attr('data-group')).hide();
	} else {
		$(obj).closest('table').find('tr.'+$(obj).attr('data-group')).hide();
		$(obj).closest('table').find('tr.no-data.'+$(obj).attr('data-group')).removeAttr('style');
	}
}

function init_video_type(obj) {	
	$('input[name="3_video_type_id[]"]', obj).each(function(i,o) {
		var type_id = $(o).val();
		var video_id = $(o).closest('tr').find('input[name="3_video_link[]"]').val();
		
		if ($.trim(video_id) != '') {
			$(o).closest('tr').find('.video-preview').html(
					'<object width="290" height="245"> '+
					'	<param name="movie" value="'+video_links[type_id]+video_id+'?hl=en_US&version=3&rel=0"></param> '+
					'	<param name="allowFullScreen" value="true"></param> '+
					'	<param name="allowscriptaccess" value="always"></param> '+
					'	<embed src="'+video_links[type_id]+video_id+'?hl=en_US&version=3&rel=0" type="application/x-shockwave-flash" width="290" height="250" allowscriptaccess="always" allowfullscreen="true"></embed> '+
					'</object>');
		} else {
			$(o).closest('tr').find('.video-preview').html('');
		}
	});
}

function recipe_change(obj) {
	$(obj).closest('table').find('.recipe-preview').html('');
	var recipe_id = $(obj).closest('tr').find('input[name="0_recipe_id[]"]').val();
	var lang_id = $(obj).closest('.lang-tab').find('input[name="language_id[]"]').val();
	
	if (recipe_id == '') {
		$(obj).closest('table').find('.recipe-preview').html('');
	} else {
		$.post(base_url+'manager/products_products/load_recipe', {
			lang_id: lang_id,
			recipe_id: recipe_id
		}, function(data) {
			$(obj).closest('table').find('.recipe-preview').html(data);
		});
	}
}

function toggle_discount() {
	if ($('#discount_active').is(':checked')) {
		$('#discount_price').removeAttr('disabled');
		$('#discount_color_combobox').removeAttr('disabled');
	} else {
		$('#discount_price').attr('disabled', 'disabled');
		$('#discount_color_combobox').attr('disabled', 'disabled');
	}
	update_discount();
}
function update_discount() {
	if ($('#discount_active').is(':checked')) {
		var price = parseFloat($('#price').val());
		var discount_price = parseFloat($('#discount_price').val());
		
		if (price != 0) var percents = 100 - (discount_price * 100 / price);
		else var percents = 100;
		if (isNaN(percents)) percents = 0;
			
		$('#discount_percents').html(Math.round(percents).toString()+'%');
	} else {
		$('#discount_percents').html('');
	}
}

function toggle_coffee_gift() {
	if ($('#coffee_gift_active').is(':checked')) {
		$('#coffee_gift_amount').removeAttr('disabled');
	} else {
		$('#coffee_gift_amount').attr('disabled', 'disabled');
	}
}