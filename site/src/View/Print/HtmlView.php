<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
defined('_JEXEC') or die;

use RJCreations\Component\Usersched\Site\View\UschedView;

class UserschedViewPrint extends UschedView
{
	public $post;
	public $skin;
	public $categories;
	protected $html;

	public function display ($tpl=null): void
	{
		$data = json_decode($this->post->getRaw('data','{}'));
		$this->html = $data->html;
		$this->skin = $data->skin;

		$m = $this->getModel();
		$this->categories = $m->getUdTable('categories'); $this->categories = $this->categories ?: [];	//if (!$this->categories) $this->categories = [];

		parent::display($tpl);
	}

	protected function categoriesCSS ()
	{
		$css = '';
		if ($this->categories)
		foreach ($this->categories as $cat) {
			$css .= '.dhx_cal_event div.evCat'.$cat['id']
			.',.dhx_cal_event_line.dhx_cal_event_line_start.dhx_cal_event_line_end.evCat'.$cat['id']
			.',.dhx_cal_event_clear.dhx_cal_event_line_start.dhx_cal_event_line_end.evCat'.$cat['id']
			.' {background-image:none;';
			if ($cat['bgcolor']) $css .= 'background-color:'.$cat['bgcolor'].';';
			if ($cat['txcolor']) $css .= 'color:'.$cat['txcolor'].';';
			$css .= "}\n";
		}
		return $css;
	}

}
