var change_alias = false;
var change_menu_title = false;

function content_init(obj) {		
	// BUTTONS
	$(obj).find('a.button, button.button').button();
	$(obj).find('select').combobox();
	
	// INIT BUTTONS
	content_lang_buttons(obj);
	
	// CONTENT TYPE
	$(obj).find('select[name$="_content_type_id"], select[name$="_content_type_id_combobox_select"]').each(function(index, obj) {
		init_content_type(obj);
	});
	
	$(obj).find('input.date[data="from"]').each(function(i,o) {
		$(o).issCalendar({
			show_time: true,
			show_tbd: true,
			max_date: $(o).closest('td').find('input.date[data="to"]')
		});
	});
	$(obj).find('input.date[data="to"]').each(function(i,o) {
		$(o).issCalendar({
			show_time: true,
			show_tbd: true,
			min_date: $(o).closest('td').find('input.date[data="from"]')
		});
	});
	
	
	$(obj).find('.input-title').focus(function() {
		var tab = $(this).closest('div.ui-tabs-panel');		
		change_alias = false;
		if (title_to_alias($(this).val()) == $(tab).find('.input-alias').val() || $(tab).find('.input-alias').val() == '')
			change_alias = true;
		change_menu_title = false;
		if ($(this).val() == $(tab).find('.input-menu_title').val() || $(tab).find('.input-menu_title').val() == '')
			change_menu_title = true;
	});
	$(obj).find('.input-title').keyup(function(event) {
		var tab = $(this).closest('div.ui-tabs-panel');		
		if (change_alias) 
			$(tab).find('.input-alias').val(title_to_alias($(this).val()));	
		if (change_menu_title) 
			$(tab).find('.input-menu_title').val($(this).val());
	});
	$(obj).find('.input-title').bind('change', function() {
		var tab = $(this).closest('div.ui-tabs-panel');		
		if (change_alias) 
			$(tab).find('.input-alias').val(title_to_alias($(this).val()));
		if (change_menu_title) 
			$(tab).find('.input-menu_title').val($(this).val());	
	});
	$(obj).find('.input-title').bind('paste', function() {
		var tab = $(this).closest('div.ui-tabs-panel');		
		if (change_alias) 
			$(tab).find('.input-alias').val(title_to_alias($(this).val()));	
		if (change_menu_title) 
			$(tab).find('.input-menu_title').val($(this).val());
	});
	$(obj).find('.input-alias').bind('change', function() {
		$(this).val(title_to_alias($(this).val()));
	});
	$(obj).find('.input-alias').bind('keyup', function() {
		$(this).val(title_to_alias($(this).val()));
	});
	$(obj).find('.input-alias').bind('paste', function() {
		$(this).val(title_to_alias($(this).val()));
	});
	
	$(obj).find('.file_upload_input').uploadify({
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
	
	$('#add-gallery-items').uploadify({
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
			$.post(base_url+'manager/pages/load_gallery_item', {
				image_src: response
			}, function(tr) {			
				tr = $(tr).insertBefore($('#gallery_data_div .clear'));
				gallery_init(tr);

				if ($('#gallery_data_div input[name="page_main_image_chk"]:checked').length == 0) {
					$('#gallery_data_div input[name="page_main_image_chk"]:first').attr('checked',true);
					changeMainImage($('#gallery_data_div input[name="page_main_image_chk"]:first'));
				}					
			});
		},
		'onAllComplete': 	function() {
			page_loading('');  
		}
	});
	gallery_init($('body #data_panel table.gallery_table tbody'));
	
	var config = {
		//contentsCss: '',
		width: '100%',
		height: 700
	};		
	$('textarea.editor', obj).each( function() {
	    CKEDITOR.replace( $(this).attr('id'),config );
	});
	
	var config = {
		width: 521,
		height: 100,
		resize_enabled: false,	
		removePlugins: 'elementspath',
		//contentsCss: '',
		toolbar : [
			['Bold','Italic','Underline','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BulletedList','NumberedList','-','Source'],
			'/',
			['Format','Font','FontSize','TextColor','BGColor','-','RemoveFormat']
        ]
	};
	$('textarea.editor_simple', obj).each( function() {
	    CKEDITOR.replace( $(this).attr('id'),config );
	});
}
function title_to_alias(alias) {
	alias = alias.replace(/Ā|ā/g,'a',alias);
	alias = alias.replace(/Č|č/g,'c',alias);
	alias = alias.replace(/Ē|ē/g,'e',alias);
	alias = alias.replace(/Ģ|ģ/g,'g',alias);
	alias = alias.replace(/Ī|ī/g,'i',alias);
	alias = alias.replace(/Ķ|ķ/g,'k',alias);
	alias = alias.replace(/Ļ|ļ/g,'l',alias);
	alias = alias.replace(/Ņ|ņ/g,'n',alias);
	alias = alias.replace(/Ō|ō/g,'o',alias);
	alias = alias.replace(/Š|š/g,'s',alias);
	alias = alias.replace(/Ū|ū/g,'u',alias);
	alias = alias.replace(/Ž|ž/g,'z',alias);
	
	alias = alias.replace(/а|А/g,'a',alias);
	alias = alias.replace(/б|Б/g,'b',alias);
	alias = alias.replace(/в|В/g,'v',alias);
	alias = alias.replace(/г|Г/g,'g',alias);
	alias = alias.replace(/д|Д/g,'d',alias);
	alias = alias.replace(/е|Е|ё|Ё|э|Э/g,'e',alias);
	alias = alias.replace(/ж|Ж|з|З/g,'z',alias);
	alias = alias.replace(/и|И/g,'i',alias);
	alias = alias.replace(/й|Й/g,'j',alias);
	alias = alias.replace(/к|К/g,'k',alias);
	alias = alias.replace(/л|Л/g,'l',alias);
	alias = alias.replace(/м|М/g,'m',alias);
	alias = alias.replace(/н|Н/g,'n',alias);
	alias = alias.replace(/о|О/g,'o',alias);
	alias = alias.replace(/п|П/g,'p',alias);
	alias = alias.replace(/р|Р/g,'r',alias);
	alias = alias.replace(/с|С|ш|Ш|щ|Щ/g,'s',alias);
	alias = alias.replace(/т|Т/g,'t',alias);
	alias = alias.replace(/у|У/g,'u',alias);
	alias = alias.replace(/ф|Ф/g,'f',alias);
	alias = alias.replace(/х|Х/g,'h',alias);
	alias = alias.replace(/ц|Ц|ч|Ч/g,'c',alias);
	alias = alias.replace(/ъ|Ъ|ь|Ь/g,'',alias);
	alias = alias.replace(/ы|Ы/g,'y',alias);
	alias = alias.replace(/ю|Ю/g,'ju',alias);
	alias = alias.replace(/я|Я/g,'ja',alias);
	 	
	
	alias = alias.replace(/[^a-z0-9-]/ig,'-',alias);
	alias = alias.replace(/[-]+/ig,'-',alias);
	
	return alias.toLowerCase();
}

function init_content_type(obj) {
	var tab = $(obj).closest('div.ui-tabs-panel');
	
	switch ($(obj).val()) {
		case "1":
		default:
			$(tab).find('#content_tr').show();
			$(tab).find('#link_tr').hide();
			break;
		case "2":
			$(tab).find('#content_tr').hide();
			$(tab).find('#link_tr').show();
			break;
		case "3":
			$(tab).find('#content_tr').hide();
			$(tab).find('#link_tr').hide();
			break;
	}
}

function gallery_init(obj) {
	$(obj).find('.vAlign').vAlign();
}
function removeGalleryItem(obj) {
	jConfirm('Are you sure to delete this Image?', 'Are you sure?', function(r) {
		if (r) {
			page_loading('Deleting image...');
					
			// DELETE TMP
			rmFileTmp($(obj).closest('div.gallery-item').find('input[name="page_image_src[]"]'));
			
			// REMOVE ROW
			$(obj).closest('div.gallery-item').remove();
			if ($('#gallery_data_div input[name="page_main_image_chk"]:checked').length == 0) {
				$('#gallery_data_div input[name="page_main_image_chk"]:first').attr('checked',true);
				changeMainImage($('#gallery_data_div input[name="page_main_image_chk"]:first'));
			}	
			
			page_loading('');
		}
	});	
}
function changeMainImage(obj) {
	$('input[name="page_main_image[]"]').val('0');
	$(obj).parent('div.gallery-main-image').find('input[name="page_main_image[]"]').val('1');
}