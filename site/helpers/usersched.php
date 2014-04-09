<?php
defined('_JEXEC') or die;

abstract class UserSchedHelper {

	//default config view settings
	public static $dfltConfig = array(
		'settings_width' => '680px',
		'settings_height' => '600px',
		'settings_eventnumber' => 5,
		'settings_posts' => false,
		'settings_repeat' => true,
		'settings_firstday' => false,
		'settings_multi_day' => true,
		'settings_fullday' => false,
		'settings_day' => true,
		'settings_week' => true,
		'settings_month' => true,
		'settings_agenda' => false,
		'settings_week_agenda' => false,
		'settings_year' => true,
		'settings_map' => false,
		'settings_defaultmode' => 'month',
		'settings_skin' => '',
		'settings_debug' => false,
		'settings_collision' => false,
		'settings_expand' => true,
		'settings_pdf' => true,
		'settings_ical' => true,
		'settings_minical' => false,
		'templates_defaultdate' => '%d %M %Y',
		'templates_monthdate' => '%F %Y',
		'templates_weekdate' => '%l',
		'templates_daydate' => '%d/%m/%Y',
		'templates_hourdate' => '%H:%i',
		'templates_monthday' => '%d',
		'templates_minmin' => 5,
		'templates_hourheight' => 42,
		'templates_starthour' => 0,
		'templates_endhour' => 24,
		'templates_agendatime' => 30,
		'templates_eventtext' => 'return event.text;',
		'templates_eventheader' => 'return scheduler.templates.hour_scale(start) + " - " + scheduler.templates.hour_scale(end);',
		'templates_eventbartext>' => 'return "<span title=\'"+event.text+"\'>" + event.text + "</span>";',
		'templates_username' => false,
	);

	public static function uState ($vari, $set=false, $val='', $glb=false)
	{
		$stvar = ($glb?'':'com_usersched.').$vari;
		$app = JFactory::getApplication();
		if ($set) {
			$app->setUserState($stvar, $val);
			return;
		}
		return $app->getUserState($stvar, '');
	}

	public static function groupTitle ($gid)
	{
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT `title`' .
			' FROM `#__usergroups`' .
			' WHERE `id` = '. (int) $gid
		);
		$title = $db->loadResult();
		return $title;
	}

}