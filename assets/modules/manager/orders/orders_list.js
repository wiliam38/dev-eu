$().ready(function() {
	$('.button').button();
	
	$('#filter_status_id').multiSelect();
});

function toggleOrderDetails(obj) {
	var id = $(obj).closest('tr').attr('data-id');
	$(obj).closest('tr').nextAll('tr[data-level="'+id+'"]').remove();
	
	if ($(obj).hasClass('ui-icon-plus')) {
		// SHOW
		$(obj).removeClass('ui-icon-plus').addClass('ui-icon-minus');
		var new_tr = $('<tr data-level="'+id+'"><td style="background-color: #D5D5D5;"><td style="padding: 0px 0px 10px 0px; background-color: #D5D5D5;" colspan="8"></td></tr>').insertAfter($(obj).closest('tr'));
		$(new_tr).showActivity();
		
		$.post(base_url+'manager/orders_orders/details', {
			order_id: id
		}, function(data) {
			var new_data = $('td:last', new_tr).html(data);
			$('button', new_data).button();
		});
	} else {
		// HIDE
		$(obj).addClass('ui-icon-plus').removeClass('ui-icon-minus');		
	}
}

function setOrderStatus(order_id,status_id,obj) {	
	jConfirm('Vai pasūtījumu pārlikt uz statusu "'+$(obj).text()+'"?', 'Vai esat pārliecināts?', function(r) {
		if (r) {
			$(obj).closest('tr').showActivity();
			
			$.post(base_url+'manager/orders_orders/status', {
				order_id: order_id,
				status_id: status_id
			}, function(data) {
				if (data.status == 1) {
					tr_class = $(obj).closest('tr').find('.show-details').attr('class');
					var new_tr = $(data.response).replaceAll($(obj).closest('tr'));
					$(new_tr).find('.show-details').attr('class', tr_class);
					$('.button', new_tr).button();
					
					// VIEW DETAILS
					det_tr = $('#data_panel table.data_table tr[data-level="'+order_id+'"]');
					var new_data = $(det_tr).children('td:last').html(data.response_detail);
					$('button', new_data).button();
				} else {
					$(obj).closest('tr').hideActivity();
				}
			}, 'json');
		}
	});
}

function setOrderPayStatus(order_id,pay_status_id,obj) {	
	var dlg = $('<div></div>').dialog({
		title: 'Vai esat pārliecināts?',
		modal: true,
		width: '400px',
		resizable: false,
		close: function() { $(dlg).remove(); },
		buttons: {
			'Saglabāt': function() {	
				$(dlg).showActivity();
				
				$.post(base_url+'manager/orders_orders/pay_status', {
					order_id: order_id,
					pay_status_id: pay_status_id,
					pay_date: $('input[name="pay_date"]', dlg).val()
				}, function(data) {
					if (data.status == 1) {
						$(dlg).dialog('close');
						
						tr_class = $(obj).closest('tr').find('.show-details').attr('class');
						var new_tr = $(data.response).replaceAll($(obj).closest('tr'));
						$(new_tr).find('.show-details').attr('class', tr_class);
						$('.button', new_tr).button();
					} else {
						$(dlg).hideActivity();
					}
				}, 'json');
			},
			'Atcelt': function() {
				$(dlg).dialog('close');
			}
		}
	}).showActivity();
	
	if (pay_status_id == '10') {
		$(dlg).html('<b>Vai vēlaties mainīt apmaksas statusu uz "Apmaksāts"?</b>');
		$(dlg).append('<br><br>&nbsp;&nbsp;&nbsp;<b>Datums:</b> <input type="text" name="pay_date" value="" style="vertical-align: middle; width: 120px;"/>');
		$(dlg).css({ 'overflow': 'visible' });
		$('input[name="pay_date"]', dlg)
			.val($.datepicker.formatDate('dd-M-yy', new Date()))
			.issCalendar();
	} else {
		$(dlg).html('<br><b>Vai vēlaties mainīt apmaksas statusu uz "Nav apmaksāts"?</b>');		
	}
	
	$(dlg).hideActivity();
}

