{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=4}

{if $action eq "list"}
	<div class="title">
		{__($page_title)}
	</div>
	<table class="data_table">
		<thead>
			<tr>
				<th style="width: 300px;">{__('Title')}</th>
				<th style="width: 130px;">{__('Category Type')}</th>
				<th style="width: 130px;">{__('Status')}</th>
				<th style="width: 210px;"></th>
			</tr>
		</thead>
		<tbody>
			{include file="$this_file" action="view" parent_id="0" level="0"}
		</tbody>
		<tfoot>			
			<tr>
				<td colspan="{$cols}">
					<a class="button" href="manager/{$link}/edit/new">{__('Add')}</a>
				</td>
			</tr>
		</tfoot>
	</table>
	
	<script type="text/javascript">
		var category_link = '{$link}';
		var category_delete_title = '{__("Are you sure?")}';
		var category_delete_msg = '{__("Are you sure to delete this Category?")}';
	</script>	
{/if}

{if $action eq "view"}
	{foreach item="data" from=$categories}
		{if $data.parent_id == $parent_id}
			<tr data-id="{$data.id}">
				<td style="padding-left: {$level*25+2}px;">{$data.title|strip_tags}</td>
				<td>{__({$data.type_description})}</td>
				<td>{__({$data.status_description})}</td>
				<td>
					<a class="button" href="manager/{$link}/edit/{$data.id}">{__('Edit')}</a>
					<button type="button" onclick="delete_category(this);">{__('Delete')}</button>
					<a class="button" href="manager/{$link}/settings/{$data.id}" style="margin-left: 10px;">{__('Settings')}</a>
					{* <a class="button" href="manager/{$link}/edit/new/{$data.id}" style="margin-left: 10px;">{__('Add here')}</a> *}
				</td>
			</tr>
			{include file=$this_file action="view" parent_id=$data.id level=$level+1}
		{/if}
	{/foreach}	
{/if}