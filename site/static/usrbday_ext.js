scheduler.bdayYrs = [];
scheduler.attachEvent("onViewChange", function (new_mode, new_date) {
	var ny;
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
			var cy = new_date.getFullYear();
			getBdays(cy-1);
			getBdays(cy+1);
	}
});

function getBdays (yr) {
	var currentURL = window.location;
	var live_site = currentURL.protocol+'//'+currentURL.host+usched_base;
	if (scheduler.bdayYrs.indexOf(yr)==-1) {
		scheduler.bdayYrs.push(yr);
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(e) {
			if (this.readyState < 4) { return; }
			if (this.status !== 200) {
				console.log(this.statusText || this.status);
			} else if (this.status === 200) {
				var gevents = JSON.parse(this.response);	//[];
				var evs = scheduler.json.parse(gevents);
				scheduler._process_loading(evs);
			}
		};
		xhr.open('GET', live_site+'/components/com_usersched/bdayajax.php?y='+yr);
		xhr.send();
	}
}
