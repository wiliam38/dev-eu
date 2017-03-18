{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action == 'list'}
	<div class="text-content home-banners" id="banner">
		{foreach item=data from=$homes name="homes"}
			<div class="item" {if !$smarty.foreach.homes.first}style="display: none;"{/if}>
				{if file_exists($data.image_src)}<img src="{$base_url}{$data.image_src}" class="banner-image" alt="{$data.l_title}" onload="bannerSizes(this)"/>{/if}
				<div class="text">
					<div class="title" style="color: {$data.color_type_value|default:'#FFFFFF'}; border-bottom-color: {$data.color_type_value|default:'#FFFFFF'};">{$data.l_title}</div>
					<div class="intro" style="color: {$data.color_type_value|default:'#FFFFFF'};">{$data.l_intro}</div>
					<div class="link">
						{if !empty($data.l_link)}
							<a class="button-green" href="{$data.l_link}">{__('home.btn_discover_it')}</a>
						{/if}
						{* if count($homes)>1}
							<a class="button-prev {$data.color_type_name|default:'white'}" href="#prev" onclick="bannerGoto({$smarty.foreach.homes.index-1}); return false;"></a>
							<a class="button-next {$data.color_type_name|default:'white'}" href="#next" onclick="bannerGoto({$smarty.foreach.homes.index+1}); return false;"></a>
						{/if *}
					</div>
				</div>
				<div class="paginate {$data.color_type_name|default:'white'}" id="paginate" {if count($homes)<=1}style="display: none;"{/if}>
					{foreach item=sub_data from=$homes name="homes_paginate"}
						<a href="#goto-{$smarty.foreach.homes_paginate.iteration}" {if $sub_data.id == $data.id}class="active"{/if} onclick="bannerGoto({$smarty.foreach.homes_paginate.index}); return false;"></a>
					{/foreach}
				</div>
			</div>
		{/foreach}
	</div>
{/if}