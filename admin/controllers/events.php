<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

JLoader::register('UserSchedHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usersched.php');

/**
 * Events list controller class.
 */
class UserschedControllerEvents extends JControllerLegacy
{

	public function __construct($config = array())
	{
		parent::__construct($config);
		if (!isset($this->input)) $this->input = Factory::getApplication()->input;		//J2.x
	}

	public function delete ()
	{
		$dels = $this->input->get('cid',array(),'array');
		$view = $this->input->get('view');
		$uid = $this->input->getInt('uid',-1);
		$isGrp = $this->input->getBool('isGrp',false);

		$model = $this->getModel('events', '', array('uid'=>$uid,'isgrp'=>$isGrp));
		$model->deleteEvents($dels, $uid, $isGrp);

		$this->setRedirect('index.php?option=com_usersched&view='.$view.'&uid='.$uid.($isGrp?('&isGrp='.$isGrp):''), Text::_('COM_USERSCHED_MSG_COMPLETE'));
	}

}
