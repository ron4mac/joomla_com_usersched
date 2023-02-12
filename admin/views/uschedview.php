<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

JLoader::register('UserSchedHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usersched.php');

class UserschedView extends JViewLegacy
{
	public function display ($tpl=null)
	{
		UserschedHelper::addSubmenu($this->relm);
		$this->sidebar = ((int)JVERSION < 4) ? JHtmlSidebar::render() : '';
		parent::display($tpl);
	}

}
