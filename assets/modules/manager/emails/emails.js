$().ready(function() {
	$('input[type="button"]').button();
});

function send_emails(obj) {
	showActivity('Sending e-mails...');
	
	$.post(base_url+'manager/emails/send_emails',
	function(data) {
		window.location.reload();
	});
}

function cancel_emails(obj, id) {
	jConfirm('Are you sure to cancel this email?', 'Cancel?', function(r) {
		if (r == true) {
			showActivity('Cancelling e-mail...');
	
			$.post(base_url+'manager/emails/cancel_email/'+id,
			function(data) {
				window.location.reload();
			});
		}
	});	
}