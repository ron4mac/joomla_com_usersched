/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.2.2
*/
//'use strict';
/*global scheduler*/

scheduler.__alerts = {
	locale: {
		alert_title: 'Alerts',
		alert_users: 'Alert users',
		alert_method: 'Alert method',
		alert_email: 'Email',
		alert_SMS: 'SMS',
		alert_both: 'Both',
		alert_lead: 'Alert lead',
		alert_minutes: 'minute(s)',
		alert_hours: 'hour(s)',
		alert_days: 'day(s)',
		alert_weeks: 'week(s)',
		alert_show: 'Show',
		alert_hide: 'Hide'
	}
};

scheduler.locale.labels.section_alerts = scheduler.__alerts.locale.alert_title;
//	var lsl = scheduler.config.lightbox.sections.length;
//	scheduler.config.lightbox.sections.splice(lsl-2,0,{ name: 'alerts', height: 42, map_to: 'text', type: 'alerts_editor', button:'shide' });

scheduler.locale.labels.button_shide = scheduler.__alerts.locale.alert_show;


scheduler.form_blocks.alerts_editor = {
	render:(sns) => {
		let htm = `<div class="sched_alrthd"><span>Alertees: </span><span></span></div>
<div class="dhx_form_alerts" style="height:0px;display:none">
	<form id ="alert_form">
		<div class="sched_alertsf">
			<div id="sel_alertusers" class="sched_alrtatru">
				<label>${scheduler.__alerts.locale.alert_users}</label>
				<div class="us-checklist">
					${scheduler.alertWho}
				</div>
			</div>
			<div class="sched_alrtatrm">
				<label>${scheduler.__alerts.locale.alert_method}</label>
				<select id="alertmethod" name="alertmethod" class="alrt_meth">
					<option value="1">${scheduler.__alerts.locale.alert_email}</option>
					<option value="2">${scheduler.__alerts.locale.alert_SMS}</option>
					<option value="3">${scheduler.__alerts.locale.alert_both}</option>
				</select>
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
	aecntelm: null,
	updtae: (sel) => {
		aecntelm.innerHTML = sel.parentElement.querySelectorAll('.us-ckund:checked').length || 'None';
	},
	set_value: (node, value, ev) => {
//	console.log(node,value,ev);
		scheduler.fillAlert(node.nextElementSibling.firstElementChild,value,ev);
		aecntelm = node.children[1];
		aecntelm.innerHTML = scheduler.alertees ? scheduler.alertees : 'None';
	},
	get_value: (node, ev) => {
		scheduler.getAlert(node.nextElementSibling.firstElementChild,ev);
		return ev.text;
	},
	focus: (node) => {
		let a = node.nextElementSibling.firstElementChild.childNodes[1];
		a.select();
		a.focus();
	},
	button_click: (sect, btn, evt) => {
//	console.log(sect,btn,evt);
		let cont = document.getElementById('alert_form').parentElement;
		let btnd = btn.children[1];
		if (cont.style.height=='0px') {
			cont.style.display = 'block';
			cont.style.height = 'auto';
			btnd.innerHTML = scheduler.__alerts.locale.alert_hide;
		} else {
			cont.style.display = 'none';
			cont.style.height = '0px';
			btnd.innerHTML = scheduler.__alerts.locale.alert_show;
		}
		scheduler.setLightboxSize();
	}
};

scheduler.fillAlert = (elem,val,evt) => {
	elem.alertmethod.value = evt.alert_meth ? evt.alert_meth : 1;
//console.log(evt);
	let alertees = evt.alert_user ? evt.alert_user.split(/,/) : [];
	let select = elem.querySelectorAll('.us-ckund');
	scheduler.alertees = 0;
	for (let i = 0, l = select.length, o; i < l; i++) {
		o = select[i];
		if (alertees.indexOf(o.value) != -1 ) {
			o.checked = true;
			scheduler.alertees++;
		} else {
			o.checked = false;
		}
	}

//	elem.alertlead_mul.value = evt.alert_lead;
	if (evt.alert_lead) {
		let mlead = evt.alert_lead / 60;
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
};

scheduler.getAlert = (elem,evt) => {
	evt.alert_meth = elem.alertmethod.value;

	let users = [];
	let options = elem.querySelectorAll('.us-ckund:checked');
	let opt;
	for (let i=0, iLen=options.length; i<iLen; i++) {
		opt = options[i];
		users.push(opt.value || opt.text);
	}
	evt.alert_user = users.join(',');

	let alertlead = 0;
	//console.log(elem.alertlead_val);
	if (elem.alertlead_val.value) {
		let lval = elem.alertlead_val.value;
		switch (elem.alertlead_mul.value*1) {
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
};


// modify a SELECT block render to include a class (and no hard coded height)
scheduler.form_blocks.select.render = (sns) => {
	let html = '<div class="dhx_cal_ltext"><select class="'+sns.class+'" style="width:100%;">';
	for (let i = 0; i < sns.options.length; i++)
		html+='<option value="'+sns.options[i].key+'">'+sns.options[i].label+'</option>';
	html+='</select></div>';
	return html;
};


// contain modalbox size/width for small screens
scheduler.___modalbox = scheduler.modalbox;
scheduler.modalbox = (parms) => {
	console.log(parms);
	if (scheduler.$container.offsetWidth < 700 && parms.width) {
		parms.width = '300px';
	}
	scheduler.___modalbox(parms);
}