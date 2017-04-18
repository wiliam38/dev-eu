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
		<div class="center" style="font-size: 24px; margin-top: 20px;">INVOICE</div>
		
		<table style="width: 100%; margin-top: 40px;" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td class="border-bottom title" style="width: 40%;">Date: {$invoice.date|date_format:'%d.%m.%Y'}</td>
					<td class="border-bottom title right"  style="width: 60%;">Invoice Nr. 19B{$invoice.full_number}</td>
				</tr>
				
				<tr><td>Supplier</td><td class="border-bottom"><b>{$company.name}</b></td></tr>
				<tr><td>VAT/Reg. Nr.</td><td class="border-bottom"><b>{$company.reg_nr}</b></td></tr>
				<tr><td>Address</td><td class="border-bottom"><b>{$company.address}</b></td></tr>
				<tr><td>Bank</td><td class="border-bottom">{$company.bank}</td></tr>
				<tr><td>SWIFT</td><td class="border-bottom">{$company.bank_code}</td></tr>
				<tr><td>Account</td><td class="border-bottom">{$company.account}</td></tr>
				
				<tr><td colspan="2">&nbsp;</td></tr>
				
				<tr><td>Payer</td><td class="border-bottom"><b>
					{if !empty($invoice.company)}{$invoice.company}{else}{$invoice.contact_name}{/if}
				</b></td></tr>
				<tr><td>Reg. Nr.</td><td class="border-bottom">{$invoice.reg_nr}</td></tr>
				<tr><td>VAT Nr.</td><td class="border-bottom">{$invoice.vat_nr}</td></tr>
				<tr><td>Delivery address</td><td class="border-bottom">
					{$invoice.shipping_info|escape} {if $invoice.shipping_id == 5}({$invoice.shipping_statoil_address}){/if}
					{if trim($invoice.address) != ''} - {$invoice.address}{/if}
					{if trim($invoice.shipping_pickup_time) != ''} - {$invoice.shipping_pickup_time}{/if}
				</td></tr>
				<tr><td>Tel Nr.</td><td class="border-bottom">{$invoice.phone}</td></tr>
				<tr><td>Payment type</td><td class="border-bottom">{$invoice.pay_type_name}</td></tr>
				<tr><td>Type of transaction</td><td class="border-bottom">Selling goods</td></tr>
				{if !empty($invoice.company)}<tr><td>Contact</td><td class="border-bottom">{$invoice.contact_name}</td></tr>{/if}
				<tr><td>Comments</td><td class="border-bottom">{$invoice.notes}</td></tr>
			</tbody>
		</table>
		
		<table class="products_table" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th style="width: 40px;">Nr.</th>
					<th style="width: 100px;">Code</th>
					<th>Description</th>
					<th style="width: 60px;">Qty</th>
					<th style="width: 60px;">Unit</th>
					<th style="width: 80px;">Price excl. VAT ({$invoice.curr_name})</th>
					<th style="width: 80px;">Discount ({$invoice.curr_name})</th>
					<th style="width: 80px;">Amount excl. VAT incl. Discount ({$invoice.curr_name})</th>
				</tr>
			</thead>
			<tbody>
				{foreach item="data" from=$products name="products"}
					<tr>
						<td class="center">{$smarty.foreach.products.iteration}</td>
						<td>{$data.product_code}</td>
						<td>{$data.product_reference}</td>
						<td class="right">{$data.qty|number_format:0:'.':''}</td>
						<td class="center">pcs.</td>
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
					<td colspan="7"><b>Subtotal excl. VAT ({$invoice.curr_name})</b></td>
					<td class="right">{$total.price_wo_vat|number_format:2:'.':''}</td>
				</tr>
				
				{if $invoice.shipping == '1'}
					<tr>
						<td colspan="7"><b>Delivery excl. VAT ({$invoice.curr_name})</b></td>
						<td class="right">{$invoice.shipping_total|number_format:2:'.':''}</td>
					</tr>
				{/if}			
				
				<tr>
					<td colspan="7"><b>VAT {if $invoice.no_vat!=1}21{else}0{/if}% ({$invoice.curr_name})</b></td>
					<td class="right">{$total.vat|number_format:2:'.':''}</td>
				</tr>
				
				<tr>
					<td colspan="7"><b>Total incl. VAT ({$invoice.curr_name})</b></td>
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
		Total in words {$invoice.curr_name}: {$total.total_vat_lv}<br/>
		<br/>	
		{if !empty($invoice_coffee_gift_info)}
			{$invoice_coffee_gift_info}
			<br/>
		{/if}
		<br/>
		<table>
			<tr>
				<td style="vertical-align: top; padding-right: 10px; white-space: nowrap;">Products Issued:</td>
				<td style="font-size: 12px; text-align: center;">
					<br>
					______________________________________<br>
					<div style="font-size: 10px;">(Name, Surename, Signature)</div>
				</td>
				<td style="vertical-align: top; padding-right: 10px; padding-left: 50px; white-space: nowrap;">Products Received:</td>
				<td style="font-size: 12px; text-align: center;">
					<br>
					______________________________________<br>
					<div style="font-size: 10px;">(Name, Surename, Signature)</div>
				</td>
			</tr>
		</table>	
	</body>
</html>