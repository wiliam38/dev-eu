{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=9}

{if $action eq "list"}
	<div class="title">
		{__('Orders')}
	</div>
	
	<table class="data_table">
		<colgroup>
			<col style="width: 30px;"/>
			<col style="width: 100px;"/>
			<col style="width: 100px;"/>
			<col style="width: 120px;"/>
			<col style="width: 180px;"/>
			<col style="width: 100px;"/>
			<col style="width: 100px;"/>
			<col style="width: 100px;"/>
			<col style="width: 320px;"/>
		</colgroup>
	
		<thead>
			<tr class="filter">
				<th colspan="{$cols}">
					<form method="post" action="manager/orders_orders/load"> 
						<span style="line-height: 21px;">{__('Search')}:</span>
						<input type="text" name="filter_search" value="{$filter.search|default:''}" style="width: 150px;"/>		
								
						<span style="line-height: 21px; margin-left: 15px;">{__('Status')}:</span>
						<select id="filter_status_id" multiple="multiple" style="width: 150px;">
							{foreach item="status" from=$order_status}
								<option value="{$status.id}" {if $status.id|in_array:$filter.status_id|default:array()}selected{/if}>{$status.description}</option>
							{/foreach}						
						</select>
						<button type="submit" class="button" style="margin-left: 15px;">{__('Search')}</button>
						
						<button type="button" class="button" onclick="exportXmlPopup(this);" style="float: right; margin-left: 15px; padding-left: 10px; padding-right: 10px;">{__('Export .xml')}</button>
					</form>
				</th>
			</tr>
			<tr>
				<th></th>
				<th>
					{__('Date')}
					<div class="order-by {$filter.order_by|default:''|orderby:'1'}" onclick="order_by(1, this)"></div>
				</th>
				<th>
					{__('Order Nr.')}
					<div class="order-by {$filter.order_by|default:''|orderby:'2'}" onclick="order_by(2, this)"></div>
				</th>
				<th>
					{__('Total')}
					<div class="order-by {$filter.order_by|default:''|orderby:'3'}" onclick="order_by(3, this)"></div>
				</th>
				<th>
					{__('Buyer Name')}
					<div class="order-by {$filter.order_by|default:''|orderby:'4'}" onclick="order_by(4, this)"></div>
				</th>
				<th>
					{__('Pay Status')}
					<div class="order-by {$filter.order_by|default:''|orderby:'7'}" onclick="order_by(7, this)"></div>
				</th>
				<th>
					{__('Pay Date')}
					<div class="order-by {$filter.order_by|default:''|orderby:'8'}" onclick="order_by(8, this)"></div>
				</th>
				<th>
					{__('Status')}
					<div class="order-by {$filter.order_by|default:''|orderby:'5'}" onclick="order_by(5, this)"></div>
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$orders}
				{include file="$this_file" action="view"}
			{foreachelse}
				<tr>
					<td colspan="{$cols}">No data!</td>
				</tr>
			{/foreach}
		</tbody>
		<tfoot>
			<tr class="add">
				<td colspan="{$cols}">
					<form method="post" action="{$base_url}manager/orders_orders/load" id="paginate_form">
						<input type="hidden" name="order_by" value="{$filter.order_by|default:''|escape}"/>
						<input type="hidden" name="filter_search" value="{$filter.search|default:''|escape}"/>
						{foreach item="status_id" from=$filter.status_id|default:array()}<input type="hidden" name="filter_status_id[]" value="{$status_id|escape}"/>{/foreach}
						<input type="hidden" name="page" value="{$paginate.page|default:''|escape}"/>
						<input type="submit" style="display: none;"/>
		
						{include file="{$smarty.current_dir}/../../global/manager_data_pages.tpl"}
					</form>
				</td>
			</tr>
		</tfoot>
	</table>	
{/if}

