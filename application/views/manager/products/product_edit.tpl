{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action eq 'load'}	
	<form method="POST" action="manager/{$link}/save" style="width: 950px;" id="product_form">		
		<div id="resource_buttons" class="">
			{__($page_title)}: {$product.lang[1].1_title|default:$product.lang[2].1_title|default:$product.lang[3].1_title|default:''|strip_tags}
			<div id="buttons" class="ui-widget ui-widget-content ui-corner-all">
				<button type="submit">{__('Save')}</button>
				<button type="button" onclick="$('#return_form').submit();">{__('Cancel')}</button>
				{if !empty($product.id) && $product.id != 'new'}&nbsp;&nbsp;&nbsp;&nbsp;<button type="button"  id="product_delete">{__('Delete')}</button>{/if}
			</div>
		</div>
		<div id="resource_tabs">
			<ul>
				<li><a href="#general">{__('General')}</a></li>
			</ul>
			<div id="general">
				<input type="hidden" name="product_id" id="product_id" value="{$product.id|default:'new'}"/>
				<input type="hidden" name="return_url" value="{$return_url}"/>
				{*<input type='hidden' id='pageListJSON' value='[["",""]{include file=$this_file action="link_list" parent_id="0" level="0"}]'/>*}
					
				<table class="resource_data">
					<tr>
						<th style="width: 170px;">{__('Status')}:</th>
						<td>
							<select name="status_id" style="width: 228px;">
								{foreach item="data" from=$status}
									<option value="{$data.id}" {if $product.status_id|default:1 == $data.id}selected="selected"{/if}>{__($data.description)}</option>
								{/foreach}								
							</select>
						</td>
					</tr>
					<tr style="display: none;">
						<th>{__('Reference')}:</th>
						<td><input type="text" name="reference" value="{$product.reference|default:''}"/></td>
					</tr>
					<tr>
						<th>{__('Category')}:</th>
						<td>
							{if $product.id|default:'new' == 'new'}
								<select name="category_id" style="width: 228px;" onchange="category_settings();">
									{foreach item="data" from=$categories}
										<option value="{$data.id}" {if $data.id|default:array()|in_array:$category_array}selected="selected"{/if}>{__($data.title)}</option>
									{/foreach}								
								</select>		
							{else}
								{foreach item="data" from=$categories}
									{if $data.id|default:array()|in_array:$category_array}
										<input type="hidden" name="category_id" value="{$data.id}"/>
										{__($data.title)}
									{/if}
								{/foreach}
							{/if}				
						</td>
					</tr>
					<tr>
						<th>{__('Price (w/o VAT)')}:</th>
						<td>
							<div>
							<input type="text" name="price" id="price" value="{$product.price|default:0|number_format:4:'.':''}" style="width: 162px; float: left; margin-right: 3px;" onchange="update_discount();"  onkeyup="update_discount();"/>	
							<select name="currency_id" style="width: 55px;">
								{foreach item="data" from=$currencies}
									<option value="{$data.id}" {if $product.currency_id|default:null == $data.id}selected="selected"{/if}>{$data.name}</option>
								{/foreach}								
							</select>	
							</div>						
						</td>
					</tr>
					<tr style="display: none;">
						<th>{__('Units')}:</th>
						<td>
							<select name="unit_type_id" style="width: 140px;">
								{foreach item="data" from=$units}
									<option value="{$data.id}" {if $product.unit_type_id|default:null == $data.id}selected="selected"{/if}>{__($data.description)}</option>
								{/foreach}								
							</select>						
						</td>
					</tr>
					<tr>
						<th>{__('VAT')}:</th>
						<td>
							<select name="vat_type_id" style="width: 140px;">
								{foreach item="data" from=$vat_types}
									<option value="{$data.id}" {if $product.vat_type_id|default:21 == $data.id}selected="selected"{/if}>{$data.description}</option>
								{/foreach}								
							</select>
						</td>
					</tr>
					<tr>
						<th style="vertical-align: top; padding-top: 5px;">{__('Discount price (w/o VAT)')}:</th>
						<td>
							<input type="checkbox" name="discount_active" id="discount_active" value="1" {if $product.discount_active|default:0 == 1}checked="checked"{/if} onchange="toggle_discount();" style="vertical-align: middle;"/><input type="text" name="discount_price" id="discount_price" value="{$product.discount_price|default:0|number_format:4:'.':''}" style="width: 97px; vertical-align: middle;" {if $product.discount_active|default:0 != 1}disabled="disabled"{/if} onchange="update_discount();"  onkeyup="update_discount();"/>	
							<select name="discount_color" id="discount_color" style="width: 100px;" {if $product.discount_active|default:0 != 1}disabled="disabled"{/if}>
								<option value="green" {if $product.discount_color|default:null == 'green'}selected="selected"{/if}>zaļš</option>
								<option value="lv_red" {if $product.discount_color|default:null == 'lv_red'}selected="selected"{/if}>zaļš ar LV karogu</option>								
							</select>
							<span class="discaount-price" style="margin-left: 10px;" id="discount_percents">{if $product.discount_active|default:0 == 1}{(100-$product.discount_percents|default:0)|number_format:0:'.':''}%{/if}</span>
						</td>
					</tr>
					<tr id="coffee_gift_tr">
						<th style="vertical-align: top; padding-top: 5px;">{__('Coffee gift (VAT included)')}:</th>
						<td>
							<input type="checkbox" name="coffee_gift_active" id="coffee_gift_active" value="1" {if $product.coffee_gift_active|default:0 == 1}checked="checked"{/if} onchange="toggle_coffee_gift();" style="vertical-align: middle;"/><input type="text" name="coffee_gift_amount" id="coffee_gift_amount" value="{$product.coffee_gift_amount|default:0|number_format:2:'.':''}" style="width: 230px; vertical-align: middle;" {if $product.coffee_gift_active|default:0 != 1}disabled="disabled"{/if}/>
						</td>
					</tr>
					<tr>
						<th style="vertical-align: top; padding-top: 5px;">{__('Additional options')}:</th>
						<td>
							<input type="checkbox" name="new" value="1" {if $product.new|default:0 == 1}checked="checked"{/if} style="vertical-align: middle;"/>{__('New product')|lower}<br>
							<input type="checkbox" name="gift" value="1" {if $product.gift|default:0 == 1}checked="checked"{/if} style="vertical-align: middle;"/>{__('Gift product')|lower}<br>
						</td>
					</tr>
				</table>
			</div>
		</div>	
		<div id="resource_lang_tabs">
			<ul>
				{foreach item=lang from=$languages}
					{if $lang.id == $def_lang_id}{$def_lang=$lang}{/if} 
					<li><a href="#lang-{$lang.id}">{__($lang.name)}</a></li>
				{/foreach}
				<li id="prices_tab"><a href="#prices">{__('Colors / Icons')}</a></li>
				<li><a href="#categories">{__('Settings')}</a></li>
				<li id="gallery_tab"><a href="#gallery">{__('Gallery')}</a></li>
				<input type="hidden" id="def_lang_id" value="{$def_lang.id|default:null}"/>
			</ul>
			{foreach item=lang from=$languages}
				<div id="lang-{$lang.id}" class="lang-tab">
					{if $product.lang[$lang.id].id|default:'' ne ''}
						{include file=$this_file action='lang_tab'}
					{else}
						{include file=$this_file action='lang_tab_empty'}					
					{/if}
				</div>
			{/foreach}
			<div id="categories">
				{include file=$this_file action="category_list" parent_id="0" level="0"}
			</div>
			<div id="prices">
				{include file=$this_file action="prices_tab"}
			</div>
			<div id="gallery">
				{include file=$this_file action="gallery_tab"}
			</div>
		</div>
	</form>
	
	<form method="post" action="{$base_url}manager/{$link}/load{if !empty($filter.page)}?p={$filter.page}{/if}" id="return_form">
		<input type="hidden" name="order_by" value="{$filter.order_by|default:''|escape}"/>
		<input type="hidden" name="search" value="{$filter.search|default:''|escape}"/>
		{foreach item="category_id" from=$filter.category_id|default:array()}<input type="hidden" name="category_id[]" value="{$category_id|escape}"/>{/foreach}
		{foreach item="status_id" from=$filter.status_id|default:array()}<input type="hidden" name="status_id[]" value="{$status_id|escape}"/>{/foreach}
		<input type="hidden" name="page" value="{$filter.page|default:''|escape}"/>
		<input type="submit" style="display: none;"/>
	</form>	
	
	<script type="text/javascript">
		var product_link = '{$link}';
		var product_delete_title = '{__("Are you sure?")}';
		var product_delete_msg = '{__("Are you sure to delete this Product?")}';
		var product_image_delete_title = '{__("Are you sure?")}';
		var product_image_delete_msg = '{__("Are you sure to delete this Product image?")}';
		var product_price_delete_title = '{__("Are you sure?")}';
		var product_price_delete_msg = '{__("Are you sure to delete this Product price?")}';
		{section name=i loop=$languages}{$languages[i].manager_name=__($languages[i].name)}{/section}
		var lang_data = {json_encode($languages)};

		function content_lang_buttons(obj) {
			$('a[action=create_lang]', obj).click(function() {
				page_loading('{__("Loading...")}');
				var panel = $('#data_panel');
				var tab = $(this).closest('div.ui-tabs-panel');
				
				$.post(base_url+'manager/'+product_link+'/load_lang_tab', {
					lang_id:			$(tab).find('#language_id').val(),
					new_id:				$(panel).find('#general #new_id').val()
				}, function(data) {
					var obj = $(tab).html(data);
					
					content_init(obj);
					page_loading('');
				});
			});


			$('a[action=copy_lang]', obj).click(function() {
				var panel = $('#data_panel');
				var tab = $(this).closest('div.ui-tabs-panel');

				var dlg = $('<div></div>').dialog({
					modal: true,
					close: function() { $(dlg).remove(); },
					resizable: false,
					title: '{__("Select language")}',
					buttons: {
						'{__("Copy")}': function() {
							page_loading('Loading...');
							$.post(base_url+'manager/'+product_link+'/load_lang_tab', {
								lang_id:			$(tab).find('#language_id').val(),
								new_id:				$(panel).find('#general #new_id').val(),
								lang_data: 			$('#lang-'+$('select[name="language_id"]', dlg).val()).postData()
							}, function(data) {
								var obj = $(tab).html(data);
								
								content_init(obj);
								page_loading('');
							});
							$(dlg).dialog('close');
						},
						'{__("Cancel")}': function() {
							$(dlg).dialog('close');
						}
					}
				}).showActivity();
				$(dlg).append('<div style="font-size: 14px; margin-bottom: 5px;">{__("Select language from which to copy")}:</div><select name="language_id"></select>');
				for (var i=0; i<lang_data.length; i++) {
					if (lang_data[i].id != $(tab).find('#language_id').val()) $('select[name="language_id"]', dlg).append('<option value="'+lang_data[i].id+'">'+lang_data[i].manager_name+'</option>');
				}
				$(dlg).hideActivity();
			});

			$('a[action=delete_lang]', obj).click(function() {
				var panel = $('#data_panel');
				var tab = $(this).closest('div.ui-tabs-panel');
				
				jConfirm('{__("Are you sure to delete this translation")}?', '{__("Are you sure")}?', function(r) {
					if (r) {
						page_loading('Loading...');
						
						$.post(base_url+'manager/'+product_link+'/remove_lang_tab', {
							lang_id:			$(tab).find('#language_id').val(),
							lang_data: 			$(tab).postData()							
						}, function(data) {
							var obj = $(tab).html(data);							
							content_init(obj);
							page_loading('');
						});
					}
				});
			});
		}
	</script>
{/if}

