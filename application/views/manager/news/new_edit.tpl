{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action eq 'load'}	
	<form method="POST" action="manager/news_news/save">
		
		<div id="resource_buttons">
			{__('Standard new')}: {$new.admin_title|default:''}
			<div id="buttons" class="ui-widget ui-widget-content ui-corner-all">
				<a class="button" onclick="submit(this);">{__('Save')}</a>
				<a class="button" href="{$return_url}">{__('Cancel')}</a>&nbsp;&nbsp;&nbsp;&nbsp;
				<a class="button" id="new_delete">{__('Delete')}</a>
			</div>
		</div>
		<div id="resource_tabs">
			<ul>
				<li><a href="#general">{__('General')}</a></li>
			</ul>
			<div id="general">
				<input type="hidden" name="new_id" id="new_id" value="{$new.id|default:'new'}"/>
				<input type="hidden" name="return_url" value="{$return_url}"/>
					
				<table class="resource_data">
					<tr>
						<th>{__('Manager Title')}:</th>
						<td><input type="text" name="admin_title" id="admin_title" value="{$new.admin_title|default:''}"/></td>
					</tr>
					<tr>
						<th>{__('Type')}:</th>
						<td>
							<select name="type_id" id="type_id" onchange="init_type();">
								{foreach item="data" from=$types}
									<option value="{$data.id}" {if $new.type_id|default:null == $data.id}selected="selected"{/if}>{__($data.description)}</option>
								{/foreach}								
							</select>
						</td>
					</tr>
					<tr>
						<th>{__('Main new')}:</th>
						<td><input type="checkbox" name="main" value="1" {if $new.main|default:'0' == '1'}checked="checked"{/if}/></td>
					</tr>
					<tr>
						<th>{__('Image')}:</th>
						<td style="vertical-align: middle;">
							<div class="image_icon">
								<input type="hidden" name="image_src" value="{$new.image_src|default:''}"/>
								<img {if $new.image_src|default:'' ne ''}src="{$new.image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
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
				<li style="display: none;"><a href="#answers">{__('Answers')}</a></li>
				<li id="gallery_tab"><a href="#gallery">{__('Gallery')}</a></li>
			</ul>
			{foreach item=lang from=$languages}
				<div id="lang-{$lang.id}">
					{if $new.lang[$lang.id].id|default:'' ne ''}
						{include file=$this_file action='lang_tab'}
					{else}
						{include file=$this_file action='lang_tab_empty'}
					{/if}
				</div>
			{/foreach}
			<div id="answers">
				{include file=$this_file action='answers_tab'}
			</div>
			<div id="gallery">
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
			
			$('#new_delete').click(function() {
				if ($('#new_id').val() != 'new') {
					jConfirm('{__("Are you sure to delete this New")}?','{__("Are you sure")}?', function(r) {
						if (r) {
							page_loading('{__("Deleting...")}');
							$.post(base_url+'manager/news_news/delete', {
								new_id:	$('#new_id').val()
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
			
			content_init($('body #data_panel'));
			init_type();
			init_answer($('body #answers_table'));
		});
		
		function init_type() {
			var type_id = $('#type_id').val();
			
			if (type_id == 20) {
				// NEW WITH LINK 
				$('#resource_lang_tabs a[href="#answers"]').parent().hide();
				$('#resource_lang_tabs #vote_time').hide();
				$('#resource_lang_tabs .type-10').hide();
				$('#resource_lang_tabs .type-20').show();

				$('#gallery_tab').hide();
			} else {
				// NEW
				$('#resource_lang_tabs a[href="#answers"]').parent().hide();
				$('#resource_lang_tabs #vote_time').hide();
				$('#resource_lang_tabs .type-20').hide();
				$('#resource_lang_tabs .type-10').show();
				
				$('#gallery_tab').show();
				
				$('tr#content_tr').show();
				$('tr#content_tr').prev('tr').show();
				
				$('th#intro_title').html('{__("Introtext")}:')
				
				$('#resource_lang_tabs').tabs( "option", "selected", 0);
			}
		}

		function content_lang_buttons(obj) {
			$('a[action=create_lang]', obj).click(function() {
				page_loading('{__("Loading...")}');
				var panel = $('#data_panel');
				var tab = $(this).closest('div.ui-tabs-panel');
				
				$.post(base_url+'manager/news_news/load_lang_tab', {
					lang_id:			$(tab).find('#language_id').val(),
					new_id:				$(panel).find('#general #new_id').val()
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
							$.post(base_url+'manager/news_news/load_lang_tab', {
								lang_id:			$(tab).find('#language_id').val(),
								new_id:				$(panel).find('#general #new_id').val(),
								lang_data: 			$('#lang-'+$('select[name="language_id"]', dlg).val()).postData()
							}, function(data) {
								var obj = $(tab).html(data);
								
								content_init(obj);
								init_type();
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
						
						$.post(base_url+'manager/news_news/remove_lang_tab', {
							lang_id:			$(tab).find('#language_id').val(),
							lang_data: 			$(tab).postData()							
						}, function(data) {
							var obj = $(tab).html(data);							
							content_init(obj);
							init_type();
							page_loading('');
						});
					}
				});
			});
		}
	</script>
{/if}

{if $action eq 'lang_tab'}
	<input type="hidden" name="{$lang.id}_new_content_id" value="{$new.lang[$lang.id].id|default:'new'}">
	<input type="hidden" name="language_id[]" id="language_id" value="{$lang.id}">
	<table class="resource_data">
		<tr>
			<th>{__('Status')}:</th>
			<td>
				<select name="{$lang.id}_status_id" id="status_id">
					{foreach item=status from=$new_status}
						<option value="{$status.id}" {if $new.lang[$lang.id].status_id|default:'1' eq $status.id}selected{/if}>{__($status.description)}</option> 
					{/foreach}
				</select>
				<a class="button" action="delete_lang" style="float: right;">{__('Delete translation')}</a>
			</td>
		</tr>
		<tr>
			<th>{__('Publish date from/to')}:</th>
			<td>
				<input type="text" name="{$lang.id}_pub_date" id="pub_date_{$lang.id}" data="from" class="date" style="width: 110px;" readonly="readonly" value="{if $new.lang[$lang.id].pub_date|default:'0000-00-00 00:00:00' ne '0000-00-00 00:00:00'}{$new.lang[$lang.id].pub_date|date_format:'%d-%b-%Y %H:%M'}{/if}"/> 
				<div style="display: none;">
					<input type="text" name="{$lang.id}_unpub_date" id="unpub_date_{$lang.id}" data="to" class="date" style="width: 110px;" readonly="readonly" value="{if $new.lang[$lang.id].unpub_date|default:'0000-00-00 00:00:00' ne '0000-00-00 00:00:00'}{$new.lang[$lang.id].unpub_date|date_format:'%d-%b-%Y %H:%M'}{/if}"/>
				</div>
			</td>
		</tr>
		<tr id="vote_time" style="display: none;">
			<th>{__('Can vote from/to')}:</th>
			<td>
				<input type="text" name="{$lang.id}_vote_from" id="vote_from_{$lang.id}" data="from" class="date" style="width: 110px;" readonly="readonly" value="{if $new.lang[$lang.id].vote_from|default:'0000-00-00 00:00:00' ne '0000-00-00 00:00:00'}{$new.lang[$lang.id].vote_from|date_format:'%d-%b-%Y %H:%M'}{/if}"/> / 
				<input type="text" name="{$lang.id}_vote_to" id="vote_to_{$lang.id}" data="to" class="date" style="width: 110px;" readonly="readonly" value="{if $new.lang[$lang.id].vote_to|default:'0000-00-00 00:00:00' ne '0000-00-00 00:00:00'}{$new.lang[$lang.id].vote_to|date_format:'%d-%b-%Y %H:%M'}{/if}"/>
			</td>
		</tr>
		<tr>
			<th>{__('Title')}:</th>
			<td><input type="text" name="{$lang.id}_title" id="title" value="{$new.lang[$lang.id].title|default:''|escape:'html'}" style="width: 517px;"></td>
		</tr>
		<tr>
			<th id="intro_title">{__('Introtext')}:</th>
			<td><textarea name="{$lang.id}_intro" id="intro_{$lang.id}" class="editor_simple">{$new.lang[$lang.id].intro|default:''}</textarea></td>
		</tr>
		<tr class="type-10">
			<th>&nbsp;</th>
			<td></td>
		</tr>
		<tr class="type-10">
			<th>{__('Content')}:</th>
			<td></td>
		</tr>
		<tr id="content_tr" class="type-10">
			<th colspan="2"><textarea name="{$lang.id}_content" id="content_{$lang.id}" class="editor">{$new.lang[$lang.id].content|default:''}</textarea></td>
		</tr>
		<tr class="type-20" style="display: none;">
			<th>{__('Link')}:</th>
			<td><input type="text" name="{$lang.id}_link" value="{$new.lang[$lang.id].link|default:''|escape:'html'}" style="width: 517px;"></td>
		</tr>
	</table>
{/if}
{if $action == 'lang_tab_empty'}
	<input type="hidden" id="new_content_id" value="none">
	<input type="hidden" id="language_id" value="{$lang.id}">
	{__('In this language page not exist')}<br>
	<br>
	<a class="button" action="create_lang" style="width: 330px;">{__('Create empty translation')}</a><br/>
	<a class="button" action="copy_lang" style="width: 330px; margin-top: 5px;">{__('Copy translation from other language')}</a>
{/if}

{if $action == 'answers_tab'}
	<table id="answers_table" class="data_table">
		<thead>
			<tr>
				{foreach item=lang from=$languages}
					<th style="width: 250px;">{__('Answer')}<br/><i>{__($lang.name)}</i></th>
				{/foreach}
				<th style="width: 150px;">{__('Image')}</th>
				<th style="width: 100px;">{__('Type')}</th>
				<th style="width: 200px;">{__('Count')}</th>
				<th style="width: 70px;"></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$answers}
				{include file=$this_file action="answer_row"}
			{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="{4+$languages|count}">
					<a class="button" onclick="add_answer();">{__('Add')}</a>
				</td>
			</tr>
		</tfoot>
	</table>
{/if}
{if $action == 'answer_row'}
	<tr>
		{foreach item=lang from=$languages}
			<td>
				<input type="hidden" name="{$lang.id}_answer_content_id[]" value="{$data.lang[{$lang.id}].id|default:'new'}"/>
				<input type="text" name="{$lang.id}_answer[]" value="{$data.lang[{$lang.id}].answer|default:''}"/>
			</td>
		{/foreach}
		<td class="center">
			<div class="image_icon">
				<input type="hidden" name="answer_image_src[]" value="{$data.image_src|default:''}"/>
				<img {if $data.image_src|default:'' ne ''}src="{$data.image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
			</div>
			<a class="button">{__('Browse')}<input id="answer_image_src_{$data.id|default:'new'}_{$smarty.now}" class="file_upload_input" type="file"/></a>
			<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'));">{__('Remove')}</a>
		</td>
		<td>
			<input type="hidden" name="answer_id[]" value="{$data.id|default:'new'}"/>
			
			<select name="answer_type_id[]" style="width: 97px;">
				{foreach item="type" from=$answer_types}
					<option value="{$type.id}" {if $type.id == $data.type_id|default:null}selected="selected"{/if}>{__($type.description)}</option>
				{/foreach}
			</select>
		</td>
		<td>
			{$data.count|default:0} <i>({$data.count_percents|default:0|number_format:0:'.':''} %)</i>
			{if $data.type_id|default:null == 20}
				<table>
					{foreach item="answer" from=$data.answers|default:array()}
						<tr style="background: transparent; height: 15px;">
							<td style="border: 0px none; width: 20px; padding: 0px;"></td>
							<td style="border: 0px none; width: 20px; padding: 0px;">{$answer.count|default:0}</td>
							<td style="border: 0px none; width: 140px; padding: 0px;">{$answer.answer_value}</td>
						</tr>
					{/foreach}
				</table>
			{/if}
		</td>
		<td>
			<a type="button" class="button" onclick="delete_answer(this);">{__('Delete')}</a>
		</td>
	</tr>
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
			<input type="radio" name="new_main_image_chk" onchange="changeMainImage(this);" {if $data.id|default:'new' eq $new.main_image_id|default:'0'}checked{/if} style="margin-right: 0px;"/> {__('Main image')}
			<input type="hidden" name="new_main_image[]" value="{if $data.id|default:'new' eq $new.main_image_id|default:'0'}1{else}0{/if}">
		</div>
		
		<div class="gallery-image-wrapper">
			<input type="hidden" name="new_image_id[]" value="{$data.id|default:'new'}">
			<input type="hidden" action="image" name="new_image_src[]" value="{$data.image_src|default:''}">
			<img {if $data.image_src|default:'' ne ''}src="{$data.image_src|default:''|thumb}"{/if} style="max-width: 200px; max-height: 170px;" class="vAlign">
		</div>
		
		{* foreach item=lang from=$languages}
			<input type="hidden" name="{$lang.id}_new_image_content_id[]" value="{$data.lang[$lang.id].id|default:'new'}">
			<span class="gallery-lang-title">{$lang.tag|strtoupper}:</span> 
				<input type="text" name="{$lang.id}_new_image_content_title[]" value="{$data.lang[$lang.id].title|default:''}" style="width: 160px;">
				<input type="hidden" name="{$lang.id}_new_image_content_description[]" value="{$data.lang[$lang.id].description|default:''}" style="width: 160px; margin-left: 32px; margin-bottom: 2px;"><br/>
		{/foreach *}
		
		<a href="#none" onclick="removeGalleryItem(this); return false;" title="{__('Remove')}" class="gallery-item-remove">×</a>
	</div>
{/if}