<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="description" content="{if $page.description|trim != ''}{$page.description|escape:'html'}{else}{$settings.description|escape:'html'}{/if}" />
		<meta name="keywords" content="{if $page.keywords|trim != ''}{$page.keywords|escape:'html'}{else}{$settings.keywords|escape:'html'}{/if}" />
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
		<base href="{$base_url}"/>
		{if $canonical|default:'' != ''}<link rel="canonical" href="{$canonical}"/>{/if}
		
		<title>{$page.title}{if $settings.site_name|trim != ''} :: {$settings.site_name}{/if}</title>	
		<link type="text/plain" rel="author" href="{$base_url}humans.txt" />
		
		<link rel="stylesheet" type="text/css" href="{'assets/templates/main/style.css'|mtime}"/>
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,700italic,400,700,600&subset=latin-ext,cyrillic-ext' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Roboto:400,300,300italic,400italic,700,700italic&subset=cyrillic-ext,latin-ext' rel='stylesheet' type='text/css'>
		
		<link rel="stylesheet" type="text/css" href="{'assets/libs/jquery-ui/jquery-ui.custom.css'|mtime}"/>
		<script src="{'assets/libs/jquery/jquery.min.js'|mtime}" type="text/javascript"></script>
		<script src="{'assets/libs/jquery-ui/jquery-ui.custom.min.js'|mtime}" type="text/javascript"></script>
		
		<link rel="stylesheet" type="text/css" href="{'assets/libs/jquery-ui/jquery-ui-combobox.19bar.css'|mtime}"/>
		<script src="{'assets/libs/jquery-ui/jquery-ui-combobox.js'|mtime}" type="text/javascript"></script>
		
		<link rel="stylesheet" type="text/css" href="{'assets/libs/jquery-plugins/fullPage/jquery.fullPage.css'|mtime}"/>
		<script src="{'assets/libs/jquery-plugins/fullPage/jquery.fullPage.min.js'|mtime}" type="text/javascript"></script>
		
		<link rel="stylesheet" type="text/css" href="{'assets/libs/jquery-plugins/mCustomScrollbar/jquery.mCustomScrollbar.css'|mtime}"/>
		<script src="{'assets/libs/jquery-plugins/mCustomScrollbar/jquery.mCustomScrollbar.concat.min.js'|mtime}" type="text/javascript"></script>
		
		<script src="{'assets/libs/jquery-plugins/form/jquery.form.js'|mtime}" type="text/javascript"></script>
		<script src="{'assets/libs/jquery-plugins/checkbox/checkbox.js'|mtime}" type="text/javascript"></script>
		
		<link rel="stylesheet" type="text/css" href="{'assets/plugins/userlogin/userlogin.css'|mtime}"/>
		<script src="{'assets/plugins/userlogin/userlogin.js'|mtime}" type="text/javascript"></script>
		
		<link rel="stylesheet" type="text/css" href="{'assets/plugins/orders/current_order.css'|mtime}"/>
		<script src="{'assets/plugins/orders/current_order.js'|mtime}" type="text/javascript"></script>

		<link rel="stylesheet" type="text/css" href="{'assets/libs/ionicons/css/ionicons.min.css'|mtime}"/>
		
		<script src="{'assets/templates/global/global.js'|mtime}" type="text/javascript"></script>
		{if !in_array($page.id, array(1,3))}
			<script type="text/javascript">
				$().ready(function() {
					$('#page_content').mCustomScrollbar({
						scrollbarPosition: 'outside',
						mouseWheel:{ scrollAmount: (navigator.platform.toUpperCase().indexOf('MAC')!==-1?80:200) }
					});
				});
			</script>
		{/if}
	</head>
	<body>
		{$gtm_enabled="{setting name="google.tag_manager_enabled"}"}
		{$gtm_key="{setting name="google.tag_manager_key"}"}

		{if $gtm_enabled == "1" && !empty($gtm_key)}
			<!-- Google Tag Manager -->
			<noscript><iframe src="//www.googletagmanager.com/ns.html?id={$gtm_key}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
			<script>
				(function(w,d,s,l,i) {
						w[l]=w[l]||[];
						w[l].push( { 'gtm.start':new Date().getTime(),event:'gtm.js'});
						var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
						j.async=true;
						j.src='//www.googletagmanager.com/gtm.js?id='+i+dl;
						f.parentNode.insertBefore(j,f);
				})(window,document,'script','dataLayer','{$gtm_key}');
			</script>
			<!-- End Google Tag Manager -->
		{/if}
	
		<div id="fb-root"></div>
		<script>
			(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=454761581237657&version=v2.0";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		</script>
	
		<div class="page" id="page">
			<div class="left-menu">
				<div class="logo"><a href="{$base_url}{$lang_tag}">
					{if {setting name="default.lv_flag_logo"} == '1'}
						<img src="{$base_url}assets/templates/main/img/logo-lv-flag.png?v=2" alt="19 BAR"/>
					{else}
						<img src="{$base_url}assets/templates/main/img/logo.png" alt="19 BAR"/>
					{/if}
				</a></div>
				<div class="category-menu">
					{plugin name="left_category_menu"}
				</div>
				<div class="languages">
					{plugin name="languages"}
				</div>
			</div>
			<div class="top-menu">
				{plugin name="top_menu"} 
			</div>
			<div class="content-wrapper" id="content" style="display: none;">
				<div class="content {if $page.plugin_controller == 1}no-padding{/if}">
					<div class="cart-content-button" id="cart_content_button">
						<div class="arrow"></div>
						<button class="button-green" onclick="$('#cart_button').click();">
							<span class="nr" id="cart_qty">{$cart_qty|default:0}</span> 
							<span class="one" {if $cart_qty|default:0 != 1}style="display: none"{/if}>{__('orders.item')}</span>
							<span class="more" {if $cart_qty|default:0 == 1}style="display: none"{/if}>{__('orders.items')}</span>
						</button>
					</div>
					<div id="page_content" class="{if !in_array($page.id, array(1,2,3,4))}text-content{/if}">
						{if !in_array($page.id, array(1,2,3,4)) && !empty($page.image_src)}
							<div class="image">
								<img src="{$base_url}{$page.image_src}"/>
							</div>
							<div class="text">
						{/if}
						{eval var=$page.content}
						{if $page.id == 7}
							<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false&libraries=places"></script>
							<script type="text/javascript">
								// place_id = ChIJpQGxms3P7kYR8s2eU6Yp4l8
								$().ready(function() {
									var map = new google.maps.Map(document.getElementById('google_map'), {
										center: new google.maps.LatLng(56.9536933, 24.121637),
										zoom: 15
									});

									var request = {
										placeId: 'ChIJpQGxms3P7kYR8s2eU6Yp4l8'
									};

									var infowindow = new google.maps.InfoWindow();
									var service = new google.maps.places.PlacesService(map);

									service.getDetails(request, function(place, status) {
										if (status == google.maps.places.PlacesServiceStatus.OK) {
											var marker = new google.maps.Marker({
												map: map,
												position: place.geometry.location
											});
											google.maps.event.addListener(marker, 'click', function() {
												infowindow.setContent(place.name);
												infowindow.open(map, this);
											});
										}
									});
								});
							</script>
							<div id="google_map" class="google-map"></div>
						{/if}
						{if !in_array($page.id, array(1,2,3,4)) && !empty($page.image_src)}</div>{/if}
					</div>
					<div class="overlay" id="overlay"></div>
					<div class="profile" id="profile">
						<a href="#close" onclick="menu_login($('#login_button')); return false;" class="close-button"></a>
						<div id="profile_content">
							{plugin name="userlogin"}
						</div>
					</div>
					<div class="cart" id="cart">
						<a href="#close" onclick="menu_cart($('#cart_button')); return false;" class="close-button"></a>
						<div id="cart_content"></div>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>		
	</body>
</html>