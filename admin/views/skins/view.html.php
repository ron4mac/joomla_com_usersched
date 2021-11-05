<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once JPATH_COMPONENT_ADMINISTRATOR.'/views/uschedview.php';

/**
 * View class for a list of calendar skins.
 */
class UserschedViewSkins extends UserschedView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $relm = 'skins';

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');	//var_dump($this->state);

		// Check for errors.
		//		if (count($errors = $this->get('Errors'))) {
		//			JError::raiseError(500, implode("\n", $errors));
		//			return false;
		//		}
		if (isset($this->state->task)) {
			$tpl = $this->state->task;
			parent::display($tpl);
			Factory::getApplication()->input->setVar('hidemainmenu', true);
		} else {
			$this->addToolbar();
			parent::display($tpl);
		}
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$canDo	= UserSchedHelper::getActions();

		JToolBarHelper::title(Text::_('COM_USERSCHED_MENU').' : '.Text::_('COM_USERSCHED_MANAGER_SKINS'), 'calendar usersched');

		JToolBarHelper::deleteList(Text::_('COM_USERSCHED_SKINS_DELETEOK'),"skins.delete");

		// Add a modal upload button.
		$icon = '<i class="icon-upload"></i>';
		$bar = JToolBar::getInstance('toolbar');
		$upbut = '<a class="modal btn btn-small" href="#upload_div" rel="{size: {x: 375, y: 225}}">'.$icon.' Upload</a>';
		$bar->appendButton('Custom', $upbut, 'skin-upload');

		//JToolBarHelper::trash('usersched.trash');

	//	if ($canDo->get('core.edit.state')) {
	//		JToolBarHelper::custom('scheds.reset', 'refresh.png', 'refresh_f2.png', 'JUSERSCHED_RESET', false);
	//	}

		JToolBarHelper::divider();
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_usersched');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('calendar_skins', true);
	}

	protected function state ($vari, $set=false, $val='', $glb=false)
	{
		$stvar = ($glb?'':'com_usersched.').$vari;
		$app = Factory::getApplication();
		if ($set) {
			$app->setUserState($stvar, $val);
			return;
		}
		return $app->getUserState($stvar, '');
	}

}
