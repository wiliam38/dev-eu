{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=9}

{if $action eq "load"}
	<div class="title">
		{__('Users')}
	</div>		
	<table class="data_table">
		<thead>
			<tr class="filter">
				<th colspan="{$cols}">
					<form method="post" action="{$base_url}manager/users" id="filter_form">
						<input type="hidden" name="order_by" value="{$filter_data.order_by|default:''|escape}"/>
										
						<span style="line-height: 21px;">{__('Search')}:</span> 
						<input type="text" name="search" value="{$filter_data.search|default:''|escape}" style="width: 200px;"/>
						
						<span style="line-height: 21px; margin-left: 15px;">{__('Status')}:</span>
						<select name="status_id[]" id="status_id" style="width: 157px;" multiple="multiple">
							<option value="">--- {__('ALL')} ---</option>
							{foreach item="data" from=$status}
								<option value="{$data.id|escape}" {if in_array($data.id, $filter_data.status_id|default:array())}selected="selected"{/if}>{__($data.description)|escape}</option>
							{/foreach}
						</select>
					
						<button type="submit" class="button" style="margin-left: 15px;">{__('Search')}</button>
						
						<button type="button" class="button" onclick="exportXML(this);" style="float: right; margin-left: 15px; padding-left: 10px; padding-right: 10px;">{__('Export .xml')}</button>
					</form>
				</th>
			</tr>
			<tr>
				<th width="200">
					{__('Full name')}
					<div class="order-by {$filter_data.order_by|default:''|orderby:'1'}" onclick="order_by(1, this)"></div>
				</th>
				<th width="120">
					{__('User name')}
					<div class="order-by {$filter_data.order_by|default:''|orderby:'2'}" onclick="order_by(2, this)"></div>
				</th>
				<th width="200">
					{__('E-mail')}
					<div class="order-by {$filter_data.order_by|default:''|orderby:'3'}" onclick="order_by(3, this)"></div>
				</th>
				<th width="100">
					{__('Company')}
					<div class="order-by {$filter_data.order_by|default:''|orderby:'8'}" onclick="order_by(8, this)"></div>
				</th>
				<th width="100">
					{__('PRO Category')}
					<div class="order-by {$filter_data.order_by|default:''|orderby:'7'}" onclick="order_by(7, this)"></div>
				</th>
				<th width="120">
					{__('Status')}
					<div class="order-by {$filter_data.order_by|default:''|orderby:'4'}" onclick="order_by(4, this)"></div>
				</th>
				<th width="110">
					{__('Registration Date')}
					<div class="order-by {$filter_data.order_by|default:''|orderby:'9'}" onclick="order_by(9, this)"></div>
				</th>
				<th width="110">
					{__('Last Login')}
					<div class="order-by {$filter_data.order_by|default:''|orderby:'5'}" onclick="order_by(5, this)"></div>
				</th>
				{*
				<th width="70">
					{__('Number of Login')}
					<div class="order-by {$filter_data.order_by|default:''|orderby:'6'}" onclick="order_by(6, this)"></div>
				</th>
				*}
				<th width="140"></th>
			</tr>			
		</thead>
		<tbody>			
			{foreach item=data from=$users}
				{include file=$this_file action='view'}
			{foreachelse}
				<tr>
					<td colspan="{$cols}">
						{__('No Data!')}
					</td>
				</tr>
			{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="{$cols}">
					<form method="post" action="{$base_url}manager/users" id="paginate_form">
						<input type="hidden" name="order_by" value="{$filter_data.order_by|default:''|escape}"/>
						<input type="hidden" name="search" value="{$filter_data.search|default:''|escape}"/>
						{foreach item="status_id" from=$filter_data.status_id|default:array()}<input type="hidden" name="status_id[]" value="{$status_id|escape}"/>{/foreach}						
						<input type="hidden" name="page" value="{$paginate.page|default:''|escape}"/>
						<input type="submit" style="display: none;"/>
						
						<button type="button" onclick="$('#paginate_form').attr('action','{$base_url}manager/usereditor/load/new'); $('#paginate_form').submit();" class="button">{__('Add')}</button>
		
						{include file="{$smarty.current_dir}/../../global/manager_data_pages.tpl"}
					</form>
				</td>
			</tr>
		</tfoot>
	</table>
{/if}

{if $action eq 'view'}
	<tr id="{$data.id}">
		<td>{$data.first_name} {$data.last_name}</td>
		<td>{$data.username}</td>
		<td>{$data.email}</td>
		<td>{$data.company|escape}</td>
		<td class="center">
			{if !empty($data.pro_category)}
				<input type="checkbox" disabled="disabled" checked="checked" style="vertical-align: middle;"/><br/>
				({$data.pro_coffee_coef|number_format:2:'.':''},{$data.pro_machines_coef|number_format:2:'.':''},{$data.pro_accessories_coef|number_format:2:'.':''})
			{/if}
		</td>
		<td>{__($data.status_description)}</td>
		<td class="right">{$data.creation_datetime|date_format:'%d.%m.%Y  %H:%M'}</td>
		<td class="right">{if $data.last_login != '0000-00-00 00:00:00'}{$data.last_login|date_format:'%d.%m.%Y  %H:%M'}{/if}</td>
		{* <td class="right">{$data.num_logins}</td> *}
		<td>
			<a href="javascript:;" onclick="$('#paginate_form').attr('action','{$base_url}manager/usereditor/load/{$data.id}'); $('#paginate_form').submit();" class="button">{__('Edit')}</a>
			{if $data.id ne 1}
				<a class="button" action="user_delete">{__('Delete')}</a>
			{/if}
		</td>
	</tr>
{/if}