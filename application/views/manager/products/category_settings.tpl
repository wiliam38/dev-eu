{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=(count($languages)+5)}

{if $action eq "list"}
	<div id="resource_buttons">
		{__($page_title)}: {$category.title}
		<div id="buttons" class="ui-widget ui-widget-content ui-corner-all">
			<a class="button" href="manager/{$link}/load" style="margin: 0px 15px;">{__('Back')}</a>
		</div>
	</div>
	<input type="hidden" id="category_id" value="{$category.id}"/>
	
	<table class="data_table">
		<colgroup>
			{foreach item="data" from=$languages}
				<col style="width: 200px;"/>
			{/foreach}
			<col style="width: 150px;"/>
			<col style="width: 130px;"/>
			<col style="width: 100px;"/>
			<col style="width: 130px;"/>
			<col style="width: 170px;"/>
		</colgroup>
	
		<thead>
			<tr>
				{foreach item="data" from=$languages}
					<th>{__('Title')}<br/>{__({$data.name})}</th>
				{/foreach}
				<th>{__('Image')}</th>
				<th>{__('Type')}</th>
				<th>{__('Order Index')}</th>
				<th>{__('Status')}</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$category_settings|default:array()}
				{include file=$this_file action="view"}
				{include file=$this_file action="sub_load" category_setting_values=$data.values category_setting=$data}
			{/foreach}
		</tbody>
		<tfoot>			
			<tr data-id="new">
				<td colspan="{$cols}">
					<button class="button" onclick="setting_edit(this);">{__('Add')}</button>
				</td>
			</tr>
		</tfoot>
	</table>
	
	<script type="text/javascript">
		var settings_delete_title = '{__("Are you sure?")}';
		var settings_delete_msg = '{__("Are you sure to delete this parameter?")}';
		var settings_sub_delete_title = '{__("Are you sure?")}';
		var settings_sub_delete_msg = '{__("Are you sure to delete this parameter value?")}';
	</script>
{/if}

{if $action == 'view'}
	<tr data-id="{$data.id|default:'new'}">
		{foreach item="lang" from=$languages}
			<td>{$data.lang[$lang.id].title|escape}</td>
		{/foreach}	
		<td class="image_td">
			<div class="image_icon">
				<img {if $data.image_src|default:'' ne ''}src="{$data.image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
			</div>
		</td>
		<td>{__($data.type_name)}</td>	
		<td>{$data.order_index|number_format:0:'.':''|escape}</td>	
		<td>{__($data.status_name)}</td>
		<td>
			<button class="button" onclick="setting_edit(this);">{__('Edit')}</button>
			<button class="button" onclick="setting_delete(this);">{__('Delete')}</button>
		</td>
	</tr>
{/if}

{if $action == 'edit'}
	<tr data-id="{$data.id|default:'new'}" class="edit">
		{foreach item="lang" from=$languages}
			<td>
				<input type="hidden" name="language_id[]" value="{$lang.id}"/>
				<input type="hidden" name="content_id[]" value="{$data.lang[$lang.id].id|default:'new'}"/>
				<input type="text" name="title[]" value="{$data.lang[$lang.id].title|default:''|escape}"/>
			</td>
		{/foreach}
		<td class="image_td">
			<input type="hidden" name="id" value="{$data.id|default:'new'}"/>
			<input type="hidden" name="category_id" value="{$data.category_id}"/>
			<input type="hidden" name="job" value="save"/>
		
			<div class="image_icon">
				<input type="hidden" name="image_src" value="{$data.image_src|default:''}"/>
				<img {if $data.image_src|default:'' ne ''}src="{$data.image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
			</div>
			<a class="button">{__('Browse')}<input id="image_src_{$smarty.now}" class="file_upload_input" type="file"/></a>
			<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
		</td>
		<td>
			{if $data.id|default:'new' == 'new'}
				<select name="type_id" style="width: 98px;">
					{foreach item="type" from=$types}
						<option value="{$type.id}" {if $data.type_id|default:null == $type.id}selected="selected"{/if}>{__($type.name)}</option>
					{/foreach}
				</select>
			{else}
				{__($data.type_name)}
			{/if}
		</td>
		<td><input type="text" name="order_index" value="{$data.order_index|default:0|number_format:0:'.':''|escape}"/></td>
		<td>
			<select name="status_id" style="width: 98px;">
				{foreach item="status" from=$statuses}
					<option value="{$status.id}" {if $data.status_id|default:null == $status.id}selected="selected"{/if}>{__($status.name)}</option>
				{/foreach}
			</select>
		</td>
		<td>
			<button class="button" onclick="setting_save(this);">{__('Save')}</button>
			<button class="button" onclick="setting_view(this);">{__('Cancel')}</button>
		</td>
	</tr>
{/if}

