<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
namespace RJCreations\Component\Usersched\Administrator\View\Calendars;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use RJCreations\Component\Usersched\Administrator\View\UschedView;


/**
 * View class for a list of group schedulers.
 */
class HtmlView extends UschedView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $relm = 'calendars';

	// Display the view
	public function display ($tpl = null): void
	{
		$m = $this->getModel();

		$this->items		= $m->getItems();
		$this->pagination	= $m->getPagination();
		$this->state		= $m->getState();	//var_dump($this->state);

	//	// Check for errors.
	//	if (count($errors = $m->getErrors()))) {
	//		JError::raiseError(500, implode("\n", $errors));
	//		return false;
	//	}

		$this->addToolbar();
		parent::display($tpl);
	}

	// Add the page title and toolbar.
	protected function addToolbar ()
	{
		$canDo	= \UserSchedHelper::getActions();

		ToolBarHelper::title(Text::_('COM_USERSCHED_MENU').' : '.Text::_('COM_USERSCHED_MANAGER_GSCHEDS'), 'calendar usersched');

		ToolBarHelper::deleteList(Text::_('COM_USERSCHED_MANAGER_DELETEOK'));
		//ToolBarHelper::trash('usersched.trash');

	//	if ($canDo->get('core.edit.state')) {
	//		ToolBarHelper::custom('scheds.reset', 'refresh.png', 'refresh_f2.png', 'JUSERSCHED_RESET', false);
	//	}

		ToolBarHelper::divider();
		if ($canDo->{'core.admin'}) {
			ToolBarHelper::preferences('com_usersched');
		}
		ToolBarHelper::divider();
		ToolBarHelper::help('group_schedulers', true);
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
