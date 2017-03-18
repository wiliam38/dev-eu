{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=7}

{if $action eq "list"}
	<div class="title">
		Sellers
	</div>
	{*
	<form method="post" action="manager/modules/interiorproducts"> 
		<table class="filter_table">
			<tr>
				<th>Name:</th>
				<td><input name="filter_name" value="{$filter.name}"/></td>
				<th>Category:</th>
				<td>
					<select id="filter_category_id" multiple="multiple">
						{include file=$this_file action="category_filter" parent_id=$product_parent_id level="0"}
					</select>
				</td>			
				<th>Status:</th>
				<td>
					<select id="filter_status_id" multiple="multiple">
						{foreach item="status" from=$product_status}
							<option value="{$status.id}" {if $status.id|in_array:$filter.status_id}selected{/if}>{$status.name}</option>
						{/foreach}						
					</select>
				</td>
				<td><button action="settings_show" style="margin-left: 20px">Show</button></td>			
			</tr>
		</table>
	</form>
	*}
	<table class="data_table">
		<thead>
			<tr>
				<th style="width: 250px;">Name</th>
				<th style="width: 100px;">City</th>
				<th style="width: 130px;">Reg Nr. / VAT</th>
				<th style="width: 100px;">Phone</th>
				<th style="width: 150px;">E-mail</th>
				<th style="width: 80px;">Status</th>
				<th style="width: 130px;"></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$categories}
				<tr data-id="{$data.id}">
					<td>{$data.name|strip_tags}</td>
					<td>{$data.city_name}</td>
					<td>{$data.vat}</td>
					<td>{$data.phone}</td>
					<td>{$data.email}</td>
					<td>{$data.status_description}</td>
					<td>
						<input type="button" class="button" onclick="window.location='manager/products_mfrs/edit/{$data.id}';" value="Edit"/>
						<input type="button" class="button" action="mfr_delete" value="Delete"/>
					</td>
				</tr>
			{/foreach}	
		</tbody>
		<tfoot>			
			<tr>
				<td colspan="{$cols}">
					<a class="button" href="manager/products_mfrs/edit/new">Add</a>
				</td>
			</tr>
		</tfoot>
	</table>	
{/if}