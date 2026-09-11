<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
namespace RJCreations\Component\Usersched\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Filesystem\Folder;
use RJCreations\Library\RJUserCom;

//\JLoader::register('UschedHelper', JPATH_ADMINISTRATOR.'/components/com_usersched/helpers/usched.php');
\JLoader::register('UserSchedHelper', JPATH_ADMINISTRATOR.'/components/com_usersched/helpers/usersched.php');
//JLoader::registerPrefix('RJUser', JPATH_LIBRARIES . '/rjuser');

class DisplayController extends BaseController
{
	protected $default_view = 'usersched';

	public function remove (): void
	{
		$dels = $this->input->get('cid',[],'array');
		$view = $this->input->get('view');
		foreach ($dels as $del) {
			[$guid,$mnu] = explode('|',$del);
			RJUserCom::deleteStorageInstance($guid,$mnu);
		}
		$this->setRedirect('index.php?option=com_usersched&view='.$view, Text::_('COM_USERSCHED_MSG_COMPLETE'));
	}

	/****** OTHER OVERRIDES ******/

	public function getModel ($name = '', $prefix = '', $config = [])
	{
		if ($name == 'events'){
			$config['uid'] = $this->input->getInt('uid', 0);
			$config['isgrp'] = $this->input->getBool('isgrp', false);
		}

		return parent::getModel($name, $prefix, $config);
	}

}