{if $action eq "view"}
	<tr data-id="{$data.id}">
		<td><a href="#details" class="ui-icon ui-icon-plus show-details" onclick="toggleOrderDetails(this); return false;"></a></td>
		<td style="text-align: right;">{$data.date|date_format:'%d-%b-%Y'}</td>
		<td style="text-align: right;">{if $data.number && $data.number != '000'}19B{$data.number}{/if}</td>
		<td style="text-align: right;">{$data.total_total|number_format:2:'.':''} {$data.curr_symbol}</td>
		<td>{if trim($data.company) != ''}{$data.company|strip_tags}{else}{$data.contact_name|strip_tags}{/if}</td>
		<td class="center"><a href="#pay_status" class="black" onclick="setOrderPayStatus('{$data.id}','{if $data.pay_status_id == 10}1{else}10{/if}',this); return false;">{$data.pay_status_description|strip_tags}</a></td>
		<td style="text-align: right;">{$data.pay_date|date_format:'%d-%b-%Y'}</td>
		<td class="center">{$data.status_description|strip_tags}</td>
		<td>
			{if $data.status_id == 1}
				<button onclick="setOrderStatus('{$data.id}',10,this);" class="button">Iesniegts</button>
				<button onclick="setOrderStatus('{$data.id}',5,this);" class="button">Atcelts</button>
			{elseif $data.status_id == 5}
				<button onclick="setOrderStatus('{$data.id}',1,this);" class="button">Nav iesn.</button>
				<button class="button" style="opacity: 0;">&nbsp;</button>
			{elseif $data.status_id == 10}
				<button onclick="setOrderStatus('{$data.id}',20,this);" class="button">Pieņemts</button>
				<button onclick="setOrderStatus('{$data.id}',5,this);" class="button">Atcelts</button>
			{elseif $data.status_id == 20 || $data.status_id == 30}
				<button onclick="issueInvoice(this, '{$data.id}', 1);" class="button">Izsniegt</button>
				<button onclick="setOrderStatus('{$data.id}',5,this);" class="button">Atcelts</button>
			{elseif $data.status_id == 40}
				<button onclick="setOrderStatus('{$data.id}',50,this);" class="button" {if $data.pay_status_id != 10}disabled="disabled"{/if}>Izpildīts</button>
				<button onclick="setOrderStatus('{$data.id}',5,this);" class="button">Atcelts</button>
			{elseif $data.status_id == 50}
				<button class="button" style="opacity: 0;">&nbsp;</button>
				<button onclick="setOrderStatus('{$data.id}',5,this);" class="button">Atcelts</button>
			{/if}			
			
			<button class="button" onclick="window.open('{$base_url}manager/orders_orders/bill/{$data.id}');" style="margin-left: 10px;">{__('Rēķins')}</button>
			{if $data.status_id >= 30}
				<button class="button" onclick="viewInvoice(this,'{$data.id}');">{__('Pavadzīme')}</button>
			{/if}
		</td>
	</tr>
{/if}

