<?php defined('SYSPATH') or die('No direct script access.'); 
return array 
( 
    'banners' => array(
				'name' => 'banners',
				'model' => 'Homes',
				'template' => 'plugins/homes/homes',
				'parameters' => array ( )  ), 
    'languages' => array(
				'name' => 'languages',
				'model' => 'Languages',
				'template' => 'plugins/languages/menu',
				'parameters' => array ( )  ), 
    'left_category_menu' => array(
				'name' => 'left_category_menu',
				'model' => 'Categories',
				'template' => 'plugins/products/left_menu',
				'parameters' => array ( 
						'parent_list' => '0', )  ), 
    'news' => array(
				'name' => 'news',
				'model' => 'News',
				'template' => 'plugins/news/news',
				'parameters' => array ( )  ), 
    'orders_current' => array(
				'name' => 'orders_current',
				'model' => 'Orders',
				'template' => 'plugins/orders/current_order',
				'parameters' => array ( 
						'type' => 'current', )  ), 
    'products_list' => array(
				'name' => 'products_list',
				'model' => 'Products',
				'template' => 'plugins/products/products',
				'parameters' => array ( )  ), 
    'recipes' => array(
				'name' => 'recipes',
				'model' => 'Recipes',
				'template' => 'plugins/recipes/recipes',
				'parameters' => array ( )  ), 
    'top_menu' => array(
				'name' => 'top_menu',
				'model' => 'List',
				'template' => 'plugins/menu/top_menu',
				'parameters' => array ( 
						'parent_list' => '0', )  ), 
    'userlogin' => array(
				'name' => 'userlogin',
				'model' => 'Userslogin',
				'template' => 'plugins/userlogin/login',
				'parameters' => array ( )  ), 
); 