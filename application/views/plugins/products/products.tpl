{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action == 'products_list'}
	<div class="products-list category-{$category_id}">					
			{if $category_id == 2 && $display|default:'list' == 'list'}
				{* APARATI SARAKSTS *}
				
				{if $category_id == 2}
					<div class="display-menu">
						<a href="{$base_url}{$product_page_alias}?display=array" class="item-array {if $display|default:'list' == 'array'}active{/if}"></a>
						<a href="{$base_url}{$product_page_alias}?display=list" class="item-list {if $display|default:'list' != 'array'}active{/if}"></a>
					</div>
				{/if}
				
				<div class="products-array-data" id="fullpage">
					{$product_cnt=0}	
					<div class="section"><div class="data-block">
					{foreach item="data" from=$products name="products"}
						{if !$smarty.foreach.products.first && $smarty.foreach.products.index%3 == 0}<div class="clear"></div></div></div><div class="section"><div class="data-block">{/if}
						<div class="item">
							<div class="main-image" id="product_color_images_{$data.id}">
								{$active=true}								
								{foreach item=color from=$data.product_references}
									{if !empty($color.image_src)}
										<a href="{$page.full_alias}/{if !empty($data.l_category_parent_alias)}{$data.l_category_parent_alias}/{/if}{$data.l_category_alias}-c{$data.category_id}/{$data.l_alias}-i{$data.id}" class="image-wrapper {if $active === true}active{/if}" data-balance="{$color.balance}" data-color="{$color.id}" data-order-btn="{if $color.balance <= 0}{__('products.add_to_cart_order')}{else}{__('products.add_to_cart')}{/if}">
											<img src="{$base_url}{$color.image_src|thumb}" class="vAlign bottom"/>
										</a>
										{$active=false}
									{/if}
								{/foreach}
								
								<div class="product-icons">
									{if $data.coffee_gift_active == 1}
										{$amount=$data.coffee_gift_amount|number_format:0:'.':''}
										{if $data.curr_symbol|lower == 'eur'}{$amount=$amount|cat:'€'}{else}{$amount=$amount|cat:$data.curr_symbol}{/if}
										<div class="product-coffee-gift">
											{$amount}
											<div class="text">{__('products.coffee_gift')}</div>											
											<div class="coffee-gift-info {if $smarty.foreach.products.iteration%3 == 0}left{else}right{/if}" style="display: none;">												
												<div class="line-1">{__('products.coffee_gift_info_line_1')|replace:':amount':$amount}</div>
												<div class="line-2">{__('products.coffee_gift_info_line_2')}</div>
												<div class="arrow"></div>
											</div>
										</div>
									{/if}
									{if $data.new == 1}<div class="product-new">{__('products.new_product')}</div>{/if}
									{if $data.gift == 1}<div class="product-gift"></div>{/if}
									{if $data.discount_active == 1}<div class="product-discount {$data.discount_color}">-{100-$data.discount_percents|number_format:0:'.':''}%</div>{/if}
								</div>								
							</div>
							
							<div class="title">
								{$data.l_1_title}
								<div class="price">{$data.price_pvn|number_format:2:'.':''} {$data.curr_symbol}</div>
							</div>
							
							<div class="functions">
								{$settings_functions=explode('-----', $data.l_settings_functions)}
								{foreach item=setting_image_src from=$settings_functions}
									{if !empty($setting_image_src)}
										<img src="{$base_url}{$setting_image_src}" class="function-item"/>
									{/if}
								{/foreach}	
								<div class="clear"></div>						
							</div>
							
							<div class="colors product-color-change" id="product_colors_{$data.id}">
								{foreach item=color from=$data.product_references}
									{if !empty($color.image_src)}
										<a href="#color" onclick="productReference(this); return false;" data-balance="{$color.balance}" data-color="{$color.id}" data-product_id="{$data.id}" data-order-btn="{if $color.balance <= 0}{__('products.add_to_cart_order')}{else}{__('products.add_to_cart')}{/if}" style="background-color: {$color.color};"></a>
									{/if}
								{/foreach}
								<div class="clear"></div>
							</div>
							
							<a class="discover" onclick="productOpen(this);" href="{$page.full_alias}/{if !empty($data.l_category_parent_alias)}{$data.l_category_parent_alias}/{/if}{$data.l_category_alias}-c{$data.category_id}/{$data.l_alias}-i{$data.id}">
								{__('products.discover_it')}
							</a>
							<input type="button" class="add-to-chart" value="{if $data.product_references[0].balance <= 0}{__('products.add_to_cart_order')}{else}{__('products.add_to_cart')}{/if}" onclick="add_to_order($(this).closest('.item').find('.main-image .image-wrapper.active'), 1, this);"/>
						</div>
					{/foreach}
					<div class="clear"></div></div></div>
			{else if $category_id == 1}
				{* KAPSULAS *}
				<div class="products-coffee-data" id="fullpage">
					{$last_title='---'}
					<div class="section"><div class="data-block">
					{$iteration=0}
					{$group_cnt=1}
					{foreach item="data" from=$products name="products"}
						{$iteration=$iteration+1}
						{if $iteration != 1 && $iteration%30 == 1}
							<div class="clear"></div></div></div><div class="section"><div class="data-block">
							{$last_title='---'}
						{else if $iteration != 1 && $iteration%10 == 1}
							<div class="clear"></div>
							{$last_title='---'}
						{/if}
						{$title=explode('-----', $data.l_settings_coffee_category)}
						{$next_title=explode('-----', $products[$smarty.foreach.products.iteration].l_settings_coffee_category|default:'')}
						{if $last_title != $title[0] && $iteration%10 == 0}
							<div class="clear"></div>
							{$last_title='---'}
							{$iteration=$iteration+1}
						{/if}
						{if $iteration%10 == 9 && $last_title != $title[0]}
							{$2_title=explode('-----', $products[$smarty.foreach.products.iteration+1].l_settings_coffee_category|default:'')}
							{$3_title=explode('-----', $products[$smarty.foreach.products.iteration+3].l_settings_coffee_category|default:'')}
							{if $title[0] == $2_title[0] && $title[0] != $3_title[0]}
								<div class="clear"></div>
								{$last_title='---'}
								{$iteration=0}
							{/if}
						{/if}
						<div class="item {if $data.inactive|default:0 == 1}inactive-item{/if}">
							{if $last_title != $title[0]}
								{$last_title=$title[0]}
								{$group_cnt=1}
							{else}
								{$group_cnt=$group_cnt+1}
							{/if}
							{if $next_title[0] != $title[0]}
								<span class="category-title {if $category_data[$title[0]].active_cnt == 0}inactive-title{/if}" {if $next_title[0] != $title[0]}style="width: {$group_cnt*100-20}%;"{/if}>
									{$title[0]}
								</span>
							{/if}

							<a class="image" href="{$page.full_alias}/{if !empty($data.l_category_parent_alias)}{$data.l_category_parent_alias}/{/if}{$data.l_category_alias}-c{$data.category_id}/{$data.l_alias}-i{$data.id}">
								{if !empty($data.reference_image_src)}
									<span class="image-wrapper">
										<img src="{$base_url}{$data.reference_image_src|thumb}" class="vAlign bottom"/>
									</span>
								{/if}

								<div class="product-icons">
									{if $data.new == 1}<div class="product-new">{__('products.new_product')}</div>
									{elseif $data.gift == 1}<div class="product-gift"></div>
									{elseif $data.discount_active == 1}<div class="product-discount {$data.discount_color}">-{100-$data.discount_percents|number_format:0:'.':''}%</div>{/if}
								</div>
							</a>
							<span class="title">{$data.l_1_title}</span>

							<span class="coffee-info">
								<span class="arrow"></span>
								<div class="title">
									{$data.l_1_title}
									<div class="price {if $data.discount_active}discount{/if}">
										{if $data.discount_active}
											<span class="old-price-value">&nbsp;&nbsp;{$data.price_pvn|number_format:2:'.':''} {$data.curr_symbol}&nbsp;&nbsp;</span>
											{$data.discount_price_pvn|number_format:2:'.':''}
										{else}
											{$data.price_pvn|number_format:2:'.':''}
										{/if}
										{$data.curr_symbol}
									</div>
								</div>
								<div class="intensity">
									<div class="value">{$data.l_intensity}</div>
									{section name=intensity start=1 loop=13 step=1}
										<div class="item {if $smarty.section.intensity.index <= $data.l_intensity}active{/if}"></div>
									{/section}
									<div class="clear"></div>
								</div>
								<div class="flavor">
									{$data.l_1_flavor}
								</div>

								<div class="buttons">
									{if $data.reference_balance > 0}
										<select class="small" style="width: 62px; margin: 0px 5px;" name="qty" onchange="coffee_qty_change(this);">
											{section name=qty start=10 loop=301 step=10}
												{if $data.reference_balance >= $smarty.section.qty.index}<option value="{$smarty.section.qty.index}" data-btn="{if $data.reference_balance >= $smarty.section.qty.index}{__('products.add_to_cart')}{else}{__('products.add_to_cart_order')}{/if}">x{$smarty.section.qty.index}</option>{/if}
											{/section}
										</select>
										<input type="button" class="button-buy add-to-chart" value="{if $data.reference_balance >= 10}{__('products.add_to_cart')}{else}{__('products.add_to_cart_order')}{/if}" style="width: 110px;" onclick="add_to_order('{$data.reference_image_id|default:null}', $(this).closest('.buttons').find('*[name=qty]'), this);"/>
									{else}
										{__('products.not_available')}
									{/if}
									<a class="button-gray" style="width: 166px; margin-top: 7px;" href="{$page.full_alias}/{if !empty($data.l_category_parent_alias)}{$data.l_category_parent_alias}/{/if}{$data.l_category_alias}-c{$data.category_id}/{$data.l_alias}-i{$data.id}">
										{__('products.discover_it')}
									</a>
								</div>
							</span>
						</div>
					{/foreach}
					<div class="clear"></div></div></div>	
			{else}	
				{* PAREJAS REZGIS *}
				
				{if $category_id == 2}
					<div class="display-menu">
						<a href="{$base_url}{$product_page_alias}?display=array" class="item-array {if $display|default:'list' == 'array'}active{/if}"></a>
						<a href="{$base_url}{$product_page_alias}?display=list" class="item-list {if $display|default:'list' != 'array'}active{/if}"></a>
					</div>
				{/if}
				
				<div class="products-list-data {if $category_id == 3}accessories{/if}" id="fullpage">
					{$product_cnt=0}
					<div class="section"><div class="data-block">
					
					{$row_num=0}{$slide_num=0}
					{$pro_category_setting_id=null}

					{foreach item="data" from=$products name="products"}
						{$row_num=$row_num+1}{$slide_num=$slide_num+1}
						{if $category_id == 8}
							{if $pro_category_setting_id != $data.pro_category_setting_id && !$smarty.foreach.products.first}
								{if $slide_num > 4}{$slide_num=9}{else}{$slide_num=5}{/if}
								{$row_num=5}
							{/if}				

							{$pro_category_setting_id=$data.pro_category_setting_id}
						{/if}						

						{if $slide_num == 9}
							{$row_num=1}{$slide_num=1}
							<div class="clear"></div></div></div><div class="section"><div class="data-block">
						{elseif $row_num == 5}
							{$row_num=1}
							<div class="clear"></div>
						{/if}
						<a class="item" href="{$page.full_alias}/{if !empty($data.l_category_parent_alias)}{$data.l_category_parent_alias}/{/if}{$data.l_category_alias}-c{$data.category_id}/{$data.l_alias}-i{$data.id}{if $show_all_colors|default:false}-k{$data.product_reference_id}{/if}">
							<span class="image">
								{if $show_all_colors|default:false}
									{if !empty($data.product_reference_image_src)}
										<span class="image-wrapper">
											<img src="{$base_url}{$data.product_reference_image_src|thumb}" class="vAlign bottom"/>
										</span>
									{/if}								
								{else}
									{if !empty($data.reference_image_src)}
										<span class="image-wrapper">
											<img src="{$base_url}{$data.reference_image_src|thumb}" class="vAlign bottom"/>
										</span>
									{/if}
								{/if}
								
								<div class="product-icons">
									{if $data.coffee_gift_active == 1 && $category_id == 2}
										{$amount=$data.coffee_gift_amount|number_format:0:'.':''}
										{if $data.curr_symbol|lower == 'eur'}{$amount=$amount|cat:'€'}{else}{$amount=$amount|cat:$data.curr_symbol}{/if}
										<div class="product-coffee-gift">
											{$amount}
											<div class="text">{__('products.coffee_gift')}</div>											
											<div class="coffee-gift-info {if in_array($smarty.foreach.products.iteration%4,array(0,3))}left{else}right{/if}" style="display: none;">												
												<div class="line-1">{__('products.coffee_gift_info_line_1')|replace:':amount':$amount}</div>
												<div class="line-2">{__('products.coffee_gift_info_line_2')}</div>
												<div class="arrow"></div>
											</div>
										</div>
									{/if}
									{if $data.new == 1}<div class="product-new">{__('products.new_product')}</div>{/if}
									{if $data.gift == 1}<div class="product-gift"></div>{/if}
									{if $data.discount_active == 1}<div class="product-discount {$data.discount_color}">-{100-$data.discount_percents|number_format:0:'.':''}%</div>{/if}
								</div>
							</span>
							<span class="title">
								{$data.l_1_title}
								{if $category_id != 8}
									<span class="price">{$data.price_pvn|number_format:2:'.':''} {$data.curr_symbol}</span>
								{/if}
							</span>
							<span class="discover">
								{__('products.discover_it')}
							</span>
						</a>
					{/foreach}
					<div class="clear"></div></div></div>			
			{/if}
		</div>
		{if $category_id == 1}
			<div class="how_to_choose">
				<a class="text button-green" href="#popup" onclick="showHowToChoose(); return false;" id="how_to_choose">
					{__('products.how_to_choose_coffee')}
				</a>
			</div>
			<div class="how_to_choose_content coffee-popup" id="how_to_choose_content">
				<div class="wrapper">
					<div id="how_to_choose_fullpage">
						<div class="section">
							<div class="how_to_choose_content_text">
								<div class="image-wrapper">
									<img src="{$base_url}assets/plugins/products/img/popup-coffee-map.png?v=2" class="main-image"/>
								</div>
								<div class="coffee-map map-title">{__('info_popup.coffee_map_title')}</div>
								<div class="coffee-map map-mexico">{__('info_popup.coffee_map_mexico')}</div>
								<div class="coffee-map map-columbia">{__('info_popup.coffee_map_columbia')}</div>
								<div class="coffee-map map-brasil">{__('info_popup.coffee_map_brasil')}</div>
								<div class="coffee-map map-india">{__('info_popup.coffee_map_india')}</div>
								<div class="coffee-map map-indonesia">{__('info_popup.coffee_map_indonesia')}</div>
								<div class="coffee-map map-vietnam">{__('info_popup.coffee_map_vietnam')}</div>
							</div>
						</div>
						<div class="section">
							<div class="how_to_choose_content_text">
								<div class="image-wrapper">
									<img src="{$base_url}assets/plugins/products/img/popup-coffee-transport.png?v=2" class="main-image"/>
								</div>
								
								<div class="coffee-prod prod-title">{__('info_popup.coffee_prod_title')}</div>
								<div class="coffee-prod prod-ievaksana">{__('info_popup.coffee_prod_ievaksana')}</div>
								<div class="coffee-prod prod-transportesana">{__('info_popup.coffee_prod_transportesana')}</div>
								<div class="coffee-prod prod-jauksana">{__('info_popup.coffee_prod_jauksana')}</div>
								<div class="coffee-prod prod-grauzdesana">{__('info_popup.coffee_prod_grauzdesana')}</div>
								<div class="coffee-prod prod-malsana">{__('info_popup.coffee_prod_malsana')}</div>
								<div class="coffee-prod prod-1">{__('info_popup.coffee_prod_1')}</div>
								<div class="coffee-prod prod-2">{__('info_popup.coffee_prod_2')}</div>
								<div class="coffee-prod prod-3">{__('info_popup.coffee_prod_3')}</div>
								<div class="coffee-prod prod-footer">{__('info_popup.coffee_prod_footer')}</div>
							</div>
						</div>
					</div>
					
					<a href="#close" onclick="$('#how_to_choose').click(); return false;" class="close-button"></a>
					<a href="javascript:prevBlock2()" class="how-to-choose-prev" id="how_to_choose_prev"></a>
					<a href="javascript:nextBlock2()" class="how-to-choose-next" id="how_to_choose_next"></a>
				</div>
			</div>
		{/if}
		{if $category_id == 2}
			<div class="how_to_choose">
				<a class="text button-green" href="#popup" onclick="showHowToChoose(); return false;" id="how_to_choose">
					{__('products.how_to_choose_machine')}
				</a>
			</div>
			<div class="how_to_choose_content" id="how_to_choose_content">
				<div class="wrapper">
					<div id="how_to_choose_fullpage">
						<div class="section">
							<div class="how_to_choose_content_text">
								<img src="{$base_url}assets/plugins/products/img/popup-aparati.png" class="main-image"/>
								<div class="aparati-text aparati-1">{__('info_popup.aparati_1')}</div>
								<div class="aparati-text aparati-2">{__('info_popup.aparati_2')}</div>
								<div class="aparati-text aparati-3">{__('info_popup.aparati_3')}</div>
								<div class="aparati-text aparati-4">{__('info_popup.aparati_4')}</div>
								<div class="aparati-text aparati-5">{__('info_popup.aparati_5')}</div>
								<div class="aparati-text aparati-6">{__('info_popup.aparati_6')}</div>
							</div>
						</div>
					</div>
					
					<a href="#close" onclick="$('#how_to_choose').click(); return false;" class="close-button"></a>
					<a href="javascript:prevBlock2()" class="how-to-choose-prev" id="how_to_choose_prev"></a>
					<a href="javascript:nextBlock2()" class="how-to-choose-next" id="how_to_choose_next"></a>
				</div>
			</div>
		{/if}
	</div>
	<div class="product-filter" id="product_filter">	
		{include file=$this_file action="product_filter"}
	</div>
	
	<a href="javascript:prevBlock()" class="section-prev {if $category_id == 2}aparati{/if} category-{$category_id}" id="data_block_prev"></a>
	<a href="javascript:nextBlock()" class="section-next product-list-next category-{$category_id}" id="data_block_next"></a>
{/if}

