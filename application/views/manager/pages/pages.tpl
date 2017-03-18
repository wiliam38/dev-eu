{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action eq 'load'}	
	<form method="POST" action="{$base_url}manager/pages/save" style="min-width: 800px; width: 100%;">
		<div id="resource_buttons">
			{__('Page')}: {$page.admin_title}
			<div id="buttons" class="ui-widget ui-widget-content ui-corner-all">
				<a class="button" onclick="submit(this);">{__('Save')}</a>
				<a href="{$base_url}manager/home/load" class="button">{__('Cancel')}</a>
				{if !empty($page.id) AND $page.id != 'new'}
					&nbsp;&nbsp;&nbsp;&nbsp;<a class="button" action="delete_page">{__('Delete')}</a>
				{/if}
			</div>
		</div>	
		<div id="resource_tabs">
				<ul>
					<li><a href="#general">{__('General')}</a></li>
				</ul>
				<div id="general">
					<input type="hidden" name="page_id" id="page_id" value="{$page.id}"/>
					<input type='hidden' id='pageListJSON' value='{json_encode($tree_data)}'/>
						
					<table class="resource_data">
						<tr>
							<th>{__('Template')}:</th>
							<td>
								<select name="template_id" id="template_id">
									{foreach item=tpl from=$templates}
										<option value="{$tpl.id}" {if $tpl.id eq $page.template_id}selected{/if} >{$tpl.name}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<th>{__('Manager Title')}:</th>
							<td><input type="text" name="admin_title" id="admin_title" value="{$page.admin_title|escape}"/></td>
						</tr>
						<tr>
							<th>{__('Parent')}:</th>
							<td>
								<select name="parent_id" id="parent_id">
									<option value="0">--- {__('Main page')} ---</option>
									{include file=$this_file action="parents_list" parent_id="0" level="0"}
								</select>
							</td>
						</tr>
						<tr>
							<th>{__('Order Index')}:</th>
							<td><input type="text" name="order_index" id="order_index" value="{$page.order_index|escape}"/></td>
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
					{if !empty($page.conf_gallery)}<li><a href="#gallery">{__('Gallery')}</a></li>{/if}
				</ul>
				{foreach item=lang from=$languages}
					<div id="lang-{$lang.id}">
						{if $page.lang[$lang.id].id|default:'' ne ''}
							{include file=$this_file action='lang_tab'}
						{else}
							{include file=$this_file action='lang_tab_empty'}
						{/if}
					</div>
				{/foreach}
				{if !empty($page.conf_gallery)}
					<div id="gallery">
						{include file=$this_file action="gallery_tab"}
					</div>
				{/if}
			</div>		
	</form>
	
	<script type="text/javascript">
		var pages_tree = {json_encode($tree_data)};
		{section name=i loop=$languages}{$languages[i].manager_name=__($languages[i].name)}{/section}
		var lang_data = {json_encode($languages)};
	
		$().ready(function() {
			$("#resource_tabs").tabs();
			$("#resource_lang_tabs").tabs();
			$('a.button').button();
			
			$('a[action=delete_page]').click(function() {
				jConfirm('{__("Are you sure to delete this Page")}?', '{__("Are you sure")}?', function(r) {
					if (r) {
						page_loading('Deleting...');
						$.post(base_url+'manager/pages/delete', {
							page_id:			$('#data_panel #general #page_id').val()
						}, function(data) {
							window.location = base_url+"manager/home/load";
						});
					}
				});
			});
			
			content_init($('body #data_panel'));
		});

		function content_lang_buttons(obj) {
			$('a[action=create_lang]', obj).click(function() {
				page_loading('Loading...');
				var panel = $('#data_panel');
				var tab = $(this).closest('div.ui-tabs-panel');
				
				$.post(base_url+'manager/pages/load_lang_tab', {
					lang_id:			$(tab).find('#language_id').val(),
					page_id:			$(panel).find('#general #page_id').val(),
					page_parent_id:		$(panel).find('#general #parent_id').val(),
					template_id:		$(panel).find('#template_id').val(),
					lang_data: 			{ }
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
							$.post(base_url+'manager/pages/load_lang_tab', {
								lang_id:			$(tab).find('#language_id').val(),
								page_id:			$(panel).find('#general #page_id').val(),
								page_parent_id:		$(panel).find('#general #parent_id').val(),
								template_id:		$(panel).find('#template_id').val(),
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
						
						$.post(base_url+'manager/pages/remove_lang_tab', {
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

{if $action eq 'parents_list'}
	{foreach item=parent from=$parents_data}
		{if $parent.parent_id eq $parent_id && $parent.id ne $page.id}
			<option value="{$parent.id}" {if $page.parent_id|default:'0' eq $parent.id}selected{/if}>
				{section name=waistsizes start=0 loop=$level step=1}&nbsp;&nbsp;&nbsp;&nbsp;{/section}
				{$parent.admin_title}
			</option> 
			{include file=$this_file action="parents_list" parent_id=$parent.id level=$level+1}
		{/if}
	{/foreach}
{/if}

{if $action eq 'lang_tab_empty'}
	<input type="hidden" id="page_content_id" value="none">
	<input type="hidden" id="language_id" value="{$lang.id}">
	{__('In this language page not exist')}<br>
	<br/>
	<a class="button" action="create_lang" style="width: 330px;">{__('Create empty translation')}</a><br/>
	<a class="button" action="copy_lang" style="width: 330px; margin-top: 5px;">{__('Copy translation from other language')}</a>
{/if}
{if $action eq 'lang_tab'}
	<input type="hidden" name="{$lang.id}_page_content_id" id="page_content_id" value="{$page.lang[$lang.id].id}"/>
	<input type="hidden" name="{$lang.id}_language_id" id="language_id" value="{$lang.id}"/>
	<table class="resource_data">
		<tr>
			<th>{__('Status')}:</th>
			<td>
				<select name="{$lang.id}_status_id" id="status_id">
					{foreach item=status from=$page_status}
						<option value="{$status.id}" {if $page.lang[$lang.id].status_id|default:'1' eq $status.id}selected{/if}>{__($status.description)}</option> 
					{/foreach}
				</select>				
				<a class="button" action="delete_lang" style="float: right;">{__('Delete translation')}</a>
			</td>
		</tr>
		<tr>
			<th>{__('Publish date from/to')}:</th>
			<td>
				<input type="text" name="{$lang.id}_pub_date" id="pub_date_{$lang.id}" data="from" class="date" style="width: 110px;" readonly="readonly" value="{if $page.lang[$lang.id].pub_date|default:'0000-00-00 00:00:00' ne '0000-00-00 00:00:00'}{$page.lang[$lang.id].pub_date|date_format:'%d-%b-%Y %H:%M'}{/if}"/> / 
				<input type="text" name="{$lang.id}_unpub_date" id="unpub_date_{$lang.id}" data="to" class="date" style="width: 110px;" readonly="readonly" value="{if $page.lang[$lang.id].unpub_date|default:'0000-00-00 00:00:00' ne '0000-00-00 00:00:00'}{$page.lang[$lang.id].unpub_date|date_format:'%d-%b-%Y %H:%M'}{/if}"/>
			</td>
		</tr>
		<tr>
			<th>{__('Title')}:</th>
			<td><input type="text" name="{$lang.id}_title" id="title" class="input-title" value="{$page.lang[$lang.id].title|default:''|escape}" style="width: 517px;"/></td>
		</tr>
		<tr {if empty($page.conf_title_image)}style="display: none;"{/if}>
			<th>{__('Title image')}:</th>
			<td style="vertical-align: middle;">
				<div class="image_icon">
					<input type="hidden" name="{$lang.id}_title_image_src" value="{$page.lang[$lang.id].title_image_src|default:''}"/>
					<img {if $page.lang[$lang.id].title_image_src|default:'' ne ''}src="{$page.lang[$lang.id].title_image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
				</div>
				<a class="button">{__('Browse')}<input id="{$lang.id}_title_image_src" class="file_upload_input" type="file"/></a>
				<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
			</td>
		</tr>
		<tr {if empty($page.conf_introtext)}style="display: none;"{/if}>
			<th>{__('Introtext')}:</th>
			<td><textarea name="{$lang.id}_intro" id="intro_{$lang.id}" class="editor_simple">{$page.lang[$lang.id].intro|default:''}</textarea></td>
		</tr>
		<tr {if empty($page.conf_image)}style="display: none;"{/if}>
			<th>{__('Image')}:</th>
			<td style="vertical-align: middle;">
				<div class="image_icon">
					<input type="hidden" name="{$lang.id}_image_src" value="{$page.lang[$lang.id].image_src|default:''}"/>
					<img {if $page.lang[$lang.id].image_src|default:'' ne ''}src="{$page.lang[$lang.id].image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
				</div>
				<a class="button">{__('Browse')}<input id="{$lang.id}_image_src" class="file_upload_input" type="file"/></a>
				<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
			</td>
		</tr>
		<tr>
			<th>{__('Alias')}:</th>
			<td>
				<input type="text" name="{$lang.id}_alias" id="alias" class="input-alias" value="{$page.lang[$lang.id].alias|default:''|escape}" style="width: 517px;"/>
			</td>
		</tr>
		<tr {if empty($page.conf_target)}style="display: none;"{/if}>
			<th>{__('Target')}:</th>
			<td>
				<select name="{$lang.id}_target_type_id" id="target_type_id">
					{foreach item=types from=$target_types}
						<option value="{$types.id}" {if $page.lang[$lang.id].target_type_id|default:'1' eq $types.id}selected{/if}>{__($types.name)}</option> 
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<th>{__('Menu title')}:</th>
			<td><input type="text" name="{$lang.id}_menu_title" id="menu_title" class="input-menu_title" value="{$page.lang[$lang.id].menu_title|default:''|escape}" style="width: 517px;"/></td>
		</tr>
		<tr {if empty($page.conf_menu_image)}style="display: none;"{/if}>
			<th>{__('Menu image')}:</th>
			<td style="vertical-align: middle;">
				<div class="image_icon">
					<input type="hidden" name="{$lang.id}_menu_image_src" value="{$page.lang[$lang.id].menu_image_src|default:''}"/>
					<img {if $page.lang[$lang.id].menu_image_src|default:'' ne ''}src="{$page.lang[$lang.id].menu_image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
				</div>
				<a class="button">{__('Browse')}<input id="{$lang.id}_menu_image_src" class="file_upload_input" type="file"/></a>
				<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
			</td>
		</tr>
		<tr>
			<th>{__('Hide from menu')}:</th>
			<td><input type="checkbox" name="{$lang.id}_menu_hide" id="menu_hide" {if $page.lang[$lang.id].menu_hide|default:'0' eq '1'}checked="checked"{/if}/></td>
		</tr>
		<tr>
			<th>{__('Searchable')}:</th>
			<td><input type="checkbox" name="{$lang.id}_searchable" id="searchable" {if $page.lang[$lang.id].searchable|default:'1' eq '1'}checked="checked"{/if}/></td>
		</tr>
		<tr {if empty($page.conf_seo)}style="display: none;"{/if}>
			<th>{__('SEO Keywords')}:</th>
			<td><input type="text" name="{$lang.id}_keywords" id="keywords" value="{$page.lang[$lang.id].keywords|default:''|escape}" style="width: 517px;"/></td>
		</tr>
		<tr {if empty($page.conf_seo)}style="display: none;"{/if}>
			<th>{__('SEO Description')}:</th>
			<td><textarea name="{$lang.id}_description" id="description" style="min-width: 517px; max-width: 517px; height: 50px;">{$page.lang[$lang.id].description|default:''|escape}</textarea></td>
		</tr>
		<tr>
			<th>{__('Content type')}:</th>
			<td>
				<select name="{$lang.id}_content_type_id" id="content_type_id" onchange="init_content_type(this);">
					{foreach item=types from=$content_types}
						<option value="{$types.id}" {if $page.lang[$lang.id].content_type_id|default:'1' eq $types.id}selected{/if}>{__($types.name)}</option> 
					{/foreach}
				</select>
			</td>
		</tr>
		<tr id="content_tr">
			<td colspan="2"><div style="max-width: 1200px;"><textarea name="{$lang.id}_content" id="content_{$lang.id}" class="editor">{$page.lang[$lang.id].content|default:''}</textarea></div></td>
		</tr>
		<tr id="link_tr" style="display: none;">
			<th>{__('Redirect link')}:</th>
			<td><input type="text" name="{$lang.id}_redirect_link" id="redirect_link" value="{$page.lang[$lang.id].redirect_link|default:''}" style="width: 517px;"/></td>
		</tr>
	</table>
{/if}

{*
	GALLERY TAB
*}
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
			<input type="radio" name="page_main_image_chk" onchange="changeMainImage(this);" {if $data.id|default:'new' eq $page.main_image_id|default:'0'}checked{/if} style="margin-right: 0px;"/> {__('Main image')}
			<input type="hidden" name="page_main_image[]" value="{if $data.id|default:'new' eq $page.main_image_id|default:'0'}1{else}0{/if}">
		</div>
		
		<div class="gallery-image-wrapper">
			<input type="hidden" name="page_image_id[]" value="{$data.id|default:'new'}">
			<input type="hidden" action="image" name="page_image_src[]" value="{$data.image_src|default:''}">
			<img {if $data.image_src|default:'' ne ''}src="{$data.image_src|default:''|thumb}"{/if} style="max-width: 200px; max-height: 170px;" class="vAlign">
		</div>
		
		{foreach item=lang from=$languages}
			<input type="hidden" name="{$lang.id}_page_image_content_id[]" value="{$data.lang[$lang.id].id|default:'new'}">
			<span class="gallery-lang-title">{$lang.tag|strtoupper}:</span> 
				<input type="text" name="{$lang.id}_page_image_content_title[]" value="{$data.lang[$lang.id].title|default:''}" style="width: 160px;">
				<input type="hidden" name="{$lang.id}_page_image_content_description[]" value="{$data.lang[$lang.id].description|default:''}" style="width: 160px; margin-left: 32px; margin-bottom: 2px;"><br/>
		{/foreach}
		
		<a href="#none" onclick="removeGalleryItem(this); return false;" title="{__('Remove')}" class="gallery-item-remove">×</a>
	</div>
{/if}