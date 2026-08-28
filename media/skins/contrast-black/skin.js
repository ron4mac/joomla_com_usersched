

scheduler.skin = scheduler.skin || 'contrast_black';


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
};

// apply the config initially and each time scheduler repaints or resizes:

scheduler.resetConfig();
scheduler.attachEvent('onBeforeViewChange', scheduler.resetConfig);
scheduler.attachEvent('onSchedulerResize', scheduler.resetConfig);
