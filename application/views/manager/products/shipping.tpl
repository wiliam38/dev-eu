{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=9}

{if $action eq "load"}
	<div class="title">
		{__('Shipping terms')}
	</div>

	<table class="data_table gallery_table">
		<thead>
			<tr>
				{foreach item=lang from=$languages}
					<th width="200">
						{__($lang.name|strip_tags)}<br/>
						{__('Title')} / <span style="color: teal;">{__('Description')}</span>
					</th>
				{/foreach}
				<th rowspan="3" width="130">{__('Invoice Language')}</th>
				<th rowspan="3" width="180">{__('Price for Coffee (w/o VAT)')}</th>
				<th rowspan="3" width="180">{__('Price for Machines (w/o VAT)')}</th>
				<th rowspan="3" width="180">{__('Price for Other (w/o VAT)')}</th>
				<th rowspan="3" width="100">{__('Currency')}</th>
				{*
					<th rowspan="3" width="180">{__('Price (w/o VAT)')} / {__('Currency')}</th>
					<th rowspan="3" width="100">{__('Price qty')}</th>						
				*}
				<th rowspan="3" width="100">{__('VAT')}</th>
				<th rowspan="3" width="100">{__('Order Index')}</th>			
				<th rowspan="3" width="100">{__('Status')}</th>				
				<th rowspan="2" width="150"></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$shippings}
				{include file=$this_file action="view"}
			{/foreach}
		</tbody>
		<tfoot>			
			<tr data-id="new">
				<td colspan="{count($languages)+$cols}">
					<button type="button" class="button" onclick="shipping_edit(this);">{__('Add')}</button>
				</td>
			</tr>
		</tfoot>
	</table>	
{/if}

{if $action eq "view"}
	<tr data-id="{$data.id}">
		{foreach item=lang from=$languages}
			<td>
				{$data.lang[$lang.id].title|default:''|escape}
				<div style="color: teal;">{$data.lang[$lang.id].description|default:''|escape}</div>
			</td>
		{/foreach}
		<td>{__($data.invoice_language_name|strip_tags)}</td>
		<td>{$data.price_coffee|number_format:4:'.':''}</td>
		<td>{$data.price_machines|number_format:4:'.':''}</td>
		<td>{$data.price_other|number_format:4:'.':''}</td>
		<td>{$data.currency_name|escape}</td>
		<td style="display: none;">{$data.price_qty|number_format:0:'.':''}</td>
		<td>{$data.vat_type_description|escape}</td>
		<td>{$data.order_index|escape}</td>
		<td>{$data.status_description|escape}</td>
		<td>
			<button type="button" class="button" onclick="shipping_edit(this);">{__('Edit')}</button>
			<button type="button" class="button" onclick="shipping_delete(this);">{__('Delete')}</button>
			{if $data.id == 5}
				<a class="button" href="manager/types?category_name=shippings_statoil_id" target="_blank" style="margin: 0px 2px;">{__('Address')}</a>
			{/if}
		</td>
	</tr>
{/if}

{if $action eq "edit"}
	<tr data-id="{$data.id|default:'new'|escape}" class="edit">
		{foreach item=lang from=$languages}
			<td>
				<input type="hidden" name="content_id[]" value="{$data.lang[$lang.id].id|default:'new'|escape}"/>
				<input type="hidden" name="language_id[]" value="{$lang.id|escape}"/>
				
				<input type="text" name="title[]" value="{$data.lang[$lang.id].title|default:''|escape}"/><br/>
				<input type="text" name="description[]" value="{$data.lang[$lang.id].description|default:''|escape}"/>
			</td>
		{/foreach}
		<td>
			<select name="invoice_language_id" style="width: 70px;">
				{foreach item="inv_lang" from=$invoice_languages}
					<option value="{$inv_lang.id}" {if $data.invoice_language_id|default:'' == $inv_lang.id}selected="selected"{/if}>{__($inv_lang.name|strip_tags)}</option>
				{/foreach}								
			</select>	
		</td>
		<td>
			<input type="hidden" name="id" value="{$data.id|default:'new'|escape}"/>		
			<input type="text" name="price_coffee" value="{$data.price_coffee|default:0|number_format:4:'.':''}" style="width: 100px;"/>		
		</td>
		<td>
			<input type="text" name="price_machines" value="{$data.price_machines|default:0|number_format:4:'.':''}" style="width: 100px;"/>		
		</td>
		<td>
			<input type="text" name="price_other" value="{$data.price_other|default:0|number_format:4:'.':''}" style="width: 100px;"/>		
		</td>
		<td>
			<select name="currency_id" style="width: 55px;">
				{foreach item="curr" from=$currencies}
					<option value="{$curr.id}" {if $data.currency_id|default:'' == $curr.id}selected="selected"{/if}>{$curr.name}</option>
				{/foreach}								
			</select>
		</td>
		<td style="display: none;"><input type="text" name="price_qty" value="{$data.price_qty|default:0|number_format:0:'.':''}"/></td>
		<td>
			<select name="vat_type_id" style="width: 70px;">
				{foreach item="vat" from=$vat_types}
					<option value="{$vat.id}" {if $data.vat_type_id|default:'' == $vat.id}selected="selected"{/if}>{$vat.description}</option>
				{/foreach}								
			</select>	
		</td>
		<td><input type="text" name="order_index" value="{$data.order_index|default:0|escape}"/></td>
		<td>
			<select name="status_id" style="width: 100px;">
				{foreach item="s" from=$status}
					<option value="{$s.id}" {if $data.status_id|default:'' == $s.id}selected="selected"{/if}>{$s.description}</option>
				{/foreach}								
			</select>
		</td>
		<td>
			<button type="button" class="button" onclick="shipping_save(this);">{__('Save')}</button>
			<button type="button" class="button" onclick="shipping_view(this);">{__('Cancel')}</button>
		</td>
	</tr>
{/if}