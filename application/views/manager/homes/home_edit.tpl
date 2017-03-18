{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action eq 'load'}	
	<form method="POST" action="manager/homes_homes/save">
		
		<div id="resource_buttons">
			{__('Home page')}: {$home.admin_title|default:''}
			<div id="buttons" class="ui-widget ui-widget-content ui-corner-all">
				<a class="button" onclick="submit(this);">{__('Save')}</a>
				<a class="button" href="{$return_url}">{__('Cancel')}</a>&nbsp;&nbsp;&nbsp;&nbsp;
				<a class="button" id="home_delete">{__('Delete')}</a>
			</div>
		</div>
		<div id="resource_tabs">
			<ul>
				<li><a href="#general">{__('General')}</a></li>
			</ul>
			<div id="general">
				<input type="hidden" name="home_id" id="home_id" value="{$home.id|default:'new'}"/>
				<input type="hidden" name="return_url" value="{$return_url}"/>
					
				<table class="resource_data">
					<tr>
						<th>{__('Manager Title')}:</th>
						<td><input type="text" name="admin_title" id="admin_title" value="{$home.admin_title|default:''}"/></td>
					</tr>
					<tr>
						<th>{__('Order Index')}:</th>
						<td><input type="text" name="order_index"value="{$home.order_index|default:''}"/></td>
					</tr>
					<tr>
						<th>{__('Text color')}:</th>
						<td>
							<select name="color_type_id">
								{foreach item="data" from=$color_types}
									<option value="{$data.id}" {if $data.id == $home.color_type_id}selected="selected"{/if}>{__($data.name)}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<th>{__('Image')}:</th>
						<td style="vertical-align: middle;">
							<div class="image_icon">
								<input type="hidden" name="image_src" value="{$home.image_src|default:''}"/>
								<img {if $home.image_src|default:'' ne ''}src="{$home.image_src|default:''|thumb}"{/if} style="max-width: 260px; max-height: 260px;" onerror="$(this).hide();" />
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
			</ul>
			{foreach item=lang from=$languages}
				<div id="lang-{$lang.id}">
					{if $home.lang[$lang.id].id|default:'' ne ''}
						{include file=$this_file action='lang_tab'}
					{else}
						{include file=$this_file action='lang_tab_empty'}
					{/if}
				</div>
			{/foreach}
		</div>
	</form>
	
	<script type="text/javascript">
		{section name=i loop=$languages}{$languages[i].manager_name=__($languages[i].name)}{/section}
		var lang_data = {json_encode($languages)};
	
		$().ready(function() {	
			$("#resource_tabs, #resource_lang_tabs").tabs();
			
			$('#resource_tabs select').combobox();
			
			$('#home_delete').click(function() {
				if ($('#home_id').val() != 'new') {
					jConfirm('{__("Are you sure to delete this Home")}?','{__("Are you sure")}?', function(r) {
						if (r) {
							page_loading('{__("Deleting...")}');
							$.post(base_url+'manager/homes_homes/delete', {
								home_id:	$('#home_id').val()
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
			    	'dbsid': unescape(document.cookie).match(/iss_w_box_session_database=([^;]*)/)[1].match(/[a-z0-9-]*$/i)[0],
			    	'resize': 'no'
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
		});

		function content_lang_buttons(obj) {
			$('a[action=create_lang]', obj).click(function() {
				page_loading('{__("Loading...")}');
				var panel = $('#data_panel');
				var tab = $(this).closest('div.ui-tabs-panel');
				
				$.post(base_url+'manager/homes_homes/load_lang_tab', {
					lang_id:			$(tab).find('#language_id').val(),
					home_id:				$(panel).find('#general #home_id').val()
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
							$.post(base_url+'manager/homes_homes/load_lang_tab', {
								lang_id:			$(tab).find('#language_id').val(),
								home_id:			$(panel).find('#general #home_id').val(),
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
						
						$.post(base_url+'manager/homes_homes/remove_lang_tab', {
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

{if $action eq 'lang_tab'}
	<input type="hidden" name="{$lang.id}_home_content_id" value="{$home.lang[$lang.id].id|default:'new'}">
	<input type="hidden" name="language_id[]" id="language_id" value="{$lang.id}">
	<table class="resource_data">
		<tr>
			<th>{__('Status')}:</th>
			<td>
				<select name="{$lang.id}_status_id" id="status_id">
					{foreach item=status from=$home_status}
						<option value="{$status.id}" {if $home.lang[$lang.id].status_id|default:'1' eq $status.id}selected{/if}>{__($status.description)}</option> 
					{/foreach}
				</select>
				<a class="button" action="delete_lang" style="float: right;">{__('Delete translation')}</a>
			</td>
		</tr>
		<tr>
			<th>{__('Title')}:</th>
			<td><input type="text" name="{$lang.id}_title" id="title" value="{$home.lang[$lang.id].title|default:''|escape:'html'}" style="width: 517px;"></td>
		</tr>
		<tr>
			<th>{__('Link')}:</th>
			<td><input type="text" name="{$lang.id}_link" value="{$home.lang[$lang.id].link|default:''|escape:'html'}" style="width: 517px;"></td>
		</tr>
		<tr>
			<th id="intro_title">{__('Introtext')}:</th>
			<td><textarea name="{$lang.id}_intro" id="intro_{$lang.id}" class="editor_simple">{$home.lang[$lang.id].intro|default:''}</textarea></td>
		</tr>
	</table>
{/if}

{if $action == 'lang_tab_empty'}
	<input type="hidden" id="home_content_id" value="none">
	<input type="hidden" id="language_id" value="{$lang.id}">
	{__('In this language page not exist')}<br>
	<br>
	<a class="button" action="create_lang" style="width: 330px;">{__('Create empty translation')}</a><br/>
	<a class="button" action="copy_lang" style="width: 330px; margin-top: 5px;">{__('Copy translation from other language')}</a>
{/if}