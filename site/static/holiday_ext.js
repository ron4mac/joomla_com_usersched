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

function gcal_fetch (url) {
	var tempscript = null;
	var callback = function(data) {
		document.body.removeChild(tempscript);
		tempscript = null;
		var gevents = [], items = data.items, ix;
		for (ix=0; ix<items.length; ix++) {
			var item = items[ix];
			gevents.push({text:item.summary,start_date:item.start.date,end_date:item.end.date,xevt:"isHoliday"});
		}
		var evs = scheduler.json.parse(gevents);
		scheduler._process_loading(evs);
	};
	var uniqfunc = 'jsonp'+Math.round(Date.now()+Math.random()*1000001);
	window[uniqfunc] = function (json) {
		if (json.error) { console.log(uniqfunc+": "+json.error.message); }
		callback(json);
		delete window[uniqfunc];
	};
	tempscript = document.createElement("script");
	tempscript.type = "text/javascript";
//	tempscript.id = "tempscript";
	tempscript.src = url + "&callback=" + uniqfunc;
	document.body.appendChild(tempscript);
}

function getGoogleHolidays (yr) {
	if (scheduler.gHolyYrs.indexOf(yr)==-1) {
		scheduler.gHolyYrs.push(yr);
		gcurl = 'https://www.googleapis.com/calendar/v3/calendars/usa__en%40holiday.calendar.google.com/events?key=AIzaSyAxlVigwBVLSu-ryKOr1c4mXextZ6nPkyc';
		gcurl += '&timeMin='+yr+'-01-01T00%3A00%3A00%2B00%3A00&timeMax='+(yr+1)+'-01-01T00%3A00%3A00%2B00%3A00&singelEvents=true';
		var gcfobj = new gcal_fetch(gcurl);
	}
}
