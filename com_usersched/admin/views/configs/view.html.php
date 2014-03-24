<?php
defined('_JEXEC') or die;

/**
 * View class for a list of user schedules.
 */
class UserSchedViewConfigs extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		jimport('joomla.html.pagination');
		$this->items = UserSchedHelper::getUserScheds();
		$this->pagination = new JPagination(count($this->items), 0, 20);
		$this->state		= $this->get('State');

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
		$app = JFactory::getApplication();
		if ($set) {
			$app->setUserState($option.'_'.$vari, $val);
			return;
		}
		return $app->getUserState(($glb ? '' : "{$option}_").$vari, '0');
	}

}
