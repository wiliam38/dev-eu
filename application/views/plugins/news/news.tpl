{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action == 'list'}
	<div class="news-list" id="news_list">
		{foreach item=data from=$news name="news"}
			<a href="{if $data.type_id == 20}{$data.l_link|link}{else}{$news_page.full_alias}/{$data.l_full_alias}{/if}" class="new-item {if $smarty.foreach.news.first}first{elseif $smarty.foreach.news.index%4 <= 2 && $smarty.foreach.news.index%4 != 0}odd{else}even{/if}">
				{if file_exists($data.image_src)}
					<span class="new-image">
						<img src="{$data.image_src}" class="new-image vAlign" alt="{$data.l_title}"/>
					</span>
				{/if}
				<span class="new-intro {if !file_exists($data.image_src)}no-image{/if}">
					<span class="hover-border"></span>
					<span class="date">{$data.l_pub_date|date_format:'d.m.Y'}</span>
				
					<h2>{$data.l_title}</h2>
					<span class="text">{$data.l_intro}</span>
				</span>
				<div class="clear"></div>
			</a>
		{/foreach}	
		<div class="clear"></div>		
	</div>
{/if}

{if $action == 'view'}
	<a href="{$base_url}{$page.full_alias}" class="data-block-close"></a>
	
	<div class="new-view" id="new_view">
		{if count($images) > 0}
			<div class="images">
				<div class="sliderkit photosgallery-captions" id="new_gallery">
					{if count($images) > 1}
						<div class="sliderkit-nav">
							<div class="sliderkit-nav-clip">
								<ul>
									{foreach item="image" from=$images}
										<li><a href="#" rel="nofollow" title="[link title]"><img src="{$base_url}{$image.image_src|thumb}" alt="" class="vAlign"/></a></li>
									{/foreach}
								</ul>
							</div>
							
							<div class="sliderkit-btn sliderkit-go-btn sliderkit-go-prev"><a rel="nofollow" href="#" title="Previous photo"><span>Previous photo</span></a></div>
							<div class="sliderkit-btn sliderkit-go-btn sliderkit-go-next"><a rel="nofollow" href="#" title="Next photo"><span>Next photo</span></a></div>
						</div>
					{/if}
					<div class="sliderkit-panels">
						{foreach item="image" from=$images}
							<div class="sliderkit-panel">
								<img src="{$base_url}{$image.image_src}" alt="" class="vAlign"/>
							</div>
						{/foreach}
					</div>
				</div>
			</div>
		{/if}
		<div class="text {if count($images) > 0}with-images{/if}">
			<div class="date">{$new.l_pub_date|date_format:'d.m.Y'}</div>
			<h1>{$new.l_title}</h1>
			{$new.l_content}
			<div class="social-icons">
				<div class="fb-like" data-href="{$base_url}{$page.full_alias}/{$new.l_full_alias}" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
				<a href="https://twitter.com/share" class="twitter-share-button">Tweet</a>
				<script>!function(d,s,id) { var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)) { js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs); } } (document, 'script', 'twitter-wjs');</script>	
			</div>
		</div>
		<div class="clear"></div>
	</div>	
	
	<script type="text/javascript">
		$().ready(function() {
			$('#page_content').parent('.content').css({
				'background-color': '#FFFFFF',
				'padding': '60px 0px 60px 0px'
			});
		});
	</script>
{/if}