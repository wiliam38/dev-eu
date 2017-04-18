{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action eq "login"}	
	<div id="login_login">
		<h2>{__('user_login.login_title')}:</h2>
		<form id="login_form" method="post" action="plugins/userlogin/login_login">
		    {__('user_login.login_intro')}	    
		    <table class="login-table">		    	
		    	<tr>
		    		<th>{__('user_login.email')}:</th>
		    		<td><input type="text" name="email" value="" class="wb-input" tabindex="1"></td>
		    		<td rowspan="2" class="login-social">
		    			<a href="#facebook" class="login-facebook" onclick="return false;"></a>
		  				<a href="#draugiem" class="login-draugiem" onclick="return false;"></a>
		    		</td>
		    	</tr>
		    	<tr>
		    		<th>{__('user_login.password')}:</th>
		    		<td><input type="password" name="password" value="" class="wb-input" tabindex="2"></td>
		    	</tr>
		    	<tr style="height: 0px;">
		    		<th></th>
		    		<td id="login_error" coslpan="2"></td>
		    	</tr>	    	
		    	<tr>
		    		<th></th>
		    		<td>
		    			<input type="submit" class="button-green" value="{__('user_login.button_login')}" style="width: 237px;"/><br/>
		    			<a href="#forgot_password" style="font-size: 12px;" onclick="forgot_password(); return false;">{__('user_login.lost_password')}</a>
		    		</td>
		    		<td></td>
		    	</tr>
		    </table>
		</form>
			
		<h2 style="margin-top: 20px;">{__('user_registration.register_title')}:</h2>
		<form id="register_form" method="post" action="plugins/userlogin/register_registration">
			{__('user_registration.register_intro')}
			<table class="login-table">
				<tr>
		    		<th>{__('user_registration.first_name')}: <span class="must-fill">*</span></th>
		    		<td><input type="text" name="first_name" value="" class="wb-input"></td>
		    		<td></td>
		    	</tr>
		    	<tr>
		    		<th>{__('user_registration.last_name')}: <span class="must-fill">*</span></th>
		    		<td><input type="text" name="last_name" value="" class="wb-input"></td>
		    		<td></td>
		    	</tr>				
				<tr>
		    		<th>{__('user_registration.company')}: <span class="must-fill pro-chk" style="display: none;">*</span></th>
		    		<td><input type="text" name="company" value="" class="wb-input"></td>
		    		<td></td>
		    	</tr>
				<tr>
		    		<th>{__('user_registration.reg_nr')}: <span class="must-fill pro-chk" style="display: none;">*</span></th>
		    		<td><input type="text" name="reg_nr" value="" class="wb-input"></td>
		    		<td></td>
		    	</tr>
				<tr>
		    		<th>{__('user_registration.vat_nr')}:</th>
		    		<td><input type="text" name="vat_nr" value="" class="wb-input"></td>
		    		<td></td>
		    	</tr>
				<tr>
		    		<th>{__('user_registration.email')}: <span class="must-fill">*</span></th>
		    		<td><input type="text" name="email" value="" class="wb-input"></td>
		    		<td></td>
		    	</tr>
		    	<tr>
					<th>{__('user_registration.phone')}: <span class="must-fill">*</span></th>
					<td><input type="text" name="phone" class="wb-input"/></td>
					<td></td>
				</tr>
				<tr>
		    		<th>{__('user_registration.password')}: <span class="must-fill">*</span></th>
		    		<td><input type="password" name="password" value="" class="wb-input"></td>
		    		<td></td>
		    	</tr>
		    	<tr>
		    		<th>{__('user_registration.password2')}: <span class="must-fill">*</span></th>
		    		<td><input type="password" name="password2" value="" class="wb-input"></td>
		    		<td></td>
		    	</tr>		    	
		    	<tr>
		    		<th></th>
		    		<td>
						<input type="checkbox" name="pro_category_request" value="1" style="float: left; margin-right: 10px; margin-top: 2px;"/>
						<div style="float: left; width: 270px; font-size: 12px;">{__('user_registration.pro_category_request')}</div>
		    		<td>
		    		<td></td>
		    	</tr>	

	    	
		    	<tr>
		    		<td></td>
		    		<td style="padding: 5px 0px 5px 0px;">
		    			<input type="checkbox" name="rights" style="float: left; margin-right: 10px; margin-top: 2px;"/> 
						<div style="float: left; width: 270px; font-size: 12px;">{__('user_registration.rights')}<br/><a href="{$base_url}{page id=6 name=full_alias}" target="_blank">{__('user_registration.rights_link')}</a></div>
		    		</td>
		    	</tr>
		    	
		    	<tr style="height: 0px;">
		    		<th></th>
		    		<td id="register_error"></td>
		    		<td></td>
		    	</tr>
		    	
		    	<tr>
		    		<th></th>
		    		<td>
		    			<input type="submit" class="button-green" value="{__('user_registration.button_register')}" style="width: 237px;"/><br/>
		    			{* <input type="button" class="button-gray" onclick="menu_login($('#login_button'));" value="{__('user_registration.button_close')}" style="width: 237px; margin-top: 15px;"/> *}
		    		</td>
		    		<td></td>
		    	</tr>
		    </table>
		</form>
	</div>
	
	<script type="text/javascript">
		$().ready(function() {
			$('a.login-draugiem').click(function(e) {
				e.preventDefault();
				popup(base_url+'plugins/userlogin/draugiem_login',400,550);
			});
			$('a.login-facebook').click(function(e) {
				e.preventDefault();
				popup(base_url+'plugins/userlogin/facebook_login',990,450);
			});
			
			$('#login_form').ajaxForm({
				dataType: 'json',
				beforeSubmit: function() {
					$('#login_form #login_error').html('');
					showActivity($('#login_form input[type="submit"]'));
				},
				success: function(data) {
					if (data.status == '1') {
						$('#login_hello').text(data.hello_text);
						closeProfile();
					} else {
						hideActivity($('#login_form input[type="submit"]'));
						$('#login_form #login_error').html(data.error);
					}
				}
			});
			
			$('#register_form').ajaxForm({
				dataType: 'json',
				beforeSubmit: function() {
					$('#register_form #register_error').html('');
					showActivity($('#register_form input[type="submit"]'));
				},
				success: function(data) {
					if (data.status == '2') window.location.reload();
					else if (data.status == '1') $('#register_form').html(data.response);
					else {
						hideActivity($('#register_form input[type="submit"]'));
						$('#register_form #register_error').html(data.error);
					}
				}
			});
			
			$('#register_form input[name="rights"]').checkbox({
				class_name: 'input-checkbox'
			});
			$('#register_form input[name="pro_category_request"]').checkbox({
				class_name: 'input-checkbox',
				onChange: function(obj) {
					if ($(obj).is(':checked')) {
						$('#register_form .pro-chk').show();
					} else {
						$('#register_form .pro-chk').hide();
					}
				}
			});
		});
	</script>	
{/if}

