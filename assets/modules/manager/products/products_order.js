$().ready(function() {
	$('#filter_form select').combobox({
		onChange: function() {
			$('#filter_form').submit();
		}
	});
	
	$('#product_table').tableDnD({
		dragHandle: '.handle',
		onDragClass: 'edit',
		onDrop: function(table, row) {
			page_loading('Saving...');
			
			$.post(base_url+'manager/products_products/order_save',
				$('#product_table').postData(),
			function(data) {
				page_loading(''); 
			});
		}
	});
});