<?php
defined('_JEXEC') or die;

abstract class JHtmlUsersched
{
	public static function colorPicker ($id, $torb, $val)
	{
		$html = '<input class="minicolors" type="text" name="category_'.$torb.'color[]" data-cid="'.$torb.'.'.$id.'"';
		$html .= ' data-control="wheel" data-position="bottom"';
		$html .= ' value="'.$val.'" />';
		return $html;
	}

	public static function makeLinks (&$txt)
	{
		$pat = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
		$txt = preg_replace($pat, '<a href="http$2://$4" target="_blank" title="$0">$0</a>', $txt);
	}

}