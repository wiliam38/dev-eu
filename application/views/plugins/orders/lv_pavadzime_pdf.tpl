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
		<div class="center" style="font-size: 24px; margin-top: 20px;">PAVADZĪME</div>
		
		<table style="width: 100%; margin-top: 40px;" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td class="border-bottom title" style="width: 40%;">Datums: {$invoice.date|date_format:'%d.%m.%Y'}</td>
					<td class="border-bottom title right"  style="width: 60%;">Rēķina Nr. 19B{$invoice.full_number}</td>
				</tr>
				
				<tr><td>Piegādātājs</td><td class="border-bottom"><b>{$company.name}</b></td></tr>
				<tr><td>PVN/Reģ. Nr.</td><td class="border-bottom"><b>{$company.reg_nr}</b></td></tr>
				<tr><td>Adrese</td><td class="border-bottom"><b>{$company.address}</b></td></tr>
				<tr><td>Banka</td><td class="border-bottom">{$company.bank}</td></tr>
				<tr><td>Kods</td><td class="border-bottom">{$company.bank_code}</td></tr>
				<tr><td>Konts</td><td class="border-bottom">{$company.account}</td></tr>
				
				<tr><td colspan="2">&nbsp;</td></tr>
				
				<tr><td>Maksātājs</td><td class="border-bottom"><b>
					{if !empty($invoice.company)}{$invoice.company}{else}{$invoice.contact_name}{/if}
				</b></td></tr>
				<tr><td>Pers. kods vai Reģ. Nr.</td><td class="border-bottom">{$invoice.reg_nr}</td></tr>
				<tr><td>PVN Nr.</td><td class="border-bottom">{$invoice.vat_nr}</td></tr>
				<tr><td>Piegādes adrese</td><td class="border-bottom">
					{$invoice.shipping_info|escape} {if $invoice.shipping_id == 5}({$invoice.shipping_statoil_address}){/if}
					{if trim($invoice.address) != ''} - {$invoice.address}{/if}
					{if trim($invoice.shipping_pickup_time) != ''} - {$invoice.shipping_pickup_time}{/if}
				</td></tr>
				<tr><td>Tālrunis</td><td class="border-bottom">{$invoice.phone}</td></tr>
				<tr><td>Maksājuma veids</td><td class="border-bottom">{$invoice.pay_type_name}</td></tr>
				<tr><td>Darījuma veids</td><td class="border-bottom">Preču pārdošana</td></tr>
				{if !empty($invoice.company)}<tr><td>Kontaktpersona</td><td class="border-bottom">{$invoice.contact_name}</td></tr>{/if}
				<tr><td>Komentārs</td><td class="border-bottom">{$invoice.notes}</td></tr>
			</tbody>
		</table>
		
		<table class="products_table" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th style="width: 40px;">Nr.</th>
					<th style="width: 100px;">Kods</th>
					<th>Nosaukums</th>
					<th style="width: 60px;">Daudz.</th>
					<th style="width: 60px;">Mērv.</th>
					<th style="width: 80px;">Cena bez PVN ({$invoice.curr_name})</th>
					<th style="width: 80px;">Atlaide ({$invoice.curr_name})</th>
					<th style="width: 80px;">Summa bez PVN ar atlaidi ({$invoice.curr_name})</th>
				</tr>
			</thead>
			<tbody>
				{foreach item="data" from=$products name="products"}
					<tr>
						<td class="center">{$smarty.foreach.products.iteration}</td>
						<td>{$data.product_code}</td>
						<td>{$data.product_reference}</td>
						<td class="right">{$data.qty|number_format:0:'.':''}</td>
						<td class="center">gab.</td>
						<td class="right">{$data.original_price|number_format:4:'.':''}</td>
						<td class="right">
							{if (($data.original_price|number_format:4:'.':'')-($data.price|number_format:4:'.':'')) > 0} 
								{((($data.original_price|number_format:4:'.':'') - ($data.price|number_format:4:'.':'')))|number_format:4:'.':''}
							{else}
								-
							{/if}
						</td>
						<td class="right">{(($data.price|number_format:4:'.':'')*$data.qty)|number_format:4:'.':''}</td>
					</tr>
				{/foreach}
				
				<tr>
					<td colspan="7"><b>Kopsumma bez PVN ({$invoice.curr_name})</b></td>
					<td class="right">{$total.price_wo_vat|number_format:2:'.':''}</td>
				</tr>
				
				{if $invoice.shipping == '1'}
					<tr>
						<td colspan="7"><b>Piegāde bez PVN ({$invoice.curr_name})</b></td>
						<td class="right">{$invoice.shipping_total|number_format:2:'.':''}</td>
					</tr>
				{/if}			
				
				<tr>
					<td colspan="7"><b>PVN {if $invoice.no_vat!=1}21{else}0{/if}% ({$invoice.curr_name})</b></td>
					<td class="right">{$total.vat|number_format:2:'.':''}</td>
				</tr>
				
				<tr>
					<td colspan="7"><b>Summa apmaksai ar PVN ({$invoice.curr_name})</b></td>
					<td class="right">{$total.total_vat|number_format:2:'.':''}</td>
				</tr>
			</tbody>
		</table>
		{if $invoice.no_vat == 1}
			<br/>	
			<br/>	
			Total VAT amount 0% -Article 138 (1) of the EU VAT Directive (2006/112/EC)
		{/if}
		<br/>	
		<br/>	
		Summa vārdiem {$invoice.curr_name}: {$total.total_vat_lv}<br/>
		<br/>	
		{if !empty($invoice_coffee_gift_info)}
			{$invoice_coffee_gift_info}
			<br/>
		{/if}
		<br/>
		<table>
			<tr>
				<td style="vertical-align: top; padding-right: 10px; white-space: nowrap;">Preci izsniedza:</td>
				<td style="font-size: 12px; text-align: center;">
					<br>
					______________________________________<br>
					<div style="font-size: 10px;">(vārds, uzvārds, paraksts)</div>
				</td>
				<td style="vertical-align: top; padding-right: 10px; padding-left: 50px; white-space: nowrap;">Preci saņēma:</td>
				<td style="font-size: 12px; text-align: center;">
					<br>
					______________________________________<br>
					<div style="font-size: 10px;">(vārds, uzvārds, paraksts)</div>
				</td>
			</tr>
		</table>	
	</body>
</html>