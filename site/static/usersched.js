scheduler.__lang = {
	locale: {
		lang_more: 'more(%d)'
	}
};

function mergeCfgObjects (obj1, obj2) {
	for (var p in obj2)
		if (obj2.hasOwnProperty(p))
			obj1[p] = typeof obj2[p] === 'object' ? mergeCfgObjects(obj1[p], obj2[p]) : obj2[p];
	return obj1;
}

function sched_setAutoEnd () {
	var old_setValue = scheduler.form_blocks.time.set_value;	//console.log(old_setValue.e);
	scheduler.form_blocks.time.set_value = function(node,value,ev,config){
			//console.log(ev);
			var is_fd = (scheduler.date.time_part(ev.start_date)===0 && scheduler.date.time_part(ev.end_date)===0);
			old_setValue.apply(this, arguments);
			var s=node.getElementsByTagName("select");
			var map = config._time_format_order;	//console.log(config);
			ev.sdnd_adj = (ev.event_length*1) ? ev.event_length : (ev.end_date - ev.start_date)/1000;
			//console.log('esa'+ev.sdnd_adj);
			

			function _update_lightbox_select() {
				var nsd = new Date(s[map[3]].value,s[map[2]].value,s[map[1]].value,0,s[map[0]].value);
				var nnd = new Date(nsd.getTime() + ev.sdnd_adj*1000);
				s[4+map[1]].value = nnd.getDate();
				s[4+map[2]].value = nnd.getMonth();
				s[4+map[3]].value = nnd.getFullYear();
				var hm = nnd.getHours() * 60 + nnd.getMinutes();
				s[4+map[0]].value = hm;
			}
			function _sched_update_evtdiff() {
				var sd = new Date(s[map[3]].value,s[map[2]].value,s[map[1]].value,0,s[map[0]].value);
				var nd = new Date(s[map[3]+4].value,s[map[2]+4].value,s[map[1]+4].value,0,s[map[0]+4].value);
				ev.sdnd_adj = (nd-sd)/1000;
			}

			for(var i=0; i<4; i++) {
				s[i].onchange = function(){_update_lightbox_select();};
			}
			for(i=4; i<8; i++) {
				s[i].onchange = function(){_sched_update_evtdiff();};
			}
		};
	if (scheduler.form_blocks.calendar_time) {
		var oldc_setValue = scheduler.form_blocks.calendar_time.set_value;
		scheduler.form_blocks.calendar_time.set_value = function(node,val,evt){
			var evLen = evt.end_date - evt.start_date;
			var inputs = node.getElementsByTagName("input");
			var selects = node.getElementsByTagName("select");
			scheduler.config.event_duration = 60;
			scheduler.config.auto_end_date = true;
//			oldc_setValue.apply(this, arguments);
			//console.log(evLen,inputs,selects,val,evt);


			function _update_minical_select() {
				start_date = scheduler.date.add(inputs[0]._date, selects[0].value, "minute");
				end_date = new Date(start_date.getTime() + evLen);

				inputs[1].value = scheduler.templates.calendar_time(end_date);
				inputs[1]._date = scheduler.date.date_part(new Date(end_date));

				//selects[1].value = end_date.getHours() * 60 + end_date.getMinutes();
			}

			oldc_setValue.apply(this, arguments);
		};
	}
}
//sched_setAutoEnd();


// other scheduler modifications
scheduler.feature = {};
scheduler.xy.scroll_width = 0;
scheduler.config.left_border = true;	// fix skin css's for the border type/color

// add category class for styling
scheduler.templates.event_class = function(start, end, event) {
	if (event.xevt) return event.xevt;
	if (event.category) return "evCat"+event.category;
	return "";
};

// add count to event overflow in month display
scheduler.templates.month_events_link = function(date, count){
	return "<a>"+scheduler.__lang.locale.lang_more.replace("%d",count)+"</a>";
};

// inhibit clicks on events from external sources (google holidays, joomla birthdays)
scheduler.attachEvent("onClick",function(id,e) {
	if (scheduler.getEvent(id).xevt) return false;
	return true;
});
scheduler.attachEvent("onDblClick",function(id,e) {
	if (scheduler.getEvent(id).xevt) return false;
	return true;
});

