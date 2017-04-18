{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=$languages|@count+3}

{if $action eq "load"}
	<div class="title">
		{__('Lexicons')}
	</div>
	
	<table class="data_table lexicon_table" style="min-width: 1000px;">
		<colgroup>
			<col style="width: 200px"/>
			{foreach item=lang from=$languages}<col style="width: 200px"/>{/foreach}
			<col style="width: 130px;"/>
			<col style="width: 150px;"/>
		</colgroup>
	
		<thead>
			<tr class="filter">
				<td colspan="{$cols}">
					<form method="post" action="{$base_url}manager/lexicon" id="filter_form">
						<input type="hidden" name="order_by" value="{$filter_data.order_by|default:''|escape}"/>
						
						<span style="line-height: 21px;">{__('Search')}:</span> 
						<input type="text" name="search" value="{$filter_data.search|default:''|escape}" style="width: 300px;"/>
						
						<span style="line-height: 21px; margin-left: 15px;">{__('Category')}:</span>
						<select name="category_name" style="width: 177px;">
							<option value="">--- {__('ALL')} ---</option>
							{foreach item="data" from=$categories}
								{if !empty($data.value)}<option value="{$data.value|escape}" {if $data.value == $filter_data.category_name|default:''}selected="selected"{/if}>{__($data.value)|escape}</option>{/if}
							{/foreach}
						</select>
						<button type="submit" class="button" style="margin-left: 10px">{__('Search')}</button>
						
						<button type="button" class="button" onclick="lexicon_generate_files();" style="float: right;">{__('Generate language files')}</button>
					</form>
				</td>
			</tr>
			<tr>
				<th>
					{__('System name')}
					<div class="order-by {$filter_data.order_by|default:''|orderby:'1'}" onclick="order_by(1, this)"></div>
				</th>
				{foreach item=lang from=$languages}
					<th>
						{__($lang.name|strip_tags)}<br>
						{__('translation')}
					</th>
				{/foreach}
				<th>
					{__('Last edit')}
					<div class="order-by {$filter_data.order_by|default:''|orderby:'2'}" onclick="order_by(2, this)"></div>
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody id="data_div">
			{foreach item="data" from=$lexicons}
				{include file=$this_file action='view'}
			{/foreach}
		</tbody>
		<tfoot>
			<tr class="add">
				<td colspan="{$cols}">
					<form method="post" action="{$base_url}manager/lexicon" id="paginate_form">
						<input type="hidden" name="order_by" value="{$filter_data.order_by|default:''|escape}"/>
						<input type="hidden" name="search" value="{$filter_data.search|default:''|escape}"/>
						<input type="hidden" name="category_name" value="{$filter_data.category_name|default:''|escape}"/>
						<input type="hidden" name="page" value="{$paginate.page|default:''|escape}"/>
						<input type="submit" style="display: none;"/>
						
						<button type="button" onclick="lexicon_add(this);" class="button">{__('Add')}</button>
		
						{include file="{$smarty.current_dir}/../../global/manager_data_pages.tpl"}
					</form>
				</td>
			</tr>
		</tfoot>
	</table>
{/if}

{if $action eq 'view'}
	<tr>		
		<td>{$data.name|strip_tags}</td>
		{foreach item=lang from=$languages}
			<td>{$data.lang[$lang.id].name|default:''|strip_tags|truncate:40:' ...'}</td>
		{/foreach}
		<td class="right">{if $data.user_datetime != '0000-00-00 00:00:00'}{$data.user_datetime|date_format:'%d.%m.%Y  %H:%M'}{/if}</td>
		<td>		
			<a class="button" onclick="lexicon_edit(this,'{$data.id}')">{__('Edit')}</a>
			{if $data.type_id eq "1"}
				<a class="button" onclick="lexicon_delete(this,'{$data.id}')">{__('Delete')}</a>
			{/if}
		</td>
	</tr>
{/if}

{if $action eq 'edit'}
	<tr class="edit">
		<td>
			<input type="hidden" name="id" value="{$data.id|default:'new'}"/>
			
			{if $data.type_id eq 1} <input type="text" name="system_name" value="{$data.name}"/>
			{else}{$data.name|strip_tags}{/if}
		</td>
		{foreach item=lang from=$languages}
			<td style="white-space: nowrap;">
				<input type="hidden" name="lang_id[]" value="{$lang.id}"/>
				<input type="hidden" name="lexicon_id[]" value="{$data.lang[$lang.id].id|default:'new'}"/>
				<input type="text" name="name[]" class="rich_text" value="{$data.lang[$lang.id].name|default:''|escape:'html'}"/>
				<a href="#none" onclick="lexicon_rich_text(this); return false;" class="rich_text_icon">
					<img src="assets/modules/manager/global/img/text-editor.png" alt="E"/>
				</a>
			</td>
		{/foreach}
		<td class="right">{if $data.user_datetime != '0000-00-00 00:00:00'}{$data.user_datetime|date_format:'%d.%m.%Y  %H:%M'}{/if}</td>
		<td>
			<a class="button" onclick="lexicon_save(this,'{$data.id}');">{__('Save')}</a>
			<a class="button" onclick="lexicon_view(this,'{$data.id}');">{__('Cancel')}</a>
		</td>
	</tr>
{/if}