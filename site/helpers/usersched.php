<?php
defined('_JEXEC') or die;

abstract class UserSchedHelper {

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