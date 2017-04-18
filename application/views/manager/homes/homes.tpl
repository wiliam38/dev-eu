{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=3}

{if $action eq "list"}
	<div class="title">
		{__('Home pages')}
	</div>
	
	<table class="data_table">
		<thead>
			<tr>
				<th style="width: 400px;">{__('Title')}</th>
				<th style="width: 200px;">{__('Order Index')}</th>
				<th style="width: 130px;"></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$homes}
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
					<form method="post" action="{$base_url}manager/homes_homes" id="paginate_form">
						<input type="hidden" name="page" value="{$paginate.page|default:''|escape}"/>
						<input type="submit" style="display: none;"/>
						
						<a class="button" href="manager/homes_homes/edit/new">{__('Add')}</a>
		
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
				var home_id = $(tr).attr('data-id');
				
				if (home_id != 'new') {
					jConfirm('{__("Are you sure to delete this Home")}?','{__("Are you sure")}?', function(r) {
						if (r) {
							page_loading('{__("Deleting...")}');
							$.post(base_url+'manager/homes_homes/delete', {
								home_id:	home_id
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
		<td>{__($data.order_index|strip_tags)}</td>
		<td>
			<a class="button" href="manager/homes_homes/edit/{$data.id}">{__('Edit')}</a>
			<a class="button" action="product_delete">{__('Delete')}</a>
		</td>
	</tr>
{/if}