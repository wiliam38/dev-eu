{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action eq 'load'}	
	<form method="POST" action="manager/reciepes_reciepes/save">
		
		<div id="resource_buttons">
			{__('Recipe')}: {$reciepe.admin_title|default:''}
			<div id="buttons" class="ui-widget ui-widget-content ui-corner-all">
				<a class="button" onclick="submit(this);">{__('Save')}</a>
				<a class="button" href="{$return_url}">{__('Cancel')}</a>&nbsp;&nbsp;&nbsp;&nbsp;
				<a class="button" id="reciepe_delete">{__('Delete')}</a>
			</div>
		</div>
		<div id="resource_tabs">
			<ul>
				<li><a href="#general">{__('General')}</a></li>
			</ul>
			<div id="general">
				<input type="hidden" name="reciepe_id" id="reciepe_id" value="{$reciepe.id|default:'new'}"/>
				<input type="hidden" name="return_url" value="{$return_url}"/>
					
				<table class="resource_data">
					<tr>
						<th>{__('Manager Title')}:</th>
						<td><input type="text" name="admin_title" id="admin_title" value="{$reciepe.admin_title|default:''}"/></td>
					</tr>
					<tr>
						<th>{__('Difficulty')}:</th>
						<td>
							<select name="difficulty_type_id">
								{foreach item="data" from=$difficulties}
									<option value="{$data.id}" {if $data.id == $reciepe.difficulty_type_id}selected="selected"{/if}>{__($data.name)}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<th>{__('Preparation time')}:</th>
						<td><input type="text" name="time" value="{$reciepe.time|default:''}" style="width: 225px;"/> min</td>
					</tr>
					<tr>
						<th>{__('Priority index')}:</th>
						<td><input type="text" name="order_index" value="{$reciepe.order_index|default:''}"/></td>
					</tr>
					<tr style="display: none;">
						<th>{__('Image')}:</th>
						<td style="vertical-align: middle;">
							<div class="image_icon">
								<input type="hidden" name="image_src" value="{$reciepe.image_src|default:''}"/>
								<img {if $reciepe.image_src|default:'' ne ''}src="{$reciepe.image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
							</div>
							<a class="button">{__('Browse')}<input id="image_src" class="file_upload_input" type="file"/></a>
							<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
						</td>
					</tr>
				</table>
			</div>
		</div>	
		<div id="resource_lang_tabs">
			<ul>
				{foreach item=lang from=$languages}
					<li><a href="#lang-{$lang.id}">{__($lang.name)}</a></li>
				{/foreach}
				<li><a href="#materials">{__('Materials')}</a></li>
				<li style="display: none;"><a href="#gallery">{__('Gallery')}</a></li>
			</ul>
			{foreach item=lang from=$languages}
				<div id="lang-{$lang.id}">
					{if $reciepe.lang[$lang.id].id|default:'' ne ''}
						{include file=$this_file action='lang_tab'}
					{else}
						{include file=$this_file action='lang_tab_empty'}
					{/if}
				</div>
			{/foreach}
			<div id="materials" style="display: none;">
				{include file=$this_file action="materials_tab"}
			</div>
			<div id="gallery" style="display: none;">
				{include file=$this_file action="gallery_tab"}
			</div>
		</div>
	</form>
	
	<script type="text/javascript">
		{section name=i loop=$languages}{$languages[i].manager_name=__($languages[i].name)}{/section}
		var lang_data = {json_encode($languages)};
		
		$().ready(function() {	
			$("#resource_tabs, #resource_lang_tabs").tabs();
			
			$('#resource_tabs select').combobox();
			
			$('#reciepe_delete').click(function() {
				if ($('#reciepe_id').val() != 'new') {
					jConfirm('{__("Are you sure to delete this Reciepe")}?','{__("Are you sure")}?', function(r) {
						if (r) {
							page_loading('{__("Deleting...")}');
							$.post(base_url+'manager/reciepes_reciepes/delete', {
								reciepe_id:	$('#reciepe_id').val()
							}, function(data) {
								if (data.status == '1') window.location = $('input[name="return_url"]').val();
								else {
									page_loading('');
									jAlert(data.error);
								}
							}, 'json');
						}
					});
				}
			});
			
			$('#general .file_upload_input').uploadify({
				'uploader'  : base_url+'assets/libs/jquery-plugins/uploadify/uploadify.swf',
				'cancelImg' : base_url+'assets/libs/jquery-plugins/uploadify/cancel.png',
			    'script'    : base_url+'manager/files/upload_tmp_files',
			    'scriptData': { 
			    	'dbsid': unescape(document.cookie).match(/iss_w_box_session_database=([^;]*)/)[1].match(/[a-z0-9-]*$/i)[0]
			    },
			    
			    'wmode'     : 'transparent',
			    'fileExt'   : '*.jpg;*.gif;*.png',
				'fileDesc'	: 'Images',
				'multi'		: false,		
			    'auto'		: true,
			    'removeCompleted'	: true,
			    'height'	: 50,
			    'width'		: 120,
			    'buttonImg'	: base_url+'assets/libs/jquery-plugins/uploadify/browse.gif',
			    'buttonText': '',
			 
			    'onOpen'    : 	function(event,ID,fileObj) {
									page_loading('{__("Uploading image...")}');
									rmFileTmp($(event.target).closest('tr').find('div.image_icon input'));
			    				},
			    'onComplete': 	function(event, ID, fileObj, response, data) {
			    					var thumb = response.replace(/\/([^\/]*)$/i,'/thumb_$1');
									$(event.target).closest('tr').find('div.image_icon img').attr('src',thumb);
									$(event.target).closest('tr').find('div.image_icon img').show();	    					
									$(event.target).closest('tr').find('div.image_icon input').val(response);
			    				},
				'onAllComplete': 	function() {
			    					page_loading('');  
			    			}
			});	

			$('#add-gallery-items').uploadify({
				'uploader'  : base_url+'assets/libs/jquery-plugins/uploadify/uploadify.swf',
				'cancelImg' : base_url+'assets/libs/jquery-plugins/uploadify/cancel.png',
			    'script'    : base_url+'manager/files/upload_tmp_files',
			    'scriptData': { 
			    	'dbsid': unescape(document.cookie).match(/iss_w_box_session_database=([^;]*)/)[1].match(/[a-z0-9-]*$/i)[0]
			    },
			    
			    'wmode'     : 'transparent',
			    'fileExt'   : '*.jpg;*.gif;*.png',
				'fileDesc'	: 'Images',
				'multi'		: true,		
			    'auto'		: true,
			    'removeCompleted'	: true,
			    'height'	: 22,
			    'width'		: 60,
			    'buttonImg'	: base_url+'assets/libs/jquery-plugins/uploadify/browse.gif',
			    'buttonText': '',
			 
			    'onOpen'    : 	function(event,ID,fileObj) {
					page_loading('Uploading image...');
			    },
			    'onComplete': 	function(event, ID, fileObj, response, data) {
					$.post(base_url+'manager/reciepes_reciepes/load_gallery_item', {
						image_src: response
					}, function(tr) {			
						tr = $(tr).insertBefore($('#gallery_data_div .clear'));
						gallery_init(tr);

						if ($('#gallery_data_div input[name="reciepe_main_image_chk"]:checked').length == 0) {
							$('#gallery_data_div input[name="reciepe_main_image_chk"]:first').attr('checked',true);
							changeMainImage($('#gallery_data_div input[name="reciepe_main_image_chk"]:first'));
						}					
					});
				},
				'onAllComplete': 	function() {
					page_loading('');  
				}
			});
			
			content_init($('body #data_panel'));
			gallery_init($('body #data_panel table.gallery_table tbody'));
			init_type();
		});
		
		function init_type() {
			var type_id = $('#type_id').val();
			
			// RECIEPE				
			$('tr#content_tr').show();
			$('tr#content_tr').prev('tr').show();
				
			$('th#intro_title').html('{__("Introtext")}:')
			
			$('#resource_lang_tabs').tabs( "option", "selected", 0);
		}

		function content_lang_buttons(obj) {
			$('a[action=create_lang]').click(function() {
				page_loading('{__("Loading...")}');
				var panel = $('#data_panel');
				var tab = $(this).closest('div.ui-tabs-panel');
				
				$.post(base_url+'manager/reciepes_reciepes/load_lang_tab', {
					lang_id:			$(tab).find('#language_id').val(),
					reciepe_id:			$(panel).find('#general #reciepe_id').val()
				}, function(data) {
					var obj = $(tab).html(data);
					
					content_init(obj);
					init_type();
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
							$.post(base_url+'manager/reciepes_reciepes/load_lang_tab', {
								lang_id:			$(tab).find('#language_id').val(),
								reciepe_id:			$(panel).find('#general #reciepe_id').val(),
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
						
						$.post(base_url+'manager/reciepes_reciepes/remove_lang_tab', {
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

{if $action == 'lang_tab_empty'}
	<input type="hidden" id="reciepe_content_id" value="none">
	<input type="hidden" id="language_id" value="{$lang.id}">
	{__('In this language page not exist')}<br>
	<br>
	<a class="button" action="create_lang" style="width: 330px;">{__('Create empty translation')}</a><br/>
	<a class="button" action="copy_lang" style="width: 330px; margin-top: 5px;">{__('Copy translation from other language')}</a>
{/if}

{if $action eq 'lang_tab'}
	<input type="hidden" name="{$lang.id}_reciepe_content_id" value="{$reciepe.lang[$lang.id].id|default:'new'}">
	<input type="hidden" name="language_id[]" id="language_id" value="{$lang.id}">
	<table class="resource_data">
		<tr>
			<td rowspan="5" style="width: 300px;" class="center">
				<div class="image_icon">
					<input type="hidden" name="{$lang.id}_image_src" value="{$reciepe.lang[$lang.id].image_src|default:''}"/>
					<img class="vAlign" {if $reciepe.lang[$lang.id].image_src|default:'' ne ''}src="{$reciepe.lang[$lang.id].image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
				</div>
				<a class="button">{__('Browse')}<input id="{$lang.id}_image_src_btn" class="file_upload_input" type="file"/></a>
				<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
			</td>
			<td>
				<b>{__('Status')}</b><br/>
				<select name="{$lang.id}_status_id" id="status_id">
					{foreach item=status from=$reciepe_status}
						<option value="{$status.id}" {if $reciepe.lang[$lang.id].status_id|default:'1' eq $status.id}selected{/if}>{__($status.description)}</option> 
					{/foreach}
				</select>
				<a class="button" action="delete_lang" style="float: right;">{__('Delete translation')}</a>
			</td>
		</tr>
		<tr>
			<td>
				<b>{__('Title')}</b><br/>
				<input type="text" name="{$lang.id}_title" id="title" value="{$reciepe.lang[$lang.id].title|default:''|escape:'html'}" style="width: 517px;">
			</td>
		</tr>
		<tr>
			<td style="padding-top: 5px;">
				<b>{__('Description')}</b><br/>
				<textarea name="{$lang.id}_intro" id="intro_{$lang.id}" class="editor_simple">{$reciepe.lang[$lang.id].intro|default:''}</textarea>
			</td>
		</tr>
		<tr>
			<td style="padding-top: 5px;">
				<b>{__('Ingredients')}</b><br/>
				<textarea name="{$lang.id}_ingredients" id="ingredients_{$lang.id}" class="editor_simple">{$reciepe.lang[$lang.id].ingredients|default:''}</textarea>
			</td>
		</tr>
		<tr>
			<td style="padding-top: 5px;">
				<b>{__('Preparation')}</b><br/>
				<textarea name="{$lang.id}_content" id="content_{$lang.id}" class="editor_simple">{$reciepe.lang[$lang.id].content|default:''}</textarea>
			</td>
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
			<input type="radio" name="reciepe_main_image_chk" onchange="changeMainImage(this);" {if $data.id|default:'new' eq $reciepe.main_image_id|default:'0'}checked{/if} style="margin-right: 0px;"/> {__('Main image')}
			<input type="hidden" name="reciepe_main_image[]" value="{if $data.id|default:'new' eq $reciepe.main_image_id|default:'0'}1{else}0{/if}">
		</div>
		
		<div class="gallery-image-wrapper">
			<input type="hidden" name="reciepe_image_id[]" value="{$data.id|default:'new'}">
			<input type="hidden" action="image" name="reciepe_image_src[]" value="{$data.image_src|default:''}">
			<img {if $data.image_src|default:'' ne ''}src="{$data.image_src|default:''|thumb}"{/if} style="max-width: 200px; max-height: 170px;" class="vAlign">
		</div>
		
		{foreach item=lang from=$languages}
			<input type="hidden" name="{$lang.id}_reciepe_image_content_id[]" value="{$data.lang[$lang.id].id|default:'new'}">
			<span class="gallery-lang-title">{$lang.tag|strtoupper}:</span> 
				<input type="text" name="{$lang.id}_reciepe_image_content_title[]" value="{$data.lang[$lang.id].title|default:''}" style="width: 160px;">
				<input type="hidden" name="{$lang.id}_reciepe_image_content_description[]" value="{$data.lang[$lang.id].description|default:''}" style="width: 160px; margin-left: 32px; margin-bottom: 2px;"><br/>
		{/foreach}
		
		<a href="#none" onclick="removeGalleryItem(this); return false;" title="{__('Remove')}" class="gallery-item-remove">×</a>
	</div>
{/if}

{if $action eq 'materials_tab'}	
	<table class="data_table" style="width: 100%;">
		<thead>
			<tr>
				<th width="100">{__('Image')}</th>
				<th width="300">{__('Name')}</th>
				<th width="100">{__('Qty')}</th>
				<th width="100">{__('Status')}</th>
				<th width="70"></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$linked_products name="linked"}
				{include file=$this_file action="material_edit"}
			{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<select id="linked_product_id" style="width: 200px; height: 20px;"></select>
					<a class="button" onclick="addLinkedProduct(this)" style="vertical-align: top;">{__('Add')}</a>
				</td>
			</tr>
		</tfoot>
	</table>
{/if}

{if $action eq "material_edit"}
	<tr>
		<td class="center">
			<input type="hidden" name="linked_product_recipe_material_id[]" value="{$data.recipe_material_id|default:'new'}">
			<input type="hidden" name="linked_product_id[]" value="{$data.id}">
			<img src="{$data.reference_image_src|default:''|thumb}" style="max-width: 100px; max-height: 100px; onerror="$(this).hide();"/>
		</td>
		<td>{$data.l_1_title} ({$data.l_category_title})</td>
		<td><input type="text" name="linked_product_qty[]" value="{$data.qty|default:1|number_format:0:'.':''}"/></td>		
		<td>{__($data.status_description)}</td>
		<td>
			<a class="button" onclick="removeLinkedProduct(this)">{__('Remove')}</a>
		</td>
	</tr>
{/if}