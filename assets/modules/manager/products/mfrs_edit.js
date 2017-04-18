var change_alias = false;
var change_menu_title = false;

$().ready(function() {	
	$("#resource_tabs, #resource_lang_tabs").tabs();
	
	$('#resource_tabs select').combobox();
	
	$('#mfr_delete').click(function() {
		if ($('#mfr_id').val() != 'new') {
			jConfirm('Are you sure to delete this Seller?', 'Are you sure?', function(r) {
				if (r) {
					page_loading('Deleting...');
					
					$.post(base_url+'manager/products_mfrs/delete', {
						mfr_id:	$('#mfr_id').val()
					}, function(data) {
						if (data.status == '1') window.location = $('input[name="return_url"]').val();
						else {
							page_loading('');
							alert(data.error);
						}
					}, 'json');
				}
			});
		}
	});
	
	$('a[action=create_lang]').click(function() {
		page_loading('Loading...');
		var panel = $('#data_panel');
		var tab = $(this).closest('div.ui-tabs-panel');
		
		$.post(base_url+'manager/products_mfrs/load_lang_tab', {
			lang_id:			$(tab).find('#language_id').val(),
			mfr_id:				$(panel).find('#general #mfr_id').val()
		}, function(data) {
			var obj = $(tab).html(data);
			
			content_init(obj);
			page_loading('');
		});
	});	
	
	$('.file_upload_input').uploadify({
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

	content_init($('body #data_panel'));
});


function content_init(obj) {
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
	
	// BUTTONS
	$('a.button', obj).button();
}