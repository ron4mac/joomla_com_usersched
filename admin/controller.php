<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

JLoader::register('UschedHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usched.php');
JLoader::register('UserSchedHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usersched.php');
//JLoader::registerPrefix('RJUser', JPATH_LIBRARIES . '/rjuser');

class UserschedController extends JControllerLegacy
{

	public function remove ()
	{
		jimport('joomla.filesystem.folder');
		$dels = $this->input->get('cid',array(),'array');
		$view = $this->input->get('view');
		foreach ($dels as $del) {
			$dbp = JPATH_SITE.'/'.UserSchedHelper::getDbasePath($del, $view == 'calendars');
			JFolder::delete($dbp);
		}
		$this->setRedirect('index.php?option=com_usersched&view='.$view, Text::_('COM_USERSCHED_MSG_COMPLETE'));
	}

	/****** OTHER OVERRIDES ******/

	public function getModel($name = '', $prefix = '', $config = array())
	{
		if ($name == 'events'){
			$config['uid'] = $this->input->getInt('uid', 0);
			$config['isgrp'] = $this->input->getBool('isgrp', false);
		}

		return parent::getModel($name, $prefix, $config);
	}

}
