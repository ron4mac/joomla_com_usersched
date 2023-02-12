//'use strict';
/*global scheduler*/

// adds US holiday events gotten from Google's calendar API
// Jan 2015 updated for Google API v3

scheduler.gHolyYrs = [];
scheduler.attachEvent("onViewChange", function (new_mode, new_date) {
	let ny;
	const getHolidays = (yr) => {
		if (scheduler.gHolyYrs.indexOf(yr)==-1) {
			scheduler.gHolyYrs.push(yr);
			let url = "/index.php?option=com_usersched&format=raw&task=ajax.holidays&yr="+yr+"&rg=usa__en";
			let currentURL = window.location;
			let live_site = currentURL.protocol+'//'+currentURL.host+USched.base;
			fetch(live_site + url)
			.then((resp) => { if (!resp.ok) { throw new Error('Network response was not OK'); } return resp.json(); })
			.then((data) => {
				let gevents = [], items = data.items, ix;
				for (ix=0; ix<items.length; ix++) {
					let item = items[ix];
					gevents.push({text:item.summary,start_date:item.start.date,end_date:item.end.date,xevt:"isHoliday",readonly:true});
				}
				let evs = scheduler.json.parse(gevents);
				scheduler._process_loading(evs);
			})
			.catch((error) => { console.error('There has been a problem with your fetch operation:', error); });
		}
	};

	getHolidays(new_date.getFullYear());
	switch (new_mode) {
		case "day":
			break;
		case "week":
			ny = new Date(new_date);
			ny.setDate(ny.getDate()-7);
			getHolidays(ny.getFullYear());
			ny.setDate(ny.getDate()+14);
			getHolidays(ny.getFullYear());
			break;
		case "month":
			ny = new Date(new_date);
			ny.setMonth(ny.getMonth()-1);
			getHolidays(ny.getFullYear());
			ny.setMonth(ny.getMonth()+2);
			getHolidays(ny.getFullYear());
			break;
		case "year":
			break;
		default:
			// here we'll just use a canon and make sure we have the prev and next years' holidays
			let cy = new_date.getFullYear();
			getHolidays(cy-1);
			getHolidays(cy+1);
	}
});
