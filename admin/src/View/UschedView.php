<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
namespace RJCreations\Component\Usersched\Administrator\View;

defined('_JEXEC') or die;

\JLoader::register('UserSchedHelper', JPATH_ADMINISTRATOR.'/components/com_usersched/helpers/usersched.php');

class UschedView extends \Joomla\CMS\MVC\View\HtmlView
{
	public function display ($tpl=null)
	{
		\UserschedHelper::addSubmenu($this->relm);
		$this->sidebar = ((int)JVERSION < 4) ? JHtmlSidebar::render() : '';
		parent::display($tpl);
	}

}
