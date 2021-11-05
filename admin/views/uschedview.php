<?php
defined('_JEXEC') or die;

JLoader::register('UserSchedHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usersched.php');

class UserschedView extends JViewLegacy
{
	public function display ($tpl=null)
	{	echo 'HHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHH';
		UserschedHelper::addSubmenu($this->relm);
		JHtmlSidebar::render();
		parent::display($tpl);
	}

}