// create our own style of event display
scheduler.rrenderEvent = function(container, ev) {
	var container_width = parseInt(container.style.width);
	var container_height = parseInt(container.style.height);
	var eclass = ev.category ? "evCat"+ev.category : "ushedEvt";
	// move section
	var html = "<div class='dhx_event_move dhx_header' style='width: " + (container_width-2) + "px'></div>";

	// container for event contents
	html+= "<div class='dhx_body "+eclass+"' style='width:"+(container_width-10)+"px;height:"+(container_height-16)+"px'>";
		html += "<span class='event_date'>";
		// two options here: show only start date for short events or start+end for long
		if ((ev.end_date - ev.start_date) / 60000 > 40) { // if event is longer than 40 minutes
			html += scheduler.templates.event_header(ev.start_date, ev.end_date, ev);
			html += "</span><br/>";
		} else {
			html += scheduler.templates.event_date(ev.start_date) + "</span> ";
		}
		// displaying event text
		html += "<span>" + scheduler.templates.event_text(ev.start_date, ev.end_date, ev) + "</span>";
	html += "</div>";

	// resize section
	html += "<div class='dhx_event_resize' style='width: " + (container_width-4) + "px'></div>";

	container.innerHTML = html;
	return true; // required, true - we've created custom form; false - display default one instead
};

scheduler.rrender_event_bar = function (ev) {
	var parent = this._rendered_location;
	var pos = this._get_event_bar_pos(ev);
	var y = pos.y;
	var x = pos.x;
	var x2 = pos.x2;
	var xm = 5;

	//events in ignored dates
	if (!x2) return;

	var d = document.createElement("DIV");
	var cs = "dhx_cal_event_clear";

	if (!ev._timed) {
		xm += 10;
		cs = "dhx_cal_event_line";
		if (ev.hasOwnProperty("_first_chunk") && ev._first_chunk)
			cs += " dhx_cal_event_line_start";
		if (ev.hasOwnProperty("_last_chunk") && ev._last_chunk)
			cs += " dhx_cal_event_line_end";
	}

	var cse = scheduler.templates.event_class(ev.start_date, ev.end_date, ev);
	if (cse) cs = cs + " " + cse;

	var bg_color = (ev.color ? ("background:" + ev.color + ";") : "");
	var color = (ev.textColor ? ("color:" + ev.textColor + ";") : "");
	var html = '<div event_id="' + ev.id + '" class="' + cs + '" style="position:absolute; top:' + y + "px; left:" + (x+1) + "px; width:" + (x2 - x - xm) + "px;" + color + '' + bg_color + '' + (ev._text_style || "") + '">';

	ev = scheduler.getEvent(ev.id); // ev at this point could be a part of a larger event

	if (ev._timed)
		html += scheduler.templates.event_bar_date(ev.start_date, ev.end_date, ev);

	html += scheduler.templates.event_bar_text(ev.start_date, ev.end_date, ev) + '</div>';
	html += "</div>";
	d.innerHTML = html;

	this._rendered.push(d.firstChild);
	parent.appendChild(d.firstChild);
};

scheduler.templates.xxevent_text = function(start,end,event) {
	var parts = event.text.split(/[\r\n]+/);
	return parts[0];
};
scheduler.templates.xxevent_bar_text = function(start,end,event) {
	var parts = event.text.split(/[\r\n]+/);
	return parts[0];
};
scheduler.templates.xxyear_tooltip = function(start,end,event) {
	var parts = event.text.split(/[\r\n]+/);
	return parts.join("<br/>");
};
scheduler.templates.xxtooltip_text = function(start,end,event) {
	var vm = scheduler._mode;
	var parts = event.text.split(/[\r\n]+/);
	if (!event.xevt) parts[0] += " " + scheduler.templates.event_header(start, end, event);
	var tipt = parts.join("<br/>");
	if ((vm=="year" || vm=="month") && !(event.xevt || event.event_length=="0")) tipt += "<br/><u>" + scheduler.templates.tooltip_date_format(start) + " - " + scheduler.templates.tooltip_date_format(end) + "</u>";
	return tipt;
};
scheduler.templates.xxquick_info_title = function(start,end,event) {
	var parts = event.text.split(/[\r\n]+/);
	return parts[0];
};
scheduler.templates.xxquick_info_content = function(start,end,event) {
	var parts = event.text.split(/[\r\n]+/);
	parts.shift();
	var tipt = parts.join("<br/>");
	return tipt;
};
// inhibit year tooltips if global tip is enabled
scheduler._init_year_tooltip = function() {};

