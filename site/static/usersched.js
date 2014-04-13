window.usersched = { version: '0.9.2b' };

window.addEvent("domready",function() {

	if (document.getElementById("versionbar")) {
		document.getElementById("userschedver").innerHTML = window.usersched.version;
		document.getElementById("schedulerver").innerHTML = scheduler.version;
	}

	if (scheduler.cfg_cfg) {
		//console.log(scheduler.config);console.log(scheduler.cfg_cfg);
		mergeCfgObjects(scheduler.config, scheduler.cfg_cfg);
		if (scheduler.cfg_cfg.agenda_end) {
			var d = new Date();
			scheduler.config.agenda_end = new Date(d.getTime()+(scheduler.cfg_cfg.agenda_end*86400000));
		}
		//delete(scheduler.cfg_cfg);
	}

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

//            console.log(scheduler.__categories);
            scheduler.locale.labels.section_category = "Category";

//	scheduler.config.lightbox.sections = [
//		{name:"description", height:73, map_to:"text", type:"textarea", focus:true},
//		{name:"category", height:20, type:"select", options:scheduler.__categories, map_to:"category"},
//		{name:"alerts", height:42, map_to:"text", type:"alerts_editor", button:"shide"},
//		{name:"recurring", type:"recurring", map_to:"rec_type", button:"recurring"},
//		{name:"time", height:72, type:"calendar_time", map_to:"auto"}
//	];

	var sections = [ {name:"description", height:73, map_to:"text", type:"textarea", focus:true} ];
	if (true) sections.push({name:"category", height:20, type:"select", options:scheduler.__categories, map_to:"category"});
	if (true) sections.push({name:"alerts", height:42, map_to:"text", type:"alerts_editor", button:"shide"});
	if (true) sections.push({name:"recurring", type:"recurring", map_to:"rec_type", button:"recurring"});
	if (true) sections.push({name:"time", height:72, type:"time", map_to:"auto"});
	scheduler.config.lightbox.sections = sections;

//	if (typeof(sched_extend) == 'function') sched_extend();

	scheduler.attachEvent("onBeforeEventChanged",function(dev){
		var parts = this._drag_id.toString().split("#");
		if (parts.length > 1) {
			this._drag_id= parts[0];    
			var ev = this.getEvent(parts[0]);
			ev._end_date = ev.end_date;
			ev.start_date = dev.start_date;
			ev.end_date = dev.end_date;
		}
		return true;
	});

	scheduler.init('scheduler_here', new Date(), usched_mode);

	scheduler.setLoadMode(usched_mode);
	scheduler.load(userschedlurl);

	var dp = new dataProcessor(userschedlurl);
	dp.init(scheduler);

	// force the event tooltip to hide when the mouse leaves the calendar area
	var dhxcaldatahere = document.getElementById('scheduler_here');
	var dhxcaldatas = dhxcaldatahere.getElementsByClassName('dhx_cal_data');
	var dhxcaldata = dhxcaldatas[0];
	dhxcaldata.onmouseout = function() { dhtmlXTooltip.hide() };
});

function mergeCfgObjects (obj1, obj2) {
	for (var p in obj2)
		if (obj2.hasOwnProperty(p))
			obj1[p] = typeof obj2[p] === 'object' ? mergeCfgObjects(obj1[p], obj2[p]) : obj2[p];
	return obj1;
}

function configScheduler () {
	var uid = scheduler.uid();
	var d = document.createElement("div");
	d.innerHTML = '<form id="' + uid + '" method="post" accept-charset="utf-8" enctype="application/x-www-form-urlencoded"><input type="hidden" name="calid" value="'+ jus_calid +'" /><input type="hidden" name="task" value="doConfig" /></form>';
	d.firstChild.submit();
}

// other scheduler modifications
scheduler.xy.scroll_width = 0;

scheduler.templates.event_class = function(start, end, event) {
	if (event.xevt) return event.xevt;
	if (event.category) return "evCat"+event.category;
	return "";
};
scheduler.templates.month_events_link = function(date, count){
	return "<a>more("+count+")</a>";
};

scheduler.attachEvent("onClick",function(id,e) {
	if (scheduler.getEvent(id).xevt) return false;
	return true;
});
scheduler.attachEvent("onDblClick",function(id,e) {
	if (scheduler.getEvent(id).xevt) return false;
	return true;
});

scheduler.renderEvent = function(container, ev) {
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

scheduler.render_event_bar = function (ev) {
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
	var html = '<div event_id="' + ev.id + '" class="' + cs + '" style="position:absolute; top:' + y + 'px; left:' + (x+1) + 'px; width:' + (x2 - x - xm) + 'px;' + color + '' + bg_color + '' + (ev._text_style || "") + '">';

	ev = scheduler.getEvent(ev.id); // ev at this point could be a part of a larged event

	if (ev._timed)
		html += scheduler.templates.event_bar_date(ev.start_date, ev.end_date, ev);

	html += scheduler.templates.event_bar_text(ev.start_date, ev.end_date, ev) + '</div>';
	html += '</div>';
	d.innerHTML = html;

	this._rendered.push(d.firstChild);
	parent.appendChild(d.firstChild);
};

scheduler.templates.event_bar_date = function(start,end,event) {
	return '';
};
scheduler.templates.event_text = function(start,end,event) {
	var parts = event.text.split(/[\r\n]+/);
	return parts[0];
};
scheduler.templates.event_bar_text = function(start,end,event) {
	var parts = event.text.split(/[\r\n]+/);
	return parts[0];
};
scheduler.templates.year_tooltip = function(start,end,event) {
	var parts = event.text.split(/[\r\n]+/);
	return parts.join("<br/>");
};
scheduler.templates.tooltip_text = function(start,end,event) {
	var vm = scheduler._mode;
	var parts = event.text.split(/[\r\n]+/);
	if (!event.xevt) parts[0] += " " + scheduler.templates.event_header(start, end, event);
	var tipt = parts.join("<br/>");
	if ((vm=="year" || vm=="month") && !(event.xevt || event.event_length=="0")) tipt += "<br/><u>" + scheduler.templates.tooltip_date_format(start) + " - " + scheduler.templates.tooltip_date_format(end) + "</u>";
	return tipt;
};
// inhibit year tooltips if global tip is enabled
scheduler._init_year_tooltip = function() {};