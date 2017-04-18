{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action == 'list'}
	<div class="recipes-list" id="recipes_list">
		{foreach item=data from=$recipes name="recipes"}
			<a href="{$recipes_page.full_alias}/{$data.l_full_alias}" class="recipe-item {if $smarty.foreach.recipes.index < 3}large{/if}">
				{if file_exists($data.l_image_src)}
					<span class="recipe-image">
						<img src="{$data.l_image_src}" class="recipe-image" alt="{$data.l_title}"/>
					</span>
				{/if}
				<span class="recipe-intro">
					<span class="recipe-intro-wrapper">
						<table style="width: 100%; border-collapse: collapse; border: 0px none;">
							<tr>
								<td class="title">{$data.l_title}</td>
								{if $smarty.foreach.recipes.index < 3}<td class="text"><div>{$data.l_intro|strip_tags}</div></td>{/if}
							</tr>
						</table>
					</span>
					<span class="open-btn"></span>
				</span>
			</a>
			
			{if $smarty.foreach.recipes.index == 2}
				<div class="clear"></div>	
				{if count($coffee) > 0}	
					<form class="coffee-filter" method="post" id="coffee_filter_form" {if $post_data.filter_opened|default:0 == 1}style="height: 100%;"{/if}>
						<input type="hidden" name="filter_opened" value="{$post_data.filter_opened|default:0}"/>
						
						{if count($post_data.coffee_id) > 0}
							<a href="#reset" onclick="filter_reset(); return false;" class="form-reset">
								<img src="{$base_url}assets/plugins/orders/img/btn-remove.png"/>
								{__('product_filter.button_reset')}
							</a>
						{/if}
						
						{foreach item="data" from=$coffee}
							<a href="#filter" onclick="coffee_filter(this, '{$data.id}'); return false;">
								<span class="image">
									{if in_array($data.id, $post_data.coffee_id|default:array())}
										<img src="{$base_url}assets/plugins/recipes/img/active-item.png"/>
									{else}
										<img src="{$base_url}{$data.reference_image_src}"/>
									{/if}
								</span>
								<span class="title">{$data.l_1_title} |&nbsp;<b>{$data.l_intensity}</b></span>
							</a>
						{/foreach}
						
						{foreach item="data" from=$post_data.coffee_id|default:array()}
							<input type="hidden" name="coffee_id[]" value="{$data}"/>
						{/foreach}
					</form>
					<a href="#toggle" class="coffee-filter-button" onclick="coffee_filter_toggle(this); return false;">
						<img src="{$base_url}assets/plugins/recipes/img/arrow-up.png" {if $post_data.filter_opened|default:0 == 0}style="display: none;"{/if} class="up"/>
						<img src="{$base_url}assets/plugins/recipes/img/arrow-down.png" {if $post_data.filter_opened|default:0 == 1}style="display: none;"{/if} class="down"/>
					</a>
				{/if}
			{/if}
		{/foreach}
		{if count($recipes) < 3}
			<div class="clear"></div>	
			{if count($coffee) > 0}	
				<form class="coffee-filter" method="post" id="coffee_filter_form" {if $post_data.filter_opened|default:0 == 1}style="height: 100%;"{/if}>
					<input type="hidden" name="filter_opened" value="{$post_data.filter_opened|default:0}"/>
					
					{if count($post_data.coffee_id) > 0}
						<a href="#reset" onclick="filter_reset(); return false;" class="form-reset">
							<img src="{$base_url}assets/plugins/orders/img/btn-remove.png"/>
							{__('product_filter.button_reset')}
						</a>
					{/if}
					
					{foreach item="data" from=$coffee}
						<a href="#filter" onclick="coffee_filter(this, '{$data.id}'); return false;">
							<span class="image">
								{if in_array($data.id, $post_data.coffee_id|default:array())}
									<img src="{$base_url}assets/plugins/recipes/img/active-item.png"/>
								{else}
									<img src="{$base_url}{$data.reference_image_src}"/>
								{/if}
							</span>
							<span class="title">{$data.l_1_title} |&nbsp;<b>{$data.l_intensity}</b></span>
						</a>
					{/foreach}
					
					{foreach item="data" from=$post_data.coffee_id|default:array()}
						<input type="hidden" name="coffee_id[]" value="{$data}"/>
					{/foreach}
				</form>
				<a href="#toggle" class="coffee-filter-button" onclick="coffee_filter_toggle(this); return false;">
					<img src="{$base_url}assets/plugins/recipes/img/arrow-up.png" {if $post_data.filter_opened|default:0 == 0}style="display: none;"{/if} class="up"/>
					<img src="{$base_url}assets/plugins/recipes/img/arrow-down.png" {if $post_data.filter_opened|default:0 == 1}style="display: none;"{/if} class="down"/>
				</a>
			{/if}
		{/if}			
		<div class="clear"></div>
	</div>
{/if}

