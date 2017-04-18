/*
 * jQuery Date Time Picker plugin
 *
 * 
USAGE:
	$(".someclass").issCalendar({
		show_time: flase, // true or false
		min_date: false, // string or input field
		max_date: false, // string or input field
	});
*/
(function($) {
	$.fn.issCalendar = function(init_data) {	
		$(this).each(function() {
			var obj = $(this);
			
			init_data = typeof(init_data)=='undefined'?{}:init_data;
			obj.show_time = typeof(init_data.show_time)!='undefined'?init_data.show_time:false;
			obj.min_date = typeof(init_data.min_date)!='undefined'?init_data.min_date:false;
			obj.max_date = typeof(init_data.max_date)!='undefined'?init_data.max_date:false;
			obj.show_tbd = typeof(init_data.show_tbd)!='undefined'?init_data.show_tbd:false;
			obj.show_tbd_value = typeof(init_data.show_tbd_value)!='undefined'?init_data.show_tbd_value:'';
			
			//Internationalization strings
			lang_tag = typeof(lang_tag)!='undefined'?lang_tag:'';
			if (lang_tag == 'lv') {
				obj.i18n = {
					days: [ 'P','O','T','C','P','S','Sv' ],
					months: [ 'Jan','Feb','Mar','Apr','Mai','Jūn', 'Jūl','Aug','Sep','Okt','Nov','Dec' ],
					month_names: { },
					btn_ok: 'OK',
					btn_cancel: 'Atcelt',
					btn_clear: 'Notīrīt'
				};
			} else if (lang_tag == 'ru') {
				obj.i18n = {
					days: [ 'Вс','Пн','Вт','Ср','Чт','Пт','Сб' ],
					months: [ 'Янв','Фев','Мар','Апр','Май','Июн', 'Июл','Авг','Сен','Окт','Ноя','Дек' ],
					month_names: { },
					btn_ok: 'OK',
					btn_cancel: 'Отменить',
					btn_clear: 'Очистить'
				};
			} else {
				obj.i18n = {
					days: [ "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" ],
					months: [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ],
					month_names: { },
					btn_ok: 'OK',
					btn_cancel: 'Cancel',
					btn_clear: 'Clear'
				};
			}
			
			for (i=0; i<obj.i18n.months.length; i++) {
				obj.i18n.month_names[obj.i18n.months[i].toLowerCase()] = i;
			}
			obj.parseDate = function(str_date) {	
				day = null; month = null; year = null; hour = 0; min = 0; sec = 0;				
				parts = str_date.split(' ');
				
				if (typeof(parts[0]) == 'string') {
					date_parts = parts[0].split('-');				
					day = typeof(date_parts[0])=='string'?date_parts[0]:null;
					month = typeof(date_parts[1])=='string'?obj.i18n.month_names[date_parts[1].toLowerCase()]:null;
					year = typeof(date_parts[2])=='string'?date_parts[2]:null;
				}
				
				if (typeof(parts[1]) == 'string') {
					time_parts = parts[1].split(':');	
					hour = typeof(time_parts[0])=='string'?time_parts[0]:0;
					min = typeof(time_parts[1])=='string'?time_parts[1]:0;
					sec = typeof(time_parts[2])=='string'?time_parts[2]:0;
				}				
				
				if (day == null || month == null || year == null) return null;
				else return new Date(year, month, day, hour, min, sec);
			};
			
			// INIT DATE FORMAT
			obj.show_day = false; obj.show_month = false; obj.show_year = false; obj.show_hour = false; obj.show_min = false; obj.show_sec = false;	
			if (obj.val() != '' && obj.val().toLowerCase() != obj.show_tbd_value.toString().toLowerCase()) {
				parts = obj.val().split(' ');			
				if (typeof(parts[0]) == 'string') {
					date_parts = parts[0].split('-');				
					obj.show_day = typeof(date_parts[0])=='string'?true:false;
					obj.show_month = typeof(date_parts[1])=='string'?true:false;
					obj.show_year = typeof(date_parts[2])=='string'?true:false;
				}			
				if (typeof(parts[1]) == 'string') {
					time_parts = parts[1].split(':');	
					obj.show_hour = typeof(time_parts[0])=='string'?true:false;
					obj.show_min = typeof(time_parts[1])=='string'?true:false;
					obj.show_sec = typeof(time_parts[2])=='string'?true:false;
				}	
			} else {
				obj.show_day = true;
				obj.show_month = true;
				obj.show_year = true;
				if (obj.show_time) {
					obj.show_hour = true;
					obj.show_min = true;
					obj.show_sec = false;
				}
			}
			
			// DATE INPUT
			obj
				.attr('readonly','readonly')
				.css('text-align', 'center')
				.click(function() {
					if (typeof(obj.ui) != 'undefined' && $('div', obj.ui).length != 0) {
						obj.ui.closest('div.issCalendar').remove();
						obj.ui = undefined;
					}
					$('div.issCalendar').remove();
					var cal_function = obj.show_calendar;
					
					obj.tmp_date = obj.parseDate(obj.val());
					if (obj.tmp_date==null) obj.tmp_date = new Date();
					
					if (obj.min_date) {
						if (typeof(obj.min_date) == 'string') {
							if (obj.tmp_date < obj.parseDate(obj.min_date)) obj.tmp_date = obj.parseDate(obj.min_date);
						} else {
							if (obj.tmp_date < obj.parseDate(obj.min_date.val()) && obj.min_date.val() != '' && obj.min_date.val().toString().toLowerCase() != obj.show_tbd_value.toString().toLowerCase()) obj.tmp_date = obj.parseDate(obj.min_date.val());
						}
					}
					if (obj.max_date) {
						if (typeof(obj.max_date) == 'string') {
							if (obj.tmp_date > obj.parseDate(obj.max_date)) obj.tmp_date = obj.parseDate(obj.max_date);
						} else {
							if (obj.tmp_date > obj.parseDate(obj.max_date.val()) && obj.max_date.val() != '' && obj.max_date.val().toString().toLowerCase() != obj.show_tbd_value.toString().toLowerCase()) obj.tmp_date = obj.parseDate(obj.max_date.val());
						}
					}	
					
					cal_function();
				});
			$('<img src="assets/libs/jquery-plugins/issCalendar/calendar.png" class="issCalendarButton"/>')
				.click(function() { obj.click(); })
				.insertAfter(obj);
						
			//
			// CALENDAR FUNCTION
			//
			obj.show_calendar = function() {
				init = false;
				if (typeof(obj.ui) == 'undefined') init = true;
				
				// MIN/MAX DATE
				min_date = false;
				max_date = false;
				if (obj.min_date) {
					if (typeof(obj.min_date) == 'string') min_date = obj.parseDate(obj.min_date);
					else min_date = obj.parseDate(obj.min_date.val());
					if (min_date > obj.tmp_date && min_date && min_date != '' && min_date.toString().toLowerCase() != obj.show_tbd_value.toString().toLowerCase()) obj.tmp_date = min_date;
				}
				if (obj.max_date) {
					if (typeof(obj.max_date) == 'string') max_date = obj.parseDate(obj.max_date);
					else max_date = obj.parseDate(obj.max_date.val());
					if (max_date < obj.tmp_date && max_date && max_date != '' && max_date.toString().toLowerCase() != obj.show_tbd_value.toString().toLowerCase()) obj.tmp_date = max_date;
				}
				
				if (init) {
					// UI
					obj.ui = $('<div class="issCalendar ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"><div class="issCalendarData"><table><tbody><tr><td></td><td></td></tr></tbody></table></div></div>')
						.insertAfter(obj);
					obj.ui = obj.ui.closest('div.issCalendar').find('div:first>table:first>tbody:first>tr:first>td:first');
					
					obj.ui.closest('div.issCalendar').css({ top: (obj.closest('span').height())+'px' });					
					
					// HEADER				
					obj.ui_header1 = $('<div class="issCalendarHeader ui-datepicker-header ui-widget-header ui-helper-clearfix ui-corner-all"><div class="ui-datepicker-title"></div></div>')
						.appendTo(obj.ui);
					
					// CALENDAR TABLE
					obj.ui_table1 = $('<table class="issCalendarTable"><tbody></tbody></table>')
						.appendTo(obj.ui);
					if (obj.show_time) {
						obj.ui_table1.css({ width: '308px'});
						obj.ui.closest('div.issCalendar').css({ width: '308px'});
					}
					obj.ui_table1 = $('tbody:first', obj.ui_table1);
				}	
				
				// POSITION
				$(obj.ui).closest('div.issCalendar').css({
					'left': $(obj).position().left
				});		
				if(navigator.userAgent.match(/msie [7]/i)) $(obj.ui).closest('div.issCalendar').css('marginTop', $(obj).outerHeight()+1);
				
				//
				// YEAR
				//
				$('.ui-datepicker-prev, .ui-datepicker-next', obj.ui_header1).remove();
				if (min_date && obj.tmp_date.getFullYear()-1 < min_date.getFullYear()) {
					$('<div class="ui-datepicker-prev ui-corner-all ui-state-disabled" title="Prev"><span class="ui-icon ui-icon-circle-triangle-w"></span></div>')
						.appendTo(obj.ui_header1);
				} else {
					$('<div class="ui-datepicker-prev ui-corner-all" title="Prev"><span class="ui-icon ui-icon-circle-triangle-w"></span></div>')
						.appendTo(obj.ui_header1)
						.data('date', new Date(obj.tmp_date.getFullYear()-1, 0, 1, obj.tmp_date.getHours(), obj.tmp_date.getMinutes(), obj.tmp_date.getSeconds()))
						.click(function() {
							obj.tmp_date = $(this).data('date');
							var cal_function = obj.show_calendar;
							cal_function();					
						});
				}
				if (max_date && obj.tmp_date.getFullYear()+1 > max_date.getFullYear()) {
					$('<div class="ui-datepicker-next ui-corner-all ui-state-disabled" title="Next"><span class="ui-icon ui-icon-circle-triangle-e"></span></div>')
						.appendTo(obj.ui_header1);
				} else {
					$('<div class="ui-datepicker-next ui-corner-all" title="Next"><span class="ui-icon ui-icon-circle-triangle-e"></span></div>')
						.appendTo(obj.ui_header1)
						.data('date', new Date(obj.tmp_date.getFullYear()+1, 0, 1, obj.tmp_date.getHours(), obj.tmp_date.getMinutes(), obj.tmp_date.getSeconds()))
						.click(function() {
							obj.tmp_date = $(this).data('date');
							var cal_function = obj.show_calendar;
							cal_function();	
						});
				}
				
				//
				// MONTH
				//
				if (init) {
					//$('<tr class="issCalendarTableNoBottom"><td class="issCalendarTableTitle">Month:</td><td></td></tr>')
					//	.appendTo(obj.ui_table1);
					obj.ui_month1 = $('<tr><td colspan="2"></td></tr>')
						.appendTo(obj.ui_table1);
					obj.ui_month1 = $('td:first', obj.ui_month1);
				}
				
				month_data = $('<td colspan="2"></td>');
				$('<table class="issCalendarMonthTable"><tbody></tbody></table>').appendTo(month_data);
				for (i=0; i<12; i++) {
					if (i==0 || i == 6) $('<tr></tr>').appendTo($('table tbody', month_data));
					if ((min_date && new Date(obj.tmp_date.getFullYear(), i, 1, 0, 0, 0) < new Date(min_date.getFullYear(), min_date.getMonth(), 1, 0, 0, 0)) ||
						(max_date && new Date(obj.tmp_date.getFullYear(), i, 1, 0, 0, 0) > new Date(max_date.getFullYear(), max_date.getMonth(), 1, 0, 0, 0))) {
						$('<td class="issCalendarDisabled"></td>')
							.html(obj.i18n.months[i])
							.appendTo($('tr:last', month_data));	
					} else {
						if (i == obj.tmp_date.getMonth()) {
							$('<td class="issCalendarActive"></td>')
								.html(obj.i18n.months[i])
								.appendTo($('tr:last', month_data));						
						} else {
							$('<td class="issCalendarLink"></td>')
								.html(obj.i18n.months[i])
								.appendTo($('tr:last', month_data))
								.data('date', new Date(obj.tmp_date.getFullYear(), i, 1, obj.tmp_date.getHours(), obj.tmp_date.getMinutes(), obj.tmp_date.getSeconds()))
								.click(function() {
									obj.tmp_date = $(this).data('date');
									var cal_function = obj.show_calendar;
									cal_function();	
								});
						}
					}
				}
				obj.ui_month1 = month_data.replaceAll(obj.ui_month1);
				
				//
				// Day
				//
				if (init) {
					//$('<tr class="issCalendarTableNoBottom"><td class="issCalendarTableTitle">Day:</td><td></td></tr>')
					//	.appendTo(obj.ui_table1);
					obj.ui_day1 = $('<tr><td colspan="2"></td></tr>')
						.appendTo(obj.ui_table1);
					obj.ui_day1 = $('td:first', obj.ui_day1);
				}	
				
				day_data = $('<td colspan="2"></td>');
				$('<table class="issCalendarDayTable"><tbody></tbody></table>').appendTo(day_data);
				//<th>W</th>
				$('<tr><th>'+obj.i18n.days[1]+'</th><th>'+obj.i18n.days[2]+'</th><th>'+obj.i18n.days[3]+'</th><th>'+obj.i18n.days[4]+'</th><th>'+obj.i18n.days[5]+'</th><th>'+obj.i18n.days[6]+'</th><th>'+obj.i18n.days[0]+'</th></tr>')
					.appendTo($('table tbody', day_data));
				
				max_day = 32 - new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), 32).getDate();
				week_day = new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), 1).getDay() - 1;
				if (week_day < 0) week_day = 6;

				// ADD FIRST WEEK
				week_tr = $('<tr></tr>').appendTo($('table tbody', day_data));
				//$('<th></th>')
				//	.html(Math.ceil((((new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), 1) - new Date(obj.tmp_date.getFullYear(),0,1)) / 86400000) + new Date(obj.tmp_date.getFullYear(),0,1).getDay()+1)/7))
				//	.appendTo(week_tr);									
				for (i=0; i<week_day; i++) $('<td></td>').appendTo(week_tr);
				
				for (i=1; i<=max_day; i++) {
					if (week_day == 7) {
						week_day = 0;
						week_tr = $('<tr></tr>').appendTo($('table tbody', day_data));
						//$('<th></th>')
						//	.html(Math.ceil((((new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), i) - new Date(obj.tmp_date.getFullYear(),0,1)) / 86400000) + new Date(obj.tmp_date.getFullYear(),0,1).getDay()+1)/7))
						//	.appendTo(week_tr);
					}					
					
					if ((min_date && new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), i, 23, 59, 59) < min_date) ||
						(max_date && new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), i, 0, 0, 0) > max_date)) {
						$('<td class="issCalendarDisabled"></td>')
							.html(i)
							.appendTo(week_tr);	
					} else {
						if (i == obj.tmp_date.getDate()) {
							$('<td class="issCalendarActive"></td>')
								.html(i)
								.appendTo(week_tr);
						} else {
							$('<td class="issCalendarLink"></td>')
								.html(i)
								.appendTo(week_tr)
								.data('date', new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), i, obj.tmp_date.getHours(), obj.tmp_date.getMinutes(), obj.tmp_date.getSeconds()))
								.click(function() {
									obj.tmp_date = $(this).data('date');
									var cal_function = obj.show_calendar;
									cal_function();	
								});
						}
					}
					
					week_day++;
				}		
				
				for (i=week_day; i<7; i++) $('<td></td>').appendTo(week_tr);
				
				obj.ui_day1 = day_data.replaceAll(obj.ui_day1);
				
				//
				// TIME
				//
				if (obj.show_time) {
					if (init) {
						obj.ui_time1 = $('<td rowspan="5" class="issCalendarTime"></td>')
							.appendTo($('tr:first', obj.ui_table1));
						$('<table><tbody><tr><td></td><td></td></tr></tbody></table>')
							.appendTo(obj.ui_time1);
						obj.ui_hour1 = $('tr:first td:first', obj.ui_time1);
						obj.ui_minute1 = $('tr:first td:last', obj.ui_time1);
					}
					
					//
					// HOURS
					//
					hour_data = $('<td><table style="width: 50px;"><tbody><tr><th colspan="2">Hour</th></tr></tbody></table></td>');
					for (i=0; i<12; i++) {
						$('<tr></tr>')
							.appendTo($('tbody', hour_data));
						
						if ((min_date && new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), obj.tmp_date.getDate(), i, obj.tmp_date.getMinutes(), obj.tmp_date.getSeconds()) < min_date) ||
							(max_date && new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), obj.tmp_date.getDate(), i, obj.tmp_date.getMinutes(), obj.tmp_date.getSeconds()) > max_date)) {
							$('<td class="issCalendarDisabled"></td>')
								.html((i<10?'0':'') + (i))
								.appendTo($('tbody tr:last', hour_data));
						} else {
							if (obj.tmp_date.getHours() == i) {
								$('<td class="issCalendarActive"></td>')
									.html((i<10?'0':'') + (i))
									.appendTo($('tbody tr:last', hour_data));
							} else {
								$('<td class="issCalendarLink"></td>')
									.html((i<10?'0':'') + (i))
									.data('date', new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), obj.tmp_date.getDate(), i, obj.tmp_date.getMinutes(), 0))
									.appendTo($('tbody tr:last', hour_data))
									.click(function() {
										obj.tmp_date = $(this).data('date');
										var cal_function = obj.show_calendar;
										cal_function();	
									});
							}
						}
						if ((min_date && new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), obj.tmp_date.getDate(), i+12, obj.tmp_date.getMinutes(), obj.tmp_date.getSeconds()) < min_date) ||
							(max_date && new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), obj.tmp_date.getDate(), i+12, obj.tmp_date.getMinutes(), obj.tmp_date.getSeconds()) > max_date)) {
							$('<td class="issCalendarDisabled"></td>')
								.html(i+12)
								.appendTo($('tbody tr:last', hour_data));	
						} else {
							if (obj.tmp_date.getHours() == i+12) {
								$('<td class="issCalendarActive"></td>')
									.html(i+12)
									.appendTo($('tbody tr:last', hour_data));
							} else {
								$('<td class="issCalendarLink"></td>')
									.html(i+12)
									.data('date', new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), obj.tmp_date.getDate(), i+12, obj.tmp_date.getMinutes(), 0))
									.appendTo($('tbody tr:last', hour_data))
									.click(function() {
										obj.tmp_date = $(this).data('date');
										var cal_function = obj.show_calendar;
										cal_function();	
									});
							}
						}
					}
					obj.ui_hour1 = $(hour_data).replaceAll(obj.ui_hour1);
					
					//
					// MINUTES
					//
					minute_data = $('<td><table style="width: 20px; margin-left: 7px;"><tbody><tr><th>Min</th></tr></tbody></table></td>');
					for (i=0; i<12; i++) {
						$('<tr></tr>')
							.appendTo($('tbody', minute_data));
						
						if ((min_date && new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), obj.tmp_date.getDate(), obj.tmp_date.getHours(), i*5, obj.tmp_date.getSeconds()) < min_date) ||
							(max_date && new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), obj.tmp_date.getDate(), obj.tmp_date.getHours(), i*5, obj.tmp_date.getSeconds()) > max_date)) {
							$('<td class="issCalendarDisabled"></td>')
								.html(':' + (i<2?'0':'') + (i*5))
								.appendTo($('tbody tr:last', minute_data));
						} else {
							$('<td class="issCalendarLink"></td>')
								.html(':' + (i<2?'0':'') + (i*5))
								.data('date', new Date(obj.tmp_date.getFullYear(), obj.tmp_date.getMonth(), obj.tmp_date.getDate(), obj.tmp_date.getHours(), i*5, 0))
								.appendTo($('tbody tr:last', minute_data))
								.click(function() {
									obj.tmp_date = $(this).data('date');
									var cal_function = obj.show_calendar;
									cal_function();	
								});
							if ((obj.tmp_date.getMinutes() > (i-1)*5 && obj.tmp_date.getMinutes() <= i*5) || (i*5 >= 55 && obj.tmp_date.getMinutes() >= 55)) $('tbody tr:last td:last', minute_data).addClass('issCalendarActive');
						}
					}
					obj.ui_minute1 = $(minute_data).replaceAll(obj.ui_minute1);
				}	
				
				//
				// CURRENT DATE
				//
				display_date = '';
				if (obj.show_day) display_date += (obj.tmp_date.getDate()<10?'0':'') + obj.tmp_date.getDate();
				if (obj.show_month) display_date += '-' + obj.i18n.months[obj.tmp_date.getMonth()];
				if (obj.show_year) display_date += '-' + obj.tmp_date.getFullYear();
				if (obj.show_hour) display_date += ' ' + (obj.tmp_date.getHours()<10?'0':'') + obj.tmp_date.getHours();
				if (obj.show_min) display_date += ':' + (obj.tmp_date.getMinutes()<10?'0':'') + obj.tmp_date.getMinutes();
				if (obj.show_sec) display_date += ':' + (obj.tmp_date.getSeconds()<10?'0':'') + obj.tmp_date.getSeconds();
				$('.ui-datepicker-title', obj.ui_header1).html(display_date);
				
				
				//
				// BUTTONS
				//
				if (init) {	
					obj.ui = obj.ui.closest('div.issCalendar');
					
					$('<div class="issCalendarButtons"></div>')
						.appendTo(obj.ui);
					$('<input type="button" value="OK"/>')
						.appendTo($(obj.ui).find('div.issCalendarButtons'))
						.click(function() {
							display_date = '';
							if (obj.show_day) display_date += (obj.tmp_date.getDate()<10?'0':'') + obj.tmp_date.getDate();
							if (obj.show_month) display_date += '-' + obj.i18n.months[obj.tmp_date.getMonth()];
							if (obj.show_year) display_date += '-' + obj.tmp_date.getFullYear();
							if (obj.show_hour) display_date += ' ' + (obj.tmp_date.getHours()<10?'0':'') + obj.tmp_date.getHours();
							if (obj.show_min) display_date += ':' + (obj.tmp_date.getMinutes()<10?'0':'') + obj.tmp_date.getMinutes();
							if (obj.show_sec) display_date += ':' + (obj.tmp_date.getSeconds()<10?'0':'') + obj.tmp_date.getSeconds();
							obj.val(display_date);
							
							obj.ui.closest('div.issCalendar').remove();
							obj.ui = undefined;
						})
						.button();
					if (obj.show_tbd) {
						$('<input type="button" value="Clear"/>')
							.appendTo($(obj.ui).find('div.issCalendarButtons'))
							.click(function() {
								obj.val(obj.show_tbd_value);
								
								obj.ui.closest('div.issCalendar').remove();
								obj.ui = undefined;
							})
							.button();
					}
					$('<input type="button" value="Cancel"/>')
						.appendTo($(obj.ui).find('div.issCalendarButtons'))
						.click(function() {
							obj.ui.closest('div.issCalendar').remove();
							obj.ui = undefined;
						})
						.button();
						
					if (obj.show_time) {
						obj.ui.closest('div.issCalendar').find('div.issCalendarButtons').css({ width: '308px'});
					}
					
				}
				
				// POSITION CALENDAR
				if (init) { 
					right_margin = obj.ui.closest('div.issCalendar').offset().left + obj.ui.closest('div.issCalendar').width();
					window_width = $(window).width();
					if (window_width < right_margin && (obj.ui.closest('div.issCalendar').offset().left - (window_width-right_margin)) > 0) {
						obj.ui.closest('div.issCalendar').css('margin-left', (window_width-right_margin).toString()+'px');
					}
				}
			}
		});		
	}
})(jQuery);