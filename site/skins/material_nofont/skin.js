scheduler.config.fix_tab_position = false;

scheduler.attachEvent("onTemplatesReady", function() {
	if (!scheduler.config.fix_tab_position) {
		let navline_divs = scheduler._els.dhx_cal_navline[0].getElementsByTagName('div');
		let position = 0;

		for (let i=0; i<navline_divs.length; i++) {
			let div = navline_divs[i];
			let name = div.dataset.tab;
			if (name) { // mode tab
				div.style.right = "auto";
				div.style.left = position + "px";
				position += div.offsetWidth - 1;
				div.className += " " + name;
			}
		}
	}
});
