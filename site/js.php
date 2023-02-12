<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

/*	codess
	A - admin mode
	R - recurring events
	Y - year view
	J - my ext (alerts & auto end date change)
	H - holidays (google)
	B - joomla user birthdays
	M - Mobile device
*/
$jsfiles = [];
$needRO = false;
$codes = $_GET['c'];$lcl = $_GET['l'];

$jsfiles[] = 'scheduler/codebase/dhtmlxscheduler.js';
$jsfiles[] = 'scheduler/codebase/locale/locale_'.$lcl.'.js';
if (strpos($codes,'Y') !== false) $jsfiles[] = 'scheduler/codebase/ext/dhtmlxscheduler_year_view.js';
if (strpos($codes,'G') !== false) $jsfiles[] = 'scheduler/codebase/ext/dhtmlxscheduler_agenda_view.js';
if (strpos($codes,'R') !== false) {
	$jsfiles[] = 'scheduler/codebase/ext/dhtmlxscheduler_recurring.js';
	$jsfiles[] = 'scheduler/codebase/locale/recurring/locale_recurring_'.$lcl.'.js';
}
if (strpos($codes,'A') === false) {
	$needRO = true;
	//$jsfiles[] = 'scheduler/codebase/ext/dhtmlxscheduler_readonly.js';
	$scpt = 'scheduler.config.readonly_form = true;';
	//block all modifications
	$scpt .= 'scheduler.attachEvent("onBeforeDrag",function(){return false;});';
	$scpt .= 'scheduler.attachEvent("onClick",function(){return false;});';
	$scpt .= 'scheduler.config.details_on_dblclick = true;';
	$scpt .= 'scheduler.config.dblclick_create = false;';
	$jsfiles[] = ['s'=>$scpt];
}
$jsfiles[] = 'scheduler/codebase/ext/dhtmlxscheduler_minical.js';
if (strpos($codes,'M') !== false) {
	$jsfiles[] = 'scheduler/codebase/ext/dhtmlxscheduler_quick_info.js';
} else {
	$jsfiles[] = 'scheduler/codebase/ext/dhtmlxscheduler_tooltip.js';
}
$jsfiles[] = 'scheduler/codebase/ext/dhtmlxscheduler_expand.js';
$jsfiles[] = 'scheduler/codebase/ext/dhtmlxscheduler_pdf.js';
if (strpos($codes,'J') !== false) {
	$jsfiles[] = 'static/rjc_ext.js';
	$jsfiles[] = 'static/locale_alerts_'.$lcl.'.js';
}
$jsfiles[] = 'static/usersched.js';
$jsfiles[] = 'static/locale_lang_'.$lcl.'.js';
if (strpos($codes,'H') !== false) {
	$needRO = true;
	$jsfiles[] = 'static/holiday_ext.js';
}
if (strpos($codes,'B') !== false) {
	$needRO = true;
	//$jsfiles[] = 'scheduler/codebase/ext/dhtmlxscheduler_readonly.js';
	$jsfiles[] = 'static/usrbday_ext.js';
}
if ($needRO) {
	$jsfiles[] = 'scheduler/codebase/ext/dhtmlxscheduler_readonly.js';
}
$lastmod = 0;
$totsize = 0;
$jss = [];
foreach ($jsfiles as $jsf) {
	if (is_array($jsf)) {
		$totsize += strlen($jsf['s']) + 1;
		$jss[] = $jsf['s'];
	} else {
		$lastmod = max($lastmod, @filemtime($jsf));
		$fsz = @filesize($jsf);
		$totsize += ($fsz ?: 12) + strlen($jsf) + 6;
		$jss[] = $jsf;
	}
}
$hash = $lastmod . '-' . $totsize . '-' . md5(implode(':',$jss));

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == $hash)
{
	// Return visit and no modifications, so do not send anything 
	header ($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified'); 
//	header ('Content-Length: 0'); 
} else {
	//package the script files for one access
	header('Access-Control-Expose-Headers: ETag');
	header('Content-type: text/javascript');
	header('Content-Length: ' . $totsize);
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastmod) . ' GMT');
	header('ETag: ' . $hash);
	header('Cache-Control: must-revalidate');
	foreach ($jsfiles as $jsf) {
		if (is_array($jsf)) {
			echo $jsf['s'];
		} else {
			echo"/*{$jsf}*/\n";
			if (!@readfile($jsf)) echo"/*MISSING*/\n";
		}
		echo"\n";
	}
}