{if $action == 'product_filter'}
	<form id="product_filter_form" method="post" action="{$base_url}{$page_data.full_alias}/{$curr_category.l_alias}-c{$category_id}">
		<input type="submit" style="display: none;"/>
		<input type="hidden" name="filter_post" value="1"/>
		
		{if $category_id == 1}
			{foreach item="filter" from=$category_settings}
				{if $filter.id == 1}
					<h2 style="margin-top: 0px;">{__('product_filter.intensity')}:</h2>
					{section name=i loop=$filter.values step=-1} 
						<div class="intensity-item">
							{$filter.values[i].l_title}
							<a href="#intensity" onclick="product_filter('filter_intensity','{$filter.values[i].id}',this); return false;" class="intensity {if in_array($filter.values[i].id, $products_filter.filter_intensity|default:array())}active{/if}"></a>
						</div>
					{/section}
				{else if $filter.id == 2}
					<h2>{__('product_filter.cup_size')}:</h2>
					{foreach item="value" from=$filter.values}
						<a href="#cup-size" onclick="product_filter('filter_cup_size','{$value.id}',this); return false;" class="button {if !empty($value.image_src)}image{/if} {if in_array($value.id, $products_filter.filter_cup_size|default:array())}active{/if}">
							{if !empty($value.image_src)}<div class="icon" style="background-image: url('{$base_url}{$value.image_src}');"></div>{/if}
							{$value.l_title}
						</a>
					{/foreach}
				{else if $filter.id == 3}
					<h2>{__('product_filter.family')}:</h2>
					{foreach item="value" from=$filter.values}
						<a href="#flavor" onclick="product_filter('filter_flavor','{$value.id}',this); return false;" class="button {if !empty($value.image_src)}image{/if} {if in_array($value.id, $products_filter.filter_flavor|default:array())}active{/if}">
							{if !empty($value.image_src)}<div class="icon" style="background-image: url('{$base_url}{$value.image_src}');"></div>{/if}
							{$value.l_title}
						</a>
					{/foreach}
				{/if}
			{/foreach}
			
			{foreach item="data" from=$products_filter.filter_intensity|default:array()}<input type="hidden" name="filter_intensity[]" value="{$data}"/>{/foreach}
			{foreach item="data" from=$products_filter.filter_cup_size|default:array()}<input type="hidden" name="filter_cup_size[]" value="{$data}"/>{/foreach}
			{foreach item="data" from=$products_filter.filter_flavor|default:array()}<input type="hidden" name="filter_flavor[]" value="{$data}"/>{/foreach}
		{else if $category_id == 2}
			{foreach item="filter" from=$category_settings}
				{if $filter.id == 4}
					<h2 style="margin-top: 0px;">{__('product_filter.range')}:</h2>
					{foreach item="value" from=$filter.values name="range"}
						<a href="#type" onclick="product_filter('filter_type','{$value.id}',this); return false;" class="big-icon {if in_array($value.id, $products_filter.filter_type|default:array())}active{/if}">
							<div class="icon">
								{if !empty($value.image_src)}<img src="{$base_url}{$value.image_src}"/>{/if}
							</div>								
							<div class="title">{$value.l_title}</div>
						</a>
						{if $smarty.foreach.range.iteration%2 == 0}<div class="clear"></div>{/if}
					{/foreach}
					<div class="clear"></div>
				{else if $filter.id == 5}
					<h2>{__('product_filter.product_functions')}:</h2>
					<div class="functions">
						{foreach item="value" from=$filter.values}
							<a  href="#function" onclick="product_filter('filter_function','{$value.id}',this); return false;" class="icons {if !empty($value.image_src)}image{/if} {if in_array($value.id, $products_filter.filter_function|default:array())}active{/if}">
								{if !empty($value.image_src)}<div class="icon" style="background-image: url('{$base_url}{$value.image_src}');"></div>{/if}
								{$value.l_title}
							</a>
						{/foreach}
					</div>
				{/if}
			{/foreach}
			
			<h2>{__('product_filter.product_price')}:</h2>
			<select name="filter_price[]" style="width: 114px; font-style: italic;" onchange="$('#product_filter_form').submit();">
				{foreach item="data" from=$prices_filter}
					<option value="{$data.value}" {if in_array($data.value, $products_filter.filter_price|default:array())}selected="selected"{/if}>{$data.name}</option>
				{/foreach}
			</select>
			
			{foreach item="data" from=$products_filter.filter_type|default:array()}<input type="hidden" name="filter_type[]" value="{$data}"/>{/foreach}
			{foreach item="data" from=$products_filter.filter_function|default:array()}<input type="hidden" name="filter_function[]" value="{$data}"/>{/foreach}
		{else if $category_id == 3}
			{foreach item="filter" from=$category_settings}
				{if $filter.id == 7}
					<h2 style="margin-top: 0px;">{__('product_filter.range')}:</h2>
					{foreach item="value" from=$filter.values}
						<a href="#type" onclick="product_filter('filter_category','{$value.id}',this); return false;" class="big-icon accessories {if in_array($value.id, $products_filter.filter_category|default:array())}active{/if}" style="height: 101px;">
							<div class="icon">
								{if !empty($value.image_src)}<img src="{$base_url}{$value.image_src}"/>{/if}
							</div>								
							<div class="title">{$value.l_title}</div>
						</a>
					{/foreach}
					<div class="clear"></div>
				{else if $filter.id == 9}
					<h2>{__('product_filter.product_type')}:</h2>
					<select name="filter_product_type[]" style="width: 114px; font-style: italic;" onchange="$('#product_filter_form').submit();">
						<option value=""></option>
						{foreach item="value" from=$filter.values}
							<option value="{$value.id}" {if in_array($value.id, $products_filter.filter_product_type|default:array())}selected="selected"{/if}>{$value.l_title}</option>
						{/foreach}
					</select>
				{/if}
			{/foreach}
			
			{foreach item="data" from=$products_filter.filter_category|default:array()}<input type="hidden" name="filter_category[]" value="{$data}"/>{/foreach}
		{else if $category_id == 8}
			<h2 style="margin-top: 0px;">{__('product_filter.pro_category')}:</h2>
			{foreach item="filter" from=$category_settings}
				{foreach item="value" from=$filter.values}
					<a href="#type" onclick="product_filter('filter_pro_category','{$value.id}',this); return false;" class="big-icon pro {if in_array($value.id, $products_filter.filter_pro_category|default:array())}active{/if}">
						<div class="icon">
							{if !empty($value.image_src)}<img src="{$base_url}{$value.image_src}"/>{/if}
						</div>								
						<div class="title">{$value.l_title}</div>
					</a>
				{/foreach}
				<div class="clear"></div>
			{/foreach}

			{foreach item="data" from=$products_filter.filter_pro_category|default:array()}<input type="hidden" name="filter_pro_category[]" value="{$data}"/>{/foreach}
		{/if}
		
		<a href="#reset" onclick="filter_reset(); return false;" class="form-reset">
			<img src="{$base_url}assets/plugins/orders/img/btn-remove.png"/>
			{__('product_filter.button_reset')}
		</a>
	</form>
	<div class="search-portals">
		<a href="http://www.kurpirkt.lv" title="Atrodi telefonus, datorus, fotokameras un citas preces interneta veikalos" target="_blank">
			<img style="Border: none;" alt="Atrodi telefonus, datorus, fotokameras un citas preces interneta veikalos" src="http://www.kurpirkt.lv/media/kurpirkt120.gif" width=120 height=40>
		</a>
		{*
		<a href="http://www.salidzini.lv/" target="_blank">
			<img border="0" alt="Salidzini.lv logotips" title="Kuponi, Octa, Plan&#353;etdatori, &#256;trie kred&#299;ti, Mobilie telefoni, Portat&#299;vie datori, Interneta veikali, Ce&#316;ojumi, Kasko, Digit&#257;l&#257;s fotokameras, L&#275;tas aviobi&#316;etes" src="http://static.salidzini.lv/images/logo_button.gif"/>
		</a>
		*}
	</div>
{/if}

