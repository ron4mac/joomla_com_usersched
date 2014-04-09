<?php
defined('JPATH_PLATFORM') or die;

abstract class JHtmlMyGrid
{

	public static function checkall ()
	{
		if (USERSCHED_J30) {
			$html = JHtml::_('grid.checkall');
		} else {
			$html = '<input type="checkbox" name="checkall-toggle" value="" title="'.JText::_('JGLOBAL_CHECK_ALL').'" onclick="Joomla.checkAll(this)" />';
		}
		return $html;
	}

}