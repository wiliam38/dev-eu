<!DOCTYPE html>
<html>
	<head>
		<meta name="author" content="http://www.insisoft.lv"/>
		
		<meta charset="utf-8">
		<base href="{$base_url}" />
		<title>ISS Manager</title>
		
		{foreach item=file from=$css_file|default:null}
			<link rel="stylesheet" type="text/css" href="{$file|mtime}"/>
		{/foreach}
		
		{foreach item=file from=$js_file|default:null}
			<script src="{$file|mtime}" type="text/javascript"></script>
		{/foreach}
	</head>
	<body>
		<div>{$content|default:""}</div>
	</body>
</html>