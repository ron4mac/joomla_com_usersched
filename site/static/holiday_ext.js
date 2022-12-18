//'use strict';
/*global scheduler*/

// adds US holiday events gotten from Google's calendar API
// Jan 2015 updated for Google API v3

scheduler.gHolyYrs = [];
scheduler.attachEvent("onViewChange", function (new_mode, new_date){
	getGoogleHolidays(new_date.getFullYear());
	switch (new_mode) {
		case "day":
			break;
		case "week":
			var ny = new Date(new_date);
			ny.setDate(ny.getDate()-7);
			getGoogleHolidays(ny.getFullYear());
			ny.setDate(ny.getDate()+14);
			getGoogleHolidays(ny.getFullYear());
			break;
		case "month":
			var ny = new Date(new_date);
			ny.setMonth(ny.getMonth()-1);
			getGoogleHolidays(ny.getFullYear());
			ny.setMonth(ny.getMonth()+2);
			getGoogleHolidays(ny.getFullYear());
			break;
		case "year":
			break;
		default:
			// here we'll just use a canon and make sure we have the prev and next years' holidays
			var cy = new_date.getFullYear();
			getGoogleHolidays(cy-1);
			getGoogleHolidays(cy+1);
	}
});

function getGoogleHolidays (yr) {
	if (scheduler.gHolyYrs.indexOf(yr)==-1) {
		scheduler.gHolyYrs.push(yr);
		var url = "/index.php?option=com_usersched&format=raw&task=ajax.holidays&yr="+yr+"&rg=usa__en";
		var currentURL = window.location;
		var live_site = currentURL.protocol+'//'+currentURL.host+usched_base;
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(e) {
			if (this.readyState < 4) { return; }
			if (this.status !== 200) {
				console.log(this.statusText || this.status);
			} else if (this.status === 200) {
				var data = JSON.parse(this.response);
				var gevents = [], items = data.items, ix;
				for (ix=0; ix<items.length; ix++) {
					var item = items[ix];
					gevents.push({text:item.summary,start_date:item.start.date,end_date:item.end.date,xevt:"isHoliday"});
				}
				var evs = scheduler.json.parse(gevents);
				scheduler._process_loading(evs);
			}
		};
		xhr.open('GET', live_site + url);
		xhr.send();
	}
}
