{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=5}

{if $action eq "list"}
	<div class="title">
		{__('News')}
	</div>
	
	<table class="data_table">
		<thead>
			<tr>
				<th style="width: 100px;">{__('Date')}</th>
				<th style="width: 400px;">{__('Title')}</th>
				<th style="width: 60px;">{__('Main new')}</th>
				<th style="width: 200px;">{__('Type')}</th>
				<th style="width: 130px;"></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$news}
				{include file="$this_file" action="view"}
			{foreachelse}
				<tr>
					<td colspan="{$cols}">{__('No data')}!</td>
				</tr>
			{/foreach}
		</tbody>
		<tfoot>			
			<tr>
				<td colspan="{$cols}">
					<form method="post" action="{$base_url}manager/news_news" id="paginate_form">
						<input type="hidden" name="page" value="{$paginate.page|default:''|escape}"/>
						<input type="submit" style="display: none;"/>
						
						<a class="button" href="manager/news_news/edit/new">{__('Add')}</a>
		
						{include file="{$smarty.current_dir}/../../global/manager_data_pages.tpl"}
					</form>	
				</td>
			</tr>
		</tfoot>
	</table>

	<script type="text/javascript">
		$().ready(function() {		
			$('a[action="product_delete"]').click(function() {
				var tr = $(this).closest('tr');
				var new_id = $(tr).attr('data-id');
				
				if (new_id != 'new') {
					jConfirm('{__("Are you sure to delete this New")}?','{__("Are you sure")}?', function(r) {
						if (r) {
							page_loading('{__("Deleting...")}');
							$.post(base_url+'manager/news_news/delete', {
								new_id:	new_id
							}, function(data) {
								if (data.status == '1') {
									$(tr).remove();	
									page_loading('');
								} else {
									page_loading('');
									jAlert(data.error);
								}
							}, 'json');
						}
					});
				}
			});
		});		
	</script>
{/if}

{if $action eq "view"}
	<tr data-id="{$data.id}">
		<td>{$data.lang[$def_lang_id].pub_date|default:''|date_format:'%d-%b-%Y'|strip_tags}</td>
		<td>{$data.admin_title|strip_tags}</td>
		<td class="center">{if $data.main == 1}X{/if}</td>
		<td>{__($data.type_description|strip_tags)}</td>
		<td>
			<a class="button" href="manager/news_news/edit/{$data.id}">{__('Edit')}</a>
			<a class="button" action="product_delete">{__('Delete')}</a>
		</td>
	</tr>
{/if}