function exportXmlPopup(obj) {
	var title = $(obj).text();
	
	var dlg = $('<div></div>').dialog({
		title: title,
		modal: true,
		resizable: false,
		close: function() { $(dlg).remove(); },
		buttons: {
			'Export': function() {	
				$('#export_xml_form').submit();
				
				$(dlg).dialog('close');
			},
			'Cancel': function() {
				$(dlg).dialog('close');
			}
		}
	}).showActivity();	
	
	$.post(base_url+'manager/orders_orders/excel_popup', function(data) {
		if (data.status == 1) {
			$(dlg).html(data.response);
			$(dlg).hideActivity();
		} else {
			$(dlg).dialog('close');
			jError(data.error, 'Error!');
		}
	}, 'json');
}

function coffee_gift(obj) {
	var dlg = $('<div id="coffee_gift_dlg"></div>').dialog({
		title: 'Rēķins Nr. '+$(obj).closest('tr').find('td:eq(2)').text(),
		modal: true,
		close: function() { $(dlg).remove(); },
		width: 350,
		resizable: false,
		buttons: {
			'Aizvērt': function() {
				$(dlg).dialog('close');
			}
		}
	}).showActivity();	
	
	$.post(base_url+'manager/orders_orders/coffee_gift_popup', {
		order_id: $(obj).closest('tr[data-level]').prev('tr[data-id]').attr('data-id')
	}, function(data) {
		if (data.status == 1) {
			$(dlg).html(data.response);
			$(dlg).hideActivity();
		} else {
			$(dlg).dialog('close');
			jError(data.error, 'Error!');
		}
	}, 'json');
}
function coffee_gift_status(order_id, status_id, status_name) {
	jConfirm('Vai vēlaties mainīt statusu uz "'+status_name+'"?', 'Vai esat pārliecināts?', function(r) {
		if (r) {
			$('#coffee_gift_dlg').dialog('close');
			
			$.post(base_url+'manager/orders_orders/coffee_gift_status', {
				order_id: order_id,
				status_id: status_id
			}, function(data) {
				if (data.status == 1) {
					// VIEW DETAILS
					det_tr = $('#data_panel table.data_table tr[data-level="'+order_id+'"]');
					var new_data = $(det_tr).children('td:last').html(data.response_detail);
					$('button', new_data).button();
				} else {
					jError(data.error, 'Error!');
				}
			}, 'json');
		}
	});
}

function recreateInvoices(obj, order_id) {
	showActivity('Loading...');
	
	$.post(base_url+'manager/orders_orders/recreate_invoices', {
		order_id: order_id
	}, function(data) {
		if (data.status == 1) {
			
		} else {
			jError(data.error, 'Error!');
		}
		showActivity('');
	}, 'json');
}

function issueInvoice(obj, order_id, type) {
	question = 'Vai vēlaties izsniegt visu pasūtījumu?';
	if (type == 2) question = 'Vai vēlaties izsniegt atzīmētos produktus?';
	
	jConfirm(question, 'Vai esat pārliecināts?', function(r) {
		if (r) {			
			showActivity('Loading...');
			
			// POST DATA
			post_data = $('#data_panel table.data_table tr[data-level="'+order_id+'"]').postData();
			post_data.order_id = order_id;
			post_data.type = type;
			
			$.post(base_url+'manager/orders_orders/issue_invoice',
				post_data,
			function(data) {
				if (data.status == 1) {
					// VIEW ROW
					tr = $('#data_panel table.data_table tr[data-id="'+order_id+'"]');
					tr_class = $(tr).find('.show-details').attr('class');
					var new_tr = $(data.response).replaceAll(tr);
					$(new_tr).find('.show-details').attr('class', tr_class);
					$('.button', new_tr).button();
					
					// VIEW DETAILS
					det_tr = $('#data_panel table.data_table tr[data-level="'+order_id+'"]');
					var new_data = $(det_tr).children('td:last').html(data.response_detail);
					$('button', new_data).button();
				} else {
					jError(data.error, 'Error!');
				}
				showActivity('');
			}, 'json');
		}
	});
}

function viewInvoice(obj, order_id) {
	$(obj).closest('tr').showActivity();
	
	$.post(base_url+'manager/orders_orders/popup_invoices', {
		order_id: order_id
	}, function(data) {
		if (data.status == 1) {
			window.open(data.response);
		} else if (data.status == 2) {
			var dlg = $('<div></div>')
				.dialog({
					modal: true,
					title: 'Pavadzīmes',
					resizable: false,
					buttons: {
						'OK': function() {
							$(dlg).dialog('close');
						}
					},
					close: function() {
						$(dlg).remove();
					}
				})
				.html(data.response);
		} else {
			jError(data.error, 'Error!');
		}
		
		$(obj).closest('tr').hideActivity();
	}, 'json');
}