{* if $action eq 'link_list'}
	{foreach item=parent from=$parents_data name="link_list"}
		{if $parent.parent_id eq $parent_id}
			,["{section name=waistsizes start=0 loop=$level step=1}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$parent.admin_title}","{$parent.id}"]{include file=$this_file action="link_list" parent_id=$parent.id level=$level+1}
		{/if}
	{/foreach}
{/if *}

{if $action eq "category_list"}
	{foreach item="category" from=$categories}
		{if $category.parent_id eq $parent_id}
			<div style="margin-left: {$level*25}px;" id="categories">
				{*
				<div class="category">
					<input type="radio" name="category_id" value="{$category.id}" {if $category.type_id == 20}disabled{/if} {if $category.id|in_array:$category_array}checked{/if} onclick=""/>
					<span style="font-weight: bold; color: #000000;">{$category.title}</span>
				</div>
				*}
				<div class="category-settings" {if !$category.id|in_array:$category_array}style="display: none;"{/if} id="category_settings_{$category.id}">
					{foreach item="setting" from=$category.settings}
						<div class="item">
							<div class="title">{$setting.l_title}</div>
							<div class="values">
								{foreach item="value" from=$setting.values}
									<div class="value-item">
										<input type="checkbox" name="{$category.id}_setting_value_id[]" value="{$value.id}" {if in_array($value.id, $selected_setting_values)}checked="checked"{/if}/>{$value.l_title}
									</div>
								{/foreach}
								<div class="clear"></div>
							</div>
						</div>
					{/foreach}
				</div>
			</div>
			{include file=$this_file action="category_list" parent_id="{$category.id}" level="{$level+1}"}
		{/if}
	{/foreach}
{/if}

