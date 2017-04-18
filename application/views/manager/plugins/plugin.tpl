{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=6}

{if $action eq "load"}
	<div class="title">
		{__('Plugins')}
	</div>
	
	<table class="data_table">
		<thead>
			<tr class="filter">
				<td colspan="6">
					<a class="button" onclick="plugins_generate_files();" style="margin-left: 20px; float: right;">{__('Generate settings files')}</a>
				</td>
			</tr>
			<tr>
				<th style="width: 150px;">{__('Name')}</th>
				<th style="width: 250px;">{__('Model')}</th>
				<th style="width: 250px;">{__('Template')}</th>
				<th style="width: 200px;">{__('Parameters')}</th>
				<th style="width: 100px;">{__('User')}</th>
				<th style="width: 140px;"></th>
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
					<a class="button" action="plugins_add">{__('Add')}</a>
				</td>
			</tr>
		</tfoot>
	</table>	
{/if}

{if $action eq "view"}
	<tr data_id="{$data.id}">
		<td>{$data.name|strip_tags}</td>
		<td>{$data.model|strip_tags}</td>
		<td>{$data.template|strip_tags}</td>
		<td>{$data.parameters|strip_tags|replace:'|':'<br>'}</td>
		<td>{$data.user_full_name|strip_tags}</td>
		<td>
			<a class="button" action="plugins_edit">{__('Edit')}</a>
			<a class="button" action="plugins_delete">{__('Delete')}</a>
		</td>
	</tr>
{/if}

{if $action eq "edit"}
	<tr data_id="{$data.id}" class="edit">
		<td><input type="text" id="name" value="{$data.name}"></td>
		<td><input type="text" id="model" value="{$data.model}"></td>
		<td><input type="text" id="template" value="{$data.template}"></td>
		<td><textarea type="text" id="parameters" style="height: auto; min-width: 200px; max-width: 200px;">{$data.parameters}</textarea></td>
		<td></td>
		<td>
			<a class="button" action="plugins_save">{__('Save')}</a>
			<a class="button" action="plugins_view">{__('Cancel')}</a>
		</td>
	</tr>
{/if}