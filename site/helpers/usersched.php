<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

abstract class UserSchedHelper {

	//default config view settings
	public static $dfltConfig = [
		'settings_width' => '680px',
		'settings_height' => '600px',
		'settings_eventnumber' => 5,
		'settings_posts' => false,
		'settings_repeat' => true,
		'settings_firstday' => false,
		'settings_multi_day' => true,
		'settings_fullday' => true,
		'settings_day' => true,
		'settings_week' => true,
		'settings_month' => true,
		'settings_year' => false,
		'settings_agenda' => false,
		'settings_week_agenda' => false,
		'settings_map' => false,
		'settings_defaultmode' => 'month',
		'settings_skin' => '',
		'settings_debug' => false,
		'settings_collision' => true,
		'settings_expand' => true,
		'settings_pdf' => true,
		'settings_ical' => true,
		'settings_minical' => false,
		'templates_defaultdate' => '%d %M %Y',
		'templates_monthdate' => '%F %Y',
		'templates_weekdate' => '%l',
		'templates_daydate' => '%d %M',
		'templates_hourdate' => '%g:%i%a',
		'templates_monthday' => '%j',
		'templates_minmin' => 15,
		'templates_starthour' => 8,
		'templates_endhour' => 22,
		'templates_agendatime' => 30,
		'templates_eventtext' => 'return event.text;',
		'templates_eventheader' => 'return scheduler.templates.hour_scale(start) + " - " + scheduler.templates.hour_scale(end);',
		'templates_eventbartext>' => 'return "<span title=\'"+event.text+"\'>" + event.text + "</span>";',
		'templates_username' => false,
		'lang_tag' => 'en-GB'
	];

	public static function uState ($vari, $set=false, $val='', $glb=false)
	{
		$stvar = ($glb?'':'com_usersched.').$vari;
		$app = Factory::getApplication();
		if ($set) {
			$app->setUserState($stvar, $val);
			return;
		}
		return $app->getUserState($stvar, '');
	}

	public static function groupTitle ($gid)
	{
		$db = Factory::getDatabase();
		$db->setQuery(
			'SELECT `title`' .
			' FROM `#__usergroups`' .
			' WHERE `id` = '. (int) $gid
		);
		$title = $db->loadResult();
		return $title;
	}

}