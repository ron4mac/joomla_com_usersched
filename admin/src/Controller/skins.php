<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;

class UserschedControllerSkins extends BaseController
{

	public function __construct ($config = [])
	{
		parent::__construct($config);
		$this->input ??= Factory::getApplication()->input;		//J2.x
	}

	public function delete (): void
	{
		$dels = $this->input->get('cid',[],'array');
		$view = $this->input->get('view');

		$model = $this->getModel('skins');
		$model->deleteSkins($dels);

		$this->setRedirect('index.php?option=com_usersched&view='.$view, Text::_('COM_USERSCHED_MSG_COMPLETE').print_r($this->input->post,true));
	}

	public function addSkin (): void
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

	public function makeDfltU (): void
	{
		$this->setDefaultSkin('default_skin');
	}
	public function makeDfltG (): void
	{
		$this->setDefaultSkin('group_default_skin');
	}
	public function makeDfltS (): void
	{
		$this->setDefaultSkin('site_default_skin');
	}

	private function setDefaultSkin (string $which): void
	{
		$rows = $this->input->get('cid',[],'array');
		$view = $this->input->get('view','skins');
		$this->setCompParam($which, $rows[0]);
		$this->setRedirect('index.php?option=com_usersched&view='.$view);
	}

	private function setCompParam (string $param, $val): bool
	{
		// To access the extensions table we need the id of the component
		$compomentId = ComponentHelper::getComponent('com_usersched')->id;
		assert($compomentId != 0); // make sure that no error will cause the creation of a new entry in the extenions table

		// set the new value using set()
		$params = ComponentHelper::getParams('com_usersched');
		$params->set($param, $params->get($param)==$val?'':$val);

		$db = Factory::getContainer()->get('DatabaseDriver');
		// get an instance of the table class, load the component, overwrite the param-string with the new parameter values
		$table = new Extension($db);
		$table->load($compomentId);
		$table->bind(['params' => $params->toString()]);

		// check and store with some simple error handling
		if (!$table->check()) {
			throw new \Exception("set:{$param} check: ".$table->getError());
		}
		if (!$table->store()) {
			throw new \Exception("set:{$param} store: ".$table->getError());
		}
		return true;
	}

}
