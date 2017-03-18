{if empty($param)}{$param='p'}{/if}

{if $paginate|default:'' != '' && $paginate.total_pages > 1}
	{if $paginate.show_pages < 3}{$paginate.show_pages=3}{/if}

	{if $paginate.page < $paginate.show_pages|default:5/2}
		{$start=1}
		{if $paginate.show_pages|default:5 + 1 >= $paginate.total_pages}
			{$loop=$paginate.total_pages + 1}
		{else}
			{$loop=$paginate.show_pages|default:5 + 1}
		{/if}
	{elseif $paginate.page > ($paginate.total_pages-$paginate.show_pages|default:5 / 2)}
		{if $paginate.show_pages|default:5 + 1 >= $paginate.total_pages}
			{$start=1}
		{else}
			{$start=$paginate.total_pages-$paginate.show_pages|default:5 + 1}
		{/if}
		{$loop=$paginate.total_pages + 1}	
	{else}
		{if $paginate.page - floor($paginate.show_pages|default:5 / 2) == 1}
			{$start=1}
		{else}
			{$start=$paginate.page - floor($paginate.show_pages|default:5 / 2)}
		{/if}
		{if $paginate.page + floor($paginate.show_pages|default:5 / 2) == $paginate.total_pages}
			{$loop=$paginate.total_pages + 1}
		{else}
			{$loop=$paginate.page + floor($paginate.show_pages|default:5/2) + 1}
		{/if}
		
	{/if}
	
	{$data_page_name="data_pages_"|cat:(1|rand:$smarty.now)}

	<div class="data_pages" id="{$data_page_name}">
		{if $paginate.page != 1}
			<a href="{$href|default:'#none'}?{$param}=1" class="item" data-page="1" onclick="return false;" style="font-size: 13px;">«</a>
		{else}
			<span class="item">«</span>
		{/if}
		{if $paginate.page > 1}
			<a href="{$href|default:'#none'}?{$param}={$paginate.page-1}" class="item" data-page="{$paginate.page-1}" onclick="return false;"><</a>
		{else}
			<span class="item"><</span>
		{/if}		
			
		{section name="p" start="{$start}" loop="{$loop}"}		
			{if $paginate.page != $smarty.section.p.index}
				<a href="{$href|default:'#none'}?{$param}={$smarty.section.p.index}" class="item" data-page="{$smarty.section.p.index}" onclick="return false;">{$smarty.section.p.index}</a>
			{else}
				<span class="item active">{$smarty.section.p.index}</span> 
			{/if}
		{/section}
		
		<input type="text" value="" style="width: 30px; border: 1px solid #999999; height: 19px; text-align: center; border-radius: 0px !important;"
			onkeydown="if (event.which==13) { event.preventDefault(); $(this).next('a').click(); }"/>
		<a href="{$href|default:'#none'}?{$param}=" data-page="prev_input" class="item" style="padding: 0px 4px;" onclick="return false;" style="min-width: 0px;">{__('Go to')}</a>
		{__('of')}
		
		
		{if $paginate.page != $paginate.total_pages}
			<a 	href="{$href|default:'#none'}?{$param}={$paginate.total_pages}" data-page="{$paginate.total_pages}" class="item" style="margin-left: 1px;" onclick="return false;">
				{$paginate.total_pages}
			</a>
		{else}
			<span class="item" style="margin-left: 1px;">{$paginate.total_pages}</span>
		{/if}
		
		{if $paginate.page < $paginate.total_pages}
			<a href="{$href|default:'#none'}?{$param}={$paginate.page+1}" class="item" data-page="{$paginate.page+1}" onclick="return false;">></a>
		{else}
			<span class="item">></span>
		{/if}
		{if $paginate.page != $paginate.total_pages}
			<a href="{$href|default:'#none'}?{$param}={$paginate.total_pages}" class="item" data-page="{$paginate.total_pages}" onclick="return false;">»</a>
		{else}
			<span class="item">»</span>
		{/if}
	</div>
	
	<script type="text/javascript">
		$().ready(function() {
			$('#{$data_page_name} a.item').click(function(e) {
				{if empty($onclick)}	
					if ($(this).attr('data-page') == 'prev_input') $(this).attr('data-page', $(this).prev('input').val());
								
					var href = typeof($(this).closest('form').attr('action'))!='undefined'?$(this).closest('form').attr('action'):window.location;
					
					if (href.toString().match(/[\?&]p[^&]*/ig)) {
						href = href.toString().replace(/([\?&])p[^&]*/ig,'$1{$param}='+$(this).attr('data-page'));
					} else {
						if (href.toString().match(/[?]/ig)) href = href + '&{$param}='+$(this).attr('data-page');
						else href = href + '?{$param}='+$(this).attr('data-page');
					}		
					
					if (typeof($(this).closest('form').attr('action'))!='undefined') {
						$(this).closest('form').attr('action',href);
										
						if (!$(this).closest('form').find('input[type="submit"]')) {
							$(this).closest('form').append('<input type="submit" style="display: none;"/>');
						}
						
						$(this).closest('form').submit();
					} else {
						window.location = href;
					}
				{else}
					eval('{$onclick|default:''}');
				{/if}
			});
		});
	</script>
{/if}