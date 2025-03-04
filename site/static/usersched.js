/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.2.2
*/
//'use strict';
/*global scheduler*/

var USched = {
	setAutoEnd: () => {
		let old_setValue = scheduler.form_blocks.time.set_value;
		scheduler.form_blocks.time.set_value = function (node,value,ev,config) {
				old_setValue.apply(this, arguments);
				let s = node.getElementsByTagName('select');
				let map = config._time_format_order;	//console.log(config);
				ev.sdnd_adj = (ev.event_length*1) ? ev.event_length : (ev.end_date - ev.start_date)/1000;

				const _update_lightbox_select = () => {
					let nsd = new Date(s[map[3]].value,s[map[2]].value,s[map[1]].value,0,s[map[0]].value);
					let nnd = new Date(nsd.getTime() + ev.sdnd_adj*1000);
					s[4+map[1]].value = nnd.getDate();
					s[4+map[2]].value = nnd.getMonth();
					s[4+map[3]].value = nnd.getFullYear();
					let hm = nnd.getHours() * 60 + nnd.getMinutes();
					s[4+map[0]].value = hm;
				};
				const _sched_update_evtdiff = () => {
					let sd = new Date(s[map[3]].value,s[map[2]].value,s[map[1]].value,0,s[map[0]].value);
					let nd = new Date(s[map[3]+4].value,s[map[2]+4].value,s[map[1]+4].value,0,s[map[0]+4].value);
					ev.sdnd_adj = (nd-sd)/1000;
				};

				for (let i=0; i<4; i++) {
					s[i].onchange = _update_lightbox_select;
				}
				for (let i=4; i<8; i++) {
					s[i].onchange = _sched_update_evtdiff;
				}
			};

		if (scheduler.form_blocks.calendar_time) {
			let oldc_setValue = scheduler.form_blocks.calendar_time.set_value;
			scheduler.form_blocks.calendar_time.set_value = function (node,val,evt) {
					var evLen = evt.end_date - evt.start_date;
					var inputs = node.getElementsByTagName('input');
					var selects = node.getElementsByTagName('select');
					scheduler.config.event_duration = 60;
					scheduler.config.auto_end_date = true;

					function _update_minical_select() {
						let start_date = scheduler.date.add(inputs[0]._date, selects[0].value, 'minute');
						let end_date = new Date(start_date.getTime() + evLen);

						inputs[1].value = scheduler.templates.calendar_time(end_date);
						inputs[1]._date = scheduler.date.date_part(new Date(end_date));
					}

					oldc_setValue.apply(this, arguments);
				};
		}
	},

	mergeObjs: (obj1, obj2) => {
		for (let p in obj2)
			if (obj2.hasOwnProperty(p))
				obj1[p] = typeof obj2[p] === 'object' ? USched.mergeObjs(obj1[p], obj2[p]) : obj2[p];
		return obj1;
	},

	printView: (evt,elm) => {
		evt.preventDefault();
		if (!scheduler.expanded) {
			if (!confirm('Printing is best done from the expanded view. Print anyway?')) return;
		}
		scheduler.exportToPDF({format:'full',orientation:'landscape',header:'<style>.dhx_expand_icon{display:none}</style>'});
		document.querySelector('#usched_container .uschd-ham-menu').click();
	},

	init: () => {
		// merge configuration
		if (scheduler.cfg_cfg) {
			USched.mergeObjs(scheduler.config, scheduler.cfg_cfg);
			if (scheduler.cfg_cfg.agenda_end) {
				let d = new Date();
				scheduler.config.agenda_end = new Date(d.getTime()+(scheduler.cfg_cfg.agenda_end*86400000));
			}
		}

		// show a loading graphic
		scheduler.config.show_loading = true;
		// setup the category label
///		scheduler.locale.labels.section_category = "Category";
		// set the height of the description
///		scheduler.config.lightbox.sections[0].height = 60;
		// get the repeat section
///		let rs = scheduler.config.lightbox.sections[1];
		// add category selection after description, removing repeat
///		scheduler.config.lightbox.sections.splice(1, 1, {name:"category", type:"select", class:"us-cat-sel", map_to:"category", options:scheduler.__categories});
		// move repeats to the end
//		scheduler.config.lightbox.sections.push(rs);
		// tack alerts to the end
///		if (scheduler.feature.canAlert) scheduler.config.lightbox.sections.push({name:"alerts", map_to:"text", type:"alerts_editor", button:"shide"});

		scheduler.config.left_border = true;	// fix skin css's for the border type/color
		scheduler.config.prevent_cache = true;
		scheduler.config.details_on_create=true;
		scheduler.config.details_on_dblclick=true;
		scheduler.config.occurrence_timestamp_in_utc = true;
		scheduler.config.include_end_by = true;
		scheduler.config.repeat_precise = true;
		scheduler.config.full_day = true;

		scheduler.keys.edit_save = -1;	//keep enter/return key from saving event


			scheduler.templates.event_class = function(start, end, event) {
				//console.log(start, end, event.classname, event.category);
				if (event.xevt == 'isHoliday') return 'usched-evt-hday';
				let cats = event.alert_user ? 'usched-alert ' : '';
				return cats + (event.category ? ('evt-ctg-'+event.category) : 'evt-no-ctg');
			};


		// the built-in auto_end_date does not work as well as my method (see setAutoEnd)
		scheduler.config.event_duration = 60; 
//		scheduler.config.auto_end_date = true;

		if (document.getElementById('versionbar')) {
			document.getElementById('schedulerver').innerHTML = scheduler.version;
		}

		const compactView = {
			config: {
				header: {
					rows: [
						{ cols: ["prev","date","next"] },
						{ cols: ["day","week","month","spacer","today"] }
					]
				}
			//	header: ["day","week","month","date","prev","today","next"]
			},
			templates: {
				month_scale_date: scheduler.date.date_to_str("%D"),
				week_scale_date: scheduler.date.date_to_str("%D, %j"),
				event_bar_date: function(start,end,ev) { return ""; }
			}
		};
		const fullView = {
			config: {
				header: ["day","week","month","date","prev","today","next"]
			},
			templates: {
				month_scale_date: scheduler.date.date_to_str("%l"),
				week_scale_date: scheduler.date.date_to_str("%l, %F %j"),
				event_bar_date: function(start,end,ev) { return "• <b>"+scheduler.templates.event_date(start)+"</b> "; }
			}
		};

		function resetConfig(){
			var settings;
			let _CW = document.getElementById('usched_container').offsetWidth;
			//if(window.innerWidth < 1024){
			if (_CW < 1000){
				settings = compactView;
			} else {
				settings = fullView;
			}
			scheduler.utils.mixin(scheduler.config, settings.config, true);
			scheduler.utils.mixin(scheduler.templates, settings.templates, true);
			return true;
		}

		scheduler.config.responsive_lightbox = true;
		resetConfig();
		scheduler.attachEvent("onBeforeViewChange", resetConfig);
		scheduler.attachEvent("onSchedulerResize", resetConfig);

	//	let dsets = USched.mobile ? compactView : fullView;
	//	scheduler.utils.mixin(scheduler.config, dsets.config, true);
	//	scheduler.utils.mixin(scheduler.templates, dsets.templates, true);
	//	scheduler.utils.mixin(scheduler.xy, dsets.xy, true);

/*
		// do some things to accomodate a mobile device
		if (USched.mobile) {
			scheduler.xy.nav_height = 80;
			scheduler.config.header = {
				rows: [
					{cols: ['prev','spacer','date','next']},
					{cols: ['week','month','year','spacer','today']}
				]
			};

			scheduler.config.responsive_lightbox = true;

			const formatMonthScale = scheduler.date.date_to_str('%D');
			scheduler.templates.month_scale_date = (date) => formatMonthScale(date);
			const formatWeekScale = scheduler.date.date_to_str('%d');
			scheduler.templates.week_scale_date = (date) => formatWeekScale(date);

		} else {
			scheduler.config.header = {
				rows: [
					{cols: ['week','month','year','date','prev','today','next']}
				]
			};
		}
*/

		let plugs = USched.plugs;		//{recurring: true, readonly: true, expand: true, year_view: true};
		if (USched.mobile) {
			plugs.quick_info = true;
			scheduler.config.quick_info_detached = false;
		} else {
//			plugs.tooltip = true;
		}
		plugs.container_autoresize = true;
		plugs.pdf = true;
		scheduler.plugins(plugs);	console.log(plugs);


		// experiment here
	//	scheduler.config.fix_tab_position = false;

// probably important to catch errors saving data
scheduler.attachEvent('onSaveError', function(ids, resp){
	console.error(ids, resp);
//	let jsn = JSON.parse(resp.response);
//	alert("Failed to save/update event data");
//	scheduler.message({type:"error", text:"Failed to save/update event data", expire:5000});
	scheduler.alert({text: 'Failed to save/update event data.<br>Please refresh the view.', title: 'ERROR', ok: 'Ok'});
})
// and this
scheduler.attachEvent('onError', function(errorMessage){
    scheduler.message({
        text:'Error: ' + errorMessage
    });
    return true;
});


		scheduler.init('scheduler_here', new Date(), USched.mode);



// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	//	scheduler.config.readonly_form = true;
		//block all modifications
	//	scheduler.attachEvent("onBeforeDrag",function(){return false;});
	//	scheduler.attachEvent("onClick",function(){return false;});
	//	scheduler.config.details_on_dblclick = true;
	//	scheduler.config.dblclick_create = false;
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@




    scheduler.attachEvent('onBeforeExpand', () => document.getElementById('scheduler_here').style.zIndex = 15);
    scheduler.attachEvent('onBeforeCollapse', () => document.getElementById('scheduler_here').style.zIndex = 'inherit');


		// need to mess with sections AFTER init
		// setup the category label
		scheduler.locale.labels.section_category = 'Category';
		// set the height of the description
//		scheduler.config.lightbox.sections[0].height = 60;
		scheduler.config.lightbox.sections[2].year_range = [1940,2026];

		// get the repeat section
		let rs = scheduler.config.lightbox.sections[1];
		// add category selection after description, removing repeat
		scheduler.config.lightbox.sections.splice(1, 1, {name:'category', type:'select', class:'us-cat-sel', map_to:'category', options:scheduler.__categories});
		// move repeats to the end
		scheduler.config.lightbox.sections.push(rs);
		// tack alerts to the end
		if (scheduler.feature.canAlert) scheduler.config.lightbox.sections.push({name:'alerts', map_to:'auto', type:'alerts_editor', button:'shide'});

		// add calendar tools
		const toolb = document.querySelector('.uschedtools');
		// insert config button/icon
		//if (USched.cfgBTN) {
			//let cfgb = document.createElement("div");
			//cfgb.innerHTML = USched.cfgBTN;
		//	toolb.innerHTML += USched.cfgBTN;
			//scheduler.$container.appendChild(cfgb);
		//}

		// insert print button/icon
		//if (true) {
			//let prnb = document.createElement("div");
			//prnb.innerHTML = USched.prnBTN;
		//	toolb.innerHTML += USched.prnBTN;
			//prnb.style.position = "absolute";
			//scheduler.$container.appendChild(prnb);
		//}

		if (toolb) {
			if (USched.cfgBTN) toolb.innerHTML += USched.cfgBTN;
			toolb.innerHTML += USched.prnBTN;
		}

		scheduler.setLoadMode('month');
		scheduler.load(USched.URL);

//		let dp = new dataProcessor(USched.URL);
		let dp = scheduler.createDataProcessor({url: USched.URL, mode: 'JSON'});
		dp.init(scheduler);

		//---- need some template overrides here (after scheduler init) mostly to combat terrace skin

		// inhibit time display in event bar - takes up too much room
		scheduler.templates.event_bar_date = () => {return ''};

		if (typeof scheduler.dhtmlXTooltip != 'undefined') {
			// force the event tooltip to hide when the mouse leaves the calendar area
			let dhxcaldatahere = document.getElementById('scheduler_here');
			let dhxcaldatas = dhxcaldatahere.getElementsByClassName('dhx_cal_data');
			let dhxcaldata = dhxcaldatas[0];
			dhxcaldata.onmouseout = () => {scheduler.dhtmlXTooltip.hide()};
		}
	}
};

scheduler.__lang = {
	locale: {
		lang_more: 'more(%d)'
	}
};

USched.setAutoEnd();

// other scheduler modifications
scheduler.feature = {};
scheduler.xy.scroll_width = 0;

// add category class for styling
scheduler.templates.event_class = function(start, end, event) {
	if (event.xevt) return event.xevt;
	if (event.category) return 'evCat'+event.category;
	return '';
};
