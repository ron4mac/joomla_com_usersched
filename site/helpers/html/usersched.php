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

}