{if $action == 'view'}
	<a href="javascript:window.history.back()" class="data-block-close"></a>
	
	<div class="recipe-view" id="recipe_view">
		<div class="data-block recipe">
			<div class="main-image">
				<div class="image-wrapper">
					<img src="{$base_url}{$recipe.l_image_src}"/>
				</div>
				{foreach item="material" from=$coffee}
					<div class="coffee-item">
						<a href="{$base_url}{$material.l_full_alias}" class="image" target="_blank">
							<img src="{$base_url}{$material.reference_image_src}"/>
						</a>
						<a href="{$base_url}{$material.l_full_alias}" target="_blank">{$material.l_1_title}</a>
						<div class="intensity">
							<div class="value">{$material.intensity}</div>
							{section name=intensity start=1 loop=13 step=1}
								<div class="item {if $smarty.section.intensity.index <= $material.intensity}active{/if}"></div>
							{/section}
							<div class="clear"></div>
						</div>
					</div>
				{/foreach}
			</div>
			<div class="main-text">
				<div class="text">
					<h2 style="text-transform: uppercase;">{$recipe.l_title}</h2>
					
					<table class="spec">
						<tr>
							<th style="width: 15%;">{__('recipes.difficulty')}</th>
							<th style="width: 25%;">{__('recipes.time')}</th>
							<th style="width: 60%;">{__('recipes.materials')}</th>
						</tr>
						<tr>
							<td>{__('recipes.difficulty_'|cat:$recipe.difficulty_type_name)}</td>
							<td>{if $recipe.time != ''}{$recipe.time} min{/if}</td>
							<td>
								{foreach item="material" from=$materials name="materials"}
									<a href="{$base_url}{$material.l_full_alias}" target="_blank">{$smarty.foreach.materials.iteration} {$material.l_1_title}</a>
								{/foreach}
							</td>
						</td>
					</table>
					
					<div class="recipe-preparation" style="margin-top: 25px;">
						{$recipe.l_intro}
					</div>
					
					<h3 style="text-transform: uppercase;">{__('recipes.ingredients')}</h3>
					<div class="recipe-preparation">
						{$recipe.l_ingredients}
					</div>
					
					<h3 style="text-transform: uppercase;">{__('recipes.preparation')}</h3>
					<div class="recipe-preparation">
						{$recipe.l_content}
					</div>
				</div>
				
				
				<div class="social">
					<div class="fb-like" data-href="{$base_url}{$page.full_alias}/{$recipe.l_alias}-i{$recipe.id}" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
					<a href="https://twitter.com/share" class="twitter-share-button">Tweet</a>
					<script>!function(d,s,id) { var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)) { js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs); } } (document, 'script', 'twitter-wjs');</script>	
				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	
	<script type="text/javascript">
		$().ready(function() {
			$('#page_content').css({
				'padding-top': '75px',
				'padding-bottom': '75px'
			});
			$('#page_content .mCSB_scrollTools').css({
				'top': '30px',
				'bottom': '30px'
			});
		});
	</script>
{/if}