<?php
defined('_JEXEC') or die;

/**
 * View class for a list of user schedules.
 */
class UserSchedViewConfigs extends JViewLegacy
{
	protected $enabled;
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{	//var_dump('vht',$this);jexit();
		jimport('joomla.html.pagination');
//		$this->items		= $this->get('Items');
		$this->items = UserSchedHelper::getUserScheds();
//		$this->pagination	= $this->get('Pagination');
		$this->pagination = new JPagination(count($this->items), 0, 20);
		$this->state		= $this->get('State');
//		$this->enabled		= $this->state->params->get('enabled');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$canDo	= UserSchedHelper::getActions();

		JToolBarHelper::title(JText::_('COM_USERSCHED_MANAGER_SCHEDS'), 'usersched.png');

		JToolBarHelper::deleteList(JText::_('COM_USERSCHED_MANAGER_DELETEOK'));
		//JToolBarHelper::trash('usersched.trash');

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('scheds.reset', 'refresh.png', 'refresh_f2.png', 'JUSERSCHED_RESET', false);
		}
		//JToolBarHelper::divider();
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_usersched');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_USERSCHED');
	}

	protected function state($vari, $set=false, $val='0', $glb=false)
	{
		$mainframe =& JFactory::getApplication();
		if ($set) {
			$mainframe->setUserState($option.'_'.$vari, $val);
			return;
		}
		return $mainframe->getUserState(($glb ? '' : "{$option}_").$vari, '0');
	}

}
