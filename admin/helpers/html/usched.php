<?php
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;

abstract class JHtmlUsched
{

	public static function checkall ()
	{
		if (USERSCHED_J30) {
			$html = JHtml::_('grid.checkall');
		} else {
			$html = '<input type="checkbox" name="checkall-toggle" value="" title="'.Text::_('JGLOBAL_CHECK_ALL').'" onclick="Joomla.checkAll(this)" />';
		}
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