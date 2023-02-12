<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

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

		ToolBarHelper::title(Text::_('COM_USERSCHED_MENU').' : '.Text::_('COM_USERSCHED_MANAGER_SKINS'), 'calendar usersched');

		ToolBarHelper::deleteList(Text::_('COM_USERSCHED_SKINS_DELETEOK'),'skins.delete');
	//	ToolBarHelper::custom('skins.delete','delete','delete', 'COM_USERSCHED_SKINS_DELETEOK',true,'modal-box');
		$bar = JToolBar::getInstance('toolbar');
	//	$bar->appendButton('Confirm', Text::_('COM_USERSCHED_SKINS_DELETEOK'), 'delete', 'delete', 'skins.delete', true, 'modal-box');

		ToolBarHelper::spacer();

		// Add a modal upload button.
		$icon = '<span class="icon-upload"> </span>';
		$upbut = '<a class="modal btn btn-small" href="#upload_div" rel="{size: {x: 375, y: 225}}">'.$icon.' Upload</a>';
		$upbut = '<button
	class="button-upload btn btn-primary" 
	data-bs-toggle="modal" 
	data-bs-target="#modal-box" 
	data-bs-title="Fixing the ice shelves" 
	data-bs-id="14794" 
	data-bs-action="showCampDescription" 
	onclick="return false;">
	'.$icon.Text::_('COM_USERSCHED_UPLOAD_SKIN').'
</button>';
		$bar->appendButton('Custom', $upbut, 'skin-upload');

		//ToolBarHelper::trash('usersched.trash');

	//	if ($canDo->get('core.edit.state')) {
	//		ToolBarHelper::custom('scheds.reset', 'refresh.png', 'refresh_f2.png', 'JUSERSCHED_RESET', false);
	//	}

		ToolBarHelper::divider();
		if ($canDo->get('core.admin')) {
			ToolBarHelper::preferences('com_usersched');
		}
		ToolBarHelper::divider();
		ToolBarHelper::help('calendar_skins', true);
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
