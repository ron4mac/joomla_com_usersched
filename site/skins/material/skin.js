scheduler.config.fix_tab_position = false;

scheduler.attachEvent("onTemplatesReady", function() {
	if (!scheduler.config.fix_tab_position) {
		var navline_divs = scheduler._els["dhx_cal_navline"][0].getElementsByTagName('div');
		var position = 0;
		var inc = 14;
	
		for (var i=0; i<navline_divs.length; i++) {
			var div = navline_divs[i];
			var name = div.getAttribute("name");
			if (name) { // mode tab
				div.style.right = "auto";
				div.style.left = position + "px";
				position += div.offsetWidth - 1;
				div.className += " " + name;
			}
		}
	}
});
