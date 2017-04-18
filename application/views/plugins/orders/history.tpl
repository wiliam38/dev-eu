{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action == 'list'}
	<table style="width: 100%;" cellspacing="0">
		<thead>
			<tr style="height: 35px;">
				<th style="border-bottom: 1px solid #555555;">{lang name="history_order.date"}</th>
				<th style="border-bottom: 1px solid #555555;">{lang name="history_order.number"}</th>
				<th style="border-bottom: 1px solid #555555;">{lang name="history_order.total"}</th>
				<th style="border-bottom: 1px solid #555555;">{lang name="history_order.status"}</th>
				<th style="border-bottom: 1px solid #555555;"></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$orders}
				{include file=$this_file action="view_row"}
			{/foreach}
		</tbody>
	</table>
{/if}

{if $action == 'view_row'}
	<tr style="height: 30px;">
		<td style="text-align: center; border-bottom: 1px solid #555555;">{$data.date|date_format:'%d.%m.%Y'}</td>
		<td style="text-align: center; border-bottom: 1px solid #555555;">19B{$data.number}</td>
		<td style="text-align: center; border-bottom: 1px solid #555555;">{($data.total+($data.shipping_total*(1+$data.shipping_vat/100)))|number_format:2:'.':''} {$data.curr_name}</td>
		<td style="text-align: center; border-bottom: 1px solid #555555;">{$data.status_description}</td>
		<td style="text-align: center; border-bottom: 1px solid #555555;">
			{if $data.status_id == 9}
				<a href="plugins/firstdata/payment/{$data.id}" target="_blank">{lang name="history_order.make_payment"}</a>
			{else}
				{if $data.pdf_file}
					<a href="{$data.pdf_file}" target="_blank">{lang name="history_order.pdf"}</a>
				{/if}
			{/if}
		</td>
	</tr>
{/if}