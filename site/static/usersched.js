//'use strict';
/*global scheduler*/

var USched = {
	setAutoEnd: () => {
		let old_setValue = scheduler.form_blocks.time.set_value;
		scheduler.form_blocks.time.set_value = function (node,value,ev,config) {
				old_setValue.apply(this, arguments);
				let s = node.getElementsByTagName("select");
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
					var inputs = node.getElementsByTagName("input");
					var selects = node.getElementsByTagName("select");
					scheduler.config.event_duration = 60;
					scheduler.config.auto_end_date = true;

					function _update_minical_select() {
						let start_date = scheduler.date.add(inputs[0]._date, selects[0].value, "minute");
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
		scheduler.locale.labels.section_category = "Category";
		// set the height of the description
		scheduler.config.lightbox.sections[0].height = 60;
		// get the repeat section
		let rs = scheduler.config.lightbox.sections[1];
		// add category selection after description, removing repeat
		scheduler.config.lightbox.sections.splice(1, 1, {name:"category", type:"select", class:"us-cat-sel", map_to:"category", options:scheduler.__categories});
		// move repeats to the end
		scheduler.config.lightbox.sections.push(rs);
		// tack alerts to the end
		if (scheduler.feature.canAlert) scheduler.config.lightbox.sections.push({name:"alerts", map_to:"text", type:"alerts_editor", button:"shide"});

		scheduler.config.left_border = true;	// fix skin css's for the border type/color
		scheduler.config.prevent_cache = true;
		scheduler.config.details_on_create=true;
		scheduler.config.details_on_dblclick=true;
		scheduler.config.occurrence_timestamp_in_utc = true;
		scheduler.config.include_end_by = true;
		scheduler.config.repeat_precise = true;

		if (document.getElementById("versionbar")) {
			document.getElementById("schedulerver").innerHTML = scheduler.version;
		}

		scheduler.init("scheduler_here", new Date(), USched.mode);

		scheduler.setLoadMode(USched._mode);
		scheduler.load(USched.URL);

		let dp = new dataProcessor(USched.URL);
		dp.init(scheduler);

		//---- need some template overrides here (after scheduler init) mostly to combat terrace skin

		// inhibit time display in event bar - takes up too much room
		scheduler.templates.event_bar_date = () => {return ''};

		if (typeof dhtmlXTooltip != 'undefined') {
			// force the event tooltip to hide when the mouse leaves the calendar area
			let dhxcaldatahere = document.getElementById("scheduler_here");
			let dhxcaldatas = dhxcaldatahere.getElementsByClassName("dhx_cal_data");
			let dhxcaldata = dhxcaldatas[0];
			dhxcaldata.onmouseout = () => {dhtmlXTooltip.hide()};
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
