<?php
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
