function coffee_filter(obj, id) {
	if ($('#coffee_filter_form input[name="coffee_id[]"][value="'+id+'"]').length > 0) {
		$('#coffee_filter_form input[name="coffee_id[]"][value="'+id+'"]').remove();
	} else {
		$('#coffee_filter_form').append('<input type="hidden" name="coffee_id[]" value="'+id+'"/>');
	}
	
	$('#coffee_filter_form').submit();
}
function coffee_filter_toggle(obj) {
	if ($('#coffee_filter_form input[name="filter_opened"]').val() == 0) {	
		// OPEN
		$('#coffee_filter_form input[name="filter_opened"]').val('1');
		$('#coffee_filter_form').animate({
			'height': '100%'
		});
		$('img', obj).hide();
		$('img.up', obj).show();
	} else {
		// CLOSE
		$('#coffee_filter_form input[name="filter_opened"]').val('0');
		$('#coffee_filter_form').animate({
			'height': '110px'
		});
		$('img', obj).hide();
		$('img.down', obj).show();
	} 
}

function filter_reset() {
	$('#coffee_filter_form input[name="coffee_id[]"]').remove();
	$('#coffee_filter_form').submit();
}