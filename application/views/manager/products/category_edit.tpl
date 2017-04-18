{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action eq 'load'}	
	<form method="POST" action="manager/{$link}/save" style="width: 950px;">
		
		<div id="resource_buttons">
			{__($page_title)}: {$category.title|default:''}
			<div id="buttons" class="ui-widget ui-widget-content ui-corner-all">
				<a class="button" onclick="submit(this);">{__('Save')}</a>
				<a class="button" href="{$return_url}">{__('Cancel')}</a>&nbsp;&nbsp;&nbsp;&nbsp;
				<a class="button" id="category_delete">{__('Delete')}</a>
			</div>
		</div>
		<div id="resource_tabs">
			<ul>
				<li><a href="#general">{__('General')}</a></li>
			</ul>
			<div id="general">
				<input type="hidden" name="category_id" id="category_id" value="{$category.id}"/>
				<input type="hidden" name="return_url" value="{$return_url}"/>
				{*<input type='hidden' id='pageListJSON' value='[["",""]{include file=$this_file action="link_list" parent_id="0" level="0"}]'/>*}
					
				<table class="resource_data">
					<tr>
						<th>{__('Status')}:</th>
						<td>
							<select name="status_id">
								{foreach item="data" from=$status}
									<option value="{$data.id}" {if $category.status_id == $data.id}selected="selected"{/if}>{__({$data.description})}</option>
								{/foreach}								
							</select>
						</td>
					</tr>
					<tr>
						<th>{__('Manager Title')}:</th>
						<td>
							<input type="text" name="title" value="{$category.title}" />
						</td>
					</tr>
					<tr style="display: none;">
						<th>{__('Category Type')}:</th>
						<td>
							<select name="type_id">
								{foreach item="data" from=$types}
									<option value="{$data.id}" {if $category.type_id == $data.id}selected="selected"{/if}>{__({$data.description})}</option>
								{/foreach}								
							</select>
						</td>
					</tr>
					<tr style="display: none;">
						<th>{__('Parent Category')}:</th>
						<td>
							<select name="parent_id">
								<option value="0">--- {__('Main Category')} ---</option>
								{include file=$this_file action="parent_combo" parent_id="0" level="0"}							
							</select>
						</td>
					</tr>
					<tr>
						<th>{__('Image')}:</th>
						<td style="vertical-align: middle;">
							<div class="image_icon" style="width: 140px; text-align: center;">
								<input type="hidden" name="image_src" value="{$category.image_src|default:''}"/>
								<img {if $category.image_src|default:'' ne ''}src="{$category.image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
							</div>
							<a class="button">{__('Browse')}<input id="image_src" class="file_upload_input" type="file"/></a>
							<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
						</td>
					</tr>
					<tr>
						<th>{__('Order Index')}:</th>
						<td>
							<input type="text" name="order_index" value="{$category.order_index}" />
						</td>
					</tr>
				</table>
			</div>
		</div>	
		<div id="resource_lang_tabs">
			<ul>
				{foreach item=lang from=$languages}
					{if $lang.id == $def_lang_id}{$def_lang=$lang}{/if} 
					<li><a href="#lang-{$lang.id}">{__({$lang.name})}</a></li>
				{/foreach}
				<li style="display: none;"><a href="#gallery">{__('Gallery')}</a></li>
				<input type="hidden" id="def_lang_id" value="{$def_lang.id|default:null}"/>
			</ul>
			{foreach item=lang from=$languages}
				<div id="lang-{$lang.id}">
					{if $category.lang[$lang.id].id|default:'' ne ''}
						{include file=$this_file action='lang_tab'}
					{else}
						{include file=$this_file action='lang_tab_empty'}
					{/if}
				</div>
			{/foreach}
			<div id="gallery">
				{include file=$this_file action="gallery_tab"}
			</div>
		</div>
	</form>
	
	<script type="text/javascript">
		var category_link = '{$link}';
		{section name=i loop=$languages}{$languages[i].manager_name=__($languages[i].name)}{/section}
		var lang_data = {json_encode($languages)};
		
		function content_lang_buttons(obj) {
			$('a[action=create_lang]', obj).click(function() {
				page_loading('{__("Loading...")}');
				var panel = $('#data_panel');
				var tab = $(this).closest('div.ui-tabs-panel');
				
				$.post(base_url+'manager/'+category_link+'/load_lang_tab', {
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
							$.post(base_url+'manager/'+category_link+'/load_lang_tab', {
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
						
						$.post(base_url+'manager/'+category_link+'/remove_lang_tab', {
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

{if $action == 'parent_combo'}
	{foreach item="data" from=$all_categories}
		{if $data.parent_id eq $parent_id && $data.id ne $category.id}
			<option value="{$data.id}" {if $category.parent_id == $data.id}selected="selected"{/if} {if $data.id == $category.id}disabled{/if}>
				{section name=waistsizes start=0 loop=$level step=1}&nbsp;&nbsp;&nbsp;&nbsp;{/section}
				{$data.title}
			</option>
			{include file=$this_file action="parent_combo" parent_id=$data.id level=$level+1}		
		{/if}
	{/foreach}	
{/if}

{* if $action eq 'link_list'}
	{foreach item=parent from=$parents_data name="link_list"}
		{if $parent.parent_id eq $parent_id}
			,["{section name=waistsizes start=0 loop=$level step=1}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$parent.admin_title}","{$parent.id}"]{include file=$this_file action="link_list" parent_id=$parent.id level=$level+1}
		{/if}
	{/foreach}
{/if *}

{if $action eq 'lang_tab'}
	<input type="hidden" name="{$lang.id}_category_content_id" value="{$category.lang[$lang.id].id}">
	<input type="hidden" name="{$lang.id}_language_id" id="language_id" value="{$lang.id}">
	<table class="resource_data">
		<tr>
			<th>{__('Title')}:</th>
			<td>
				<input type="text" name="{$lang.id}_title" id="title" value="{$category.lang[$lang.id].title|default:''|escape:'html'}" style="width: 517px;">
				<a class="button" action="delete_lang" style="float: right;">{__('Delete translation')}</a>
			</td>
		</tr>
		<tr>
			<th>{__('Description')}:</th>
			<td><textarea name="{$lang.id}_description" id="description_{$lang.id}" class="editor_simple">{$category.lang[$lang.id].description|default:''}</textarea></td>
		</tr>
		<tr>
			<th>&nbsp;</th>
			<td></td>
		</tr>
		<tr style="display: none;">
			<th>{__('Content')}:</th>
			<td></td>
		</tr>
		<tr id="content_tr" style="display: none;">
			<th colspan="2"><textarea name="{$lang.id}_content" id="content_{$lang.id}" class="editor">{$category.lang[$lang.id].content|default:''}</textarea></td>
		</tr>
	</table>
{/if}

{if $action == 'lang_tab_empty'}
	<input type="hidden" id="category_content_id" value="none">
	<input type="hidden" id="language_id" value="{$lang.id}">
	{__('In this language page not exist')}<br/>
	<br/>
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
		<div class="gallery-main-image">
			<input type="radio" name="category_main_image_chk" onchange="changeMainImage(this);" {if $data.id|default:'new' eq $category.main_image_id|default:'0'}checked{/if} style="margin-right: 0px;"/> {__('Main image')}
			<input type="hidden" name="category_main_image[]" value="{if $data.id|default:'new' eq $category.main_image_id|default:'0'}1{else}0{/if}">
		</div>
		
		<div class="gallery-image-wrapper">
			<input type="hidden" name="category_image_id[]" value="{$data.id|default:'new'}">
			<input type="hidden" action="image" name="category_image_src[]" value="{$data.image_src|default:''}">
			<img {if $data.image_src|default:'' ne ''}src="{$data.image_src|default:''|thumb}"{/if} style="max-width: 200px; max-height: 170px;" class="vAlign">
		</div>
		
		{foreach item=lang from=$languages}
			<input type="hidden" name="{$lang.id}_category_image_content_id[]" value="{$data.lang[$lang.id].id|default:'new'}">
			<span class="gallery-lang-title">{$lang.tag|strtoupper}:</span> <input type="text" name="{$lang.id}_category_image_content_title[]" value="{$data.lang[$lang.id].title|default:''}" style="width: 160px;"><br/>
		{/foreach}
		
		<a href="#none" onclick="removeGalleryItem(this); return false;" title="{__('Remove')}" class="gallery-item-remove">×</a>
	</div>
{/if}