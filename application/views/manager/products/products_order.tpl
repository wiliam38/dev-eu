{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=5}

{if $action eq "list"}
	<div class="title">
		{__($page_title)}
	</div>
	
	<table class="data_table" id="product_table">
		<colgroup>
			<col style="width: 700px;"/>
			<col style="width: 150px;"/>
			<col style="width: 200px;"/>
			<col style="width: 130px;"/>
			<col style="width: 35px;"/>
		</colgroup>
		
		<thead>
			<tr class="filter">
				<th colspan="{$cols}">
					<form method="post" action="{$base_url}manager/products_products/order" id="filter_form">
						<span style="line-height: 21px; margin-left: 15px;">{__('Category')}:</span>
						<select id="category_setting_value_id" name="category_setting_value_id" style="width: 400px;">
							<option value="">{__('--- SELECT ---')}</option>
							{foreach item="cat" from=$categories}
								<optgroup label="{$cat.title}">
									{foreach item="sub_cat" from=$cat.sub_categories}
										<option value="{$sub_cat.id}" {if in_array($sub_cat.id, $filter.category_setting_value_id|default:array())}selected="selected"{/if}>{$sub_cat.l_title}</option>
									{/foreach}
								</optgroup>
							{/foreach}
							{* include file=$this_file action="category_filter" parent_id="0" level="0" *}
						</select>
					</form>
				</th>
			</tr>
			<tr>
				<th>{__('Title')}</th>
				<th>{__('Reference')}</th>
				<th>{__('Category')}</th>
				<th>{__('Status')}</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$products}
				<tr id="pr-{$data.id}">
					<td>{$data.l_1_title|default:''|strip_tags}</td>
					<td>{$data.reference_reference|strip_tags}</td>
					<td>{$data.category_list|strip_tags|truncate:'30':'...'}</td>
					<td>{__($data.status_description|strip_tags)}</td>
					<td class="center">
						<input type="hidden" name="order[]" value="{$data.id}"/>
						<img src="{$base_url}assets/libs/jquery-plugins/tablednd/arrow.png" style="cursor: move;" class="handle"/>
					</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="{$cols}">{__('No data')}!</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
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