{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=7}

{if $action eq "load"}
	<div class="title">
		{__('Statuses')}
	</div>
	
	<table class="data_table">
		<colgroup>
			<col style="width: 50px;"/>
			<col style="width: 150px;"/>
			<col style="width: 150px;"/>
			<col style="width: 100px;"/>
			{foreach item=lang from=$languages}
				<col style="width: 120px;"/>
			{/foreach}
			<col style="width: 100px;"/>
			<col style="width: 100px;"/>
			<col style="width: 150px;"/>
		</colgroup>
		
		<thead>
			<tr class="filter">
				<th colspan="{count($languages)+7}">
					<form method="post" id="filter_form">
						{__('Category')}:&nbsp;
						<select name="category_name" style="width: 177px; padding-left: 5px;" onchange="$('#filter_form').submit();">
							{foreach item="data" from=$table_status_name}
								<option value="{$data|escape}" {if $post_data.category_name|default:'' == $data}selected="selected"{/if}>{$data|escape}</option>
							{/foreach}
						</select>
						<button type="submit" class="button" style="margin-left: 10px">{__('Search')}</button>
					</form>
				</th>
			</tr>
			<tr>
				<th>{__('System ID')}</th>
				<th>{__('System name')}</th>
				<th>{__('Description')}</th>
				<th>{__('Value')}</th>
				{foreach item=lang from=$languages}
					<th>
						{__($lang.name|strip_tags)}<br>
						{__('name')}
					</th>
				{/foreach}
				<th>{__('Order Index')}</th>
				<th>{__('User')}</th>
				<th></th>
			</tr>
		</thead>
		<tbody id="data_div">
			{foreach item="data" from=$status}
				{include file="$this_file" action="view"}
			{foreachelse}
				<tr>
					<td colspan="{$languages|count+$cols}">{__('No Data!')}</td>
				</tr>
			{/foreach}
		</tbody>
		{if $allowed|default:false}
			<tfoot>
				<tr class="add" data-id="new">
					<td colspan="{$languages|count+$cols}">
						<button class="button" onclick="status_edit(this);">{__('Add')}</button>
					</td>
				</tr>
			</tfoot>
		{/if}
	</table>
{/if}

{if $action eq "view"}
	<tr data-id="{$data.id}">
		<td>{$data.status_id|strip_tags}</td>
		<td>{$data.name|strip_tags}</td>
		<td>{$data.description|strip_tags}</td>
		<td>{$data.value|strip_tags}</td>
		{foreach item=lang from=$languages}
			<td>{$data.lang[$lang.id].name|default:''|strip_tags}</td>
		{/foreach}
		<td>{$data.order_index|strip_tags}</td>
		<td>{$data.user_full_name|strip_tags}</td>
		<td style="white-space: nowrap;">
			{if $allowed|default:false}
				<button class="button" onclick="status_edit(this);">{__('Edit')}</button>
				<button class="button" onclick="status_delete(this);">{__('Delete')}</button>
			{/if}
		</td>
	</tr>
{/if}

{if $action eq "edit"}
	<tr data-id="{$data.id|default:'new'}" class="edit">
		<td>
			<input type="hidden" name="id" value="{$data.id|default:'new'}"/>
			<input type="hidden" name="table_status_name" value="{$data.table_status_name|default:''}"/>
		
			{if $data.id|default:'new' == 'new'}
				<input type="text" name="status_id" value="{$data.status_id|default:''|escape}"/>
			{else}
				{$data.status_id|default:''|strip_tags}
			{/if}
		</td>
		<td>
			{if $data.id|default:'new' == 'new'}
				<input type="text" name="name" value="{$data.name|default:''|escape}"/>
			{else}
				{$data.name|default:''|strip_tags}
			{/if}
		</td>
		<td>
			<input type="text" name="description" value="{$data.description|default:''|escape}"/>
		</td>
		<td>
			<input type="text" name="value" value="{$data.value|default:''|strip_tags}"/>
		</td>
		{foreach item=lang from=$languages}
			<td>
				<input type="hidden" name="content_id[]" value="{$data.lang[$lang.id].id|default:''|escape}"/>
				<input type="hidden" name="language_id[]" value="{$lang.id|default:''|escape}"/>
				<input type="text" name="content_name[]" value="{$data.lang[$lang.id].name|default:''|strip_tags}"/>
			</td>
		{/foreach}
		<td>
			<input type="text" name="order_index" value="{$data.order_index|default:''|escape}"/>
		</td>
		<td></td>
		<td style="white-space: nowrap;">
			<button class="button" onclick="status_save(this);">{__('Save')}</button>
			<button class="button" onclick="status_view(this);">{__('Cancel')}</button>
		</td>
	</tr>
{/if}