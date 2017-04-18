function delete_category(obj) {
	jConfirm(category_delete_msg, category_delete_title, function(r) {
		if (r) {
			page_loading('Deleting...');
			$.post(base_url+'manager/'+category_link+'/delete', {
				category_id:	$(obj).closest('tr').attr('data-id')
			}, function(data) {
				if (data.status == '1') {
					$(obj).closest('tr').remove();
					page_loading('');
				} else {
					page_loading('');
					jError(data.error, 'Error!');
				}
			}, 'json');
		}
	});
}