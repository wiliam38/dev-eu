{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action eq 'load'}	
	<form id="user_form" method="post" action="manager/usereditor/save" style="width: 950px;">
		<div id="resource_buttons">
			{__('User')}: {$user.first_name} {$user.last_name}
			<div id="buttons" class="ui-widget ui-widget-content ui-corner-all">
				<button type="submit" class="button">{__('Save')}</button>
				<button type="button" onclick="$('#return_form').submit();" class="button">{__('Cancel')}</button>
				{if $user.id ne 1 AND !empty($user.id) AND $user.id != 'new'}
					&nbsp;&nbsp;&nbsp;&nbsp;
					<button type="button" class="button" action="delete_user">{__('Delete')}</button>
				{/if}
			</div>
		</div>
		<div id="resource_tabs">
			<ul>
				<li><a href="#general">{__('User data')}</a></li>
				<li><a href="#roles">{__('Roles')}</a></li>
			</ul>
			<div id="general">
				<input type="hidden" id="user_id" name="user_id" value="{$user.id}">
				<table class="resource_data">
					<tr>
						<th style="width: 170px;">{__('First name')}:</th>
						<td><input type="text" name="first_name" value="{$user.first_name}" autocomplete="off"></td>
					</tr>
					<tr>
						<th>{__('Last name')}:</th>
						<td><input type="text" name="last_name" value="{$user.last_name}" autocomplete="off"></td>
					</tr>
					<tr>
						<th>{__('Status')}:</th>
						<td>
							<select name="status_id">
							{foreach item="data" from=$status_data}
								<option value="{$data.id}" {$data.selected}>{__({$data.description})}</option>
							{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<th>{__('E-mail')}:</th>
						<td><input type="text" name="email" value="{$user.email}" autocomplete="off"></td>
					</tr>
					<tr>
						<th>{__('Phone')}:</th>
						<td><input type="text" name="phone" value="{$user.phone}" autocomplete="off"></td>
					</tr>
					<tr>
						<th colspan="2">&nbsp;</th>
					</tr>
					<tr>
						<th>{__('Company')}:</th>
						<td><input type="text" name="company" value="{$user.company|escape}" autocomplete="off"></td>
					</tr>
					<tr>
						<th>{__('Reg. nr.')}:</th>
						<td><input type="text" name="reg_nr" value="{$user.reg_nr|escape}" autocomplete="off"></td>
					</tr>
					<tr>
						<th>{__('PVN nr.')}:</th>
						<td><input type="text" name="vat_nr" value="{$user.vat_nr|escape}" autocomplete="off"></td>
					</tr>
					<tr>
						<th>{__('PRO Category Enabled')}:</th>
						<td><input type="checkbox" name="pro_category" value="1" {if !empty($user.pro_category)}checked="checked"{/if}/></td>
					</tr>
					<tr>
						<th>{__('PRO Category Coffee Price Coef.')}:</th>
						<td><input type="text" name="pro_coffee_coef" value="{$user.pro_coffee_coef|number_format:2:'.':''}" autocomplete="off"></td>
					</tr>
					<tr>
						<th>{__('PRO Category Machines Price Coef.')}:</th>
						<td><input type="text" name="pro_machines_coef" value="{$user.pro_machines_coef|number_format:2:'.':''}" autocomplete="off"></td>
					</tr>
					<tr>
						<th>{__('PRO Category Accessories Price Coef.')}:</th>
						<td><input type="text" name="pro_accessories_coef" value="{$user.pro_accessories_coef|number_format:2:'.':''}" autocomplete="off"></td>
					</tr>
					<tr>
						<th colspan="2">&nbsp;</th>
					</tr>
					<tr>
						<th>{__('User name')}:</th>
						<td>
							{if $user.id eq 'new'}<input type="text" name="username" value="{$user.username}" autocomplete="off">
							{else}{$user.username}{/if}
						</td>
					</tr>
					
					<tr>
						<th>{__('Password')}:</th>
						<td><input type="password" name="password" value="" autocomplete="off"></td>
					</tr>
					<tr>
						<th>{__('Repeat password')}:</th>
						<td><input type="password" name="password2" value="" autocomplete="off"></td>
					</tr>
				</table>
			</div>
			<div id="roles">
				<h2>{__('User roles')}</h2>
				<table class="data_table" id="user_roles_table">
					<thead>
						<tr>
							<th style="width: 310px;">{__('Role')}</th>
							<th style="width: 200px;">{__('Data')}</th>
							<th style="width: 120px;">{__('Status')}</th>
							<th style="width: 80px;"></th>
						</tr>
					</thead>
					<tbody>
						{foreach item=user_role from=$user_roles}
							{include file=$this_file action="roles_row"}
						{/foreach}		
					</tbody>
					<tfoot>
						<tr>
							<td colspan="4">
								<button type="button" class="button add_role_popup">{__('Add')}</button>
							</td>
						</tr>
					</tfoot>		
				</table>
			</div>
		</div>
	</form>
	
	<form method="post" action="{$base_url}manager/users{if !empty($filter_data.page)}?p={$filter_data.page|default:''|escape}{/if}" id="return_form" style="display: none;">
		<input type="hidden" name="order_by" value="{$filter_data.order_by|default:''|escape}"/>
		<input type="hidden" name="search" value="{$filter_data.search|default:''|escape}"/>
		{foreach item="status_id" from=$filter_data.status_id|default:array()}<input type="hidden" name="status_id[]" value="{$status_id|escape}"/>{/foreach}
		<button type="submit"></button>
	</form>
{/if}

{if $action == 'roles_row'}
	<tr>
		<td>
			<input type="hidden" name="roles_user_role_id[]" value="{$user_role.id}"/>
			<input type="hidden" name="roles_role_id[]" value="{$user_role.role_id}"/>
			<input type="hidden" name="roles_data_id[]" value="{$user_role.data_id}"/>
											
			{$user_role.role_description}
		</td>
		<td>{$user_role.data_title}</td>
		<td>
			<select name="roles_status_id[]" style="width: 91px;">
				{foreach item="data" from=$user_role_status}
					<option value="{$data.id}" {if $data.id == $user_role.status_id}selected="selected"{/if}>{__({$data.description})}</option>
				{/foreach}
			</select>
		</td>
		<td>
			<a class="button" onclick="$(this).closest('tr').remove();">{__('Remove')}</a>
		</td>
	</tr>
{/if}

{if $action == 'roles_popup'}
	<table>
		<tr>
			<td style="width: 150px;">{__('Select role')}:</td>
			<td>
				<select id="role_id_popup" onchange="load_roles();" style="width: 277px;">
					{foreach item="data" from=$roles}
						<option value="{$data.id}" {if $role_id == $data.id}selected="selected"{/if}>{$data.description}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		
		{if $roles_data|default:false}
			<tr>
				<td>{__('Select data')}:</td>
				<td>
					<select id="data_id_popup" style="width: 277px;">
						{foreach item="data" from=$roles_data}
							<option value="{$data.id}">{$data.title}</option>
						{/foreach}
					</select>
				</td>
			</tr>
		{/if}
	</table>	
	
	<script type="text/javascript">
		$().ready(function() {
			$('select').combobox();
		});
	</script>
{/if}