{if $action eq 'lang_tab'}
	<input type="hidden" name="product_content_id[]" value="{$product.lang[$lang.id].id}">
	<input type="hidden" name="language_id[]" id="language_id" value="{$lang.id}">
	<table class="resource_data">	
	
		<tr class="cat cat-all">
			<th colspan="2" class="category-title" style="vertical-align: bottom;">
				<div style="position: relative;">
					{__('Main page')}
					<a class="button" action="delete_lang" style="position: absolute; right: 0px; bottom: 4px;">{__('Delete translation')}</a>
				</div>
			</th>
		</td>
		<tr class="cat cat-all category-title-next">
			<td rowspan="2" style="width: 300px; vertical-align: top;" class="center">
				<div class="image_icon" style="margin-top: 10px;">
					<div class="cat-2-image" style="padding-top: 115px;">{__('Image from tab "Colors / Icons"')}</div>
					<div class="cat-all-image">
						<input type="hidden" name="1_image_src[]" value="{$product.lang[$lang.id].1_image_src|default:''}"/>
						<img class="vAlign" {if $product.lang[$lang.id].1_image_src|default:'' ne ''}src="{$product.lang[$lang.id].1_image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
					</div>
				</div>
				<div class="cat-all-image">
					<a class="button">{__('Browse')}<input id="1_image_src_{$lang.id}" class="file_upload_input" type="file"/></a>
					<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
				</div>
			</td>
			<td>
				<b>{__('Title')}</b><br/>
				<input type="text" name="1_title[]" id="title" value="{$product.lang[$lang.id].1_title|default:''|escape:'html'}" style="width: 517px;">
				
				<div class="flavor" style="margin-top: 2px;">
					<b>{__('Flavor')}</b><br/>
					<input type="text" name="1_flavor[]" value="{$product.lang[$lang.id].1_flavor|default:''|escape:'html'}" style="width: 517px;">
				</div>
			</td>
		</tr>
		<tr class="cat cat-all">
			<td>
				<b>{__('Short description')}</b><br/>
				<textarea name="1_description[]" id="description_{$lang.id}" class="editor_simple">{$product.lang[$lang.id].1_description|default:''}</textarea>
			</td>
		</tr>
		
		
		
		
		<tr class="cat cat-1">
			<th colspan="2" class="category-title">
				<input type="checkbox" name="0_enabled[]" value="1" {if $product.lang[$lang.id].0_enabled|default:0 == 1}checked="checked"{/if} onclick="content_data_toggle(this);" data-group="recipe"/>
				{__('Delicious recipe')}
			</th>
		</td>
		<tr class="category-title-next recipe no-data cat cat-1" {if $product.lang[$lang.id].0_enabled|default:0 == 1}style="display: none;"{/if}>
			<td colspan="2">
				{__('Not specified!')}
			</td>
		</tr>
		<tr class="recipe cat cat-1" {if $product.lang[$lang.id].0_enabled|default:0 == 0}style="display: none;"{/if}>
			<td colspan="2" style="padding-left: 25px;">
				<b>{__('Recipe')}</b><br/>
				<select name="0_recipe_id[]" style="width: 772px;" onchange="recipe_change(this);">
					<option value="">--- none ---</option>
					{foreach item="recipe" from=$recipes}
						<option value="{$recipe.id}" {if $product.lang[$lang.id].0_recipe_id|default:null == $recipe.id}selected="selected"{/if}>{$recipe.admin_title}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr class="recipe cat cat-1" {if $product.lang[$lang.id].0_enabled|default:0 == 0}style="display: none;"{/if}>
			<td colspan="2" style="padding: 10px 0px 0px 25px;">
				<div class="recipe-preview"></div>
			</td>
		</tr>
				
		
		
		<tr class="cat cat-2">
			<th colspan="2" class="category-title">
				<input type="checkbox" name="2_enabled[]" value="1" {if $product.lang[$lang.id].2_enabled|default:0 == 1}checked="checked"{/if} onclick="content_data_toggle(this);" data-group="features"/>
				{__('Features')}
			</th>
		</td>
		<tr class="category-title-next features no-data cat cat-2" {if $product.lang[$lang.id].2_enabled|default:0 == 1}style="display: none;"{/if}>
			<td colspan="2">
				{__('Not specified!')}
			</td>
		</tr>
		<tr class="category-title-next features cat cat-2" {if $product.lang[$lang.id].2_enabled|default:0 == 0}style="display: none;"{/if}>
			<td class="center">
				<div class="image_icon" style="margin-top: 9px; padding-top: 115px; height: 125px;">{__('Gallery from tab "Gallery"')}</div>
			</td>
			<td>
				<b>{__('Features')}</b><br/>
				<textarea name="2_features[]" id="features_{$lang.id}" class="editor_simple">{$product.lang[$lang.id].2_features|default:''}</textarea>
			</td>
		</tr>
		
		
		
		<tr class="cat cat-2">
			<th colspan="2" class="category-title">
				<input type="checkbox" name="3_enabled[]" value="1" {if $product.lang[$lang.id].3_enabled|default:0 == 1}checked="checked"{/if} onclick="content_data_toggle(this);" data-group="video"/>
				{__('Video instruction')}
			</th>
		</td>
		<tr class="category-title-next video no-data cat cat-2" {if $product.lang[$lang.id].3_enabled|default:0 == 1}style="display: none;"{/if}>
			<td colspan="2">
				{__('Not specified!')}
			</td>
		</tr>
		<tr class="video cat cat-2" {if $product.lang[$lang.id].3_enabled|default:0 == 0}style="display: none;"{/if}>
			<td class="category-title-next">
				<div class="video-preview" style="width: 290px; height: 250px; border: 1px solid #A0A0A0; margin: 5px 0px;"></div>
			</td>
			<td>
				<b>{__('Type')}</b><br/>
				<select name="3_video_type_id[]" style="width: 495px;" onchange="init_video_type($(this).closest('tr'));">
					{foreach item="data" from=$video_types|default:array()}
						<option value="{$data.id}" {if $product.lang[$lang.id].3_video_type_id|default:null == $data.id}selected="selected"{/if}>{__($data.description)}</option>
					{/foreach}								
				</select>
				<script type="text/javascript">
					var video_links = [];
					{foreach item="data" from=$video_types|default:array()}
						video_links[{$data.id}] = '{$data.value|escape:"javascript"}';
					{/foreach}
				</script>
				<br/>
				<br/>
				<b>{__('Video ID')}</b><br/>
				<input type="text" name="3_video_link[]" value="{$product.lang[$lang.id].3_video_link|default:''}" style="width: 517px;" onchange="init_video_type($(this).closest('tr'));"/>			
			</td>
		</tr>
		
		
		
		<tr class="cat cat-2 cat-8">
			<th colspan="2" class="category-title">
				<input type="checkbox" name="4_enabled[]" value="1" {if $product.lang[$lang.id].4_enabled|default:0 == 1}checked="checked"{/if} onclick="content_data_toggle(this);" data-group="specs"/>
				<span class="cat-title" data-title="{__('Technical specification')}" data-title-8="{__('Details')}">{__('Technical specification')}</span>
			</th>
		</td>
		<tr class="category-title-next specs no-data cat cat-2 cat-8" {if $product.lang[$lang.id].4_enabled|default:0 == 1}style="display: none;"{/if}>
			<td colspan="2">
				{__('Not specified!')}
			</td>
		</tr>
		<tr class="category-title-next specs cat cat-2 cat-8" {if $product.lang[$lang.id].4_enabled|default:0 == 0}style="display: none;"{/if}>
			<td rowspan="2" style="width: 300px;" class="center">
				<div class="image_icon" style="height: ">
					<input type="hidden" name="4_image_src[]" value="{$product.lang[$lang.id].4_image_src|default:''}"/>
					<img class="vAlign" {if $product.lang[$lang.id].4_image_src|default:'' ne ''}src="{$product.lang[$lang.id].4_image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
				</div>
				<a class="button">{__('Browse')}<input id="4_image_src_{$lang.id}" class="file_upload_input" type="file"/></a>
				<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
			</td>
			<td>
				<b><span class="cat-title" data-title="{__('Technical specification')}" data-title-8="{__('Details')}">{__('Technical specification')}</span></b><br/>
				<textarea name="4_content[]" id="4_content_{$lang.id}" class="editor_simple">{$product.lang[$lang.id].4_content|default:''}</textarea>
			</td>
		</tr>
		<tr class="specs cat cat-2 cat-8" {if $product.lang[$lang.id].4_enabled|default:0 == 0}style="display: none;"{/if}>
			<td>
				<b>{__('User manual')}</b><br/>
				<input type="hidden" class="file-name-input" name="4_manual_src[]" value="{$product.lang[$lang.id].4_manual_src|default:''}"/>
				<input type="text" class="file-name" value="{$product.lang[$lang.id].4_manual_src|default:''}" disabled="disabled" style="width: 370px; color: #000000;"/>
				<a class="button">{__('Browse')}<input id="4_manual_src_{$lang.id}" class="manual_upload_input" type="file"/></a>
				<a class="button" onclick="openManualFileRemove($(this).closest('td').find('.file-name-input'))">{__('Remove')}</a>
				<div class="clear"></div>
			</td>
		</tr>
		
		
		
		<tr class="cat cat-2">
			<th colspan="2" class="category-title">
				<input type="checkbox" name="5_enabled[]" value="1" {if $product.lang[$lang.id].5_enabled|default:0 == 1}checked="checked"{/if} onclick="content_data_toggle(this);" data-group="content-1"/>
				{__('Content 1')}
			</th>
		</td>
		<tr class="category-title-next content-1 no-data cat cat-2" {if $product.lang[$lang.id].5_enabled|default:0 == 1}style="display: none;"{/if}>
			<td colspan="2">
				{__('Not specified!')}
			</td>
		</tr>
		<tr class="category-title-next content-1 cat cat-2" {if $product.lang[$lang.id].5_enabled|default:0 == 0}style="display: none;"{/if}>
			<td rowspan="2" style="width: 300px;" class="center">
				<div class="image_icon">
					<input type="hidden" name="5_image_src[]" value="{$product.lang[$lang.id].5_image_src|default:''}"/>
					<img class="vAlign" {if $product.lang[$lang.id].5_image_src|default:'' ne ''}src="{$product.lang[$lang.id].5_image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
				</div>
				<a class="button">{__('Browse')}<input id="5_image_src_{$lang.id}" class="file_upload_input" type="file"/></a>
				<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
				<select name="5_image_position[]" style="width: 70px; margin-left: 10px;">
					<option value="v" {if $product.lang[$lang.id].5_image_position|default:'v' == 'v'}selected="selected"{/if}>{__('vertical')}</option>
					<option value="h" {if $product.lang[$lang.id].5_image_position|default:'v' == 'h'}selected="selected"{/if}>{__('horizontal')}</option>
				</select>
			</td>
			<td>
				<b>{__('Heading')}</b><br/>
				<input type="text" name="5_title[]" value="{$product.lang[$lang.id].5_title|default:''|escape:'html'}" style="width: 517px;">
			</td>
		</tr>
		<tr class="content-1 cat cat-2" {if $product.lang[$lang.id].5_enabled|default:0 == 0}style="display: none;"{/if}>
			<td>
				<b>{__('Content')}</b><br/>
				<textarea name="5_content[]" id="5_content_{$lang.id}" class="editor_simple">{$product.lang[$lang.id].5_content|default:''}</textarea>
			</td>
		</tr>
		
		
		
		<tr class="cat cat-2">
			<th colspan="2" class="category-title">
				<input type="checkbox" name="6_enabled[]" value="1" {if $product.lang[$lang.id].6_enabled|default:0 == 1}checked="checked"{/if} onclick="content_data_toggle(this);" data-group="content-2"/>
				{__('Content 2')}
			</th>
		</td>
		<tr class="category-title-next content-2 no-data cat cat-2" {if $product.lang[$lang.id].6_enabled|default:0 == 1}style="display: none;"{/if}>
			<td colspan="2">
				{__('Not specified!')}
			</td>
		</tr>
		<tr class="category-title-next content-2 cat cat-2" {if $product.lang[$lang.id].6_enabled|default:0 == 0}style="display: none;"{/if}>
			<td rowspan="2" style="width: 300px;" class="center">
				<div class="image_icon">
					<input type="hidden" name="6_image_src[]" value="{$product.lang[$lang.id].6_image_src|default:''}"/>
					<img class="vAlign" {if $product.lang[$lang.id].6_image_src|default:'' ne ''}src="{$product.lang[$lang.id].6_image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
				</div>
				<a class="button">{__('Browse')}<input id="6_image_src_{$lang.id}" class="file_upload_input" type="file"/></a>
				<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
				<select name="6_image_position[]" style="width: 70px; margin-left: 10px;">
					<option value="v" {if $product.lang[$lang.id].6_image_position|default:'v' == 'v'}selected="selected"{/if}>{__('vertical')}</option>
					<option value="h" {if $product.lang[$lang.id].6_image_position|default:'v' == 'h'}selected="selected"{/if}>{__('horizontal')}</option>
				</select>
			</td>
			<td>
				<b>{__('Heading')}</b><br/>
				<input type="text" name="6_title[]" value="{$product.lang[$lang.id].6_title|default:''|escape:'html'}" style="width: 517px;">
			</td>
		</tr>
		<tr class="content-2 cat cat-2" {if $product.lang[$lang.id].6_enabled|default:0 == 0}style="display: none;"{/if}>
			<td>
				<b>{__('Content')}</b><br/>
				<textarea name="6_content[]" id="6_content_{$lang.id}" class="editor_simple">{$product.lang[$lang.id].6_content|default:''}</textarea>
			</td>
		</tr>
		
		
		
		<tr class="cat cat-2">
			<th colspan="2" class="category-title">
				<input type="checkbox" name="7_enabled[]" value="1" {if $product.lang[$lang.id].7_enabled|default:0 == 1}checked="checked"{/if} onclick="content_data_toggle(this);" data-group="content-3"/>
				{__('Content 3')}
			</th>
		</td>
		<tr class="category-title-next content-3 no-data cat cat-2" {if $product.lang[$lang.id].7_enabled|default:0 == 1}style="display: none;"{/if}>
			<td colspan="2">
				{__('Not specified!')}
			</td>
		</tr>
		<tr class="category-title-next content-3 cat cat-2" {if $product.lang[$lang.id].7_enabled|default:0 == 0}style="display: none;"{/if}>
			<td rowspan="2" style="width: 300px;" class="center">
				<div class="image_icon">
					<input type="hidden" name="7_image_src[]" value="{$product.lang[$lang.id].7_image_src|default:''}"/>
					<img class="vAlign" {if $product.lang[$lang.id].7_image_src|default:'' ne ''}src="{$product.lang[$lang.id].7_image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
				</div>
				<a class="button">{__('Browse')}<input id="7_image_src_{$lang.id}" class="file_upload_input" type="file"/></a>
				<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
				<select name="7_image_position[]" style="width: 70px; margin-left: 10px;">
					<option value="v" {if $product.lang[$lang.id].7_image_position|default:'v' == 'v'}selected="selected"{/if}>{__('vertical')}</option>
					<option value="h" {if $product.lang[$lang.id].7_image_position|default:'v' == 'h'}selected="selected"{/if}>{__('horizontal')}</option>
				</select>
			</td>
			<td>
				<b>{__('Heading')}</b><br/>
				<input type="text" name="7_title[]" value="{$product.lang[$lang.id].7_title|default:''|escape:'html'}" style="width: 517px;">
			</td>
		</tr>
		<tr class="content-3 cat cat-2" {if $product.lang[$lang.id].7_enabled|default:0 == 0}style="display: none;"{/if}>
			<td>
				<b>{__('Content')}</b><br/>
				<textarea name="7_content[]" id="7_content_{$lang.id}" class="editor_simple">{$product.lang[$lang.id].7_content|default:''}</textarea>
			</td>
		</tr>
		
		
		
		<tr class="cat cat-2">
			<th colspan="2" class="category-title">
				<input type="checkbox" name="8_enabled[]" value="1" {if $product.lang[$lang.id].8_enabled|default:0 == 1}checked="checked"{/if} onclick="content_data_toggle(this);" data-group="content-4"/>
				{__('Content 4')}
			</th>
		</td>
		<tr class="category-title-next content-4 no-data cat cat-2" {if $product.lang[$lang.id].8_enabled|default:0 == 1}style="display: none;"{/if}>
			<td colspan="2">
				{__('Not specified!')}
			</td>
		</tr>
		<tr class="category-title-next content-4 cat cat-2" {if $product.lang[$lang.id].8_enabled|default:0 == 0}style="display: none;"{/if}>
			<td rowspan="2" style="width: 300px;" class="center">
				<div class="image_icon">
					<input type="hidden" name="8_image_src[]" value="{$product.lang[$lang.id].8_image_src|default:''}"/>
					<img class="vAlign" {if $product.lang[$lang.id].8_image_src|default:'' ne ''}src="{$product.lang[$lang.id].8_image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
				</div>
				<a class="button">{__('Browse')}<input id="8_image_src_{$lang.id}" class="file_upload_input" type="file"/></a>
				<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
				<select name="8_image_position[]" style="width: 70px; margin-left: 10px;">
					<option value="v" {if $product.lang[$lang.id].8_image_position|default:'v' == 'v'}selected="selected"{/if}>{__('vertical')}</option>
					<option value="h" {if $product.lang[$lang.id].8_image_position|default:'v' == 'h'}selected="selected"{/if}>{__('horizontal')}</option>
				</select>
			</td>
			<td>
				<b>{__('Heading')}</b><br/>
				<input type="text" name="8_title[]" value="{$product.lang[$lang.id].8_title|default:''|escape:'html'}" style="width: 517px;">
			</td>
		</tr>
		<tr class="content-4 cat cat-2" {if $product.lang[$lang.id].8_enabled|default:0 == 0}style="display: none;"{/if}>
			<td>
				<b>{__('Content')}</b><br/>
				<textarea name="8_content[]" id="8_content_{$lang.id}" class="editor_simple">{$product.lang[$lang.id].8_content|default:''}</textarea>
			</td>
		</tr>
		
		
		
		<tr class="cat cat-2">
			<th colspan="2" class="category-title">
				<input type="checkbox" name="9_enabled[]" value="1" {if $product.lang[$lang.id].9_enabled|default:0 == 1}checked="checked"{/if} onclick="content_data_toggle(this);" data-group="content-5"/>
				{__('Content 5')}
			</th>
		</td>
		<tr class="category-title-next content-5 no-data cat cat-2" {if $product.lang[$lang.id].9_enabled|default:0 == 1}style="display: none;"{/if}>
			<td colspan="2">
				{__('Not specified!')}
			</td>
		</tr>
		<tr class="category-title-next content-5 cat cat-2" {if $product.lang[$lang.id].9_enabled|default:0 == 0}style="display: none;"{/if}>
			<td rowspan="2" style="width: 300px;" class="center">
				<div class="image_icon">
					<input type="hidden" name="9_image_src[]" value="{$product.lang[$lang.id].9_image_src|default:''}"/>
					<img class="vAlign" {if $product.lang[$lang.id].9_image_src|default:'' ne ''}src="{$product.lang[$lang.id].9_image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
				</div>
				<a class="button">{__('Browse')}<input id="9_image_src_{$lang.id}" class="file_upload_input" type="file"/></a>
				<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
				<select name="9_image_position[]" style="width: 70px; margin-left: 10px;">
					<option value="v" {if $product.lang[$lang.id].9_image_position|default:'v' == 'v'}selected="selected"{/if}>{__('vertical')}</option>
					<option value="h" {if $product.lang[$lang.id].9_image_position|default:'v' == 'h'}selected="selected"{/if}>{__('horizontal')}</option>
				</select>
			</td>
			<td>
				<b>{__('Heading')}</b><br/>
				<input type="text" name="9_title[]" value="{$product.lang[$lang.id].9_title|default:''|escape:'html'}" style="width: 517px;">
			</td>
		</tr>
		<tr class="content-5 cat cat-2" {if $product.lang[$lang.id].9_enabled|default:0 == 0}style="display: none;"{/if}>
			<td>
				<b>{__('Content')}</b><br/>
				<textarea name="9_content[]" id="9_content_{$lang.id}" class="editor_simple">{$product.lang[$lang.id].9_content|default:''}</textarea>
			</td>
		</tr>		
	</table>
{/if}

