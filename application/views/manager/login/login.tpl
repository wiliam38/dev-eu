<div class="login_box ui-widget-content ui-corner-all">
	<div class="login_box_header ui-corner-all">{__('Login')}</div>
	<form id="login" method="post">
		<table title="Login form">
			{if $error|default:NULL ne NULL}
				<tr style="min-height: 30px;">
					<td colspan="2" class="login_error" style="padding-bottom: 10px;">
						{__({$error|default:null})}
					</td>	
				</tr>
			{else}
				<tr>
					<td colspan="2" style="padding-bottom: 10px;">{__('Please enter username and password')}.</td>	
				</tr>
			{/if}
			<tr>
				<td style="width: 120px">{__('User name')}:</td>
				<td style="width: 180px">
					<input type="text" name="username" title="{__('User name')}" value="{$username|default:''}" style="width: 173px;">
				</td>
			</tr>
			<tr>
				<td>{__('Password')}:</td>
				<td><input type="password" name="password" title="{__('Password')}" style="width: 173px;"/></td>
			</tr>
			<tr>
				<td></td>
				<td style="padding-top: 10px; text-align: right;">
					<input type="submit" class="submit-hidden">			
					<span class="button" onclick="submit(this);">{__('Login')}</span>			
				</td>
			</tr>
		</table>
	</form>
</div>