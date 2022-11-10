<?php
defined('_JEXEC') or die;

abstract class JHtmlUsersched
{
	public static function colorPicker ($id, $torb, $val)
	{
		$html = '<input type="color" name="category_'.$torb.'color[]"';
		$html .= ' oninput="show_'.$torb.'(this)" onchange="show_'.$torb.'(this)" value="'.$val.'" />';
		return $html;
	}

	public static function makeLinks (&$txt)
	{
		$pat = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
		$txt = preg_replace($pat, '<a href="http$2://$4" target="_blank" title="$0">$0</a>', $txt);
	}

}