///////////////////////////////////////////////////

function usersched_init() {
/*
	if (scheduler.cfg_cfg) {
		mergeCfgObjects(scheduler.config, scheduler.cfg_cfg);
		if (scheduler.cfg_cfg.agenda_end) {
			var d = new Date();
			scheduler.config.agenda_end = new Date(d.getTime()+(scheduler.cfg_cfg.agenda_end*86400000));
		}
		//delete(scheduler.cfg_cfg);
	}

	scheduler.config.show_loading = true;
	scheduler.xy.bar_height = 18;

	scheduler.config.xml_date = "%Y-%m-%d %H:%i";
	scheduler.config.prevent_cache = true;
	scheduler.config.icons_select = ["icon_details","icon_delete"];

	scheduler.config.details_on_create = true;
	scheduler.config.details_on_dblclick = true;
	scheduler.config.full_day = true;

	scheduler.config.use_select_menu_space = true;

	scheduler.config.left_border = true;
	scheduler.keys.edit_save = -1;	//keep enter/return key from saving event

	scheduler.config.fix_tab_position = false;

//	console.log(scheduler.__categories);
	scheduler.locale.labels.section_category = "Category";
*/
//	var sections = [ {name:"description", height:73, map_to:"text", type:"textarea", focus:true} ];
//	if (true) sections.push({name:"category", height:20, type:"select", options:scheduler.__categories, map_to:"category"});
//	if (scheduler.feature.canAlert) sections.push({name:"alerts", height:42, map_to:"text", type:"alerts_editor", button:"shide"});
//	if (true) sections.push({name:"recurring", type:"recurring", map_to:"rec_type", button:"recurring"});
//	if (true) sections.push({name:"time", height:72, type:"calendar_time", map_to:"auto"});
//	scheduler.config.lightbox.sections = sections;

/*	// not sure why I had done this - perhaps original fails to make appropriate change to recurring event
	scheduler.attachEvent("onBeforeEventChanged",function(dev){
		var parts = this._drag_id.toString().split("#");
		if (parts.length > 1) {
			this._drag_id= parts[0];    
			var ev = this.getEvent(parts[0]);		//console.log(dev,ev);
			ev._end_date = ev.end_date;
			ev.start_date = dev.start_date;
			ev.end_date = dev.end_date;
		}
		return true;
	});
*/

	// replace the 'loading' locator function with ours
	scheduler.detachEvent("ev_onxls:0");
	scheduler.attachEvent("onXLS", function() {
		if (this.config.show_loading === true) {
			var t;
			t = this.config.show_loading = document.createElement("DIV");
			t.className = "dhx_loading";
			t.style.left = Math.round((this._x - 66) / 2) + "px";
			t.style.top = 60 + "px";
			this._obj.appendChild(t);
		}
	});

	if (document.getElementById("versionbar")) {
		document.getElementById("schedulerver").innerHTML = scheduler.version;
	}

	scheduler.init("scheduler_here", new Date(), usched_mode);

	scheduler.setLoadMode(usched_mode);
	scheduler.load(userschedlurl);

	var dp = new dataProcessor(userschedlurl);
	dp.init(scheduler);

	//---- need some template overrides here (after scheduler init) mostly to combat terrace skin

	// inhibit time display in event bar - takes up too much room
	scheduler.templates.event_bar_date = function(start,end,event) {
		return '';
	};

	if (typeof dhtmlXTooltip != 'undefined') {
		// force the event tooltip to hide when the mouse leaves the calendar area
		var dhxcaldatahere = document.getElementById("scheduler_here");
		var dhxcaldatas = dhxcaldatahere.getElementsByClassName("dhx_cal_data");
		var dhxcaldata = dhxcaldatas[0];
		dhxcaldata.onmouseout = function() { dhtmlXTooltip.hide(); };
	}

}
