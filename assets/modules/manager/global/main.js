var base_url = $('html head base').attr('href');

var base_path = base_url;
if (base_path.substring(0,7) == 'http://') base_path = base_path.substring(7); 
if (base_path.substring(0,8) == 'https://') base_path = base_path.substring(8); 
if (base_path.substring(0,window.location.hostname.length) == window.location.hostname) base_path = base_path.substring(window.location.hostname.length);
	
var loading = '<div style="color: #555555; margin-left: 3px;"><img src="'+base_url+'assets/modules/manager/global/img/ajax-loader.gif" style="float: left;">&nbsp;Loading...</div>';
var loading_edit = '<div style="color: #555555; margin-left: 3px;"><img src="'+base_url+'assets/modules/manager/global/img/ajax-loader-edit.gif" style="float: left;">&nbsp;Loading...</div>';

$(document).ready(function() {
	$.ajaxSetup( { type: "post" } );
	
	// TREE TABS
	$('button, a.button').button();
	
	// INIT
	init_data($('body'));
	
	// SHOW BODY
	$('body').show();
	
	// CLEAR CACHE
	$('#menu a[href="manager/pages/clear_cache"]').click(function(e) {
		e.preventDefault();
		page_loading('Clear cache...');
		
		$.post($(this).attr('href'),
		function() {
			page_loading('');
		});
	});
});

// 
// TREE ITEM INIT
//
function init_data(data) {		
	$(data).find('.resource span.action-resource-toggle').click(function() {
		if ($(this).is('.ui-icon-plus')) {
			// OPEN
			var obj = $(this);
			$(obj).addClass('ui-icon-refresh');
			$(obj).removeClass('ui-icon-plus');
			
			$.post(base_url+'manager/pages/load_sub_tree', {
				parent_id:			id(obj)
			}, function(data) {
				data = $(obj).closest('div.resource').find('div.sub_resource').html(data);
				
				$(obj).removeClass('ui-icon-refresh');
				$(obj).addClass('ui-icon-minus');
				
				// INIT
				init_data(data);
				
				// SET TOGGLES
				setToggles();
			});
		} else {
			// CLOSE
			$(this).addClass('ui-icon-plus');
			$(this).removeClass('ui-icon-minus');
			
			$(this).closest('div.resource').find('div.sub_resource').html('');
			
			// SET TOGGLES
			setToggles();
		}
	});
	
	$(data).find('.resource a').draggable({ 
		revert: true,
		delay: 200
	});
	
	$.contextMenu({
		selector:  '.resource div.link', 
        callback: function(action, options) {
        	if (action == 'edit') window.location = base_url+"manager/pages/load/"+id(options.$trigger);
            if (action == 'copy') window.location = base_url+"manager/pages/load/new-copy/"+id(options.$trigger);
    		if (action == 'delete') {
    			jConfirm('Are you sure to delete this Resource?', 'Are you sure?', function(r) {
    				if (r) {
    					$.post(base_url+'manager/pages/delete', {
    						page_id:			id(options.$trigger)
    					}, function(data) {
    						window.location = base_url+"manager/home/load";
    					});
    				}
    			});
    		}
    		if (action == 'add') window.location = base_url+"manager/pages/load/new/"+id(options.$trigger);
        },
        items: {
        	 "edit": { name: menu_edit, icon: "edit" }, 
        	 "add": { name: menu_add, icon: "add" },
        	 "copy": { name: menu_copy, icon: "copy" },
        	 "sep1": "---------",
        	 "delete": { name: menu_delete, icon: "delete" }
        }
	});
	
    // RESOURCE LANG TABS
    $('#resource_lang_tabs').each(function(i,o) {
    	$(o).prev('div#resource_tabs').css({
    		'border-bottom': '0px none',	
			'-moz-border-radius': '3px 3px 0px 0px',
			'-webkit-border-radius': '3px 3px 0px 0px',
			'border-radius': '3px 3px 0px 0px'
    	});
    });
    
    // SHOW / HIDE TREE DATA
    $('#data #tree_panel .toggle-menu').click(function() {
    	hidden = '';
    	if ($(this).closest('td#tree_panel').hasClass('hidden')) {
    		// SHOW
    		$(this).closest('td#tree_panel').removeClass('hidden');
    		$(this).html('«');    		
    	} else {
    		// HIDE
    		$(this).closest('td#tree_panel').addClass('hidden');
    		$(this).html('»');
    		hidden = 'hidden';
    	}
    	
    	$.post(base_url+'manager/pages/set_tree_hidden', {
			hidden:	hidden
		});
    });
}

