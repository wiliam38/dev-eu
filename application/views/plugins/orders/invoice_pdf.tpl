<!DOCTYPE html> 
<html>
	<head>
		<base href="{$base_url}"/>
		<style> 
			body {
				font-family: Arial, Helvetica, sans-serif;
				font-size: 14px;
			}
			.center { text-align: center; }
			.right { text-align: right; }	
			.border-bottom { border-bottom: 1px solid #000000; }
			.title {
				font-size: 16px;
				font-weight: bold;
			}
			table tr { height: 30px; }
			.products_table {
				width: 100%; 
				margin-top: 20px;
				border-collapse: collapse;
			}
			.products_table td,
			.products_table th {
				border: 1px solid #000000;
				padding: 2px;
			}
		</style>
		
	</head>
	<body>
		<div class="center"><img src="assets/plugins/orders/img/19bar_logo.png"/></div>
		<div class="center" style="font-size: 24px; margin-top: 20px;">PASŪTĪJUMA RĒĶINS</div>
		
		<table style="width: 100%; margin-top: 40px;" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td class="border-bottom title" style="width: 40%;">Datums: {$order.date|date_format:'%d.%m.%Y'}</td>
					<td class="border-bottom title right"  style="width: 60%;">Rēķina Nr. 19B{$order.number}</td>
				</tr>
				
				<tr><td>Piegādātājs</td><td class="border-bottom"><b>{$company.name}</b></td></tr>
				<tr><td>PVN/Reģ. Nr.</td><td class="border-bottom"><b>{$company.reg_nr}</b></td></tr>
				<tr><td>Adrese</td><td class="border-bottom"><b>{$company.address}</b></td></tr>
				<tr><td>Banka</td><td class="border-bottom">{$company.bank}</td></tr>
				<tr><td>Kods</td><td class="border-bottom">{$company.bank_code}</td></tr>
				<tr><td>Konts</td><td class="border-bottom">{$company.account}</td></tr>
				
				<tr><td colspan="2">&nbsp;</td></tr>
				
				<tr><td>Maksātājs</td><td class="border-bottom"><b>{$order.contact_name}</b></td></tr>
				<tr><td>Pers. kods vai PVN/Reģ. Nr.</td><td class="border-bottom">{$order.reg_nr}</td></tr>
				<tr><td>Piegādes adrese</td><td class="border-bottom">{$order.address}</td></tr>
				<tr><td>Tālrunis</td><td class="border-bottom">{$order.phone}</td></tr>
				<tr><td>Maksājuma veids</td><td class="border-bottom">{$order.pay_type_name}</td></tr>
				<tr><td>Darījuma veids</td><td class="border-bottom">Preču pārdošana</td></tr>
				<tr><td>Komentārs</td><td class="border-bottom">{$order.notes}</td></tr>
			</tbody>
		</table>
		
		<table class="products_table" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th style="width: 40px;">Nr.</th>
					<th>Nosaukums</th>
					<th style="width: 90px;">Daudzums</th>
					<th style="width: 90px;">Cena ar PVN ({$order.curr_name})</th>
					<th style="width: 90px;">Atlaide ({$order.curr_name})</th>
					<th style="width: 90px;">Summa ar PVN un atlaidi ({$order.curr_name})</th>
				</tr>
			</thead>
			<tbody>
				{foreach item="data" from=$products name="products"}
					<tr>
						<td class="center">{$smarty.foreach.products.iteration}</td>
						<td>{$data.product_reference}</td>
						<td class="right">{$data.qty|number_format:0:'.':''}</td>
						<td class="right">{$data.full_original_price|number_format:2:'.':''}</td>
						<td class="right">
							{if (($data.full_original_price|number_format:2:'.':'')-($data.full_price|number_format:2:'.':'')) > 0} 
								{((($data.full_original_price|number_format:2:'.':'') - ($data.full_price|number_format:2:'.':'')))|number_format:2:'.':''}
							{else}
								-
							{/if}
						</td>
						<td class="right">{(($data.full_price|number_format:2:'.':'')*$data.qty)|number_format:2:'.':''}</td>
					</tr>
				{/foreach}
				<tr>
					<td></td>
					<td>{$order.shipping_info|escape} {if $order.shipping_id == 5}({$order.shipping_statoil_address}){/if}</td>
					<td class="right">1</td>
					<td class="right">{$order.shipping_total|number_format:2:'.':''}</td>
					<td class="right">-</td>
					{*<td class="right">{($order.shipping_total*(1+$order.shipping_vat/100))|number_format:2:'.':''}</td>*}
					<td class="right">{$order.shipping_total|number_format:2:'.':''}</td>
				</tr>
				
				<tr>
					<td colspan="5"><b>Kopsumma bez PVN ({$order.curr_name})</b></td>
					<td class="right">{$total.l3_price_wo_discount_no_vat|number_format:2:'.':''}</td>
				</tr>
				
				{if !empty($total.l3_percents)}
					<tr>
						<td colspan="5"><b>Atlaide ({$total.l3_percents|number_format:0:'.':''}%, {$order.curr_name})</b></td>
						<td class="right">-{$total.l3_discount|number_format:2:'.':''} {$order.curr_symbol}</td>
					</tr>
				{/if}
				
				{if !empty($total.l4_percents)}
					<tr>
						<td colspan="5"><b>Atlaide ({$total.l4_percents|number_format:0:'.':''}%, {$order.curr_name})</b></td>
						<td class="right">-{$total.l4_discount|number_format:2:'.':''} {$order.curr_symbol}</td>
					</tr>
				{/if}
				
				<tr>
					<td colspan="5"><b>PVN 21% ({$order.curr_name})</b></td>
					<td class="right">{$total.vat|number_format:2:'.':''}</td>
				</tr>
				
				<tr>
					<td colspan="5"><b>Piegāde ({$order.curr_name})</b></td>
					<td class="right">{$order.shipping_total|number_format:2:'.':''}</td>
				</tr>			
				
				<tr>
					<td colspan="5"><b>Summa apmaksai ar PVN ({$order.curr_name})</b></td>
					<td class="right">{$total.total_vat|number_format:2:'.':''}</td>
				</tr>
			</tbody>
		</table>
		<br/>	
		<br/>	
		Summa vārdiem {$order.curr_name}: {$total.total_vat_lv}<br/>
		<br/>	
		{if !empty($order_coffee_gift_info)}
			{$order_coffee_gift_info}
			<br/>
		{/if}
		<br/>
		Rēķins ir sagatavots elektroniski un ir derīgs bez paraksta un zīmoga<br/>		
	</body>
</html>