{if $action eq "loged_in"}
	<div class="profile-panel">		
		<input type="button" value="{__('user_login.logout')}" class="button-gray profile-logout" onclick="logout(this);"/>
		
		<form id="edit_form" method="post" action="plugins/userlogin/save_user">
			<h2 style="margin-right: 40px;">{__('user_login.profile_title')}:</h2>
			
			{if $activated|default:false}
				{__('user_registration.activated')}
			{/if}
			
			{__('user_login.profile_intro')}
			<table class="login-table">
				<tr>
		    		<th>{__('user_registration.first_name')}: <span class="must-fill">*</span></th>
		    		<td><input type="text" name="first_name" value="{$user.first_name|escape}" class="wb-input" autocomplete="off"></td>
		    	</tr>
		    	<tr>
		    		<th>{__('user_registration.last_name')}: <span class="must-fill">*</span></th>
		    		<td><input type="text" name="last_name" value="{$user.last_name|escape}" class="wb-input" autocomplete="off"></td>
		    	</tr>
		    	<tr>
					<th>{__('user_registration.company')}:</th>
					<td><input type="text" name="company" value="{$user.company|escape}" class="wb-input" autocomplete="off"></td>
				</tr>
				<tr>
					<th>{__('user_registration.reg_nr')}:</th>
					<td><input type="text" name="reg_nr" value="{$user.reg_nr|escape}" class="wb-input" autocomplete="off"></td>
				</tr>
				<tr>
					<th>{__('user_registration.vat_nr')}:</th>
					<td><input type="text" name="vat_nr" value="{$user.vat_nr|escape}" class="wb-input" autocomplete="off"></td>
				</tr>
				<tr>
		    		<th>{__('user_registration.email')}: <span class="must-fill">*</span></th>
		    		<td><input type="text" name="email" value="{$user.email|escape}" class="wb-input" autocomplete="off"></td>
		    	</tr>
		    	<tr>
					<th>{__('user_registration.phone')}: <span class="must-fill">*</span></th>
					<td><input type="text" name="phone" value="{$user.phone|escape}" class="wb-input" autocomplete="off"/></td>
				</tr>
				<tr>
					<th>{__('user_registration.address')}:</th>
					<td><input type="text" name="address" value="{$user.address|escape}" class="wb-input" autocomplete="off"/></td>
				</tr>
				<tr>
		    		<th>{__('user_registration.password')}:</th>
		    		<td><input type="password" name="password" value="" class="wb-input" autocomplete="off"></td>
		    	</tr>
		    	<tr>
		    		<th>{__('user_registration.password2')}:</th>
		    		<td><input type="password" name="password2" value="" class="wb-input" autocomplete="off"></td>
		    	</tr>	
		    	
		    	<tr style="height: 0px;">
		    		<th></th>
		    		<td id="edit_error"></td>
		    	</tr>
		    	
		    	<tr>
		    		<th></th>
		    		<td>
		    			<input type="submit" class="button-green" value="{__('user_login.profile_save')}" style="width: 237px;"/>
		    		</td>
		    	</tr>
		    </table>
		</form>
	</div>
	
		<script type="text/javascript">
		$().ready(function() {			
			$('#edit_form').ajaxForm({
				dataType: 'json',
				beforeSubmit: function() {
					$('#edit_form #edit_error').html('');
					showActivity($('#edit_form input[type="submit"]'));
				},
				success: function(data) {
					if (data.status == '1') {
						$('#edit_form #edit_error').html(data.response);
						hideActivity($('#edit_form input[type="submit"]'));
					} else {
						hideActivity($('#edit_form input[type="submit"]'));
						$('#edit_form #edit_error').html(data.error);
					}
				}
			});
		});
	</script>	
{/if}

