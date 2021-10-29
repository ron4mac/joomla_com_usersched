<?php
defined('_JEXEC') or die;

$mtabs = array();
if ($this->settings['settings_agenda']) $mtabs[] = 'agenda_tab';
if ($this->settings['settings_day']) $mtabs[] = 'day_tab';
if ($this->settings['settings_week']) $mtabs[] = 'week_tab';
if ($this->settings['settings_month']) $mtabs[] = 'month_tab';
if ($this->settings['settings_year']) $mtabs[] = 'year_tab';
for ($i=1; $i<=count($mtabs); $i++) {
//	$cls = $i==1 ? ' first flt' : ($i == count($mtabs)? ' last flt' : ' flt');
//	echo '<div class="dhx_cal_tab'.$cls.'" name="'.$mtabs[$i-1].'"></div>';
	echo '<div class="dhx_cal_tab" name="'.$mtabs[$i-1].'"></div>';
}
?>

<div class="dhx_cal_date"></div>
<div class="dhx_cal_prev_button">&nbsp;</div>
<div class="dhx_cal_today_button"></div>
<div class="dhx_cal_next_button">&nbsp;</div>
<!-- <div class="dhx_cal_tab" name="day_tab"></div>
<div class="dhx_cal_tab" name="week_tab"></div>
<div class="dhx_cal_tab" name="month_tab"></div>
<div class="dhx_cal_tab" name="year_tab"></div> -->
