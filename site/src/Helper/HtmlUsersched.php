<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
namespace RJCreations\Component\Usersched\Site\Helper;

defined('_JEXEC') or die;

abstract class HtmlUsersched
{
	public static function colorPicker ($id, $torb, $val)
	{
		$html = '<input type="color" name="category_'.$torb.'color[]"';
		$html .= ' oninput="USched.show_'.$torb.'(this)" onchange="USched.show_'.$torb.'(this)" value="'.$val.'" />';
		return $html;
	}

	public static function makeLinks (&$txt)
	{
		$pat = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
		$txt = preg_replace($pat, '<a href="http$2://$4" target="_blank" title="$0">$0</a>', $txt);
	}

}