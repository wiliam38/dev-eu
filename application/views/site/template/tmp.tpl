<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="description" content="{if $page.description|trim != ''}{$page.description|escape:'html'}{else}{$settings.description|escape:'html'}{/if}" />
		<meta name="keywords" content="{if $page.keywords|trim != ''}{$page.keywords|escape:'html'}{else}{$settings.keywords|escape:'html'}{/if}" />
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
		<meta property="og:url" content="{$base_url}{$page.full_alias}" />
		<meta property="og:type" content="website" />		
		<meta property="og:title" content="{$page.title}{if $settings.site_name|trim != ''} :: {$settings.site_name}{/if}" />
		<meta property="og:description" content="{if $page.description|trim != ''}{$page.description|escape:'html'}{else}{$settings.description|escape:'html'}{/if}" />
		<meta property="og:image" content="{$base_url}assets/templates/global/img/social_lpts_logo.png" />
		<base href="{$base_url}"/>
		{if $canonical|default:'' != ''}<link rel="canonical" href="{$canonical}"/>{/if}		
		<title>{$page.title}{if $settings.site_name|trim != ''} :: {$settings.site_name}{/if}</title>		
		<link type="text/plain" rel="author" href="{$base_url}humans.txt" />
		<link href="{$base_url}favicon.ico" rel="icon" type="image/x-icon" />
		
		<link rel="stylesheet" type="text/css" href="{'assets/templates/main/style.css'|mtime}"/>
		
		<link rel="stylesheet" type="text/css" href="{'assets/libs/jquery-ui/jquery-ui.custom.css'|mtime}"/>
		<script src="{'assets/libs/jquery/jquery.min.js'|mtime}" type="text/javascript"></script>
		<script src="{'assets/libs/jquery-ui/jquery-ui.custom.min.js'|mtime}" type="text/javascript"></script>
	</head>
	<body>
		{$google_tag_manager={setting name="google.google_tag_manager"}}
		{if !empty($google_tag_manager)}
			<!-- Google Tag Manager -->
			<noscript><iframe src="//www.googletagmanager.com/ns.html?id={$google_tag_manager}"
			height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
			<script>(function(w,d,s,l,i) { w[l]=w[l]||[];w[l].push( { 'gtm.start':
			new Date().getTime(),event:'gtm.js' } );var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
			'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
			} )(window,document,'script','dataLayer','{$google_tag_manager}');</script>
			<!-- End Google Tag Manager -->		
		{/if}
		
		<div class="page">
			<div class="content">
				{eval var=$page.content}
			</div>
		</div>
	</body>
</html>