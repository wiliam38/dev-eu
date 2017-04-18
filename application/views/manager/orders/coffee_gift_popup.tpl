{if $action == 'popup'}
	<table style="width: 100%;" id="coffee_gift_popup">
		<tr>
			<th style="width: 135px; text-align: left;">{__('Coffee gift amount')}:</th>
			<td>{$order.sum_of_coffee_gift_amount|number_format:2:'.':''} {$order.curr_symbol}</td>
		</tr>
		<tr>
			<th style="text-align: left;">{__('Status')}:</th>
			<td>
				{if $order.coffee_gift_status_id == 10 && $order.coffee_gift_expired == 1}
					{__('Expired')}
				{else}
					{__($order.coffee_gift_status_description)}
				{/if}
			</td>
		</tr>
		<tr>
			<th style="text-align: left;">{__('Issue till date')}:</th>
			<td>{$order.coffee_gift_valid_till|date_format:'%d-%b-%Y'}</td>
		</tr>
		<tr>
			<th style="text-align: left;">{__('Issue date')}:</th>
			<td>{$order.coffee_gift_datetime|date_format:'%d-%b-%Y'}</td>
		</tr>
		<tr>
			<th style="text-align: left;">{__('Change status')}:</th>
			<td>
				{if $order.coffee_gift_status_id == 10}
					<button type="button" class="button" onclick="coffee_gift_status('{$order.id}', 20, 'Izsniegts');">Izsniegt</button>
					<button type="button" class="button" onclick="coffee_gift_status('{$order.id}', 0, 'Atcelts');">Atcelt</button>
				{elseif $order.coffee_gift_status_id == 20}
					<button type="button" class="button" onclick="coffee_gift_status('{$order.id}', 10, 'Nav izsniegts');">Nav izsniegts</button>
					<button type="button" class="button" onclick="coffee_gift_status('{$order.id}', 0, 'Atcelts');">Atcelt</button>
				{else}
					<button type="button" class="button" onclick="coffee_gift_status('{$order.id}', 10, 'Nav izsniegts');">Nav izsniegts</button>
					<button type="button" class="button" onclick="coffee_gift_status('{$order.id}', 20, 'Izsniegts');">Izsniegt</button>
				{/if}
			</td>
		</tr>
	</table>
	
	<script type="text/javascript">
		$().ready(function() {
			$('#coffee_gift_popup .button').button();
		});
	</script>
{/if}