<?php
defined('_JEXEC') or die;

class UserSchedController extends JControllerLegacy
{

	public function display ($cachable=false, $urlparams=false)
	{
		require_once JPATH_COMPONENT.'/helpers/usersched.php';

		// Load the submenu.
		$jinput = JFactory::getApplication()->input;
		UserSchedHelper::addSubmenu($jinput->get('view', 'usersched'));

		parent::display();
	}

	public function remove ()
	{
		$jinput = JFactory::getApplication()->input;
		$dels = $jinput->get('cid',array(),'array');
		$view = $jinput->get('view');
		switch ($view) {
			case 'usersched':
				$sc = '@';
				break;
			case 'calendars':
				$sc = '_';
				break;
			default:
				$sc = '-';
		}
		foreach ($dels as $del) {
			JFolder::delete(JPATH_SITE . '/userstor/'.$sc.$del.'/com_usersched');
		}
		$this->setRedirect('index.php?option=com_usersched&view='.$view);
	}

}
