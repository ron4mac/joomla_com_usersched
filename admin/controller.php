<?php
// License: GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
defined('_JEXEC') or die;

class UserschedController extends JControllerLegacy
{

	public function __construct($config = array())
	{
		parent::__construct($config);
		if (!isset($this->input)) $this->input = JFactory::getApplication()->input;		//J2.x
	}

	public function display ($cachable=false, $urlparams=false)
	{
		require_once JPATH_COMPONENT.'/helpers/usersched.php';

		// Load the submenu.
		UserschedHelper::addSubmenu($this->input->get('view', 'usersched'));

		parent::display();
	}

	public function remove ()
	{
		jimport('rjuserdata.userdata');
		$dels = $this->input->get('cid',array(),'array');
		$view = $this->input->get('view');
		foreach ($dels as $del) {
			$udb = new RJUserData('sched', false, $del, $view == 'calendars');
			$udb->destroyDatabase();
		}
		$this->setRedirect('index.php?option=com_usersched&view='.$view, JText::_('COM_USERSCHED_MSG_COMPLETE'));
	}

}
