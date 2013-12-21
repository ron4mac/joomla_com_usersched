scheduler.bdayYrs = [];
scheduler.attachEvent("onViewChange", function (new_mode, new_date){
	getBdays(new_date.getFullYear());
	switch (new_mode) {
		case "day":
			break;
		case "week":
			var ny = new Date(new_date);
			ny.setDate(ny.getDate()-7);
			getBdays(ny.getFullYear());
			ny.setDate(ny.getDate()+14);
			getBdays(ny.getFullYear());
			break;
		case "month":
			var ny = new Date(new_date);
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

function getBdays (yr) {	// Mootools specific JSONP request
	var currentURL = window.location;
	var live_site = currentURL.protocol+'//'+currentURL.host+usched_base;
	if (scheduler.bdayYrs.indexOf(yr)==-1) {
		scheduler.bdayYrs.push(yr);
		var request = new Request({
			url: live_site+'/components/com_usersched/bdayajax.php',
			data: 'y='+yr,
			onSuccess: function(data) {
				//console.log(data);
				var gevents = JSON.parse(data);	//[];
				//console.log(gevents);
				var evs = scheduler.json.parse(gevents);
				scheduler._process_loading(evs);
			},
			onFailure: function(xhr) {
				console.log(xhr);
				alert("failed");
			}
			}).send();
	}
}
