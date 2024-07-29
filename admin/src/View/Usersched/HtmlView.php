<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
namespace RJCreations\Component\Usersched\Administrator\View\Usersched;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use RJCreations\Component\Usersched\Administrator\View\UschedView;

//require_once JPATH_COMPONENT_ADMINISTRATOR.'/src/View/UserschedView.php';

/**
 * View class for a list of user schedules.
 */
class HtmlView extends UschedView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $relm = 'usersched';

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
		$canDo = \UserSchedHelper::getActions();

		\JToolBarHelper::title(Text::_('COM_USERSCHED_MENU').' : '.Text::_('COM_USERSCHED_MANAGER_USCHEDS'), 'calendar usersched');

		\JToolBarHelper::deleteList(Text::_('COM_USERSCHED_MANAGER_DELETEOK'));
		//JToolBarHelper::trash('usersched.trash');

	//	if ($canDo->get('core.edit.state')) {
	//		JToolBarHelper::custom('scheds.reset', 'refresh.png', 'refresh_f2.png', 'JUSERSCHED_RESET', false);
	//	}

		\JToolBarHelper::divider();
		if ($canDo->get('core.admin')) {
			\JToolBarHelper::preferences('com_usersched');
		}
		\JToolBarHelper::divider();
		\JToolBarHelper::help('user_schedulers', true);
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
