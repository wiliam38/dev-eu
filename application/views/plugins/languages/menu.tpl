{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action eq "lang_menu"}
	{if count($langs) > 1}
		{foreach item="data" from=$langs}
			<a href="{$data.page_alias}" class="{if $data.lang_id eq $lang_id}active{/if}">{$data.lang_ticker}</a>
		{/foreach}
	{/if}
{/if}