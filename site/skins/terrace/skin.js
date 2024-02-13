scheduler.config.fix_tab_position = false;

scheduler.attachEvent('onTemplatesReady', function() {
	if (!scheduler.config.fix_tab_position) {
		let navline_divs = scheduler._els.dhx_cal_navline[0].getElementsByTagName('div');
		let position = 0;

		for (let i=0; i<navline_divs.length; i++) {
			let div = navline_divs[i];
			let name = div.dataset.tab;
			if (name) { // mode tab
				div.style.right = 'auto';
				div.style.left = position + 'px';
				position += div.offsetWidth - 1;
				div.className += ' ' + name;
			}
		}
	}
});


// add a switch to select an appropriate config for a current screen size
scheduler.resetConfig = () => {
	const tabs = USched.tabs || ['agenda','day','week','month','year'];
	const compactHeader = {
		rows: [
			{ cols: ['prev','date','next'] },
			{ cols: tabs.concat(['spacer','today']) }
		]
	};
	const fullHeader = tabs.concat(['date','prev','today','next']);
	let header;
	if (scheduler.$container && scheduler.$container.offsetWidth < 600) {
		header = compactHeader;
	} else {
		header = fullHeader;
	}
	scheduler.config.header = header;
	return true;
}

// apply the config initially and each time scheduler repaints or resizes:

scheduler.resetConfig();
scheduler.attachEvent('onBeforeViewChange', scheduler.resetConfig);
scheduler.attachEvent('onSchedulerResize', scheduler.resetConfig);
