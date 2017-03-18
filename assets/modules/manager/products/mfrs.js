$().ready(function() {		
	$('input[action="mfr_delete"]').click(function() {
		var button = this;
		jConfirm('Are you sure to delete this Seller?', 'Are you sure?', function(r) {
			if (r) {
				page_loading('Deleting...');
				
				$.post(base_url+'manager/products_mfrs/delete', {
					mfr_id:	$(button).closest('tr').attr('data-id')
				}, function(data) {
					if (data.status == '1') {
							$(button).closest('tr').remove();
							page_loading('');
						} else {
							page_loading('');
							jAlert(data.error);
						}
				}, 'json');
			}
		});
	});
	
	$('input.button').button();
});