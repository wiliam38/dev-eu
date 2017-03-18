{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=8}

{if $action eq "load"}
	<div class="title">
		{__('Languages')}
	</div>
	<table class="data_table">
		<colgroup>
			<col style="width: 150px;"/>
			<col style="width: 70px;"/>
			<col style="width: 70px;"/>
			<col style="width: 100px;"/>
			<col style="width: 70px;"/>
			<col style="width: 112px;"/>
			<col style="width: 100px;"/>
			<col style="width: 230px;"/>
		</colgroup>
		
		<thead>
			<tr>
				<th>{__('Name')}</th>
				<th>{__('Ticker')}</th>
				<th>{__('Tag')}</th>
				<th>{__('Image')}</th>
				<th>{__('Order Index')}</th>
				<th>{__('Status')}</th>
				<th>{__('User')}</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$plugins}
				{include file="$this_file" action="view"}
			{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="{$cols}">
					<a class="button" action="languages_add">{__('Add')}</a>
				</td>
			</tr>
		</tfoot>
	</table>		
{/if}

{if $action eq "view"}
	<tr data_id="{$data.id}">
		<td>{$data.name|strip_tags} <i>({$data.id})</i></td>
		<td>{$data.ticker|strip_tags}</td>
		<td>{$data.tag|strip_tags}</td>
		<td>
			{if $data.img_src ne ''}
				<img src="{$data.img_src}" onerror="$(this).hide();">
			{/if}
		</td>
		<td>{$data.order_index|strip_tags}</td>
		<td>{__($data.status_description|strip_tags)}</td>
		<td>{$data.user_full_name|strip_tags}</td>
		<td>
			<a class="button" action="languages_edit">{__('Edit')}</a>
			<a class="button" action="languages_delete">{__('Delete')}</a>
			{if $data.default eq 0}
				<a class="button" action="languages_default">{__('Set default')}</a>
			{/if}
		</td>
	</tr>
{/if}

{if $action eq "edit"}
	<tr data_id="{$data.id}" class="edit">
		<td><input type="text" id="name" value="{$data.name}"></td>
		<td><input type="text" id="ticker" value="{$data.ticker}"></td>
		<td><input type="text" id="tag" value="{$data.tag}"></td>
		<td><input type="text" id="img_src" value="{$data.img_src}"></td>
		<td><input type="text" id="order_index" value="{$data.order_index}"></td>
		<td>
			<select id="status_id" style="width: 80px;">
				{foreach item=status_data from=$status}
					<option value="{$status_data.id}" {$status_data.selected}>{__($status_data.description)}</option>
				{/foreach}
			</select>
		</td>
		<td></td>
		<td>
			<a class="button" action="languages_save">{__('Save')}</a>
			<a class="button" action="languages_view">{__('Cancel')}</a>
		</td>
	</tr>
{/if}