jQuery.fn.numeric = function(decimal)
{
	// PARAMS
	decimal = typeof(decimal)!='undefined'?decimal:'.';
	
	$(this).bind('keypress', function(e) {
		if (e.which >= 48 && e.which <= 57 || e.which == 44 || e.which == 46 || e.which == 45) {
			if ((e.which == 44 || e.which == 46) && $(this).val().match(new RegExp(/[.,]+/))) e.preventDefault();
			if (e.which == 45 &&  $(this).val() != '') e.preventDefault();
		} else {
			e.preventDefault();
		}
	});
	
	$(this).bind('keyup', function(e) {
		var tmp_val = $(this).val();
		var val = ''; 
		var seperator = false;

		tmp_val = tmp_val.replace(/[,]/gi,decimal);
		tmp_val = tmp_val.replace(/[.]/gi,decimal);
		
		for(var i=0; i<tmp_val.length; i++) {
			var key = tmp_val[i].charCodeAt(0);
			
			if (key >= 48 && key <= 57) val += tmp_val[i];
			else if (tmp_val[i] == '-' && i == 0) val += tmp_val[i];
			else if (tmp_val[i] == decimal && !seperator) {
				seperator = true;
				val += tmp_val[i];
			}
		} 	
				
		if ($(this).val() != val) $(this).val(val);		
	});
}