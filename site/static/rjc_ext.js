scheduler.__alerts = {
	locale: {
		alert_title: 'Alerts',
		alert_users: "Alert users",
		alert_method: "Alert method",
		alert_email: "Email",
		alert_SMS: "SMS",
		alert_both: "Both",
		alert_lead: "Alert lead",
		alert_minutes: "minute(s)",
		alert_hours: "hour(s)",
		alert_days: "day(s)",
		alert_weeks: "week(s)",
		alert_show: "Show",
		alert_hide: "Hide"
	}
};

scheduler.locale.labels.section_alerts = scheduler.__alerts.locale.alert_title;
//	var lsl = scheduler.config.lightbox.sections.length;
//	scheduler.config.lightbox.sections.splice(lsl-2,0,{ name: "alerts", height: 42, map_to: "text", type: "alerts_editor", button:"shide" });
scheduler.locale.labels.button_shide = scheduler.__alerts.locale.alert_show;

scheduler.form_blocks["alerts_editor"] = {
	render:function(sns) {
		let htm = `<div class="dhx_form_alerts" style="height:0px">
	<form id ="alert_form">
	<div class="sched_alertsf">
		<div class="sched_alrtatru">
			<label>${scheduler.__alerts.locale.alert_users}
			<select id="sel_alertusers" name="sel_alertusers[]" multiple="multiple" class="alrt_users">
				${scheduler.alertWho}
			</select>
			</label>
		</div>
		<div class="sched_alrtatrm">
			<label>${scheduler.__alerts.locale.alert_method}
			<select id="alertmethod" name="alertmethod" class="alrt_meth">
				<option value="1">${scheduler.__alerts.locale.alert_email}</option>
				<option value="2">${scheduler.__alerts.locale.alert_SMS}</option>
				<option value="3">${scheduler.__alerts.locale.alert_both}</option>
			</select>
			</label>
		</div>
		<div class="sched_alrtatrxl">
			<label>${scheduler.__alerts.locale.alert_lead}</label>
			<input name="alertlead_val" id="alertlead_val" type="number" min="0" class="alrt_lead">
			<select size="1" name="alertlead_mul" id="alertlead_mul">
				<option value="1">${scheduler.__alerts.locale.alert_minutes}</option>
				<option value="2">${scheduler.__alerts.locale.alert_hours}</option>
				<option value="3">${scheduler.__alerts.locale.alert_days}</option>
				<option value="4">${scheduler.__alerts.locale.alert_weeks}</option>
			</select>
		</div>
	</div>
	</form>
	</div>
`;
		return htm;
	},
	set_value:function(node, value, ev) {
		sched_fillAlert(node.firstElementChild,value,ev);
	},
	get_value:function(node, ev) {
		sched_getAlert(node.firstElementChild,ev);
		return ev.text;
	},
	focus:function(node) {
		var a = node.firstElementChild.childNodes[1];
		a.select();
		a.focus();
	},
	button_click:function(ix, el, sect, cont){
		if (cont.style.height=='0px') {
			cont.style.height = "auto";
			el.nextSibling.innerHTML = scheduler.__alerts.locale.alert_hide;
		} else {
			cont.style.height = "0px";
			el.nextSibling.innerHTML = scheduler.__alerts.locale.alert_show;
		}
		scheduler.setLightboxSize();
	}
};


function sched_fillAlert(elem,val,evt) {
	elem.alertmethod.value = evt.alert_meth ? evt.alert_meth : 1;
//console.log(evt);
	var optionsSelected = evt.alert_user ? evt.alert_user.split(/,/) : [];
	var select = elem.sel_alertusers;
	for (var i = 0, l = select.options.length, o; i < l; i++) {
		o = select.options[i];
		if (optionsSelected.indexOf(o.value) != -1 ) {
			o.selected = true;
		} else {
			o.selected = false;
		}
	}

//	elem.alertlead_mul.value = evt.alert_lead;
	if (evt.alert_lead) {
		var mlead = evt.alert_lead / 60;
		if (mlead % 10080 === 0) { //weeks
			elem.alertlead_mul.value = 4;
			elem.alertlead_val.value = mlead / 10080;
		} else if(mlead % 1440 === 0) { //days
			elem.alertlead_mul.value = 3;
			elem.alertlead_val.value = mlead / 1440;
		} else if(mlead % 60 === 0) { //hours
			elem.alertlead_mul.value = 2;
			elem.alertlead_val.value = mlead / 60;
		} else { //minutes
			elem.alertlead_mul.value = 1;
			elem.alertlead_val.value = mlead;
		}
	} else {
		elem.alertlead_mul.value = 1;
		elem.alertlead_val.value = 0;
	}

	//console.log(elem);console.log(val);console.log(evt);
}
function sched_getAlert(elem,evt) {
	evt.alert_meth = elem.alertmethod.value;

	var users = [];
	var options = elem.sel_alertusers && elem.sel_alertusers.options;
	var opt;
	for (var i=0, iLen=options.length; i<iLen; i++) {
		opt = options[i];
		if (opt.selected) {
			users.push(opt.value || opt.text);
		}
	}
	evt.alert_user = users.join(",");

	var alertlead = 0;
	//console.log(elem.alertlead_val);
	if (elem.alertlead_val.value) {
		var lval = elem.alertlead_val.value;
		switch(elem.alertlead_mul.value*1) {
			case 1: //minutes
				alertlead = lval * 60;
				break;
			case 2: //hours
				alertlead = lval * 3600;
				break;
			case 3: //days
				alertlead = lval * 86400;
				break;
			case 4: //weeks
				alertlead = lval * 604800;
				break;
		}
	}
	evt.alert_lead = alertlead;

	//console.log(elem);console.log(evt);
}
/*
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
*/