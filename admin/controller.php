<?php
// License: GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

JLoader::register('UschedHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usched.php');
JLoader::register('UserSchedHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usersched.php');

class UserschedController extends JControllerLegacy
{

	public function __construct($config = array())
	{
		parent::__construct($config);
		if (!isset($this->input)) $this->input = Factory::getApplication()->input;		//J2.x
	}

	public function display ($cachable=false, $urlparams=false)
	{
	//	require_once JPATH_COMPONENT.'/helpers/usersched.php';

		// Load the submenu.
		UserschedHelper::addSubmenu($this->input->get('view', 'usersched'));

		parent::display();
	}

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
