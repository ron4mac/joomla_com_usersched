<?php
defined('_JEXEC') or die;

class UserschedController extends JControllerLegacy
{

	public function display ($cachable=false, $urlparams=false)
	{
		require_once JPATH_COMPONENT.'/helpers/usersched.php';

		// Load the submenu.
		$jinput = JFactory::getApplication()->input;
		UserschedHelper::addSubmenu($jinput->get('view', 'usersched'));

		parent::display();
	}

	public function remove ()
	{
		jimport('rjuserdata.userdata');
		$jinput = JFactory::getApplication()->input;
		$dels = $jinput->get('cid',array(),'array');
		$view = $jinput->get('view');
		foreach ($dels as $del) {
			$udb = new RJUserData('sched', false, $del, $view == 'calendars');
			$udb->destroyDatabase();
		}
		$this->setRedirect('index.php?option=com_usersched&view='.$view, JText::_('COM_USERSCHED_MSG_COMPLETE'));
	}

}
