//'use strict';
/*global scheduler*/

scheduler.bdayYrs = [];
scheduler.attachEvent("onViewChange", function (new_mode, new_date) {
	let ny;
	const getBdays = (yr) => {
		let currentURL = window.location;
		let live_site = currentURL.protocol+'//'+currentURL.host+USched.base;
		if (scheduler.bdayYrs.indexOf(yr)==-1) {
			scheduler.bdayYrs.push(yr);
			let url = "/index.php?option=com_usersched&format=raw&task=Raw.birthdays&y="+yr;
			fetch(live_site + url)
			.then((resp) => { if (!resp.ok) { throw new Error('Network response was not OK'); } return resp.json(); })
			.then((data) => {
				let evs = scheduler.json.parse(data);
				scheduler._process_loading(evs);
			})
			.catch((error) => { console.error('There has been a problem with your fetch operation:', error); });
		}
	};
	getBdays(new_date.getFullYear());
	switch (new_mode) {
		case "day":
			break;
		case "week":
			ny = new Date(new_date);
			ny.setDate(ny.getDate()-7);
			getBdays(ny.getFullYear());
			ny.setDate(ny.getDate()+14);
			getBdays(ny.getFullYear());
			break;
		case "month":
			ny = new Date(new_date);
			ny.setMonth(ny.getMonth()-1);
			getBdays(ny.getFullYear());
			ny.setMonth(ny.getMonth()+2);
			getBdays(ny.getFullYear());
			break;
		case "year":
			break;
		default:
			// here we'll just use a canon and make sure we have the prev and next years' birthdays
			let cy = new_date.getFullYear();
			getBdays(cy-1);
			getBdays(cy+1);
	}
});