{if $action == 'forgot_password'}
	<h2>{__('user_login.forgot_title')}:</h2>
	<form method="post" action="{$base_url}plugins/userlogin/forgot_password" id="forgot_form">		
	    <table class="login-table">
	    	<tr>
	    		<td colspan="2">
	    			{__('user_login.forgot_intro')}
	    		</td>	    		
	    	</tr>
	    	<tr>
	    		<th>{__('user_login.email')}:</th>
	    		<td><input type="text" name="email" value="" class="wb-input"></td>
	    	</tr>
	    	
	    	<tr style="height: 0px;">
	    		<th></th>
	    		<td id="forgot_error" style="color: #FF0000;"></td>
	    	</tr>
	    	
	    	<tr>
	    		<th></th>
	    		<td>
	    			<input type="submit" class="button-green" value="{__('user_login.btn_forgot')}" style="width: 237px;"/>
	    		</td>
	    	</tr>
	    </table>
	</form>
	
	<script type="text/javascript">
		$().ready(function() {
			$('#forgot_form').ajaxForm({
				dataType: 'json',
				beforeSubmit: function() {
					$('#forgot_form #forgot_error').html('');
					showActivity($('#forgot_form input[type="submit"]'));
				},
				success: function(data) {
					if (data.status == '1') $('#forgot_form').html(data.response);
					else {
						hideActivity($('#forgot_form input[type="submit"]'));
						$('#forgot_form #forgot_error').html(data.error);
					}
				}
			});
		});
	</script>
{/if}

{if $action == 'forgot_password_form'}
	<h2>{__('user_login.forgot_title')}:</h2>
	<form method="post" action="{$base_url}plugins/userlogin/forgot_password_change" id="forgot_form">
		{__('user_login.restore_intro')}		
	    <table class="login-table">
	    	<tr>
	    		<th>{__('user_registration.password')}:</th>
	    		<td><input type="password" name="password" value="" class="wb-input"></td>
	    	</tr>
	    	<tr>
	    		<th>{__('user_registration.password2')}:</th>
	    		<td><input type="password" name="password2" value="" class="wb-input"></td>
	    	</tr>
	    	
	    	<tr style="height: 0px;">
	    		<th></th>
	    		<td id="forgot_error" style="color: #FF0000; padding-top: 5px;"></td>
	    	</tr>
	    	
	    	<tr>
	    		<th></th>
	    		<td>
	    			<input type="submit" class="button-green" value="{__('user_login.btn_forgot')}" style="width: 237px;"/>
	    		</td>
	    	</tr>
	    </table>
	</form>
	
	<script type="text/javascript">
		$().ready(function() {
			$('#forgot_form').ajaxForm({
				dataType: 'json',
				beforeSubmit: function() {
					$('#forgot_form #forgot_error').html('');
					showActivity($('#forgot_form input[type="submit"]'));
				},
				success: function(data) {
					if (data.status == '1') $('#forgot_form').html(data.response);
					else {
						hideActivity($('#forgot_form input[type="submit"]'));
						$('#forgot_form #forgot_error').html(data.error);
					}
				}
			});
		});
	</script>
{/if}
