/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	config.contentsCss = ['assets/modules/manager/ckeditor_style.css'];

	// Define changes to default configuration here. For example:
	config.language = typeof(manager_lang)!='undefined'?manager_lang:'en';
	config.skin = 'moono';
	config.extraPlugins = 'video';
	
	config.allowedContent = true;
	
	config.width = 675;
	config.height = 400;
	config.toolbarCanCollapse = false;
	
	config.format_tags = 'p;h1;h2;h3;div;pre';
	
	config.toolbar = [
		['Bold','Italic','Underline','Strike','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','Subscript','Superscript','-','BulletedList','NumberedList','-','Outdent','Indent','Blockquote','-','Source'],
		'/',
		['Undo','Redo','-','Cut','Copy','Paste','PasteText','PasteFromWord','-','Find','Replace','-','Link','Unlink','Anchor','-','Image','Video','Flash','Table','HorizontalRule','-','SpecialChar','Smiley'],
		'/',
		['Styles','Format','Font','FontSize','-','TextColor','BGColor','-','RemoveFormat']
	];
	
	config.filebrowserBrowseUrl = base_url+'manager/filebrowser/open/files';
	config.filebrowserImageBrowseUrl = base_url+'manager/filebrowser/open/images';
	config.filebrowserFlashBrowseUrl = base_url+'manager/filebrowser/open/flash';
	config.filebrowserUploadUrl = base_url+'manager/filebrowser/upload/files';
	config.filebrowserImageUploadUrl = base_url+'manager/filebrowser/upload/images';
	config.filebrowserFlashUploadUrl = base_url+'manager/filebrowser/upload/flash';
		
	config.baseHref = base_url;
};
