{$this_file="{$smarty.current_dir}/{$smarty.template}"}
{$cols=11}

{if $action eq "load"}
	<div class="title">
		{__('Templates')}
	</div>
	<table class="data_table">
		<thead>
			<tr>
				<th style="width: 150px;">{__('Name')}</th>
				<th style="width: 200px;">{__('Template')}</th>
				<th style="width: 70px;">{__('Show image')}</th>
				<th style="width: 70px;">{__('Show title image')}</th>
				<th style="width: 70px;">{__('Show menu image')}</th>
				<th style="width: 70px;">{__('Show Introtext')}</th>
				<th style="width: 70px;">{__('Show SEO')}</th>
				<th style="width: 70px;">{__('Show Target')}</th>
				<th style="width: 70px;">{__('Show Gallery')}</th>
				<th style="width: 100px;">{__('User')}</th>
				<th style="width: 140px;"></th>
			</tr>
		</thead>
		<tbody>
			{foreach item="data" from=$plugins}
				{include file="$this_file" action="view"}
			{/foreach}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="{$cols}">
					<a class="button" action="templates_add">{__('Add')}</a>
				</td>
			</tr>
		</tfoot>
	</table>	
{/if}

{if $action eq "view"}
	<tr data_id="{$data.id}">
		<td>{$data.name|strip_tags}</td>
		<td>{$data.tpl_name|strip_tags}</td>
		<td class="center"><input type="checkbox" name="conf_image" value="1" {if !empty($data.conf_image)}checked="checked"{/if} disabled="disabled"/></td>
		<td class="center"><input type="checkbox" name="conf_title_image" value="1" {if !empty($data.conf_title_image)}checked="checked"{/if} disabled="disabled"/></td>
		<td class="center"><input type="checkbox" name="conf_menu_image" value="1" {if !empty($data.conf_menu_image)}checked="checked"{/if} disabled="disabled"/></td>
		<td class="center"><input type="checkbox" name="conf_introtext" value="1" {if !empty($data.conf_introtext)}checked="checked"{/if} disabled="disabled"/></td>
		<td class="center"><input type="checkbox" name="conf_seo" value="1" {if !empty($data.conf_seo)}checked="checked"{/if} disabled="disabled"/></td>
		<td class="center"><input type="checkbox" name="conf_target" value="1" {if !empty($data.conf_target)}checked="checked"{/if} disabled="disabled"/></td>
		<td class="center"><input type="checkbox" name="conf_gallery" value="1" {if !empty($data.conf_gallery)}checked="checked"{/if} disabled="disabled"/></td>
		<td>{$data.user_full_name|strip_tags}</td>
		<td>
			<a class="button" action="templates_edit">{__('Edit')}</a>
			<a class="button" action="templates_delete">{__('Delete')}</a>
		</td>
	</tr>
{/if}

{if $action eq "edit"}
	<tr data_id="{$data.id}" class="edit">
		<td>
			<input type="hidden" name="id" value="{$data.id}"/>
			<input type="text" name="name" value="{$data.name}"/>
		</td>
		<td><input type="text" name="tpl_name" value="{$data.tpl_name|strip_tags}"/></td>
		<td class="center"><input type="checkbox" name="conf_image" value="1" {if !empty($data.conf_image)}checked="checked"{/if}/></td>
		<td class="center"><input type="checkbox" name="conf_title_image" value="1" {if !empty($data.conf_title_image)}checked="checked"{/if}/></td>
		<td class="center"><input type="checkbox" name="conf_menu_image" value="1" {if !empty($data.conf_menu_image)}checked="checked"{/if}/></td>
		<td class="center"><input type="checkbox" name="conf_introtext" value="1" {if !empty($data.conf_introtext)}checked="checked"{/if}/></td>
		<td class="center"><input type="checkbox" name="conf_seo" value="1" {if !empty($data.conf_seo)}checked="checked"{/if}/></td>
		<td class="center"><input type="checkbox" name="conf_target" value="1" {if !empty($data.conf_target)}checked="checked"{/if}/></td>
		<td class="center"><input type="checkbox" name="conf_gallery" value="1" {if !empty($data.conf_gallery)}checked="checked"{/if}/></td>
		<td></td>
		<td>
			<a class="button" action="templates_save">{__('Save')}</a>
			<a class="button" action="templates_view">{__('Cancel')}</a>
		</td>
	</tr>
{/if}