{if $action == 'lang_tab_empty'}
	<input type="hidden" id="product_content_id" value="none">
	<input type="hidden" id="language_id" value="{$lang.id}">
	{__('In this language page not exist')}<br>
	<br>
	<a class="button" action="create_lang" style="width: 330px;">{__('Create empty translation')}</a><br/>
	<a class="button" action="copy_lang" style="width: 330px; margin-top: 5px;">{__('Copy translation from other language')}</a>
{/if}

{if $action eq 'gallery_tab'}	
	<div class="gallery-data" id="gallery_data_div">
		{foreach item="data" from=$gallery_data name="gallery"}
			{include file=$this_file action="gallery_tab_edit"}
		{/foreach}
		<div class="clear"></div>
	</div>
	<div class="gallery-add">
		<div id="filelist"></div>
		<a class="button">
			{__('Add')}
			<input type="file" id="add-gallery-items">
		</a>
	</div>
{/if}

{if $action eq "gallery_tab_edit"}
	<div class="gallery-item">
		<div class="gallery-main-image" style="display: none;">
			<input type="radio" name="product_main_image_chk" onchange="changeMainImage(this);" {if $data.id|default:'new' eq $product.main_image_id|default:'0'}checked{/if} style="margin-right: 0px;"/> {__('Main image')}
			<input type="hidden" name="product_main_image[]" value="{if $data.id|default:'new' eq $product.main_image_id|default:'0'}1{else}0{/if}">
		</div>
		
		<div class="gallery-image-wrapper">
			<input type="hidden" name="product_image_id[]" value="{$data.id|default:'new'}">
			<input type="hidden" action="image" name="product_image_src[]" value="{$data.image_src|default:''}">
			<img {if $data.image_src|default:'' ne ''}src="{$data.image_src|default:''|thumb}"{/if} style="max-width: 198px; max-height: 170px;" class="vAlign">
		</div>
		<div style="display: none;">
			{foreach item=lang from=$languages}
				<input type="hidden" name="{$lang.id}_product_image_content_id[]" value="{$data.lang[$lang.id].id|default:'new'}">
				<span class="gallery-lang-title">{$lang.tag|strtoupper}:</span> <input type="text" name="{$lang.id}_product_image_content_title[]" value="{$data.lang[$lang.id].title|default:''}" style="width: 160px;"><br/>
			{/foreach}
		</div>
		
		<a href="#none" onclick="removeGalleryItem(this); return false;" title="{__('Remove')}" class="gallery-item-remove">×</a>
	</div>
{/if}

