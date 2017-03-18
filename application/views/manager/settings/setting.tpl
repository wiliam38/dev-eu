{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=5}

{if $action eq "load"}
	<div class="title">
		{__('Settings')}
	</div>
	
	<table class="data_table">
		<thead>
			<tr class="filter">
				<th colspan="{count($languages)+5}">
					{__('Category')}:
					<select id="category_name" style="width: 177px;">
						<option selected="selected" value="">--- {__('ALL')} ---</option>
					</select>
					<a class="button" action="settings_show" style="margin-left: 10px">{__('Search')}</a>
					
					<a class="button" onclick="settings_generate_files();" style="margin-left: 20px; float: right;">{__('Generate settings files')}</a>
				</th>
			</tr>
			<tr>
				<th style="width: 120px;">{__('Name')}</th>
				<th style="width: 200px;">{__('Description')}</th>
				<th style="width: 120px;">
					{__('Default')}<br>
					{__('value')}
				</th>
				{foreach item=lang from=$languages}
					<th width="120">
						{__($lang.name|strip_tags)}<br>
						{__('value')}
					</th>
				{/foreach}
				<th style="width: 100px;">{__('User')}</th>
				<th style="width: 130px;"></th>
			</tr>
		</thead>
		<tbody id="data_div">
			{include file=$this_file action="show"}
		</tbody>
		<tfoot>
			<tr class="add">
				<td colspan="{$languages|count+$cols}">
					<a class="button" action="settings_add">{__('Add')}</a>
				</td>
			</tr>
		</tfoot>
	</table>
{/if}

{if $action eq "show"}
	{foreach item="data" from=$settings}
		{include file="$this_file" action="view"}
	{/foreach}
{/if}

{if $action eq "view"}
	<tr data_id="{$data.id}">
		<td>{$data.name|strip_tags}</td>
		<td>{$data.description|strip_tags}</td>
		<td>{$data.value|strip_tags}</td>
		{foreach item=lang from=$languages}
			<td>
				{if $data.type_id ne 3} 
					{$data.lang[$lang.id].value|default:''|strip_tags}
				{/if}
			</td>
		{/foreach}
		<td>{$data.user_full_name|strip_tags}</td>
		<td style="white-space: nowrap;">
			<a class="button" action="settings_edit">{__('Edit')}</a>
			{if $data.type_id eq "2"}
				<a class="button" action="settings_delete">{__('Delete')}</a>
			{/if}
		</td>
	</tr>
{/if}

{if $action eq "edit"}
	<tr data_id="{$data.id}" class="edit">
		<td>
			{if $data.type_id eq 2} <input type="text" id="def_name" value="{$data.name}">
			{else}{$data.name|strip_tags}{/if}
		</td>
		<td>
			{if $data.type_id eq 2}<input type="text" id="def_description" value="{$data.description}">
			{else}{$data.description|strip_tags}{/if}
		</td>
		<td><input type="text" id="def_value" value="{$data.value}"></td>
		{foreach item=lang from=$languages}
			<td>
				{if $data.type_id ne 3} 
					<input type="hidden" id="lang_id" value="{$lang.id}">
					<input type="hidden" id="lang_setting_id" value="{$data.lang[$lang.id].id|default:'new'}">
					<input type="text" id="value" value="{$data.lang[$lang.id].value|default:''}">					
				{/if}
			</td>
		{/foreach}
		<td></td>
		<td style="white-space: nowrap;">
			<a class="button" action="settings_save">{__('Save')}</a>
			<a class="button" action="settings_view">{__('Cancel')}</a>
		</td>
	</tr>
{/if}