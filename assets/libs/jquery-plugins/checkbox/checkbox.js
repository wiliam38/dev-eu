/*
 * CHECKBOX 
 * 
 * PARAMS:
 *   class_name
 *   onChange
 */

jQuery.fn.checkbox = function(init) {	
	var conf = new Array();
	conf.class_name = typeof(init.class_name)!='undefined'?init.class_name:'input-checkbox'; 
	conf.onChange = typeof(init.onChange)!='undefined'?init.onChange:false;
	
	$(this).each(function() {
		var o = $(this);
	
		// CREATE NEW CHECKBOX
		var new_chk = $('<a href="#checkbox"></a>')
			.insertAfter($(o))
			.addClass(conf.class_name)
			.data('checkbox', $(o));
		if ($(o).is(':checked')) $(new_chk).addClass('active');		
		if (typeof($(o).attr('style')) != 'undefined') $(new_chk).attr('style', $(o).attr('style'));
		$(o).data('checkbox', $(new_chk));
		$(o).hide();
				
		$(new_chk).click(function(e) {
			e.preventDefault();
			
			if ($(this).data('checkbox').is(':radio')) {	
				$('input[type="radio"][name="'+$(this).data('checkbox').attr('name')+'"]').each(function(i,obj) {
					$(obj).prop('checked', false);
					$(obj).data('checkbox').removeClass('active');
				});
				
				$(this).addClass('active');
				$(this).removeClass('error');
				$(this).data('checkbox').prop('checked', true);
			} else {
				if ($(this).data('checkbox').is(':checked')) {
					$(this).removeClass('active');
					$(this).removeClass('error');
					$(this).data('checkbox').prop('checked', false);
				} else {
					$(this).addClass('active');
					$(this).removeClass('error');
					$(this).data('checkbox').prop('checked', true);
				}
			}
				
			

			if (conf.onChange) {
				var on_change_function = conf.onChange;
				on_change_function($(this).data('checkbox'));
			}
			if (!conf.onChange && $(this).attr('onchange')) {
				eval($(this).attr('onchange').replace('this', $(this).data('checkbox')));
			}
		});		
	});
}