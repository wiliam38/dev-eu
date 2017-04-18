{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action == 'load'}
	<div class="title">
		{__('E-mails Queue')}
	</div>
	<table class="data_table emails_table">
		<thead>
			<tr class="filter">
				<th colspan="8">
					<a class="button" onclick="send_emails(this);" style="width: 120px;">{__('Send e-mails')}</a>
				</th>
			</tr>
			<tr>
				<th style="width: 120px;">{__('Date')}</th>
				<th style="width: 130px;">{__('From e-mail')}</th>
				<th style="width: 130px;">{__('To e-mail')}</th>
				<th style="width: 150px;">{__('Subject')}</th>
				<th style="width: 200px;">{__('Message')}</th>
				<th style="width: 80px;">{__('Status')}</th>
				<th style="width: 130px;">{__('Status info')}</th>				
				<th style="width: 70px;"></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$emails}
				{include file="$this_file" action="view"}
			{/foreach}
		</tbody>
	</table>	
{/if}

{if $action == 'view'}
	<tr>
		<td>{$data.creation_datetime|date_format:'%d-%b-%Y %H:%M'}</td>
		<td>{$data.from_email}</td>
		<td>{$data.to_email}</td>
		<td title="{$data.subject|strip_tags|escape}">{$data.subject|strip_tags|escape|truncate:40:'...':true}</td>
		<td title="{$data.body|strip_tags|escape}">{$data.body|strip_tags|escape|truncate:40:'...':true}</td>
		<td class="center">{$data.status_description}</td>
		<td title="{$data.status_msg|strip_tags|escape}">{$data.status_msg|strip_tags|escape|truncate:40:'...':true}</td>
		<td class="center">
			{if $data.status_id == 10 || $data.status_id == 20}
				<button type="button" onclick="cancel_emails(this, '{$data.id}');">{__('Cancel')}</button>
			{/if}
		</td>
	</tr>
{/if}