<!DOCTYPE html>
<html>
	<head>
		<meta name="author" content="http://www.insisoft.lv"/>
		
		<meta charset="utf-8">
		<base href="{$base_url}"/>		
		
		<title>wBOX Manager</title>
		
		<script src="{$base_url}{'assets/libs/jquery/jquery.min.js'|mtime}" type="text/javascript"></script>
		{*<link rel="stylesheet" type="text/css" href="{$base_url}{'assets/libs/jquery-ui/jquery-ui.custom.css'|mtime}"/>*}
		<link rel="stylesheet" type="text/css" href="{$base_url}{'assets/libs/jquery-ui-aristo/Aristo.css'|mtime}"/>
		<script src="{$base_url}{'assets/libs/jquery-ui/jquery-ui.custom.min.js'|mtime}" type="text/javascript"></script>
		
		<link rel="stylesheet" type="text/css" href="{$base_url}{'assets/modules/manager/global/style.css'|mtime}"/>
		<!--[if lte IE 7]><link rel="stylesheet" type="text/css" href="{$base_url}{'assets/modules/manager/global/style_ie.css'|mtime}"/><![endif]-->
		<!--[if IE 8]><link rel="stylesheet" type="text/css" href="{$base_url}{'assets/modules/manager/global/style_ie8.css'|mtime}"/><![endif]-->
		
		<script src="{$base_url}{'assets/modules/manager/global/main.js'|mtime}" type="text/javascript"></script>
		
		<link rel="stylesheet" type="text/css" href="{$base_url}{'assets/libs/jquery-ui/jquery-ui-combobox.css'|mtime}"/>
		<script src="{$base_url}{'assets/libs/jquery-ui/jquery-ui-combobox.js'|mtime}" type="text/javascript"></script>
		
		<link rel="stylesheet" type="text/css" href="{$base_url}{'assets/libs/jquery-plugins/multiselect/jquery.multiSelect.iss.css'|mtime}"/>
		<script src="{$base_url}{'assets/libs/jquery-plugins/multiselect/jquery.multiSelect.js'|mtime}" type="text/javascript"></script>
		
		<link rel="stylesheet" type="text/css" href="{$base_url}{'assets/libs/jquery-plugins/contextMenu/jquery.contextMenu.css'|mtime}"/>
		<script src="{$base_url}{'assets/libs/jquery-plugins/contextMenu/jquery.contextMenu.js'|mtime}" type="text/javascript"></script>		
		
		<script src={'assets/libs/jquery-plugins/ui-jalert/ui-jalert.js'|mtime} type="text/javascript"></script>
		<script src={'assets/libs/jquery-plugins/form/jquery.form.js'|mtime} type="text/javascript"></script>
		
		<link rel="stylesheet" type="text/css" href="{$base_url}{'assets/libs/jquery-plugins/jAlert/jquery.alerts.css'|mtime}"/>
		<script src={'assets/libs/jquery-plugins/jAlert/jquery.alerts.js'|mtime} type="text/javascript"></script>
		
		<link rel="stylesheet" type="text/css" href="{$base_url}{'assets/libs/jquery-plugins/issCalendar/issCalendar.css'|mtime}"/>
		<script src={'assets/libs/jquery-plugins/issCalendar/issCalendar.js'|mtime} type="text/javascript"></script>
		
		{foreach item=file from=$css_file|default:null}
			<link rel="stylesheet" type="text/css" href="{if !($file|substr:0:7|lower == 'http://' || $file|substr:0:8|lower == 'https://')}{$base_url}{/if}{$file|mtime}"/>
		{/foreach}
		
		{foreach item=file from=$js_file|default:null}
			<script src="{if !($file|substr:0:7 == 'http://' || $file|substr:0:8 == 'https://')}{$base_url}{/if}{$file|mtime}" type="text/javascript"></script>
		{/foreach}
		
		<script type="text/javascript">
			var multiSelect_selectAllText = "{__('Select all')}";
			var multiSelect_noneSelected = "{__('--- Select value ---')}";
			var multiSelect_oneSelected = "{__('1 selected')}";
			var multiSelect_moreSelected = "{__('% selected')}";
			var manager_lang = "{$manager_lang}";
			var cke_local_link = "{__('Local link')}";
			var cke_local_link_error = "{__('Local link not set!')}";

			var menu_edit = "{__('Edit')}"; 
			var menu_add = "{__('Add here')}";
			var menu_copy = "{__('Copy')}";
			var menu_delete = "{__('Delete')}";
		</script>
	</head>
	<body style="display: none;">
		<div id="menu">
			{foreach item=data from=$menu_data}
				<div class='menu_item'>
					<a {if $data.link ne ''}href='{if $data.link eq '/'}{$base_url}{else}{$data.link}{/if}'{/if} {if $data.target ne ''}target='{$data.target}'{/if}>{__({$data.name})}</a>
					{if $data.sub_menu|@count gt 0}
						<div class='menu_sub_items'>
							{foreach item=sub_data from=$data.sub_menu}
								<div class='menu_item'>
									<a {if $sub_data.link ne ''}href='{if $sub_data.link eq '/'}{$base_url}{else}{$sub_data.link}{/if}'{/if} {if $sub_data.target ne ''}target='{$sub_data.target}'{/if}>{__({$sub_data.name})}</a>
								</div>
							{/foreach}
						</div>
					{/if}
				</div>
			{/foreach}
			<div style="clear: both;"></div>
		</div>
		<table class="manager-main-table">
			<tbody>
				<tr id="data" style="height: 100%;">
					<td id="tree_panel" class="{$tree_hidden|default:''}">
						<div class="title-wrapper">
							<div class="title">{__('Site structure')}</div>
							<div class="toggle-menu">{if $tree_hidden|default:'' == ''}«{else}»{/if}</div>
						</div>					
						{include file="{$smarty.current_dir}/resource_tree/resource_tree.tpl" action="tree_items"}
						<a href="{$base_url}manager/pages/load/new" class="button" style="margin: 10px;">{__('New')}</a>
						<div class="clear" style="margin-bottom: 3px;"></div>
					</td>
					<td id="data_panel">
						{$data_panel}
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>