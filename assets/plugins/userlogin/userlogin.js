function errorOAuth(error) {
	$('#login_form #login_error').html(error);
}
function closeProfile() {
	$('#login_button').removeClass('active');
	$('#overlay').animate({ 'opacity': '0' }, function() { $('#overlay').hide(); });
	$('#profile_content')
		.mCustomScrollbar('destroy')
		.html('');
	$('#profile').animate({ 'right': '-100%' });
	if (parseInt($('#cart_qty').text()) > 0) $('#cart_content_button').fadeIn();
}
function updateHello(hello_text) {
	$('#login_hello').html(hello_text);
}

function forgot_password() {
	$('#profile_content')
		.mCustomScrollbar('destroy')
		.html('')
		.css('height', '100%')
		.showActivity();	
	
	$.post(base_url+'plugins/userlogin/forgot_password_from',
	function(data) {
		$('#profile_content')
			.hideActivity()			
			.html(data.response)
			.mCustomScrollbar({ scrollbarPosition: 'outside', mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) } })
			.hide()
			.fadeIn();
	}, 'json');
}

function logout(obj) {
	$(obj).showActivity();	
	
	$.post(base_url+'plugins/userlogin/logout',
	function(data) {
		$('#cart_qty').text('0');
		$('#login_hello').text('');
		if (parseInt($('#cart_qty').text()) == 1) {
			$('#cart_qty').parent().find('.one').show();
			$('#cart_qty').parent().find('.more').hide();
		} else {
			$('#cart_qty').parent().find('.one').hide();
			$('#cart_qty').parent().find('.more').show();
		}
		closeProfile();
	}, 'json');
}

function activate(code) {	
	$.post(base_url+'plugins/userlogin/activate', {
		code: code
	}, function(data) {
		if (data.status == 1) {
			// SHOW		
			$('#login_button').addClass('active');
			$('#overlay').show().animate({ 'opacity': '0.75' });
			$('#cart').animate({ 'right': '-100%' });
			$('#cart_button').removeClass('active');
			
			$('#profile_content')
				.html(data.response)
				.mCustomScrollbar({ scrollbarPosition: 'outside', mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) } });
			$('#profile').animate({ 'right': '0px' });
		}
	}, 'json');
}