//
// LOADING
//
function loading_tr(tr) {
	if ($(tr).hasClass('edit')) $(tr).find('td:last').html(loading_edit);
	else $(tr).find('td:last').html(loading);
}

//
// SET OPENED TREES
//
function setToggles() {
	var list = '';
	$('#tree_panel .resource .ui-icon-minus').each(function(index, obj) {
		if (list != '') list += ',';
		list += id(obj);
	});
	
	$.post(base_url+'manager/pages/set_toggles', {
		opened_id:			list
	});
}

function openFileRemove(obj) {
	if ($(obj).val() != undefined) {
		page_loading('Deleting image...');
		
		rmFileTmp($(obj));

		$(obj).parent().find('img').hide();
		$(obj).parent().find('img').attr('src','');
		$(obj).val('');
		
		page_loading('');
	}
}
function openManualFileRemove(obj) {
	if ($(obj).val() != undefined) {
		page_loading('Deleting file...');
		
		rmFileTmp($(obj));

		$(obj).closest('td').find('.file-name').val('');
		$(obj).val('');
		
		page_loading('');
	}
}
function rmFileTmp(obj) {
	// DELETE TMP
	$.ajax({
		url: base_url+'manager/files/rm_tmp',
		async: false,
		type: 'POST',
		data:	{
			'file_name':	$(obj).val().replace(/\/thumb_([^\/]*)$/i,'/$1')
		}
	});
}

function submit(obj) {
	page_loading('Saving...');
	
	$(obj).closest('form').find('.ui-autocomplete-input.data_id').each(function() {
		var input = $('<input type="hidden" name="'+$(this).attr('name')+'" value="'+$(this).attr('data_id')+'"/>');
		$(this).removeAttr('name');
		$(input).insertAfter($(this));
	});
	
	$(obj).closest('form').submit();
}

//
// AJAX LOADING
//
function page_loading(text) {
	if (text == '') {
		$('#ajax-loading').remove();
	} else {
		if ($('#ajax-loading').length == 0) {
			$(document).ready(function() {
				$('body').append($('<div class="ajax-loading-bg" id="ajax-loading"><div class="ajax-loading-content">'+text+'</div></div>'));
			});
		}		
	}
}
function showActivity(text, obj) {
	if ($(obj).length == 0) page_loading(text);
	else $(obj).html('<div class="ajax-loading-content-object">'+text+'</div>');
}
function hideActivity() {
	page_loading('');
}

// ALERT
function msg(data) {
	var dlg = $('<div><b>'+data+'</b></div>').dialog({
		title: '',
		modal: true,
		close: $(dlg).dialog('destroy'),
		buttons: {
			'OK': function() {
				$(dlg).dialog('close');
			}
		}
	});	
}

// GET ID
function id(obj) {
	return $(obj).find('input[name="id"]:first').val();
}

// DATA TABLE ORDER BY
function order_by(type, obj) {
	order_by = type+'-';
	if ($(obj).hasClass('asc')) order_by = order_by + 'd';
	else order_by = order_by + 'a';	
	
	$(obj).closest('table').find('tfoot form input[name="order_by"]').val(order_by);
	$(obj).closest('table').find('tfoot form').submit();
}

