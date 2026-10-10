<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.1
*/

if (isset($_GET['skin']) && $_GET['skin']) {
	$skin = $_GET['skin'];
}
$skin = '';
if (isset($_GET['skin']) && $_GET['skin']) {
	$skin = $_GET['skin'];
}
$dhxver = '7.2';
if (isset($_GET['dxv']) && $_GET['dxv']) {
	$dhxver = $_GET['dxv'];
}
$skinPath = 'media/com_usersched/skins/'.$skin;
$topbar = '';
if (isset($skins)) {
	$topbar = '<div class="skelect"><select onchange="loadSkin(this.value)">';
	foreach ($skins as $s) {
		$sel = $s===$curskin ? ' selected' : '';
		if (!str_starts_with($s,'.')) $topbar .= '<option value="'.$s.'"'.$sel.'>'.$s.'</option>';
	}
	$topbar .= '</select></div>';
	$skinPath = 'media/com_usersched/skins/'.$curskin;
}
?>
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>Skin Preview - <?=$skin?></title>

	<link rel="stylesheet" href="https://cdn.dhtmlx.com/scheduler/<?=$dhxver?>/dhtmlxscheduler.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<link rel="stylesheet" href="<?=$skinPath?>/skin.css" type="text/css" media="screen" charset="utf-8">

	<script src="https://cdn.dhtmlx.com/scheduler/<?=$dhxver?>/dhtmlxscheduler.js" type="text/javascript" charset="utf-8"></script>
	<script>USched = {bURL: "<?=__DIR__?>"};</script>
	<script src="<?=$skinPath?>/skin.js" type="text/javascript" charset="utf-8"></script>

	<style type="text/css" media="screen">
		html, body {
			margin: 0px;
			padding: 0px;
			height: 100%;
			overflow: hidden;
		}
		/*.dhx_cal_cover { opacity:0 }*/
		.skelect {
			display: flex;
			justify-content: center;
			padding: 1rem;
			background-color: #888;
		}
		.skelect select {
			font-size: large;
		}
	</style>

	<script type="text/javascript" charset="utf-8">
		scheduler.skin = "<?=$skin?>";
		function init () {
			scheduler.config.multi_day = true;
			var pizza_size = [
				{ key: 1, label: 'Small' },
				{ key: 2, label: 'Medium' },
				{ key: 3, label: 'Large' }
			];

			scheduler.locale.labels.section_text = 'Text';
			scheduler.locale.labels.section_checkbox = 'Checkbox';
			scheduler.locale.labels.section_radiobutton = 'Radiobutton';
			scheduler.locale.labels.section_select = 'Select';
			scheduler.locale.labels.section_template = 'Template';

			scheduler.config.lightbox.sections = [
				{ name: "text", height: 50, map_to: "text", type: "textarea", focus: true },
			//	{ name: "checkbox", map_to: "single_checkbox", type: "checkbox", checked_value: "registrable", unchecked_value: "unchecked" },
			//	{ name: "radiobutton", height: 58, options: pizza_size, map_to: "radiobutton_option", type: "radio", vertical: true },
			//	{ name: "select", height: 21, map_to: "type", type: "select", options: pizza_size },
			//	{ name: "template", height: 21, map_to: "text", type: "template" },
				{ name: "recurring", type: "recurring", map_to: "rec_type", button: "recurring"},
			//	{ name: "time", height: 72, type: "calendar_time", map_to: "auto" },
				{ name: "time", height: 72, type: "time", map_to: "auto"}
			];

			scheduler.config.full_day = true;

			scheduler.config.xml_date = "%Y-%m-%d %H:%i";
			scheduler.init('scheduler_here', new Date(2010, 1, 2), "month");
			scheduler.config.wide_form = false;
			scheduler.load("components/com_usersched/prevskin/events.xml", () => scheduler.showLightbox(47));
		}
		function loadSkin (skin) {
			const url = new URL(window.location.href);
			url.searchParams.set('skin', skin);
			window.location.href = url.href;
		}
	</script>
</head>
<body onload="init();">
<?php //echo'<xmp>';var_dump($_ENV,$_SERVER);echo'</xmp>';?>
<?=$topbar?>
<div id="scheduler_here" class="dhx_cal_container" style='width:100%; height:100%;'>
	<div class="dhx_cal_navline">
		<div class="dhx_cal_prev_button">&nbsp;</div>
		<div class="dhx_cal_next_button">&nbsp;</div>
		<div class="dhx_cal_today_button"></div>
		<div class="dhx_cal_date"></div>
		<div class="dhx_cal_tab" name="day_tab" style="right:204px;"></div>
		<div class="dhx_cal_tab" name="week_tab" style="right:140px;"></div>
		<div class="dhx_cal_tab" name="month_tab" style="right:76px;"></div>
	</div>
	<div class="dhx_cal_header">
	</div>
	<div class="dhx_cal_data">
	</div>
</div>
</body>