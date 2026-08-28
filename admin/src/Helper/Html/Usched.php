<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
namespace RJCreations\Component\Usersched\Administrator\Helper\Html;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

class Usched
{

	public static function checkall ()
	{
		$html = '<input type="checkbox" name="checkall-toggle" value="" title="'.Text::_('JGLOBAL_CHECK_ALL').'" onclick="Joomla.checkAll(this)" />';
		return $html;
	}

	public static function sideBar ($sidebar)
	{
		if ((int)JVERSION > 3) return '';
		$html = '<div id="j-sidebar-container" class="span2">';
		$html .= $sidebar;
		$html .= '</div>';
		return $html;
	}

}