//
//PLUGINS
//
(function ($) {
	/*
	 * SHOW / HIDE ACTIVITY
	 */
	$.fn.showActivity = function() {
		return this.each(function(i){
			if ($(this).is('tr')) {
				if ($('div.ajax-loading-tr', $(this).closest('tr')).length == 0) {
					$(this).closest('tr').height($(this).closest('tr').height());
					$('td:last *',$(this).closest('tr')).hide();
					if ($('td:last',$(this).closest('tr')).width() < 80) $('td:last',$(this).closest('tr')).append('<div class="ajax-loading-tr"></div>');
					else $('td:last',$(this).closest('tr')).append('<div class="ajax-loading-tr">Loading...</div>');
				}
			} else if ($(this).is('td') || $(this).is('th')) {
				if ($('div.ajax-loading-tr', $(this)).length == 0) {
					$(this).closest('tr').height($(this).closest('tr').height());
					$(this).children('*').hide();
					if ($(this).width() < 80) $(this).append('<div class="ajax-loading-tr"></div>');
					else $(this).append('<div class="ajax-loading-tr">Loading...</div>');
				}
			} else if ($(this).is('.ui-dialog-content') && $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane').length > 0) {
				if ($('div.ajax-loading-tr', $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane')).length == 0) {
					$(this).closest('.ui-dialog').find('.ui-dialog-buttonpane .ui-dialog-buttonset').children('*').hide();
					if ($(this).closest('.ui-dialog').find('.ui-dialog-buttonpane .ui-dialog-buttonset').width() < 80) $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane .ui-dialog-buttonset').append('<div class="ajax-loading-tr"></div>');
					else $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane .ui-dialog-buttonset').append('<div class="ajax-loading-tr">Loading...</div>');
					$('div.ajax-loading-tr', $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane .ui-dialog-buttonset')).height($(this).closest('.ui-dialog').find('.ui-dialog-buttonpane .ui-dialog-buttonset button').outerHeight(true, true));
					$('div.ajax-loading-tr', $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane .ui-dialog-buttonset')).css({
						'background-position': 'left center',
						'line-height': $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane .ui-dialog-buttonset button').outerHeight(true, true)+'px' });
					$(this).dialog('option', 'position', 'center');
				}
			} else if ($(this).is(':input')) {
				$(this).hide();
				if ($(this).width() < 80) $('<div class="ajax-loading"></div>').insertAfter(this);
				else $('<div class="ajax-loading">Loading...</div>').insertAfter(this);
			} else if ($(this).is('tbody')) {
				var cols = '100%';
				if ($(this).closest('table').find('thead tr:first th').length > 0) cols = $(this).closest('table').find('thead tr:first th').length;
				$(this).html('<tr><td colspan="'+cols+'"><div class="ajax-loading">Loading...</div></td></tr>');
				
			} else {
				if ($('div.ajax-loading', this).length == 0) {
					$(this).children('*').hide();
					if ($(this).width() < 80 && $(this).width() > 0) $(this).append('<div class="ajax-loading"></div>');
					else $(this).append('<div class="ajax-loading">Loading...</div>');
				}
			}
		});
	};
	$.fn.hideActivity = function() {
		return this.each(function(i){
			if ($(this).is('tr')) {
				if ($('td',$(this).closest('tr')).length == 1 && $(this).closest('tr').parent('tbody').length == 1) {
					// REMOVE NEW ROW
					$(this).closest('tr').remove();
				} else {
					// SHOW BUTTONS
					$(this).closest('tr').height('');
					$('td:last div.ajax-loading-tr', $(this).closest('tr')).remove();
					$('td:last *',$(this).closest('tr')).not('.files_list').show();
				}
			} else if ($(this).is('td') || $(this).is('th')) {
				$(this).closest('tr').height('');
				$('div.ajax-loading-tr', $(this)).remove();
				$(this).children('*').not('.files_list').show();
			} else if ($(this).is('.ui-dialog-content') && $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane').length > 0) {
				$('div.ajax-loading-tr', $(this).closest('.ui-dialog').find('.ui-dialog-buttonpane .ui-dialog-buttonset')).remove();
				$(this).closest('.ui-dialog').find('.ui-dialog-buttonpane .ui-dialog-buttonset').children('*').show();
				$(this).dialog('option', 'position', 'center');
			} else if ($(this).is(':input')) {
				$(this).show();
				$(this).next('div.ajax-loading').remove();
			} else {
				$('div.ajax-loading', this).remove();
				$(this).children('*').not('.files_list').show();
			}
		});
	};
	
	/*
	 * TABLE
	 */
	$.fn.table = function(params) {
		$('tbody:first > tr', this).removeClass('tr_e');
		$('tbody:first > tr:nth-child(2n)', this).addClass('tr_e');
	};
	
	/*
	 * ENTER
	 */
	$.fn.onEnter = function(params) {
		params = params?params:{};
		var action = params.action?params.action:false;
		var button = params.button?params.button:false;
		
		return this.each(function(i, o) {
			$(o).keypress(function(evt) {
				var keyCode = evt ? (evt.which ? evt.which : evt.keyCode) : event.keyCode;
				if (keyCode == 13) { 
					if (action) eval(action);
					else if (button) $(button).click();
					else $(o).nextAll('input[type="button"]:first').click();
				}
			});		      
		});
	};
	
	/*
	 * GET POST DATA
	 */
	$.fn.postData = function() {
		var post_data = {};
		
		$(':input', this).each(function(i,o) {
			if (typeof($(o).attr('name')) != 'undefined') {
				if ($(o).attr('name').substring($(o).attr('name').length-2,$(o).attr('name').length) != '[]') {
					if ($(o).is('textarea') && typeof(CKEDITOR) !== "undefined") {
						if (CKEDITOR.instances[$(o).attr('id')]) post_data[$(o).attr('name')] = CKEDITOR.instances[$(o).attr('id')].getData();
						else post_data[$(o).attr('name')] = $(o).val();
					} else {
						if (!$(o).is(':checkbox') || $(o).is(':checked')) post_data[$(o).attr('name')] = $(o).val();
					}
				} else {
					// ARRAY
					if (typeof(post_data[$(o).attr('name').substring(0,$(o).attr('name').length-2)]) == 'undefined') {
						post_data[$(o).attr('name').substring(0,$(o).attr('name').length-2)] = [];
						var i = 0;
					} else {
						var i = post_data[$(o).attr('name').substring(0,$(o).attr('name').length-2)].length;
					}				
					if (!$(o).is(':checkbox') || $(o).is(':checked')) post_data[$(o).attr('name').substring(0,$(o).attr('name').length-2)][i] = $(o).val();
				}
			}
		});
		
		return post_data;
	};
	
	/*
	 * VERTICALLY ALIGN
	 */
	$.fn.vAlign = function() {
		return this.each(function(i){
			if ($(this).is('img')) {		
				$('<span></span>').css({
						'display': 'inline-block',
						'height': '100%',
						'width': '1px',
						'vertical-align': 'middle' })
					.insertBefore($(this));
				$(this).css({
					'vertical-align': 'middle',
					'display': 'inline',
					'margin-left': '-1px'
				});
			} else {
				var ah = $(this).height();
				if ($(this).css('margin-top')) ah = ah - parseInt($(this).css('margin-top').replace("px", ""));
				var ph = $(this).parent().height();
				var mh = Math.ceil((ph-ah) / 2);
				$(this).css('margin-top', mh);
			}
		});
	};	
	
})(jQuery);

$.fn.__tabs = $.fn.tabs;
$.fn.tabs = function (a, b, c, d, e, f) {
    var base = location.href.replace(/#.*$/, '');
    $('ul>li>a[href^="#"]', this).each(function () {
        var href = $(this).attr('href');
        $(this).attr('href', base + href);
    });
    $(this).__tabs(a, b, c, d, e, f);
};