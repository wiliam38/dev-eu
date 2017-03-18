<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>elFinder 2.0</title>

		<!-- jQuery and jQuery UI (REQUIRED) -->
		<link rel="stylesheet" type="text/css" media="screen" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css">
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>

		<!-- elFinder CSS (REQUIRED) -->
		<link rel="stylesheet" type="text/css" media="screen" href="{$base_url}assets/libs/elfinder/css/elfinder.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="{$base_url}assets/libs/elfinder/css/theme.css">

		<!-- elFinder JS (REQUIRED) -->
		<script type="text/javascript" src="{$base_url}assets/libs/elfinder/js/elfinder.min.js"></script>

		<!-- elFinder translation (OPTIONAL) -->
		<script type="text/javascript" src="{$base_url}assets/libs/elfinder/js/i18n/elfinder.{$lang_tag}.js"></script>

		<!-- elFinder initialization (REQUIRED) -->
		<script type="text/javascript" charset="utf-8">
			function getURLParam(name) {
			    return decodeURI(
			        (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
			    );
			}
			
			$().ready(function() {
				var base_url = '{$base_url}';
				var type = '{$type}';
				
				var funcNum = getURLParam('CKEditorFuncNum');
				var elf = $('#elfinder').elfinder({
					commandsOptions : {
						getfile : {
							onlyURL  : true,
							multiple : false,
							folders  : false,
							oncomplete : ''
						}
		            },
					url : base_url+'manager/filebrowser/load',
					customData : {
						base_url : base_url,
						type : type
					},
					height : 515,
					urlUpload : base_url+'manager/filebrowser/load',
					getFileCallback : function(getfile) {
						window.opener.CKEDITOR.tools.callFunction(funcNum, getfile.substr(base_url.length));
						window.close();
		            },
		            resizable: false,
					lang: '{$lang_tag}'
				}).elfinder('instance');
			});
		</script>
	</head>
	<body>

		<!-- Element where elFinder will be created (REQUIRED) -->
		<div id="elfinder"></div>

	</body>
</html>