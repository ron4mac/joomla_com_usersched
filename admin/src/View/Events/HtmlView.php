<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
namespace RJCreations\Component\Usersched\Administrator\View;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once JPATH_COMPONENT_ADMINISTRATOR.'/src/View/UserschedView.php';

/**
 * View class for a list of user schedules.
 */
class EventsView extends UserschedView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $relm = 'events';

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
		Factory::getApplication()->input->set('hidemainmenu', true);

		$canDo	= UserSchedHelper::getActions();

		$uid = $this->state->get('usched_uid');

		if ($this->state->get('usched_isgrp')) {
			$name = UserSchedHelper::getGroupTitle($uid);
		} else {
			$user = JUser::getInstance($uid);
			$name = $user->name;
		}

		JToolBarHelper::title(Text::_('COM_USERSCHED_MENU').' : '.Text::_('COM_USERSCHED_MANAGER_EVENTS').' : '.$name, 'calendar usersched');

		JToolBarHelper::deleteList(Text::_('COM_USERSCHED_EVENTS_DELETEOK'), 'events.delete');
		//JToolBarHelper::trash('usersched.trash');

	//	if ($canDo->get('core.edit.state')) {
	//		JToolBarHelper::custom('scheds.reset', 'refresh.png', 'refresh_f2.png', 'JUSERSCHED_RESET', false);
	//	}

		JToolBarHelper::divider();
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_usersched');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('usersched_events', true);
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
