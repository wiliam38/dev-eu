{if $action eq "categories"}
	{foreach item="data" from=$categories name="left_categories"}
		<a href="{$base_url}{$page_data.full_alias}/{$data.l_alias}-c{$data.id}" class="category-item {if $curr_category.id|default:null == $data.id}active{/if}">
			<span class="image-wrapper">
				{if !empty($data.image_src)}
					<img src="{$base_url}{$data.image_src}" alt="{$data.l_title|escape}"/>
				{/if}
			</span>
			{$data.l_title}
			{if !$smarty.foreach.left_categories.last}<span class="bottom-border"></span>{/if}
		</a>
	{/foreach}
{/if}