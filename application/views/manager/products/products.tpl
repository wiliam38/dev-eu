{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=8}

{if $action eq "list"}
	<div class="title">
		{__($page_title)}
	</div>
	
	<table class="data_table" id="product_table">
		<colgroup>
			<col style="width: 20px;"/>
			<col style="width: 250px;"/>
			<col style="width: 250px;"/>
			<col style="width: 200px;"/>
			<col style="width: 100px;"/>
			<col style="width: 100px;"/>
			<col style="width: 120px;"/>
			<col style="width: 150px;"/>
		</colgroup>
		
		<thead>
			<tr class="filter">
				<th colspan="{$cols}" style="white-space: nowrap;">
					<form method="post" action="{$base_url}manager/{$link}/load" id="filter_form">
						<input type="hidden" name="order_by" value="{$filter.order_by|default:''|escape}"/>
						
						<span style="line-height: 21px;">{__('Search')}:</span> 
						<input type="text" name="search" value="{$filter.search|default:''}" style="width: 200px;"/>
						
						<span style="line-height: 21px; margin-left: 15px;">{__('Category')}:</span> 
						<select id="category_id" name="filter_category_id" multiple="multiple" style="width: 140px;">
							{include file=$this_file action="category_filter" parent_id="0" level="0"}
						</select>		
						
						<span style="line-height: 21px; margin-left: 15px;">{__('Status')}:</span> 
						<select id="status_id" name="filter_status_id" multiple="multiple" style="width: 100px;">
							{foreach item="status" from=$product_status}
								<option value="{$status.id}" {if in_array($status.id,$filter.status_id|default:array())}selected="selected"{/if}>{__($status.description)}</option>
							{/foreach}						
						</select>
						
						<button type="submit" class="button" style="margin-left: 10px">{__('Search')}</button>						
						<a href="{$base_url}manager/products_products/order" class="button" style="width: 65px; margin-right: 10px;">{__('Order')}</a>
					</form>
				</th>
			</tr>
			<tr>				
				<th></th>
				<th>
					{__('Reference')}
					<div class="order-by {$filter.order_by|default:''|orderby:'1'}" onclick="order_by(1, this)"></div>
				</th>
				<th>
					{__('Title')}
					<div class="order-by {$filter.order_by|default:''|orderby:'2'}" onclick="order_by(2, this)"></div>
				</th>
				<th>
					{__('Category')}
					<div class="order-by {$filter.order_by|default:''|orderby:'3'}" onclick="order_by(3, this)"></div>
				</th>
				<th>
					{__('Additional options')}
				</th>
				<th>
					{__('Balance')}
					<div class="order-by {$filter.order_by|default:''|orderby:'5'}" onclick="order_by(5, this)"></div>
				</th>
				<th>
					{__('Status')}
					<div class="order-by {$filter.order_by|default:''|orderby:'4'}" onclick="order_by(4, this)"></div>
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{include file=$this_file action="show"}
		</tbody>
		<tfoot>
			<tr class="add">
				<td colspan="{$cols}">
					<form method="post" action="{$base_url}manager/{$link}/load" id="paginate_form">
						<input type="hidden" name="order_by" value="{$filter.order_by|default:''|escape}"/>
						<input type="hidden" name="search" value="{$filter.search|default:''|escape}"/>
						{foreach item="category_id" from=$filter.category_id|default:array()}<input type="hidden" name="category_id[]" value="{$category_id|escape}"/>{/foreach}
						{foreach item="status_id" from=$filter.status_id|default:array()}<input type="hidden" name="status_id[]" value="{$status_id|escape}"/>{/foreach}
						<input type="hidden" name="page" value="{$paginate.page|default:''|escape}"/>
						<input type="submit" style="display: none;"/>
						
						<button type="button" onclick="$(this).closest('form').attr('action','{$base_url}manager/{$link}/edit/new').submit();" class="button">{__('Add')}</button>
		
						{include file="{$smarty.current_dir}/../../global/manager_data_pages.tpl"}
					</form>
				</td>
			</tr>
		</tfoot>
	</table>	
	
	<script type="text/javascript">
		var product_link = '{$link}';
		var product_delete_title = '{__("Are you sure?")}';
		var product_delete_msg = '{__("Are you sure to delete this Product?")}';
	</script>
{/if}

{if $action == 'show'}
	{foreach item="data" from=$products}
		{include file="$this_file" action="view"}
	{foreachelse}
		<tr>
			<td colspan="{$cols}">{__('No data')}!</td>
		</tr>
	{/foreach}
{/if}

{if $action eq 'category_filter'}
	{foreach item=status from=$categories}
		{if $status.parent_id eq $parent_id}
			<option value="{$status.id}"{if in_array($status.id,$filter.category_id|default:array())}selected{/if}>
				{section name=waistsizes start=0 loop=$level step=1}&nbsp;&nbsp;&nbsp;&nbsp;{/section}
				{$status.title}
			</option> 
			{include file=$this_file action="category_filter" parent_id=$status.id level=$level+1}
		{/if}
	{/foreach}
{/if}

{if $action eq "view"}
	<tr data-id="{$data.id}">
		<td><a href="#details" class="ui-icon ui-icon-plus show-details" onclick="toggleProductDetails(this); return false;"></a></td>
		<td>{$data.reference_reference|strip_tags}</td>
		<td>{$data.lang[1].1_title|default:$data.lang[2].1_title|default:$data.lang[3].1_title|default:''|strip_tags}</td>
		<td>{$data.category_list|strip_tags|truncate:'30':'...'}</td>
		<td>
			{if $data.discount_active == 1}%{/if}
			{if $data.coffee_gift_active == 1}COFFEE{/if}
			{if $data.gift == 1}GIFT{/if}
			{if $data.new == 1}NEW{/if}			
		</td>
		<td class="right balance">{$data.balance|number_format:0:'.':''}</td>
		<td>{__($data.status_description|strip_tags)}</td>
		<td>
			<button type="button" onclick="$('#paginate_form').attr('action', '{$base_url}manager/{$link}/edit/{$data.id}').submit();">{__('Edit')}</button>
			<a class="button" action="product_delete">{__('Delete')}</a>
		</td>
	</tr>
{/if}

{if $action == 'details'}
	<div class="order-details">
		<table class="data_table prices_table" style="margin: 0px; width: 100%;">
			<colgroup>
				<col style="width: 120px;"/>
				<col style="width: 130px;"/>
				<col style="width: 400px;"/>
				<col style="width: 120px;"/>
				<col style="width: 160px;"/>
			</colgroup>
		
			<thead>
				<tr>
					<th>{__('Image')}</th>
					<th>{__('Code')}</th>
					<th>{__('Reference')}</th>
					<th>{__('Balance')}</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{foreach item="data" from=$prices name="prices"}
					<tr data-id="{$data.id}">
						<td class="center image_td">
							<img {if $data.image_src|default:'' ne ''}src="{$data.image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
						</td>
						<td>{$data.code|default:''}</td>
						<td>{$data.reference|default:''}</td>
						<td class="right balance">{$data.balance|number_format:0:'.':''}</td>
						<td>
							<button type="button" class="button" onclick="balanceUpdate(this); return false;">{__('Edit')}</button>
							<button type="button" class="button" onclick="balanceAdd(this); return false;">{__('Add')}</button>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{/if}