{if $action == 'product_view'}
	<div class="products-view">
		<div class="product-view-data" id="fullpage">
			{if $product.id != -1}
				{if $category_id == 1}
					<div class="section">
						<div class="data-block">
							<div class="main-image">
								{if !empty($product.l_1_image_src)}
									<div class="image-wrapper active" style="padding-top: 60px;">
										<img src="{$base_url}{$product.l_1_image_src}"/>
										
										<div class="product-icons">
											{if $product.new == 1}<div class="product-new">{__('products.new_product')}</div>{/if}
											{if $product.gift == 1}<div class="product-gift"></div>{/if}
											{if $product.discount_active == 1}<div class="product-discount {$product.discount_color}">-{100-$product.discount_percents|number_format:0:'.':''}%</div>{/if}
										</div>
									</div>
								{/if}
							</div>
							<div class="main-text">
								<h1>{$product.l_1_title}</h1>
								<div class="text">
									{$product.l_1_description}
								</div>
								
								<div class="intensity">
									<div class="value">{$product.l_intensity}</div>
									{section name=intensity start=1 loop=13 step=1}
										<div class="item {if $smarty.section.intensity.index <= $product.l_intensity}active{/if}"></div>
									{/section}
									<div class="clear"></div>
								</div>
								
								<div class="cup-size">
									<div class="cup-sizes">
										{foreach item=setting from=$cup_sizes name="product_settings"}
											<div class="item">
												<div class="icon" style="background-image: url('{$base_url}{$setting.image_src}');"></div>
												{$setting.title}
											</div>
										{/foreach}
									</div>
									<div class="family">
										{if trim($product.l_1_flavor|strip_tags) != ''}
											<div class="title">{__('products.aromatic_profile')}</div>
											{$product.l_1_flavor}
										{/if}
									</div>
									<div class="clear"></div>
								</div>
								
								<div class="price">
									{if $product.reference_balance > 0}																	
										<span class="price-value {if $product.discount_active}discount{/if}" style="padding-left: 0px;">
											{if $product.discount_active}
												<span class="old-price-value">&nbsp;&nbsp;{$product.price_pvn|number_format:2:'.':''} {$product.curr_symbol}&nbsp;&nbsp;</span>
												{$product.discount_price_pvn|number_format:2:'.':''}
											{else}
												{$product.price_pvn|number_format:2:'.':''}
											{/if}
											{$product.curr_symbol}
										</span>
										<select style="width: 35px; margin: 0px 5px;" name="qty" onchange="coffee_qty_change(this);" data-balance="{$product.reference_balance}">
											{section name=qty start=10 loop=301 step=10}
												{if $product.reference_balance >= $smarty.section.qty.index}<option value="{$smarty.section.qty.index}" data-btn="{if $product.reference_balance >= $smarty.section.qty.index}{__('products.add_to_cart')}{else}{__('products.add_to_cart_order')}{/if}">x{$smarty.section.qty.index}</option>{/if}
											{/section}
										</select>
										<input type="button" class="add-to-chart" value="{if $product.reference_balance >= 10}{__('products.add_to_cart')}{else}{__('products.add_to_cart_order')}{/if}" onclick="add_to_order('{$product_references[0].id|default:null}', $(this).closest('.price').find('*[name=qty]'), this);"/>
										<div class="price-order-info" {if $product.reference_balance >= 10}style="display: none;"{/if}>* {__('order_checkout.error_order_balance_delivery')}</div>
									{else}
										<span style="font-size: 18px;">{__('products.not_available')}</span>
									{/if}
								</div>
								
								<div class="social">
									<div class="fb-like" data-href="{$base_url}{$page.full_alias}/{$product.l_category_alias}-c{$product.category_id}/{$product.l_alias}-i{$product.id}" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
									<a href="https://twitter.com/share" class="twitter-share-button">Tweet</a>
									<script>!function(d,s,id) { var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)) { js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs); } } (document, 'script', 'twitter-wjs');</script>	
								</div>
							</div>
							<div class="clear"></div>
						</div>
					</div>
					
					{if !empty($product.l_0_enabled)}
						<div class="section">
							<div class="data-block recipe">
								<h1>{__('products.delicious_recipe')}</h1>
								<div class="main-image">
									{if !empty($product.l_0_recipe_image_src)}
										<div class="image-wrapper active">
											<img src="{$base_url}{$product.l_0_recipe_image_src}"/>
										</div>
									{/if}
								</div>
								<div class="main-text">
									<div class="text">
										<h2 style="text-transform: uppercase;">{$product.l_0_recipe_title}</h2>
										{$product.l_0_recipe_intro}
										
										<h3 style="text-transform: uppercase;">{__('recipes.preparation')}</h3>
										<div class="recipe-preparation">
											{$product.l_0_recipe_content}
										</div>
									</div>
									
									
									
									<div class="price" style="margin-top: 40px;">
										<a class="button-green" href="{$base_url}{$recipes_page_alias|default:''}">{__('products.all_recipes')}</a>
									</div>
									
									<div class="social">
										<div class="fb-like" data-href="{$base_url}{$page.full_alias}/{$product.l_category_alias}-c{$product.category_id}/{$product.l_alias}-i{$product.id}" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
										<a href="https://twitter.com/share" class="twitter-share-button">Tweet</a>
										<script>!function(d,s,id) { var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)) { js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs); } } (document, 'script', 'twitter-wjs');</script>	
									</div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					{/if}
				{else if $category_id == 2}			
					<div class="section">
						<div class="data-block">
							<div class="main-image" id="product_color_images_{$product.id}">
								{foreach item=color from=$product_references}
									{if !empty($color.image_src)}
										<div class="image-wrapper {if $product_reference_id|default:$product_references[0].id|default:null == $color.id}active{/if}" data-balance="{$color.balance}" data-color="{$color.id}" data-order-btn="{if $color.balance <= 0}{__('products.add_to_cart_order')}{else}{__('products.add_to_cart')}{/if}">
											<img src="{$base_url}{$color.image_src}"/>
										</div>
									{/if}
								{/foreach}
								
								<div class="product-icons">
									{if $product.coffee_gift_active == 1}<div class="product-coffee-gift">{$product.coffee_gift_amount|number_format:0:'.':''}{if $product.curr_symbol|lower == 'eur'}€{else}{$product.curr_symbol}{/if}<div class="text">{__('products.coffee_gift')}</div></div>{/if}
									{if $product.new == 1}<div class="product-new">{__('products.new_product')}</div>{/if}
									{if $product.gift == 1}<div class="product-gift"></div>{/if}
									{if $product.discount_active == 1}<div class="product-discount {$product.discount_color}">-{100-$product.discount_percents|number_format:0:'.':''}%</div>{/if}
								</div>	
							</div>
							<div class="main-text">
								<h1>{$product.l_1_title}</h1>
								{if $product.coffee_gift_active == 1}
									<div class="coffee-gift-info">
										{$amount=$product.coffee_gift_amount|number_format:0:'.':''}
										{if $product.curr_symbol|lower == 'eur'}{$amount=$amount|cat:'€'}{else}{$amount=$amount|cat:$product.curr_symbol}{/if}
										<div class="line-1">{__('products.coffee_gift_info_line_1')|replace:':amount':$amount}</div>
										<div class="line-2">{__('products.coffee_gift_info_line_2')}</div>
										<div class="arrow"></div>
									</div>
								{/if} 
								<div class="text">
									{$product.l_1_description}
								</div>
								
								<div class="functions">
									{foreach item=setting from=$functions name="product_settings"}
										<div class="item">
											{if !empty($setting.image_src)}
												<img src="{$base_url}{$setting.image_src}" class="vAlign" data-height="25"/>
											{/if}
											<div class="vAlign">{$setting.title}</div>
										</div>
										{if $smarty.foreach.product_settings.iteration%2 == 0}<div class="clear"></div>{/if}
									{/foreach}	
									<div class="clear"></div>						
								</div>
								
								<div class="colors" id="product_colors">
									{$selected_balance=0}
									{foreach item=color from=$product_references}
										{if !empty($color.image_src)}
											<a href="#color" onclick="productReference(this); return false;" data-balance="{$color.balance}" data-color="{$color.id}" data-product_id="{$product.id}" data-order-btn="{if $color.balance <= 0}{__('products.add_to_cart_order')}{else}{__('products.add_to_cart')}{/if}" style="background-color: {$color.color};"></a>
										{/if}
										{if $product_reference_id|default:$product_references[0].id|default:null == $color.id}{$selected_balance=$color.balance}{/if}
									{/foreach}
									<div class="clear"></div>
								</div>
								
								<div class="price">
									<input type="button" class="add-to-chart" value="{if $selected_balance <= 0}{__('products.add_to_cart_order')}{else}{__('products.add_to_cart')}{/if}" onclick="add_to_order($(this).closest('.data-block').find('.main-image .image-wrapper.active'), 1, this);"/>
									<span class="price-value {if $product.discount_active}discount{/if}">
										{if $product.discount_active}
											<span class="old-price-value">&nbsp;&nbsp;{$product.price_pvn|number_format:2:'.':''} {$product.curr_symbol}&nbsp;&nbsp;</span>
											{$product.discount_price_pvn|number_format:2:'.':''}
										{else}
											{$product.price_pvn|number_format:2:'.':''}
										{/if}
										{$product.curr_symbol}
									</span>
									<div class="price-order-info" {if $selected_balance > 0}style="display: none;"{/if}>* {__('order_checkout.error_order_balance_delivery')}</div>
								</div>
								
								<div class="social">
									<div class="fb-like" data-href="{$base_url}{$page.full_alias}/{$product.l_category_alias}-c{$product.category_id}/{$product.l_alias}-i{$product.id}" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
									<a href="https://twitter.com/share" class="twitter-share-button">Tweet</a>
									<script>!function(d,s,id) { var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)) { js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs); } } (document, 'script', 'twitter-wjs');</script>	
								</div>
							</div>
							<div class="clear"></div>
						</div>
					</div>
					{if !empty($product.l_2_enabled)}
						<div class="section">
							<div class="data-block">
								<div class="product-gallery" id="product_gallery_images">
									<div class="product-gallery-view" id="product_gallery_view">
										{foreach item=image from=$product_gallery name="product_gallery"}
											<div data-id="{$image.id}" class="image-wrapper" {if !$smarty.foreach.product_gallery.first}style="display: none;"{/if}>
												<img src="{$base_url}{$image.image_src}"/>
											</div>
										{/foreach}
									</div>
									
									<div class="product-gallery-list" id="product_gallery">								
										{foreach item=image from=$product_gallery}
											<a href="#image" data-id="{$image.id}" class="image-wrapper">
												<img src="{$base_url}{$image.image_src}"/>
											</a>
										{/foreach}
										<div class="clear"></div>
									</div>
								</div>
								<div class="main-text">
									<h1>{__('products.features')}</h1>
									<div class="text">
										{$product.l_2_features}
									</div>
									{if !empty($product.l_4_manual_src)}
										<div class="manual">
											<a href="{$base_url}{$product.l_4_manual_src}" target="_blank">{__('products.user_manual')}</a>
										</div>
									{/if}
								</div>
								<div class="clear"></div>
							</div>
						</div>
					{/if}
					{if !empty($product.l_3_enabled)}
						<div class="section" style="padding-right: 60px;">
							<div class="data-block">
								<div class="video">
									<div class="vAlign">
										<h1>{__('products.video_instruction')}</h1>
										{if $product.l_3_video_type_id == 10}
											<object width="700" height="410">
												<param name="movie" value="{$product.l_3_video_provider}{$product.l_3_video_link}?hl=en_US&amp;version=3&amp;rel=0"></param>
												<param name="allowFullScreen" value="true"></param>
												<param name="allowscriptaccess" value="always"></param>
												<embed src="{$product.l_3_video_provider}{$product.l_3_video_link}?hl=en_US&amp;version=3&amp;rel=0" type="application/x-shockwave-flash" width="700" height="410" allowscriptaccess="always" allowfullscreen="true"></embed>
											</object>										
										{/if}
									</div>
								</div>
							</div>
						</div>
					{/if}
					{if !empty($product.l_5_enabled)}
						<div class="section">
							<div class="data-block content {if $product.l_5_image_position == 'v'}vertical{else}horizontal{/if}">
								<div class="main-image">
									<div class="image-wrapper">
										{if !empty($product.l_5_image_src)}
											<img src="{$base_url}{$product.l_5_image_src}" {if $product.l_5_image_position == 'v'}class="vAlign"{/if}/>
										{/if}
									</div>
								</div>
								<div class="main-text">
									<div class="main-text-wrapper {if $product.l_5_image_position == 'v'}vAlign bottom{/if}">
										<h1>{$product.l_5_title}</h1>
										<div class="text">
											{$product.l_5_content}
										</div>
									</div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					{/if}
					{if !empty($product.l_6_enabled)}
						<div class="section">
							<div class="data-block content {if $product.l_6_image_position == 'v'}vertical{else}horizontal{/if} ">
								<div class="main-image">
									<div class="image-wrapper">
										{if !empty($product.l_6_image_src)}
											<img src="{$base_url}{$product.l_6_image_src}" {if $product.l_6_image_position == 'v'}class="vAlign"{/if}/>
										{/if}
									</div>
								</div>
								<div class="main-text">
									<div class="main-text-wrapper {if $product.l_6_image_position == 'v'}vAlign bottom{/if}">
										<h1>{$product.l_6_title}</h1>
										<div class="text">
											{$product.l_6_content}
										</div>
									</div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					{/if}
					{if !empty($product.l_7_enabled)}
						<div class="section">
							<div class="data-block content {if $product.l_7_image_position == 'v'}vertical{else}horizontal{/if}">
								<div class="main-image">
									<div class="image-wrapper">
										{if !empty($product.l_7_image_src)}
											<img src="{$base_url}{$product.l_7_image_src}" {if $product.l_7_image_position == 'v'}class="vAlign"{/if}/>
										{/if}
									</div>
								</div>
								<div class="main-text">
									<div class="main-text-wrapper {if $product.l_7_image_position == 'v'}vAlign bottom{/if}">
										<h1>{$product.l_7_title}</h1>
										<div class="text">
											{$product.l_7_content}
										</div>
									</div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					{/if}
					{if !empty($product.l_8_enabled)}
						<div class="section">
							<div class="data-block content {if $product.l_8_image_position == 'v'}vertical{else}horizontal{/if}">
								<div class="main-image">
									<div class="image-wrapper">
										{if !empty($product.l_8_image_src)}
											<img src="{$base_url}{$product.l_8_image_src}" {if $product.l_8_image_position == 'v'}class="vAlign"{/if}/>
										{/if}
									</div>
								</div>
								<div class="main-text">
									<div class="main-text-wrapper {if $product.l_8_image_position == 'v'}vAlign bottom{/if}">
										<h1>{$product.l_8_title}</h1>
										<div class="text">
											{$product.l_8_content}
										</div>
									</div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					{/if}
					{if !empty($product.l_9_enabled)}
						<div class="section">
							<div class="data-block content {if $product.l_9_image_position == 'v'}vertical{else}horizontal{/if}">
								<div class="main-image">
									<div class="image-wrapper">
										{if !empty($product.l_9_image_src)}
											<img src="{$base_url}{$product.l_9_image_src}" {if $product.l_9_image_position == 'v'}class="vAlign"{/if}/>
										{/if}
									</div>
								</div>
								<div class="main-text">
									<div class="main-text-wrapper {if $product.l_9_image_position == 'v'}vAlign bottom{/if}">
										<h1>{$product.l_9_title}</h1>
										<div class="text">
											{$product.l_9_content}
										</div>
									</div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					{/if}
					{if !empty($product.l_4_enabled)}
						<div class="section">
							<div class="data-block spec">
								<div class="main-image">
									<div class="image-wrapper">
										<img src="{$base_url}{$product.l_4_image_src}"/>
										{if !empty($product.l_4_manual_src)}
											<div class="manual">
												<a href="{$base_url}{$product.l_4_manual_src}" target="_blank">{__('products.user_manual')}</a>
											</div>
										{/if}
									</div>
									
								</div>
								<div class="main-text">
									<h1>{__('products.technical_specs')}</h1>
									<div class="text">
										{$product.l_4_content}
									</div>
									
									<div class="functions">
										{foreach item=setting from=$functions name="product_settings"}
											<div class="item">
												{if !empty($setting.image_src)}
													<img src="{$base_url}{$setting.image_src}" class="vAlign"/>
												{/if}
												<div class="vAlign">{$setting.title}</div>
											</div>
											{if $smarty.foreach.product_settings.iteration%2 == 0}<div class="clear"></div>{/if}
										{/foreach}	
										<div class="clear"></div>						
									</div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					{/if}
				{else if $category_id == 3}
					<div class="section">
						<div class="data-block">
							<div class="main-image">
								{if !empty($product.l_1_image_src)}
									<div class="image-wrapper active">
										<img src="{$base_url}{$product.l_1_image_src}"/>
									</div>
								{/if}
								
								<div class="product-icons">
									{if $product.new == 1}<div class="product-new">{__('products.new_product')}</div>{/if}
									{if $product.gift == 1}<div class="product-gift"></div>{/if}
									{if $product.discount_active == 1}<div class="product-discount {$product.discount_color}">-{100-$product.discount_percents|number_format:0:'.':''}%</div>{/if}
								</div>
							</div>
							<div class="main-text">
								<h1>{$product.l_1_title}</h1>
								<div class="text">
									{$product.l_1_description}
								</div>
								
								<div class="price">
									<input type="button" value="{if $product.reference_balance > 0}{__('products.add_to_cart')}{else}{__('products.add_to_cart_order')}{/if}" onclick="add_to_order('{$product.reference_image_id|default:null}', 1, this);"/>
									<span class="price-value {if $product.discount_active}discount{/if}">
										{if $product.discount_active}
											<span class="old-price-value">&nbsp;&nbsp;{$product.price_pvn|number_format:2:'.':''} {$product.curr_symbol}&nbsp;&nbsp;</span>
											{$product.discount_price_pvn|number_format:2:'.':''}
										{else}
											{$product.price_pvn|number_format:2:'.':''}
										{/if}
										{$product.curr_symbol}
									</span>
									<div class="price-order-info" {if $product.reference_balance > 0}style="display: none;"{/if}>* {__('order_checkout.error_order_balance_delivery')}</div>
								</div>
								
								<div class="social">
									<div class="fb-like" data-href="{$base_url}{$page.full_alias}/{$product.l_category_alias}-c{$product.category_id}/{$product.l_alias}-i{$product.id}" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
									<a href="https://twitter.com/share" class="twitter-share-button">Tweet</a>
									<script>!function(d,s,id) { var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)) { js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs); } } (document, 'script', 'twitter-wjs');</script>	
								</div>
								
								{if count($collection_products) > 0}
									<div class="collection">
										<h2>{__('products.collection_list')}</h2>
										
										<div class="collection-list">
											{foreach item="data" from=$collection_products}
												<a class="item" href="{$base_url}{$page.full_alias}/{if !empty($data.l_category_parent_alias)}{$data.l_category_parent_alias}/{/if}{$data.l_category_alias}-c{$data.category_id}/{$data.l_alias}-i{$data.id}">
													<span class="image-wrapper">
														<img src="{$base_url}{$data.reference_image_src|thumb}" class="vAlign"/>
													</span>
													<span class="title">{$data.l_1_title}</span>
												</a>
											{/foreach}
											<div class="clear"></div>
										</div>
									</div>
								{/if}
							</div>
							<div class="clear"></div>
						</div>
					</div>
				{else if $category_id == 8}
					<div class="section">
						<div class="data-block">
							<div class="main-image">
								{if !empty($product.l_1_image_src)}
									<div class="image-wrapper active">
										<img src="{$base_url}{$product.l_1_image_src}"/>
									</div>
								{/if}
								
								<div class="product-icons">
									{if $product.new == 1}<div class="product-new">{__('products.new_product')}</div>{/if}
									{if $product.gift == 1}<div class="product-gift"></div>{/if}
									{if $product.discount_active == 1}<div class="product-discount {$product.discount_color}">-{100-$product.discount_percents|number_format:0:'.':''}%</div>{/if}
								</div>
							</div>
							<div class="main-text">
								<h1>{$product.l_1_title}</h1>
								<div class="text">
									{$product.l_1_description}
								</div>
								
								<div class="price">
									{if !empty($user_data.pro_category)}
										{if $product.pro_category_setting_id == 19}
											{if $product.reference_balance > 0}
												<span class="price-value {if $product.discount_active}discount{/if}" style="padding-left: 0px;">
													{if is_numeric($user_data.pro_coffee_coef)}
														{if $product.discount_active}
															<span class="old-price-value">&nbsp;&nbsp;{($product.price_pvn*$user_data.pro_coffee_coef)|number_format:2:'.':''} {$product.curr_symbol}&nbsp;&nbsp;</span>
															{($product.discount_price_pvn*$user_data.pro_coffee_coef)|number_format:2:'.':''}
														{else}
															{($product.price_pvn*$user_data.pro_coffee_coef)|number_format:2:'.':''}
														{/if}
													{else}
														{if $product.discount_active}
															<span class="old-price-value">&nbsp;&nbsp;{$product.price_pvn|number_format:2:'.':''} {$product.curr_symbol}&nbsp;&nbsp;</span>
															{$product.discount_price_pvn|number_format:2:'.':''}
														{else}
															{$product.price_pvn|number_format:2:'.':''}
														{/if}
													{/if}
													{$product.curr_symbol}
												</span>
												<select style="width: 35px; margin: 0px 5px;" name="qty" onchange="coffee_qty_change(this);" data-balance="{$product.reference_balance}">
													{section name=qty start=50 loop=5001 step=50}
														{if $product.reference_balance >= $smarty.section.qty.index}<option value="{$smarty.section.qty.index}" data-btn="{if $product.reference_balance >= $smarty.section.qty.index}{__('products.add_to_cart')}{else}{__('products.add_to_cart_order')}{/if}">x{$smarty.section.qty.index}</option>{/if}
													{/section}
												</select>
												<input type="button" class="add-to-chart" value="{if $product.reference_balance > 0}{__('products.add_to_cart')}{else}{__('products.add_to_cart_order')}{/if}" onclick="add_to_order('{$product.reference_image_id|default:null}', $(this).closest('.price').find('*[name=qty]'), this);"/>
												<div class="price-order-info" {if $product.reference_balance > 0}style="display: none;"{/if}>* {__('order_checkout.error_order_balance_delivery')}</div>
											{else}
												<div class="not-available">
													{__('products.not_available')}
												</div>
											{/if}
										{elseif $product.pro_category_setting_id == 18}
											<input type="button" value="{if $product.reference_balance > 0}{__('products.add_to_cart')}{else}{__('products.add_to_cart_order')}{/if}" onclick="add_to_order('{$product.reference_image_id|default:null}', 1, this);"/>
											<span class="price-value {if $product.discount_active}discount{/if}">
												{if is_numeric($user_data.pro_machines_coef)}
													{if $product.discount_active}
														<span class="old-price-value">&nbsp;&nbsp;{($product.price_pvn*$user_data.pro_machines_coef)|number_format:2:'.':''} {$product.curr_symbol}&nbsp;&nbsp;</span>
														{($product.discount_price_pvn*$user_data.pro_machines_coef)|number_format:2:'.':''}
													{else}
														{($product.price_pvn*$user_data.pro_machines_coef)|number_format:2:'.':''}
													{/if}
												{else}
													{if $product.discount_active}
														<span class="old-price-value">&nbsp;&nbsp;{$product.price_pvn|number_format:2:'.':''} {$product.curr_symbol}&nbsp;&nbsp;</span>
														{$product.discount_price_pvn|number_format:2:'.':''}
													{else}
														{$product.price_pvn|number_format:2:'.':''}
													{/if}
												{/if}
												{$product.curr_symbol}
											</span>
											<div class="price-order-info" {if $product.reference_balance > 0}style="display: none;"{/if}>* {__('order_checkout.error_order_balance_delivery')}</div>
										{elseif $product.pro_category_setting_id == 20}
											<input type="button" value="{if $product.reference_balance > 0}{__('products.add_to_cart')}{else}{__('products.add_to_cart_order')}{/if}" onclick="add_to_order('{$product.reference_image_id|default:null}', 1, this);"/>
											<span class="price-value {if $product.discount_active}discount{/if}">
												{if is_numeric($user_data.pro_accessories_coef)}
													{if $product.discount_active}
														<span class="old-price-value">&nbsp;&nbsp;{($product.price_pvn*$user_data.pro_accessories_coef)|number_format:2:'.':''} {$product.curr_symbol}&nbsp;&nbsp;</span>
														{($product.discount_price_pvn*$user_data.pro_accessories_coef)|number_format:2:'.':''}
													{else}
														{($product.price_pvn*$user_data.pro_accessories_coef)|number_format:2:'.':''}
													{/if}
												{else}
													{if $product.discount_active}
														<span class="old-price-value">&nbsp;&nbsp;{$product.price_pvn|number_format:2:'.':''} {$product.curr_symbol}&nbsp;&nbsp;</span>
														{$product.discount_price_pvn|number_format:2:'.':''}
													{else}
														{$product.price_pvn|number_format:2:'.':''}
													{/if}
												{/if}
												{$product.curr_symbol}
											</span>
											<div class="price-order-info" {if $product.reference_balance > 0}style="display: none;"{/if}>* {__('order_checkout.error_order_balance_delivery')}</div>
										{else}												
											<input type="button" value="{if $product.reference_balance > 0}{__('products.add_to_cart')}{else}{__('products.add_to_cart_order')}{/if}" onclick="add_to_order('{$product.reference_image_id|default:null}', 1, this);"/>
											<span class="price-value {if $product.discount_active}discount{/if}">
												{if $product.discount_active}
													<span class="old-price-value">&nbsp;&nbsp;{$product.price_pvn|number_format:2:'.':''} {$product.curr_symbol}&nbsp;&nbsp;</span>
													{$product.discount_price_pvn|number_format:2:'.':''}
												{else}
													{$product.price_pvn|number_format:2:'.':''}
												{/if}
												{$product.curr_symbol}
											</span>
											<div class="price-order-info" {if $product.reference_balance > 0}style="display: none;"{/if}>* {__('order_checkout.error_order_balance_delivery')}</div>
										{/if}
									{else}
										<div class="pro-not-allowed">
											{__('products.pro_not_allowed')}
										</div>
									{/if}
								</div>
								
								<div class="social">
									<div class="fb-like" data-href="{$base_url}{$page.full_alias}/{$product.l_category_alias}-c{$product.category_id}/{$product.l_alias}-i{$product.id}" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
									<a href="https://twitter.com/share" class="twitter-share-button">Tweet</a>
									<script>!function(d,s,id) { var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)) { js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs); } } (document, 'script', 'twitter-wjs');</script>	
								</div>
								
								{if count($collection_products) > 0}
									<div class="collection">
										<h2>{__('products.collection_list')}</h2>
										
										<div class="collection-list">
											{foreach item="data" from=$collection_products}
												<a class="item" href="{$base_url}{$page.full_alias}/{if !empty($data.l_category_parent_alias)}{$data.l_category_parent_alias}/{/if}{$data.l_category_alias}-c{$data.category_id}/{$data.l_alias}-i{$data.id}">
													<span class="image-wrapper">
														<img src="{$base_url}{$data.reference_image_src|thumb}" class="vAlign"/>
													</span>
													<span class="title">{$data.l_1_title}</span>
												</a>
											{/foreach}
											<div class="clear"></div>
										</div>
									</div>
								{/if}
							</div>
							<div class="clear"></div>
						</div>
					</div>
					{if !empty($product.l_4_enabled)}
						<div class="section">
							<div class="data-block spec">
								<div class="main-image">
									<div class="image-wrapper">
										<img src="{$base_url}{$product.l_4_image_src}"/>
										{if !empty($product.l_4_manual_src)}
											<div class="manual">
												<a href="{$base_url}{$product.l_4_manual_src}" target="_blank">{__('products.user_manual')}</a>
											</div>
										{/if}
									</div>
									
								</div>
								<div class="main-text">
									<h1>{__('products.details')}</h1>
									<div class="text">
										{$product.l_4_content}
									</div>
									
									<div class="functions">
										{foreach item=setting from=$functions name="product_settings"}
											<div class="item">
												{if !empty($setting.image_src)}
													<img src="{$base_url}{$setting.image_src}" class="vAlign"/>
												{/if}
												<div class="vAlign">{$setting.title}</div>
											</div>
											{if $smarty.foreach.product_settings.iteration%2 == 0}<div class="clear"></div>{/if}
										{/foreach}	
										<div class="clear"></div>						
									</div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					{/if}
				{else}
					<div class="section">
						<div class="data-block">
						
						</div>					
					</div>
				{/if}
			{/if}
		</div>
	</div>
	<div class="product-filter" id="product_filter">
		{include file=$this_file action="product_filter"}
	</div>
	
	<a href="javascript:prevBlock()" class="section-prev" id="data_block_prev"></a>
	<a href="javascript:nextBlock()" class="section-next" id="data_block_next"></a>
	<a href="{$base_url}{$page.full_alias}/{$product.l_category_alias}-c{$product.category_id}" class="data-block-close"></a>
{/if}