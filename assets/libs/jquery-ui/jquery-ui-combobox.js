/*
 * PARAMETERS
 * 	source 		JSON AJAX source
 * 	delay		Delay before AJAX load
 * 	minLength	Min lenght for AJAX load
 * 	autoload	Load combobox on input focus
 * 	onChange	JavaScrpt fonction execuded after value change
 * 	freeText	Allow free text
 *  uiButton	Show UI arrow 
 *  columns		Columns width [0, 20, 30]
 *  maxHeight	UL max height
 * 	
 * FUNCTION
 *  $('#..._combobox_select').combobox('setValue',1);
 *  $('#..._combobox_select').combobox('reinit');
 */

(function( $ ) {
	$.widget( "ui.combobox", {
		_create: function() {		
			var obj = this;
			
			if (!obj.element.is('select')) {
				return false;
			}
			
			obj.def_value = $.trim(obj.element.find(':selected').text()).replace(/&/g, '&amp;').replace(/>/g, '&gt;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
			obj.def_id = typeof(obj.element.val())=='string'?obj.element.val():'';
			obj.loading = false;
			obj.values = new Array;
			
			if (typeof(obj.options.columns) != 'undefined') {
				for (var i=0, L=obj.options.columns.length, sum=0; i<L; sum += obj.options.columns[i++]);
				obj.width = sum+30;
			}
			
			// CREATE INPUT FOR COMBOBOX
			obj.select = obj.element;
			obj.element = $('<input type="text" value="'+obj.def_value+'"/>')
				.show()
				.insertAfter(obj.select);
			if (obj.select.attr('id')) obj.element.attr('id',obj.select.attr('id')+'_combobox');
			if (obj.select.attr('name')) {
				if (obj.select.attr('name').substr(-2) == '[]') obj.element.attr('name',obj.select.attr('name').substr(0,obj.select.attr('name').length-2)+'_combobox[]');
				else obj.element.attr('name',obj.select.attr('name')+'_combobox');
			}
			if (obj.select.attr('style')) obj.element.attr('style',obj.select.attr('style'));
			if (obj.select.attr('class')) obj.element.attr('class',obj.select.attr('class'));
			if (obj.select.attr('onfocus')) obj.element.attr('onfocus',obj.select.attr('onfocus'));
			if (obj.select.attr('onblur')) obj.element.attr('onblur',obj.select.attr('onblur'));
			if (obj.select.attr('placeholder')) obj.element.attr('placeholder',obj.select.attr('placeholder'));
			if (obj.select.attr('tabindex')) obj.element.attr('tabindex',obj.select.attr('tabindex'));
			if (obj.select.is(':disabled')) obj.element.attr('disabled',true);
			
			if (obj.select.attr('onchange')) obj.options.onChange = function() { eval(obj.select.attr('onchange').replace('this','obj.val_input')); };
			
			// CREATE HIDDEN INPUT
			obj.val_input = $('<input type="hidden" value="'+obj.def_id+'"/>')
				.insertAfter(this.element);
			if (obj.select.attr('id')) obj.val_input.attr('id',obj.select.attr('id'));
			if (obj.select.attr('name')) obj.val_input.attr('name',obj.select.attr('name'));
			
			// OLD SELECT
			obj.select.hide();
			if (obj.select.attr('id')) obj.select.attr('id',obj.select.attr('id')+'_combobox_select');
			if (obj.select.attr('name')) {
				if (obj.select.attr('name').substr(-2) == '[]') obj.select.attr('name',obj.select.attr('name').substr(0,obj.select.attr('name').length-2)+'_combobox_select[]');
				else obj.select.attr('name',obj.select.attr('name')+'_combobox_select');
			}
									
			// UPDATE INPUT FIELD			
			obj.input = this.element
				.autocomplete({
					position: { collision: "flip" },
					appendTo: this.element.parent(),
					delay: obj.options.delay ? obj.options.delay : obj.options.source ? 200 : 0,
					minLength: obj.options.minLength ? obj.options.minLength : 0,
					create: function(event, ui) {										
						if (!obj.options.source) {
							var i=0;
							obj.select.find( "option" ).map(function() {
								text = '';
								text1 = null; text1_s = true;
								text2 = null; text2_s = true;
								text3 = null; text3_s = true;
								
								if (typeof(obj.options.columns) != 'undefined') {									
									if (typeof(obj.options.columns[0]) != 'undefined') {
										text1 =  $(this).text()?$(this).text():'';
										text1_s = $(this).text()!=$(this).html()?false:true;
										if (text1_s) text += ' ' + text1;
									}
									if (typeof(obj.options.columns[1]) != 'undefined') {
										text2 = $(this).attr('data-data1')?$(this).attr('data-data1'):'';
										text2_s = $('<div>'+$(this).attr('data-data1')+'</div>').text()!=$('<div>'+$(this).attr('data-data1')+'</div>').html()?false:true;
										if (text2_s) text += ' ' + text2;
									}
									if (typeof(obj.options.columns[2]) != 'undefined') {
										text3 = $(this).attr('data-data2')?$(this).attr('data-data2'):'';
										text3_s = $('<div>'+$(this).attr('data-data2')+'</div>').text()!=$('<div>'+$(this).attr('data-data2')+'</div>').html()?false:true;
										if (text3_s) text += ' ' + text3;
									}
									text = $(document.createTextNode(text)).text();
								} else {
									text = $(this).text();
								}					
								
								obj.values[i++] = {
									"value":	$.trim($(this).text()), 
									"label":	$('<p>'+text+'</p>').text(), 
									"category":	$(this).closest("optgroup").attr("label"),
									"text":		text, 
									"text1":	text1, 
									"text1_s":	text1_s, 
									"text2":	text2, 
									"text2_s":	text2_s, 
									"text3":	text3, 
									"text3_s":	text3_s, 
									"id":		$.trim($(this).val()), 
									"option":	this };									
							});
						}
					},
					source: obj.options.source ? obj.options.source : function( request, response ) {
						$(obj.input).addClass('ui-autocomplete-loading');
						response($.ui.autocomplete.filter(obj.values, request.term) );
						$(obj.input).removeClass('ui-autocomplete-loading');
					},
					search: function( event, ui ) {
						if (obj.options.source) $(this).autocomplete( 'option', 'source', obj.options.source + (obj.options.source.indexOf('?')!=-1?"&":"?") + 'selected_id='+obj.val_input.val() );
					},
					select: function(event, ui) {
						obj.val_input.val(ui.item.id);
						obj.def_value = $.trim(ui.item.value);
						obj.def_id = ui.item.id;						
						
						// EVAL onChange function
						if (obj.options.onChange != undefined) {
							var onChange = obj.options.onChange;
							setTimeout(function() {
								onChange(obj.val_input, obj);
							}, 150);	
						}; 
						
						// IE BUG
						$(obj.input).parent().focus();
					},
					change: function( event, ui ) {
						if ( !ui.item ) {
							var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
								valid = false;
							if ( !valid ) {
								// remove invalid value, as it didn't match anything
								if (!obj.options.freeText || obj.options.freeText == false) {
									$(this).val(obj.def_value);
									$(obj.val_input).val(obj.def_id);
									return false;
								} else {
									$(obj.val_input).val('');
								}
							}
						}
						
						// IE BUGFIX
						setTimeout(function() {
							obj.loading = false;
						}, 300);
					},
					open: function( event, ui ) {
						var ac = $(obj.input).autocomplete('widget');	
						
						$(obj.input).autocomplete('widget').css('width', obj.width);
						if (obj.options.maxHeight) $(obj.input).autocomplete('widget').css('maxHeight', obj.options.maxHeight);
						
						$('#ui-active-menuitem',ac).mouseover();
						$(ac).scrollTop(0);
						if ($('#ui-active-menuitem',ac).closest('li').length > 0) $(ac).scrollTop($('#ui-active-menuitem',ac).closest('li').position().top+1);
					},
					close: function( event, ui ) {
						var ac = $(obj.input).autocomplete('widget');
						
						// IE BUFGIX
						setTimeout(function() {
							obj.loading = false;
						}, 300);
					}
				})
				.focus(function() {
					$(obj.input).select();
					setTimeout(function() {
						$(obj.input).select();
					}, 200);
				})
				.click(function(e) {					
					if (obj.input.autocomplete( "widget" ).is( ":visible" )) {
						obj.input.autocomplete( "close" );
						return;
					} 

					// pass empty string as value to search for, displaying all results
					obj.loading = true;
					var min_len = obj.input.autocomplete("option","minLength");
					obj.input.autocomplete("option","minLength",0);
					obj.input.autocomplete( "search", "" );
					obj.input.autocomplete("option","minLength",min_len);
				})
				.addClass('ui-combobox-input')
				.show();
			
			obj.input.data('uiAutocomplete')._renderItem = function( ul, item ) {
				text = '';
				pattern = new RegExp('('+obj.input.val()+')','gi');
				
				if (typeof(obj.options.columns) != 'undefined') {
					if (typeof(item.text1) == 'string') {
						if (item.text1_s) text += '<div style="width: '+obj.options.columns[0]+'px;" class="issComboboxData">'+item.text1.replace(pattern,'<span class="issComboboxActive">$1</span>')+'</div>';
						else text += '<div style="width: '+obj.options.columns[0]+'px;" class="issComboboxData">'+item.text1+'</div>';
					}
					if (typeof(item.text2) == 'string') {
						if (item.text2_s) text += '<div style="width: '+obj.options.columns[1]+'px;" class="issComboboxData">'+item.text2.replace(pattern,'<span class="issComboboxActive">$1</span>')+'</div>';
						else text += '<div style="width: '+obj.options.columns[1]+'px;" class="issComboboxData">'+item.text2+'</div>';
					}
					if (typeof(item.text3) == 'string') {
						if (item.text3_s) text += '<div style="width: '+obj.options.columns[2]+'px;" class="issComboboxData">'+item.text3.replace(pattern,'<span class="issComboboxActive">$1</span>')+'</div>';
						else text += '<div style="width: '+obj.options.columns[2]+'px;" class="issComboboxData">'+item.text3+'</div>';
					}
					text += '<div style="clear: both;"></div>';
				} else {
					text = typeof(item.text)!='undefined'?item.text.replace(pattern,'<span class="issComboboxActive">$1</span>'):item.value.replace(pattern,'<span class="issComboboxActive">$1</span>');
				}
				
				return $( "<li></li>" )
					.addClass($(item.option).attr('class'))
					.data( "item.autocomplete", item )
					.append( "<a " + ((item.id == obj.val_input.val())?' id="ui-active-menuitem" ':'') + ">" + text + "</a>" )
					.appendTo( ul );
			};
			
			obj.input.data('uiAutocomplete')._renderMenu = function(ul, items) {
				var self = this, currentCategory = "";
				$.each(items, function(index, item) {
					if (item.category != currentCategory) {
						if (item.category) {
							ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
						}
						currentCategory = item.category;
					}
					self._renderItemData(ul, item);
				});
			};			

			obj.input.data("uiAutocomplete").menu._isDivider = function( item ) {
				return false;
			};
		},
		reinit: function() {
			var obj = this;	
			if (!obj.options.source) {
				obj.values = [];
				var i=0;
				obj.select.find( "option" ).map(function() {
					text = '';
					text1 = null; text1_s = true;
					text2 = null; text2_s = true;
					text3 = null; text3_s = true;
					
					if (typeof(obj.options.columns) != 'undefined') {									
						if (typeof(obj.options.columns[0]) != 'undefined') {
							text1 =  $(this).text()?$(this).text():'';
							text1_s = $(this).text()!=$(this).html()?false:true;
							if (text1_s) text += ' ' + text1;
						}
						if (typeof(obj.options.columns[1]) != 'undefined') {
							text2 = $(this).attr('data-data1')?$(this).attr('data-data1'):'';
							text2_s = $('<div>'+$(this).attr('data-data1')+'</div>').text()!=$('<div>'+$(this).attr('data-data1')+'</div>').html()?false:true;
							if (text2_s) text += ' ' + text2;
						}
						if (typeof(obj.options.columns[2]) != 'undefined') {
							text3 = $(this).attr('data-data2')?$(this).attr('data-data2'):'';
							text3_s = $('<div>'+$(this).attr('data-data2')+'</div>').text()!=$('<div>'+$(this).attr('data-data2')+'</div>').html()?false:true;
							if (text3_s) text += ' ' + text3;
						}
						text = document.createTextNode(text);
					} else {
						text = $(this).text();
					}					
					
					obj.values[i++] = {"value": $.trim($(this).text()), "label": $('<p>'+text+'</p>').text(), "text": text, "text1": text1, "text1_s": text1_s, "text2": text2, "text2_s": text2_s, "text3": text3, "text3_s": text3_s, "id": $.trim($(this).val()), "option": this};									
				});
			}
		},
		setValue: function(val) {
			var obj = this;	
			obj.val_input.val(val);
			
			$.each(obj.values, function(i,o) {
				if (o.id == obj.val_input.val()) {
					obj.def_value = $.trim(o.value);
					obj.def_id = o.id;
					obj.input.val($.trim(o.value));
				};
			});
		}
	});
})( jQuery );