{if $action == 'details'}
	<div class="order-details">
		<table style="width: 100%; margin: 0px 0px 10px 0px;" class="data_table">
			<tbody>
				<tr><th style="width: 200px; text-align: left;">Maksātājs</th><td class="border-bottom">
					{$order.contact_name}
					<button type="button" style="float: right;" onclick="recreateInvoices(this, '{$order.id}');">Atjaunot .pdf rēķinus</button>
				</td></tr>
				<tr><th style="text-align: left;">Uzņēmums</th><td class="border-bottom">{$order.company}</td></tr>
				<tr><th style="text-align: left;">Pers. kods vai Reģ. Nr.</th><td class="border-bottom">{$order.reg_nr}</td></tr>
				<tr><th style="text-align: left;">PVN Nr.</th><td class="border-bottom">{$order.vat_nr}</td></tr>
				<tr><th style="text-align: left;">Piegādes adrese</th><td class="border-bottom">
					{$order.shipping_info|escape} {if $order.shipping_id == 5}({$order.shipping_statoil_address}){/if}
					{if trim($order.address) != ''} - {$order.address}{/if}
					{if trim($order.shipping_pickup_time) != ''} - {$order.shipping_pickup_time}{/if}
				</td></tr>
				<tr><th style="text-align: left;">Tālrunis</th><td class="border-bottom">{$order.phone}</td></tr>
				<tr><th style="text-align: left;">Maksājuma veids</th><td class="border-bottom">{$order.pay_type_name}</td></tr>
				<tr><th style="text-align: left;">Darījuma veids</th><td class="border-bottom">Preču pārdošana</td></tr>
				<tr><th style="text-align: left;">Komentārs</th><td class="border-bottom">{$order.notes}</td></tr>
				<tr style="background: #E0E0E0;">
					<th style="text-align: left;">Bezmaksas kafija</th>
					<td class="border-bottom" {if $order.status_id < 20}style="color: #999999;"{/if} {if $order.sum_of_coffee_gift_amount > 0}title="{if $order.coffee_gift_status_id == 10 && $order.coffee_gift_expired == 1}{__('Expired')}{else}{__($order.coffee_gift_status_description)}{/if}"{/if}>
						{if $order.sum_of_coffee_gift_amount > 0}
							{if $order.status_id >= 20}
								<a href="#none" class="black {if $order.coffee_gift_status_id != 10 || $order.coffee_gift_expired == 1}red{/if}" onclick="coffee_gift(this); return false;">{$order.sum_of_coffee_gift_amount|number_format:2:'.':''} {$order.curr_symbol}</a>
							{else}
								{$order.sum_of_coffee_gift_amount|number_format:2:'.':''} {$order.curr_symbol}
							{/if}
						{/if}
					</td>
				</tr>
			</tbody>
		</table>
		
		<table style="width: 100%;" class="data_table">
			<thead>
				<tr>
					<th style="width: 40px;">Nr.</th>
					<th style="width: 130px;">Kods</th>
					<th>Nosaukums</th>
					<th style="width: 60px;">Daudz.</th>
					<th style="width: 60px;">Mērv.</th>
					<th style="width: 90px;">Cena bez PVN ({$order.curr_name})</th>
					<th style="width: 70px;">Atlaide ({$order.curr_name})</th>
					<th style="width: 120px;">Summa bez PVN ar atlaidi ({$order.curr_name})</th>
					<th style="width: 100px;">Statuss</th>
				</tr>
			</thead>
			<tbody>
				{foreach item="data" from=$products name="products"}
					<tr>
						<td class="center">{$smarty.foreach.products.iteration}</td>
						<td>{$data.product_code}</td>
						<td>{$data.product_reference}</td>
						<td class="right">
							{$data.qty|number_format:0:'.':''}
							{if $data.order_qty > 0}<div style="white-space: nowrap;">({$data.order_qty|number_format:0:'.':''} jāpasūta)</div>{/if}
						</td>
						<td>gab.</td>
						<td class="right">{$data.original_price|number_format:4:'.':''}</td>
						<td class="right">
							{if (($data.original_price|number_format:4:'.':'')-($data.price|number_format:4:'.':'')) > 0} 
								{((($data.original_price|number_format:4:'.':'') - ($data.price|number_format:4:'.':'')))|number_format:4:'.':''}
							{else}
								-
							{/if}
						</td>
						<td class="right">{(($data.price|number_format:4:'.':'')*$data.qty)|number_format:2:'.':''}</td>
						<td class="center">
							{if $data.balance_qty > 0}
								{if $data.balance_qty != $data.qty}Daļēji izsniegts{else}Nav izsniegts{/if}<br/>
								<input type="checkbox" name="issue_order_detail_id[]" value="{$data.id}" {if $order.status_id >= 50 || $order.status_id < 20}disabled="disabled"{/if} style="vertical-align: middle;"/>
								<input type="text" name="issue_order_detail_qty_{$data.id}" value="{$data.balance_qty|number_format:0:'.':''}" {if $order.status_id >= 50 || $order.status_id < 20}disabled="disabled"{/if} style="width: 70px; height: 15px; vertical-align: middle; font-size: 11px;"/>
							{else}
								Izsniegts
							{/if}							
						</td>
					</tr>
				{/foreach}
				
				<tr>
					<td colspan="7"><b>Kopsumma bez PVN ({$order.curr_name})</b></td>
					<td class="right">{$total.price_wo_vat|number_format:2:'.':''}</td>
					<td></td>
				</tr>
				
				<tr>
					<td colspan="7"><b>Piegāde bez PVN ({$order.curr_name})</b></td>
					<td class="right">{$order.shipping_total|number_format:2:'.':''}</td>
					<td class="center">
						{if empty($order.shipping_issued)}
							<input type="checkbox" name="issue_shipping" value="1" style="float: left;" {if $order.status_id >= 50 || $order.status_id < 20}disabled="disabled"{/if}/>
							Nav izsniegts
						{else}
							Izsniegts
						{/if}						
					</td>
				</tr>
				
				<tr>
					<td colspan="7"><b>PVN {if $order.no_vat!=1}21{else}0{/if}% ({$order.curr_name})</b></td>
					<td class="right">{$total.vat|number_format:2:'.':''}</td>
					<td rowspan="2" class="center" style="vertical-align: middle;">
						<button type="button" onclick="issueInvoice(this, '{$order.id}', 2);" {if $order.status_id >= 50 || $order.status_id < 20}disabled="disabled"{/if}>Izsniegt izvēlētos</button>
					</td>
				</tr>			
				
				<tr>
					<td colspan="7"><b>Summa apmaksai ar PVN ({$order.curr_name})</b></td>
					<td class="right"><b>{$total.total_vat|number_format:2:'.':''}</b></td>
				</tr>
			</tbody>
		</table>		
	</div>
{/if}

{if $action == 'popup_invoices'}
	<h3 style="margin: 5px 0px 5px 0px;">Pavadzīmes:</h3>
	{foreach item="data" from=$invoices}
		<a href="{$base_url}manager/orders_orders/invoice/{$data.id}" target="_blank" style="margin: 3px 0px 3px 20px; display: inline-block;">{$data.date|date_format:'%d-%b-%Y'} - 19B{$data.full_number}</a>
	{/foreach}
{/if}