{* 
	PRICES 
*}
{if $action eq 'prices_tab'}
	<div style="font-size: 14px; font-weight: bold; margin-bottom: 10px;">{__('First image is as main product image!')}</div>

	<table class="data_table prices_table">
		<colgroup>
			<col style="width: 160px;"/>
			<col style="width: 130px;"/>
			<col style="width: 250px;"/>
			<col style="width: 120px;"/>
			{* foreach item=lang from=$languages}
				<col style="width: 120px;"/>
			{/foreach *}
			<col style="width: 120px;"/>
			<col style="width: 70px;"/>
		</colgroup>
	
		<thead>
			<tr>
				<th>{__('Image')}</th>
				<th>{__('Code')}</th>
				<th>{__('Reference')}</th>
				<th>{__('Color')}</th>
				{* foreach item=lang from=$languages}
					<th>
						{__('Title')}<br/>
						{__($lang.name)}
					</th>
				{/foreach *}
				<th>{__('Order Index')}</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$prices name="prices"}
				{include file=$this_file action="prices_tab_edit" iteration=$smarty.foreach.prices.iteration}
			{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="{count($languages)+5}">
					<button type="button" class="button" onclick="addPrice(this);">{__('Add')}</button>
				</td>
			</tr>
		</tfoot>
	</table>
{/if}

{if $action eq "prices_tab_edit"}
	<tr>
		<td class="center image_td">
			<input type="hidden" name="product_reference_id[]" value="{$data.id|default:'new'}"/>
		
			<div class="image_icon">
				<input type="hidden" name="product_reference_image_src[]" value="{$data.image_src|default:''}"/>
				<img {if $data.image_src|default:'' ne ''}src="{$data.image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
			</div>
			<a class="button">{__('Browse')}<input id="image_src_{$smarty.now}{$iteration|default:1}" class="color_upload_input" type="file"/></a>
			<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
		</td>
		<td>
			<input type="text" name="product_reference_code[]" value="{$data.code|default:''}"/>
		</td>
		<td>
			<input type="text" name="product_reference_reference[]" value="{$data.reference|default:''}"/>
		</td>
		<td>
			<input type="text" name="product_reference_color[]" value="{$data.color|default:''}"/>
		</td>
		{* foreach item=lang from=$languages}
			<td>
				<input type="text" name="product_reference_title_{$lang.id}[]" value="{$data[$lang.id].title|default:'NESTRĀDĀ!!!'}" disabled="disabled"/>
			</td>
		{/foreach *}
		<td>
			<input type="text" name="product_reference_order_index[]" value="{$data.order_index|default:0|number_format:0:'.':''}"/>
		</td>
		<td>
			<button type="button" class="button" onclick="removePrice(this);">{__('Remove')}</a>
		</td>
	</tr>
{/if}


{if $action == 'recipe_view'}
	{if !empty($recipe.l_id)}
		<table>
			<tr>
				<td style="width: 300px;" class="center">
					{if !empty($recipe.l_image_src)}
						<img src="{$base_url}{$recipe.l_image_src}" style="max-width: 300px; max-height: 250px;"/>
					{/if}
				</td>
				<td style="width: 470px; padding-left: 30px; vertical-align: top;">
					<h2>{$recipe.l_title}</h2>
					{$recipe.l_intro}
					<h3>{__('Preparation')}</h3>
					{$recipe.l_content}
				</td>
			</tr>
		</table>
	{else}
		<h3 class="center" style="margin-top: 50px;">{__('In this language page not exist')}</h3>
	{/if}
{/if}