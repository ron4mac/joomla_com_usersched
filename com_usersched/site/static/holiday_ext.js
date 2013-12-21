// adds US holiday events gotten from Google's calendar API

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

function getGoogleHolidays (yr) {	// Mootools specific JSONP request
	if (scheduler.gHolyYrs.indexOf(yr)==-1) {
		scheduler.gHolyYrs.push(yr);
		var jsonRequest = new Request.JSONP({
			url: 'http://www.google.com/calendar/feeds/usa__en%40holiday.calendar.google.com/public/full?alt=jsonc&start-min='+yr+'-01-01&start-max='+(yr+1)+'-01-01&max-results=200',
			onSuccess: function(jsn){
				//console.log(jsn.data.items);
				var gevents = [];
				Array.each(jsn.data.items, function (item,index){
					gevents.push({text:item.title,start_date:item.when[0].start,end_date:item.when[0].end,xevt:"isHoliday"});
				});
				var evs = scheduler.json.parse(gevents);
				scheduler._process_loading(evs);
				}
			}).send();
	}
}
