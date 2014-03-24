<?php
defined('_JEXEC') or die;
//echo'<xmp>';var_dump(JFactory::getLanguage()->getLocale());echo'</xmp>';jexit();
$caltyp = $this->params->get('cal_type');
switch ($caltyp) {
	case 0:
		$jID = $this->params->get('user_auth');
		break;
	case 1:
		$jID = $this->params->get('group_auth');
		break;
	case 2:
		$jID = $this->params->get('site_auth');
		break;
}
$calID = base64_encode(join(':',array($caltyp,$jID)));
$script = "var jcalid = '{$calID}';";
$script .= 'var usched_base = "'.JURI::base(true).'";';
$script .= 'var userschedlurl = "' . JURI::base() . 'index.php?option=com_usersched&view=usersched&task=calXML&calid=' . $calID .'";';
$script .= "scheduler.cfg_cfg = {$this->cfgcfg};";
if ($this->alertees) {
	$script .= 'scheduler.alertWho = \'';
	foreach ($this->alertees as $a) {
		$script .= '<option value="'.$a['id'].'">'.$a['name'].'</option>';
	}
	$script .= '\';';
}
$script .= 'scheduler.__categories = ['.implode(',',$this->categoriesJSON()).'];';
$locale = JFactory::getLanguage()->getLocale();
$jscodes = 'l='.$locale[4];
$jscodes .= '&c=YJR';
if ($this->canCfg) $jscodes .= 'A';
//try to convince the browser to reload the whole thing (for development purposes)
//$jscodes .='&'.time();
JHtml::_('behavior.framework', true);
JFactory::getDocument()->addScript('components/com_usersched/js.php?'.$jscodes);
/*
JHtml::script('components/com_usersched/scheduler/codebase/dhtmlxscheduler.js');
JHtml::script('components/com_usersched/scheduler/sources/locale/locale_'.$locale[4].'.js');
if ($this->settings['settings_year']) JHtml::script('components/com_usersched/scheduler/codebase/ext/dhtmlxscheduler_year_view.js');
//if ($this->settings['settings_repeat']) {
//	JHtml::script('components/com_usersched/scheduler/codebase/ext/dhtmlxscheduler_recurring.js');
//	JHtml::script('components/com_usersched/scheduler/sources/locale/recurring/locale_recurring_'.$locale[4].'.js');
//}
JHtml::script('components/com_usersched/scheduler/codebase/ext/dhtmlxscheduler_tooltip.js');
JHtml::script('components/com_usersched/scheduler/codebase/ext/dhtmlxscheduler_expand.js');
JHtml::script('components/com_usersched/scheduler/codebase/ext/dhtmlxscheduler_pdf.js');
JHtml::script('components/com_usersched/static/rjc_ext.js');
//JHtml::script('components/com_usersched/static/locale_alerts_'.$locale[4].'.js');
*/
JFactory::getDocument()->addScriptDeclaration($script);
JFactory::getDocument()->addStyleDeclaration($this->categoriesCSS());
/*
JHtml::script('components/com_usersched/static/usersched.js',true);
JHtml::_('behavior.framework', true);
JHtml::script('components/com_usersched/static/holiday_ext.js');
JHtml::script('components/com_usersched/static/usrbday_ext.js');
*/
$skin = $this->params->get('default_skin');
if ($this->settings['settings_skin']) $skin = $this->settings['settings_skin'];
if ($skin) {
	JHtml::stylesheet('components/com_usersched/skins/'.$skin.'/dhtmlxscheduler_custom.css');
} else {
	JHtml::stylesheet('components/com_usersched/scheduler/codebase/dhtmlxscheduler_glossy.css');	//scheduler4.0
}
JHtml::stylesheet('components/com_usersched/static/usersched.css');

$icns_left = -17;
$icns_leftx = 20;
$tabs_right = -42;
$tabs_rightx = 64;
?>

<div id="scheduler_here" class="dhx_cal_container" style='width:auto; height:646px;'>
<?php if ($this->canCfg) :?>
	<img src="components/com_usersched/static/cfg16.png" title="Configure calendar" class="usched_act" alt="" style="left:<?=$icns_left+=$icns_leftx?>px;" onclick="configScheduler()" />
<?php endif; ?>
	<img src="components/com_usersched/static/printer.png" title="Print calendar" class="usched_act" alt="" style="left:<?=$icns_left+=$icns_leftx?>px;" onclick="scheduler.toPDF('<?=JURI::base()?>components/com_usersched/pdf/generate.php')" />
	<div class="dhx_cal_navline">
		<div class="dhx_cal_prev_button">&nbsp;</div>
		<div class="dhx_cal_next_button">&nbsp;</div>
		<div class="dhx_cal_today_button"></div>
		<div class="dhx_cal_date"></div>
		<?php if ($this->settings['settings_year']): ?>
		<div class="dhx_cal_tab" name="year_tab" style="right:<?=$tabs_right+=$tabs_rightx?>px;"></div>
		<?php endif; ?>
		<?php if ($this->settings['settings_month']): ?>
		<div class="dhx_cal_tab" name="month_tab" style="right:<?=$tabs_right+=$tabs_rightx?>px;"></div>
		<?php endif; ?>
		<?php if ($this->settings['settings_week']): ?>
		<div class="dhx_cal_tab" name="week_tab" style="right:<?=$tabs_right+=$tabs_rightx?>px;"></div>
		<?php endif; ?>
		<?php if ($this->settings['settings_day']): ?>
		<div class="dhx_cal_tab" name="day_tab" style="right:<?=$tabs_right+=$tabs_rightx?>px;"></div>
		<?php endif; ?>
	</div>
	<div class="dhx_cal_header"></div>
	<div class="dhx_cal_data"></div>
</div>
<div class="userschedver">UserSched 0.9DEV</div><div class="schedulerver">Scheduler 4.0</div>
<?php
	//echo'<xmp>';var_dump($this);echo'</xmp>';
?>
