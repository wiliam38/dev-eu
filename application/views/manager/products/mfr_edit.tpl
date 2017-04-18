{$this_file="{$smarty.current_dir}/{$smarty.template}"}

{if $action eq 'load'}	
	<form method="POST" action="manager/products_mfrs/save">
		
		<div id="resource_buttons">
			Seller: {$mfr.name|default:''}
			<div id="buttons" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
				<a class="button" onclick="submit(this);">Save</a>
				<a class="button" href="{$return_url}">Cancel</a>
				{if $mfr.id|default:'new' != 'new'}
					&nbsp;&nbsp;&nbsp;&nbsp;
					<a class="button" id="mfr_delete">Delete</a>
				{/if}
			</div>
		</div>
		<div id="resource_tabs">
			<ul>
				<li><a href="#general">General</a></li>
			</ul>
			<div id="general">
				<input type="hidden" name="mfr_id" id="mfr_id" value="{$mfr.id|default:'new'}"/>
				<input type="hidden" name="return_url" value="{$return_url}"/>
				{*<input type='hidden' id='pageListJSON' value='[["",""]{include file=$this_file action="link_list" parent_id="0" level="0"}]'/>*}
					
				<table class="resource_data">
					<tr>
						<th>Name:</th>
						<td><input type="text" name="name" value="{$mfr.name|default:''}" /></td>
					</tr>
					<tr>
						<th>Status:</th>
						<td>
							<select name="status_id">
								{foreach item="data" from=$status}
									<option value="{$data.id}" {if $mfr.status_id|default:'10' == $data.id}selected="selected"{/if}>{$data.description}</option>
								{/foreach}								
							</select>
						</td>
					</tr>
					<tr>
						<th>Logo:</th>
						<td style="vertical-align: middle;">
							<div class="image_icon">
								<input type="hidden" name="logo_src" value="{$mfr.logo_src|default:''}"/>
								<img {if $mfr.logo_src|default:'' ne ''}src="{$mfr.logo_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
							</div>
							<a class="button">Browse<input id="logo_src" class="file_upload_input" type="file"/></a>
							<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">Remove</a>
						</td>
					</tr>
					<tr>
						<th>Address:</th>
						<td><input type="text" name="address" value="{$mfr.address|default:''}" /></td>
					</tr>
					<tr>
						<th>City:</th>
						<td>
							<select name="city_id">
								{foreach item="data" from=$cities}
									<option value="{$data.id}" {if $mfr.city_id|default:'' == $data.id}selected="selected"{/if}>{$data.name}</option>
								{/foreach}								
							</select>
						</td>
					</tr>
					<tr>
						<th>Reg. Nr. / VAT:</th>
						<td><input type="text" name="vat" value="{$mfr.vat|default:''}" /></td>
					</tr>
					<tr>
						<th>Home page:</th>
						<td><input type="text" name="web" value="{$mfr.web|default:''}" /></td>
					</tr>
					<tr>
						<th>E-mail:</th>
						<td><input type="text" name="email" value="{$mfr.email|default:''}" /></td>
					</tr>
					<tr>
						<th>Phone:</th>
						<td><input type="text" name="phone" value="{$mfr.phone|default:''}" /></td>
					</tr>
					<tr>
						<th>Fax:</th>
						<td><input type="text" name="fax" value="{$mfr.fax|default:''}" /></td>
					</tr>
					<tr>
						<th>Image:</th>
						<td style="vertical-align: middle;">
							<div class="image_icon">
								<input type="hidden" name="image_src" value="{$mfr.image_src|default:''}"/>
								<img {if $mfr.image_src|default:'' ne ''}src="{$mfr.image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
							</div>
							<a class="button">Browse<input id="image_src" class="file_upload_input" type="file"/></a>
							<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">Remove</a>
						</td>
					</tr>
				</table>
			</div>
		</div>	
		<div id="resource_lang_tabs">
			<ul>
				{foreach item=lang from=$languages}
					<li><a href="#lang-{$lang.id}">{$lang.name}</a></li>
				{/foreach}
			</ul>
			{foreach item=lang from=$languages}
				<div id="lang-{$lang.id}">
					{if $mfr.lang[$lang.id].id|default:'' ne ''}
						{include file=$this_file action='lang_tab'}
					{else}
						<input type="hidden" id="mfr_content_id" value="none">
						<input type="hidden" id="language_id" value="{$lang.id}">
						In this language page not exist<br>
						<br>
						<a class="button" action="create_lang">Create Translation</a>
					{/if}
				</div>
			{/foreach}
		</div>
	</form>
{/if}

{if $action eq 'lang_tab'}
	<input type="hidden" name="{$lang.id}_mfr_content_id" value="{$mfr.lang[$lang.id].id}">
	<input type="hidden" name="{$lang.id}_language_id" value="{$lang.id}">
	<table class="resource_data">
		<tr>
			<th>Display name:</th>
			<td><input type="text" name="{$lang.id}_title" id="title" value="{$mfr.lang[$lang.id].title|default:''|escape:'html'}" style="width: 517px;"></td>
		</tr>
		<tr>
			<th>Intro:</th>
			<td><textarea name="{$lang.id}_description" id="description_{$lang.id}" class="editor_simple">{$mfr.lang[$lang.id].description|default:''}</textarea></td>
		</tr>
		<tr>
			<th>&nbsp;</th>
			<td></td>
		</tr>
		<tr>
			<th>Content:</th>
			<td></td>
		</tr>
		<tr id="content_tr">
			<th colspan="2"><textarea name="{$lang.id}_content" id="content_{$lang.id}" class="editor">{$mfr.lang[$lang.id].content|default:''}</textarea></td>
		</tr>
	</table>
{/if}