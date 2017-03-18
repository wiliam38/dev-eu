// EDIOTRS	
CKEDITOR.on('dialogDefinition', function(e) {
	if ((e.data.name != 'link')) return;
	// Overrides definition.
	var definition = e.data.definition;
	definition.onOk = CKEDITOR.tools.override(definition.onOk, function(original) {
		return function() {
			editor = this.getParentEditor();
			var process = false;
			if ((this.getValueOf('info', 'linkType') == 'local_page') && !this._.selectedElement) {
				var ranges = editor.getSelection().getRanges(true);
				if ((ranges.length == 1) && ranges[0].collapsed) process = true;
			}
			original.call(this);
			if (process) {
				var value = this.getValueOf('info', 'local_page_link');
				var index = value.lastIndexOf('(');
				if (index != -1) {
					var text = CKEDITOR.tools.trim(value.substr(0, index));
					if (text) CKEDITOR.plugins.link.getSelectedLink(editor).setText(text);
				}
			}
		};
	});

	// Overrides linkType definition.
	var infoTab = definition.getContents('info');
	var content = infoTab.get('linkType');
	content.items.unshift([cke_local_link, 'local_page']);
	
	tmp_element = content.items[0];
	content.items.splice(0, 1);
	content.items.splice(1, 0, tmp_element);
	
	infoTab.elements.push({
		type: 'vbox',
		id: 'local_page_options',
		children: [{
			type: 'select',
			id: 'local_page_link',
			label: cke_local_link,
			required: true,
			items: typeof(pages_tree)!='undefined'?pages_tree:[],
			setup: function(data) {
				this.setValue(data.local_page_link || '');
			},
			onShow: function() {
				select_item = $(this.getDialog().getContentElement('info', 'local_page_link').getElement().$).find('select');
				$(select_item).css('width', '350px');
				$('option', select_item).each(function(i,o) {
					$(o).html($(o).text().replace(/ /g, '&nbsp;'));
				});
			},
			validate: function() {
				var dialog = this.getDialog();
				if (dialog.getValueOf('info', 'linkType') != 'local_page') {
					return true;
				}
				if (dialog.getValueOf('info', 'local_page_link') == '') {
					alert(cke_local_link_error);
					return false;
				}
				return true;
			}
		}]
	});
	content.onChange = CKEDITOR.tools.override(content.onChange, function(original) {
		return function() {
			original.call(this);
			var dialog = this.getDialog();
			var element = dialog.getContentElement('info', 'local_page_options').getElement().getParent().getParent();
			if (this.getValue() == 'local_page') {
				element.show();
				dialog.showPage('target');
				var uploadTab = dialog.definition.getContents('upload');
				if (uploadTab && !uploadTab.hidden) {
					dialog.hidePage('upload');
				}
			} else {
				element.hide();
			}
		};
	});
	content.setup = function(data) {
		if (!data.type || (data.type == 'url') && !data.url) { }
		else if (data.url && !data.url.protocol && data.url.url && data.url.url.substr(0,1) == '{') {
			if (data.url.url) {
				data.type = 'local_page';
				data.local_page_link = data.url.url;
				delete data.url;
			}
		}
		this.setValue(data.type);
	};
	content.commit = function(data) {
		data.type = this.getValue();
		if (data.type == 'local_page') {
			data.type = 'url';
			var dialog = this.getDialog();
			dialog.setValueOf('info', 'protocol', '');
			dialog.setValueOf('info', 'url', dialog.getValueOf('info', 'local_page_link'));
		}
	};
});