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
		<div class="center" style="font-size: 24px; margin-top: 20px;">PREPAYMENT</div>
		
		<table style="width: 100%; margin-top: 40px;" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td class="border-bottom title" style="width: 40%;">Date: {$order.date|date_format:'%d.%m.%Y'}</td>
					<td class="border-bottom title right"  style="width: 60%;">Prepayment Nr. 19B{$order.number}</td>
				</tr>
				
				<tr><td>Supplier</td><td class="border-bottom"><b>{$company.name}</b></td></tr>
				<tr><td>VAT/Reg. Nr.</td><td class="border-bottom"><b>{$company.reg_nr}</b></td></tr>
				<tr><td>Address</td><td class="border-bottom"><b>{$company.address}</b></td></tr>
				<tr><td>Bank</td><td class="border-bottom">{$company.bank}</td></tr>
				<tr><td>SWIFT</td><td class="border-bottom">{$company.bank_code}</td></tr>
				<tr><td>Account</td><td class="border-bottom">{$company.account}</td></tr>
				
				<tr><td colspan="2">&nbsp;</td></tr>
				
				<tr><td>Payer</td><td class="border-bottom"><b>
					{if !empty($order.company)}{$order.company}{else}{$order.contact_name}{/if}
				</b></td></tr>
				<tr><td>Reg. Nr.</td><td class="border-bottom">{$order.reg_nr}</td></tr>
				<tr><td>VAT Nr.</td><td class="border-bottom">{$order.vat_nr}</td></tr>
				<tr><td>Delivery address</td><td class="border-bottom">
					{$order.shipping_info|escape} {if $order.shipping_id == 5}({$order.shipping_statoil_address}){/if}
					{if trim($order.address) != ''} - {$order.address}{/if}
					{if trim($order.shipping_pickup_time) != ''} - {$order.shipping_pickup_time}{/if}
				</td></tr>
				<tr><td>Tel Nr.</td><td class="border-bottom">{$order.phone}</td></tr>
				<tr><td>Payment type</td><td class="border-bottom">{$order.pay_type_name}</td></tr>
				<tr><td>Type of transaction</td><td class="border-bottom">Selling goods</td></tr>
				{if !empty($order.company)}<tr><td>Contact</td><td class="border-bottom">{$order.contact_name}</td></tr>{/if}
				<tr><td>Comments</td><td class="border-bottom">{$order.notes}</td></tr>
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
					<th style="width: 80px;">Price excl. VAT ({$order.curr_name})</th>
					<th style="width: 80px;">Discount ({$order.curr_name})</th>
					<th style="width: 80px;">Amount excl. VAT incl. Discount ({$order.curr_name})</th>
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
						<td class="right">{(($data.price|number_format:4:'.':'')*$data.qty)|number_format:2:'.':''}</td>
					</tr>
				{/foreach}
				
				<tr>
					<td colspan="7"><b>Subtotal excl. VAT ({$order.curr_name})</b></td>
					<td class="right">{$total.price_wo_vat|number_format:2:'.':''}</td>
				</tr>
				
				<tr>
					<td colspan="7"><b>Delivery excl. VAT ({$order.curr_name})</b></td>
					<td class="right">{$order.shipping_total|number_format:2:'.':''}</td>
				</tr>			
				
				<tr>
					<td colspan="7"><b>VAT {if $order.no_vat!=1}21{else}0{/if}% ({$order.curr_name})</b></td>
					<td class="right">{$total.vat|number_format:2:'.':''}</td>
				</tr>
				
				<tr>
					<td colspan="7"><b>Total incl. VAT ({$order.curr_name})</b></td>
					<td class="right">{$total.total_vat|number_format:2:'.':''}</td>
				</tr>
			</tbody>
		</table>
		{if $order.no_vat == 1}
			<br/>	
			<br/>	
			Total VAT amount 0% -Article 138 (1) of the EU VAT Directive (2006/112/EC)
		{/if}
		<br/>	
		<br/>	
		Total in words {$order.curr_name}: {$total.total_vat_lv}<br/>
		<br/>	
		{if !empty($order_coffee_gift_info)}
			{$order_coffee_gift_info}
			<br/>
		{/if}
		<br/>
		Prepayment is processed automatically and is valid without signature.<br/>		
	</body>
</html>