{if $action == 'sub_load'}
	<tr>
		<td style="padding: 0px 0px 10px 25px; background-color: #D5D5D5;" colspan="{$cols}">
			<table class="data_table" style="margin: 0px; width: 100%;">
				<colgroup>
					{foreach item="data" from=$languages}
						<col style="width: 200px;"/>
					{/foreach}
					{if $category_setting.type_id == 20}
						<col style="width: 100px;"/>
					{/if}
					<col style="width: 150px;"/>
					<col style="width: 100px;"/>
					<col style="width: 130px;"/>
					<col style="width: 170px;"/>
				</colgroup>
			
				<thead>
					<tr>
						{foreach item="data" from=$languages}
							<th>{__({$data.name})}</th>
						{/foreach}
						{if $category_setting.type_id == 20}
							<th>{__('Color')}</th>
						{/if}
						<th>{__('Image')}</th>
						<th>{__('Index')}</th>
						<th>{__('Status')}</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{foreach item="data" from=$category_setting_values}
						{include file=$this_file action="sub_view"}
					{/foreach}
				</tbody>
				<tfoot>			
					<tr data-id="new" style="height: 20px;">
						<td colspan="{if $category_setting.type_id == 20}{$cols}{else}{$cols-1}{/if}" style="padding: 2px 2px;">
							<button class="button" onclick="setting_sub_edit(this);">{__('Add')}</button>
						</td>
					</tr>
				</tfoot>
			</table>
		</td>
	</tr>
{/if}

{if $action == 'sub_view'}
	<tr data-id="{$data.id|default:'new'}">
		{foreach item="lang" from=$languages}
			<td>{$data.lang[$lang.id].title|escape}</td>
		{/foreach}	
		{if $data.category_setting_type_id == 20}
			<td>{$data.color|default:''}</td>
		{/if}
		<td class="image_td">
			<div class="image_icon">
				<img {if $data.image_src|default:'' ne ''}src="{$data.image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
			</div>
		</td>
		<td>{$data.order_index|number_format:0:'.':''|escape}</td>	
		<td>{__($data.status_name)}</td>
		<td>
			<button class="button" onclick="setting_sub_edit(this);">{__('Edit')}</button>
			<button class="button" onclick="setting_sub_delete(this);">{__('Delete')}</button>
		</td>
	</tr>
{/if}

{if $action == 'sub_edit'}
	<tr data-id="{$data.id|default:'new'}" class="edit">
		{foreach item="lang" from=$languages}
			<td>
				<input type="hidden" name="language_id[]" value="{$lang.id}"/>
				<input type="hidden" name="content_id[]" value="{$data.lang[$lang.id].id|default:'new'}"/>
				<input type="text" name="title[]" value="{$data.lang[$lang.id].title|default:''|escape}"/>
			</td>
		{/foreach}
		{if $data.category_setting_type_id == 20}
			<td><input type="text" name="color" value="{$data.color|default:''|escape}"/></td>
		{/if}
		<td class="image_td">
			<input type="hidden" name="id" value="{$data.id|default:'new'}"/>
			<input type="hidden" name="category_setting_id" value="{$data.category_setting_id}"/>
			<input type="hidden" name="job" value="save"/>
		
			<div class="image_icon">
				<input type="hidden" name="image_src" value="{$data.image_src|default:''}"/>
				<img {if $data.image_src|default:'' ne ''}src="{$data.image_src|default:''|thumb}"{else}style="display: none;"{/if} onerror="$(this).hide();" />
			</div>
			<a class="button">{__('Browse')}<input id="image_src_{$smarty.now}" class="file_upload_input" type="file"/></a>
			<a class="button" onclick="openFileRemove($(this).parent().find('.image_icon input'))">{__('Remove')}</a>
		</td>
		<td><input type="text" name="order_index" value="{$data.order_index|default:0|number_format:0:'.':''|escape}"/></td>
		<td>
			<select name="status_id" style="width: 98px;">
				{foreach item="status" from=$statuses}
					<option value="{$status.id}" {if $data.status_id|default:null == $status.id}selected="selected"{/if}>{__($status.name)}</option>
				{/foreach}
			</select>
		</td>
		<td>
			<button class="button" onclick="setting_sub_save(this);">{__('Save')}</button>
			<button class="button" onclick="setting_sub_view(this);">{__('Cancel')}</button>
		</td>
	</tr>
{/if}