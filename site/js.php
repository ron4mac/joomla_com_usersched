<?php
/*	codes
	A - admin mode
	R - recurring events
	Y - year view
	J - my ext (alerts & auto end date change)
*/
	$codes = $_GET['c'];$lcl = $_GET['l'];
	header("Content-type: text/javascript"); 
	readfile('scheduler/codebase/dhtmlxscheduler.js');
	$opt_file = 'scheduler/sources/locale/locale_'.$lcl.'.js';
	if ($lcl && file_exists($opt_file)) readfile($opt_file);
	if (strpos($codes,'Y') !== false) readfile('scheduler/codebase/ext/dhtmlxscheduler_year_view.js');
	if (strpos($codes,'R') !== false) {
		readfile('scheduler/codebase/ext/dhtmlxscheduler_recurring.js');
		$opt_file = 'scheduler/sources/locale/recurring/locale_recurring_'.$lcl.'.js';
		if (file_exists($opt_file)) readfile($opt_file);
	}
	if (strpos($codes,'A') === false) {
		readfile('scheduler/codebase/ext/dhtmlxscheduler_readonly.js');
		echo 'scheduler.config.readonly_form = true;';
		//block all modifications
		echo 'scheduler.attachEvent("onBeforeDrag",function(){return false;});';
		echo 'scheduler.attachEvent("onClick",function(){return false;});';
		echo 'scheduler.config.details_on_dblclick = true;';
		echo 'scheduler.config.dblclick_create = false;';
	}
	readfile('scheduler/codebase/ext/dhtmlxscheduler_minical.js');
	readfile('scheduler/codebase/ext/dhtmlxscheduler_tooltip.js');
	readfile('scheduler/codebase/ext/dhtmlxscheduler_expand.js');
	readfile('scheduler/codebase/ext/dhtmlxscheduler_pdf.js');
	if (strpos($codes,'J') !== false) {
		readfile('static/rjc_ext.js');
		if ($lcl) readfile('static/locale_alerts_'.$lcl.'.js');
	}
	readfile('static/usersched.js'); 
	readfile('static/holiday_ext.js');
	readfile('static/usrbday_ext.js');
?>