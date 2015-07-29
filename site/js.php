<?php
/*	codess
	A - admin mode
	R - recurring events
	Y - year view
	J - my ext (alerts & auto end date change)
	H - holidays (google)
	B - joomla user birthdays
	M - Mobile device
*/
	function readitnl ($fp) {
		readfile($fp);
		echo "\n";
	}

	$codes = $_GET['c'];$lcl = $_GET['l'];
	header("Content-type: text/javascript"); 
	readitnl('scheduler/codebase/dhtmlxscheduler.js');
	$opt_file = 'scheduler/codebase/sources/locale/locale_'.$lcl.'.js';
	if ($lcl && file_exists($opt_file)) readitnl($opt_file);
	if (strpos($codes,'Y') !== false) readitnl('scheduler/codebase/ext/dhtmlxscheduler_year_view.js');
	if (strpos($codes,'G') !== false) readitnl('scheduler/codebase/ext/dhtmlxscheduler_agenda_view.js');
	if (strpos($codes,'R') !== false) {
		readitnl('scheduler/codebase/ext/dhtmlxscheduler_recurring.js');
		$opt_file = 'scheduler/codebase/sources/locale/recurring/locale_recurring_'.$lcl.'.js';
		if (file_exists($opt_file)) readitnl($opt_file);
	}
	if (strpos($codes,'A') === false) {
		readitnl('scheduler/codebase/ext/dhtmlxscheduler_readonly.js');
		echo 'scheduler.config.readonly_form = true;';
		//block all modifications
		echo 'scheduler.attachEvent("onBeforeDrag",function(){return false;});';
		echo 'scheduler.attachEvent("onClick",function(){return false;});';
		echo 'scheduler.config.details_on_dblclick = true;';
		echo 'scheduler.config.dblclick_create = false;';
	}
	readitnl('scheduler/codebase/ext/dhtmlxscheduler_minical.js');
	if (strpos($codes,'M') !== false) {
		readitnl('scheduler/codebase/ext/dhtmlxscheduler_quick_info.js');
	} else {
		readitnl('scheduler/codebase/ext/dhtmlxscheduler_tooltip.js');
	}
	readitnl('scheduler/codebase/ext/dhtmlxscheduler_expand.js');
	readitnl('scheduler/codebase/ext/dhtmlxscheduler_pdf.js');
	if (strpos($codes,'J') !== false) {
		readitnl('static/rjc_ext.js');
		if ($lcl) readitnl('static/locale_alerts_'.$lcl.'.js');
	}
	readitnl('static/usersched.js');
	readitnl('static/locale_lang_'.$lcl.'.js');
	if (strpos($codes,'H') !== false) {
		readitnl('static/holiday_ext.js');
	}
	if (strpos($codes,'B') !== false) {
		readitnl('static/usrbday_ext.js');
	}
?>