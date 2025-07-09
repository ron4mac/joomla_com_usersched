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
//		scheduler.plugins({recurring: true, tooltip: true});

		let plugs = USched.plugs;		//{recurring: true, readonly: true, expand: true, year_view: true};
		plugs.tooltip = true;
		plugs.container_autoresize = true;
		plugs.pdf = true;
		scheduler.plugins(plugs);	console.log(plugs);

		// merge configuration
		if (scheduler.cfg_cfg) {
			console.log(scheduler.cfg_cfg);
			USched.mergeObjs(scheduler.config, scheduler.cfg_cfg);
		//	if (scheduler.cfg_cfg.agenda_end) {
		//		let d = new Date();
		//		scheduler.config.agenda_end = new Date(d.getTime()+(scheduler.cfg_cfg.agenda_end*86400000));
		//	}
		}

		//scheduler.config.prevent_cache = true;
		scheduler.config.event_duration = 60;
		scheduler.config.auto_end_date = true;
		scheduler.config.time_step = 15;

		scheduler.config.details_on_create = true;
		scheduler.config.details_on_dblclick = true;
		scheduler.config.occurrence_timestamp_in_utc = true;
		scheduler.config.include_end_by = true;
		scheduler.config.repeat_precise = true;
		scheduler.config.full_day = true;

		scheduler.init('scheduler_here', new Date(), 'month');
		scheduler.setLoadMode('month');
		scheduler.load(USched.URL);

//		let dp = new dataProcessor(USched.URL);
		let dp = scheduler.createDataProcessor({url: USched.URL, mode: 'JSON'});
		dp.init(scheduler);
//		scheduler.setLoadMode('month');
//		scheduler.load('data/api.php');
		// send updates to the backend
//		var dp = scheduler.createDataProcessor({url: 'data/api.php', mode: 'JSON'});
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
