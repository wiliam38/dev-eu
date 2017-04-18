{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=3}

{if $action eq "list"}
	<div class="title">
		{__('Reciepes')}
	</div>
	
	<table class="data_table">
		<thead>
			<tr>
				<th style="width: 600px;">{__('Title')}</th>
				<th style="width: 150px;">{__('Priority index')}</th>
				<th style="width: 130px;"></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$reciepes}
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
					<form method="post" action="{$base_url}manager/reciepes_reciepes" id="paginate_form">
						<input type="hidden" name="page" value="{$paginate.page|default:''|escape}"/>
						<input type="submit" style="display: none;"/>
						
						<a class="button" href="manager/reciepes_reciepes/edit/new">{__('Add')}</a>
		
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
				var reciepe_id = $(tr).attr('data-id');
				
				if (reciepe_id != 'new') {
					jConfirm('{__("Are you sure to delete this Reciepe")}?','{__("Are you sure")}?', function(r) {
						if (r) {
							page_loading('{__("Deleting...")}');
							$.post(base_url+'manager/reciepes_reciepes/delete', {
								reciepe_id:	reciepe_id
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
		<td>{$data.admin_title|strip_tags}</td>
		<td class="right">{if $data.order_index != 0}{$data.order_index|escape}{/if}</td>
		<td>
			<a class="button" href="manager/reciepes_reciepes/edit/{$data.id}">{__('Edit')}</a>
			<a class="button" action="product_delete">{__('Delete')}</a>
		</td>
	</tr>
{/if}