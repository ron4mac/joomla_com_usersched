<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class UserschedControllerSkins extends JControllerLegacy
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

		$model = $this->getModel('skins');
		$model->deleteSkins($dels);

		$this->setRedirect('index.php?option=com_usersched&view='.$view, Text::_('COM_USERSCHED_MSG_COMPLETE').print_r($this->input->post,true));
	}

	public function addSkin ()
	{
		$errmsg = '';
		$upfile = $_FILES['skinfile'];
		if ($upfile['error']) {
			$errmsg = Text::_('COM_USERSCHED_UPLDERR_'.$upfile['error']);
		} else {
			$rslt = $this->getModel('skins')->addSkin($upfile['tmp_name'], $this->input->get('skin_name'));
			if ($rslt) $errmsg = Text::_('COM_USERSCHED_UPLDERRZ_'.$rslt);
		}
		if ($errmsg) {
			$this->setRedirect('index.php?option=com_usersched&view=skins', $errmsg, 'error');
		} else {
			$this->setRedirect('index.php?option=com_usersched&view=skins', Text::_('COM_USERSCHED_UPLDOK'));
		}
	}

	public function makeDfltU ()
	{
		$this->setDefaultSkin('default_skin');
	}
	public function makeDfltG ()
	{
		$this->setDefaultSkin('group_default_skin');
	}
	public function makeDfltS ()
	{
		$this->setDefaultSkin('site_default_skin');
	}

	private function setDefaultSkin ($which)
	{
		$rows = $this->input->get('cid',array(),'array');
		$view = $this->input->get('view','skins');
		$this->setCompParam($which, $rows[0]);
		$this->setRedirect('index.php?option=com_usersched&view='.$view);
	}

	private function setCompParam ($param, $val)
	{
		// To access the extensions table we need the id of the component
		$compomentId = JComponentHelper::getComponent('com_usersched')->id;
		assert($compomentId != 0); // make sure that no error will cause the creation of a new entry in the extenions table
		
		// set the new value using set()
		$params = JComponentHelper::getParams('com_usersched');
		$params->set($param, $params->get($param)==$val?'':$val);
		
		// get an instance of the table class, load the component, overwrite the param-string with the new parameter values
		$table = JTable::getInstance('extension');
		$table->load($compomentId);
		$table->bind(array('params' => $params->toString()));
		
		// check and store with some simple error handling
		if (!$table->check()) {
			$this->setError("set:{$param} check: ".$table->getError());
			return false;
		}
		if (!$table->store()) {
			$this->setError("set:{$param} store: ".$table->getError());
			return false;
		}
		return true;
	}

}
