<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.3
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Access;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Application\ApplicationHelper;

class UserSchedHelper
{

	public static function addSubmenu ($vName)
	{
		Sidebar::addEntry(
			Text::_('COM_USERSCHED_SUBMENU_USERCALS'),
			'index.php?option=com_usersched',
			$vName == 'usersched'
		);
		Sidebar::addEntry(
			Text::_('COM_USERSCHED_SUBMENU_GRPCALS'),
			'index.php?option=com_usersched&view=calendars',
			$vName == 'calendars'
		);
//		Sidebar::addEntry(
//			Text::_('COM_USERSCHED_SUBMENU_CONFIGURATIONS'),
//			'index.php?option=com_usersched&view=configs',
//			$vName == 'configs'
//		);
		Sidebar::addEntry(
			Text::_('COM_USERSCHED_SUBMENU_SKINS'),
			'index.php?option=com_usersched&view=skins',
			$vName == 'skins'
		);
	}

	public static function getActions ()
	{
		$user = Factory::getUser();
		$result = new stdClass();
		$assetName = 'com_usersched';

		$actions = Access::getActionsFromFile(JPATH_ADMINISTRATOR . '/components/com_usersched/access.xml');

		foreach ($actions as $action) {
			$result->{$action->name} = $user->authorise($action->name, $assetName);
		}

		return $result;
	}

	public static function getGroupTitle ($gid)
	{
		// Get the title of the group.
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('title'));
		$query->from($db->quoteName('#__usergroups'));
		$query->where($db->quoteName('id') . ' = ' . (int) $gid);
		$db->setQuery($query);
		$title = $db->loadResult();
		return $title;
	}

	public static function getDbasePath ($uid, $isgrp)
	{
		$cmp = ApplicationHelper::getComponentName();

		$results = Factory::getApplication()->triggerEvent('onRjuserDatapath');
		$sdp = isset($results[0]) ? trim($results[0]) : '';
		if (!$sdp) $sdp = 'userstor';

		$pfx = $isgrp ? '_' : '@';

		return $sdp.'/'.$pfx.$uid.'/'.$cmp;
	}

}