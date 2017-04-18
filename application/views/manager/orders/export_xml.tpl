{if $action == 'popup'}
	<form action="{$base_url}manager/orders_orders/excel" method="post" id="export_xml_form">
		<input type="submit" style="display: none;"/>
		<table style="margin: auto;">
			<tr>
				<td colspan="2"><b>Periods:</b></td>
			</tr>
			<tr>
				<td style="padding-left: 20px;">No:</td>
				<td><input type="text" name="date_from" value="{strtotime('-1 month')|date_format:'01-%b-%Y'}" style="width: 140px;"/></td>
			</tr>
			<tr>
				<td style="padding-left: 20px;">Līdz:</td>
				<td><input type="text" name="date_to" value="{strtotime('-1 day', strtotime($smarty.now|date_format:'01-%b-%Y'))|date_format:'%d-%b-%Y'}" style="width: 140px;"/></td>
			</tr>
			<tr>
				<td colspan="2" style="padding-top: 10px;"><b>Dokumentu tipi:</b></td>
			</tr>
			<tr><td colspan="2" style="padding-left: 20px;"><input type="checkbox" name="type[]" value="ordered" style="vertical-align: middle;" checked="checked"/>Pasūtījumi</td></tr>
			<tr><td colspan="2" style="padding-left: 20px;"><input type="checkbox" name="type[]" value="paid" style="vertical-align: middle;" checked="checked"/>Avansa maksājumi</td></tr>
			<tr><td colspan="2" style="padding-left: 20px;"><input type="checkbox" name="type[]" value="issued" style="vertical-align: middle;" checked="checked"/>Pavadzīmes</td></tr>
		</table>
	</form>
	
	<script type="text/javascript">
		$().ready(function() {
			$('#export_xml_form').parent().css({ 'overflow': 'visible' });
			$('#export_xml_form input[name="date_from"]').issCalendar({
				max_date: $('#export_xml_form input[name="date_to"]')
			});
			$('#export_xml_form input[name="date_to"]').issCalendar({
				min_date: $('#export_xml_form input[name="date_from"]')
			});
		});
	</script>
{/if}