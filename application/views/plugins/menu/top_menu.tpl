{if $action eq "load"}
	{$colors=array('#313131','#313131','#313131','#1c1c1c')}
	{foreach item="data" from=$docs name="menu"}<a href="{$base_url}{$lang_tag}/{$data.alias}" class="{if in_array($data.id,$parents)}active{/if}">
			{$data.menu_title}
			<span class="right-border" style="border-right: 1px solid {$colors[$smarty.foreach.menu.index]|default:'#1c1c1c'}"></span>
		</a>{/foreach}
	<div class="working-time">
		<div class="time">
			<div class="time-title">{__('working_time.time_title')}</div>
			<div class="time-text">{__('working_time.time')}</div>
		</div>
		<div class="phone">
			<span class="ion-android-call"></span> {__('working_time.phone')}
		</div>
	</div>
	<div class="user">
		{* <div id="login_hello">
			{if !empty($user_data)}
				{__('user_login.hello')} {$user_data.first_name} {$user_data.last_name}
			{/if}
		</div> *}
		<a href="#login" onclick="menu_login(this); return false;" class="login" id="login_button"><span class="right-border"></span></a>
		<a href="#cart" onclick="menu_cart(this); return false;" class="cart" id="cart_button"></a>
	</div>
{/if}